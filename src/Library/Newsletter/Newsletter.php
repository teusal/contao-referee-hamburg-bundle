<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

namespace Teusal\ContaoRefereeHamburgBundle\Library\Newsletter;

use Contao\BackendUser;
use Contao\CoreBundle\Mailer\TransportConfig;
use Contao\Database\Result;
use Contao\DataContainer;
use Contao\Email;
use Contao\FilesModel;
use Contao\FrontendUser;
use Contao\Message;
use Contao\NewsletterChannelModel;
use Contao\NewsletterModel;
use Contao\NewsletterRecipientsModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Validator;
use Teusal\ContaoRefereeHamburgBundle\Library\SRHistory;
use Teusal\ContaoRefereeHamburgBundle\Model\BsaSchiedsrichterModel;

/**
 * Class Newsletter.
 *
 * @property TransportConfig|null $transport
 */
class Newsletter extends \Contao\Newsletter
{
    /**
     * @var TransportConfig|null
     */
    private $transport;

    /**
     * @var array
     */
    private $arrAttachments;

    /**
     * @var array
     */
    private $arrAlreadySent = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        if (TL_MODE === 'BE') {
            $this->import(BackendUser::class, 'User');

            $availableTransports = System::getContainer()->get('contao.mailer.available_transports');
            $this->transport = $availableTransports->getTransport($this->User->email);

            if (null === $this->transport) {
                Message::addError('Es wurde keine Konfiguration zum Mailversand für Sie anhand Ihrer E-Mail-Adresse gefunden. Sie können so keine E-Mails versenden. Bitte wenden Sie sich an einen Administrator.');
                $this->redirect($this->getReferer(true));
            }
        }
    }

    /**
     * Sending the email.
     *
     * @param DataContainer $dc the data container of the newsletter to send
     *
     * @return string
     */
    public function send(DataContainer $dc)
    {
        $objNewsletter = NewsletterModel::findById($dc->id);

        if (!isset($objNewsletter)) {
            throw new \Exception('missing newsletter object while sending.');
        }

        if ($this->transport->getName() !== $objNewsletter->mailerTransport) {
            Message::addError('Sie sind nicht berechtigt, den Mailer-Transport "'.$objNewsletter->mailerTransport.'" zu verwenden. Bitte ändern Sie gegebenenfalls den Mailer-Transport in der E-Mail, um diese selbst zu versenden.');
            $this->redirect($this->getReferer(true));
        }

        if ('dev' === System::getContainer()->getParameter('kernel.environment')) {
            Message::addInfo('Es handelt sich um eine Testumgebung. Alle Newsletter-Mails werden an mail@alexteuscher.de umgeleitet.');
        }

        $returnValue = parent::send($dc);

        // Wenn wir hier her kommen, es also kein 'exit' oder 'reload()' in Newsletter gab, dann setzen wir
        // das Flag für den Info-Versand zurück, damit beim nächsten Senden eine Info versendet wird.
        $this->Database->prepare('UPDATE tl_newsletter SET infomailSent = ? WHERE id = ?')
            ->execute('', $dc->id)
        ;

        return $returnValue;
    }

    /**
     * sending the email.
     *
     * @param int $newsletterId
     */
    public function frontendSend($newsletterId)
    {
        $objNewsletter = $this->Database->prepare("SELECT n.*, c.useSMTP, c.smtpHost, c.smtpPort, c.smtpUser, c.smtpPass FROM tl_newsletter n LEFT JOIN tl_newsletter_channel c ON n.pid=c.id WHERE n.id=? AND n.sent=''")
            ->limit(1)
            ->execute($newsletterId)
        ;

        // Return if there is no newsletter
        if ($objNewsletter->numRows < 1) {
            return 0;
        }

        // Overwrite the SMTP configuration
        if ($objNewsletter->__get('useSMTP')) {
            $GLOBALS['TL_CONFIG']['useSMTP'] = true;

            $GLOBALS['TL_CONFIG']['smtpHost'] = $objNewsletter->__get('smtpHost');
            $GLOBALS['TL_CONFIG']['smtpUser'] = $objNewsletter->__get('smtpUser');
            $GLOBALS['TL_CONFIG']['smtpPass'] = $objNewsletter->__get('smtpPass');
            $GLOBALS['TL_CONFIG']['smtpEnc'] = $objNewsletter->__get('smtpEnc');
            $GLOBALS['TL_CONFIG']['smtpPort'] = $objNewsletter->__get('smtpPort');
        }

        // Add default sender address
        if ('' === $objNewsletter->__get('sender')) {
            $arrEmail = StringUtil::splitFriendlyEmail($GLOBALS['TL_CONFIG']['adminEmail']);
            $objNewsletter->__set('senderName', $arrEmail[0]);
            $objNewsletter->__set('$objNewsletter->sender', $arrEmail[1]);
        }

        $arrAttachments = [];
        // $blnAttachmentsFormatError = false;

        // Add attachments
        if ($objNewsletter->__get('addFile')) {
            $files = deserialize($objNewsletter->__get('files'));

            if (!empty($files) && \is_array($files)) {
                $objFiles = FilesModel::findMultipleByUuids($files);

                if (null === $objFiles) {
                    if (!Validator::isUuid($files[0])) {
                        // $blnAttachmentsFormatError = true;
                        \Message::addError($GLOBALS['TL_LANG']['ERR']['version2format']);
                    }
                } else {
                    while ($objFiles->next()) {
                        if (is_file(TL_ROOT.'/'.$objFiles->path)) {
                            $arrAttachments[] = $objFiles->path;
                        }
                    }
                }
            }
        }

        // Replace insert tags
        $html = $this->replaceInsertTags($objNewsletter->__get('content'));
        $text = $this->replaceInsertTags($objNewsletter->__get('text'));

        // Convert relative URLs
        if ($objNewsletter->__get('externalImages')) {
            $html = $this->convertRelativeUrls($html);
        }

        // Send newsletter
        // $referer = preg_replace('/&(amp;)?(start|mpc|token|recipient|preview)=[^&]*/', '', Environment::get('request'));

        // Get recipients
        $objRecipients = $this->Database->prepare('SELECT *, r.email FROM tl_newsletter_recipients r LEFT JOIN tl_member m ON(r.email=m.email) WHERE r.pid=? AND r.active=1 GROUP BY r.email ORDER BY r.email')
            ->execute($objNewsletter->__get('pid'))
        ;

        $intTotal = $objRecipients->numRows;

        // Send newsletter
        if ($objRecipients->numRows > 0) {
            // Update status
            $this->Database->prepare('UPDATE tl_newsletter SET sent=1, date=? WHERE id=?')
                ->execute(time(), $objNewsletter->__get('id'))
            ;

            $_SESSION['REJECTED_RECIPIENTS'] = [];

            $counted = 0;

            while ($objRecipients->next()) {
                if ($counted > 9) {
                    //sleep(1);
                    $counted = 0;
                }

                $objEmail = $this->generateEmailObject($objNewsletter, $arrAttachments);
                $this->sendNewsletter($objEmail, $objNewsletter, $objRecipients->row(), $text, $html);
                ++$counted;
            }
        }

        // Deactivate rejected addresses
        if (!empty($_SESSION['REJECTED_RECIPIENTS'])) {
            $intRejected = \count($_SESSION['REJECTED_RECIPIENTS']);
            $intTotal -= $intRejected;

            foreach ($_SESSION['REJECTED_RECIPIENTS'] as $strRecipient) {
                $this->Database->prepare("UPDATE tl_newsletter_recipients SET active='' WHERE email=?")
                    ->execute($strRecipient)
                ;

                $this->log('Recipient address "'.$strRecipient.'" was rejected and has been deactivated', __METHOD__, TL_ERROR);
            }
        }

        Message::addConfirmation(sprintf($GLOBALS['TL_LANG']['tl_newsletter']['confirm'], $intTotal));

        return $intTotal;
    }

    /**
     * Generate the e-mail object and return it.
     *
     * @param array $arrAttachments
     *
     * @return Email
     */
    protected function generateEmailObject(Result $objNewsletter, $arrAttachments)
    {
        $this->arrAttachments = $arrAttachments;

        return parent::generateEmailObject($objNewsletter, $arrAttachments);
    }

    /**
     * Compile the newsletter and send it.
     *
     * @param Email  $objEmail      the email object
     * @param Result $objNewsletter the newsletter database result
     * @param array  $arrRecipient
     * @param string $text
     * @param string $html
     * @param string $css
     *
     * @return bool
     */
    protected function sendNewsletter(Email $objEmail, Result $objNewsletter, $arrRecipient, $text, $html, $css = null)
    {
        if (!isset($objNewsletter)) {
            throw new \Exception('missing newsletter object while sending.');
        }

        $objNewsletterChannel = NewsletterChannelModel::findById($objNewsletter->__get('pid'));

        if (!isset($objNewsletterChannel)) {
            throw new \Exception('missing newsletter channel object while sending.');
        }

        // there is a left join to member. since we did not re-created the unique at email, we need to skip records
        // TODO https://github.com/teusal/contao-referee-hamburg-bundle/issues/6
        if (\in_array($arrRecipient['recipient'], $this->arrAlreadySent, true)) {
            return true;
        }

        // reload the recipient because of the member left join. there could be wrong names in firstname/lastname
        $arrRecipient = NewsletterRecipientsModel::findById($arrRecipient['recipient'])->row();

        // send infomails if the option is activated
        if (!$_GET['preview'] && $objNewsletterChannel->__get('sendInfomail') && !$objNewsletter->__get('infomailSent')) {
            $this->sendInfomails($objNewsletterChannel, $objNewsletter, $html, $css);
        }

        // replace email in dev environment
        if ('dev' === System::getContainer()->getParameter('kernel.environment')) {
            $arrRecipient['email'] = 'mail@alexteuscher.de';
        }

        // extend recipient array, translate keys firstname, lastname and salutationPersonal
        $arrRecipient['vorname'] = $arrRecipient['firstname'];
        $arrRecipient['nachname'] = $arrRecipient['lastname'];
        $arrRecipient['anrede_persoenlich'] = $arrRecipient['salutationPersonal'];

        // Add optional reply to
        if (\strlen($objNewsletter->__get('replyToAddress'))) {
            $objEmail->replyTo($objNewsletter->__get('replyToAddress'));
        }

        // add optional cc
        $arrCc = $this->getCc($objNewsletter, $arrRecipient);

        if (!empty($arrCc) && \is_array($arrCc)) {
            $objEmail->sendCc($arrCc);
        }

        // prepend channel informations if desired
        if ($objNewsletterChannel->__get('prependChannelInformation')) {
            if (!empty($text)) {
                $text = '**Verteiler: '.$objNewsletterChannel->__get('channelInformationText').'**\r\n------------------------------------------\r\n\r\n'.$text;
            }

            if (!empty($html)) {
                $html = '<p><strong>Verteiler: '.$objNewsletterChannel->__get('channelInformationText').'</strong><br />------------------------------------------</p>'.$html;
            }
        }

        // sending the email
        $return = parent::sendNewsletter($objEmail, $objNewsletter, $arrRecipient, $text, $html, $css);

        $this->arrAlreadySent[] = $arrRecipient['id'];

        // writing the referee history if the option is activated
        if ($objNewsletterChannel->__get('writeRefereeHistory') && $arrRecipient['refereeId'] && (!\is_array($_SESSION['REJECTED_RECIPIENTS']) || !\in_array($arrRecipient['email'], $_SESSION['REJECTED_RECIPIENTS'], true))) {
            SRHistory::insert($arrRecipient['refereeId'], $objNewsletter->__get('pid'), ['E-Mail', 'INFO'], 'Der Schiedsrichters %s wurde via E-Mail-Verteiler "%s" angeschrieben. Betreff: '.$objEmail->__get('subject'), __METHOD__);
        }

        return $return;
    }

    /**
     * returns an array with a list of cc email addresses of chairman as well as vice chairman.
     *
     * @param Result $objNewsletter the newsletter database result
     * @param array  $arrRecipient  the recipient of the email
     *
     * @return array|null list of cc email addresses of chairman as well as vice chairman or NULL
     */
    private function getCc(Result $objNewsletter, $arrRecipient)
    {
        if ('dev' === System::getContainer()->getParameter('kernel.environment')) {
            return null;
        }

        if (!$objNewsletter->__get('ccChairman')) {
            return null;
        }

        if (!$arrRecipient['refereeId']) {
            return null;
        }

        $objSR = BsaSchiedsrichterModel::findByPk($arrRecipient['refereeId']);

        if (!isset($objSR)) {
            return null;
        }

        return $this->Database->prepare('SELECT tl_bsa_schiedsrichter.email FROM tl_bsa_schiedsrichter JOIN tl_bsa_verein_obmann ON (tl_bsa_schiedsrichter.id = tl_bsa_verein_obmann.obmann OR tl_bsa_schiedsrichter.id = tl_bsa_verein_obmann.stellv_obmann_1 OR tl_bsa_schiedsrichter.id = tl_bsa_verein_obmann.stellv_obmann_2) WHERE tl_bsa_verein_obmann.verein = ? AND tl_bsa_schiedsrichter.email <> ? AND tl_bsa_schiedsrichter.email <> ?')
            ->execute($objSR->__get('verein'), $objSR->__get('email'), '')
            ->fetchEach('email')
        ;
    }

    /**
     * sending the infomail to configured recipients.
     *
     * @param NewsletterChannelModel $objNewsletterChannel the channel object
     * @param Result                 $objNewsletter        the newsletter database result
     * @param array                  $arrRecipient
     * @param string                 $text
     * @param string                 $html
     * @param string|null            $css
     */
    private function sendInfomails(NewsletterChannelModel $objNewsletterChannel, $objNewsletter, $html, $css): void
    {
        if (!$objNewsletterChannel->__get('prependChannelInformation')) {
            return;
        }

        if (TL_MODE === 'BE') {
            $this->Import(BackendUser::class, 'User');
            $strSentBy = ' vom Backend-User <b>'.$this->User->name.'</b>';
            $strSentByTxt = ' vom Backend-User "'.$this->User->name.'"';
        } elseif (TL_MODE === 'FE') {
            $this->import(FrontendUser::class, 'User');
            $strSentBy = ' vom Frontend-User <b>'.$this->User->firstname.' '.$this->User->lastname.'</b>';
            $strSentByTxt = ' vom Frontend-User "'.$this->User->firstname.' '.$this->User->lastname.'"';
        } else {
            throw new \Exception('Unknown source of usage. TL_MODE: '.TL_MODE);
        }

        $title = $objNewsletterChannel->__get('title');

        $htmlInfo = sprintf('<p>Folgender Newsletter wurde soeben%s an den Verteiler <b>%s</b> versendet:</p><hr />%s', $strSentBy, $title, $html);
        $textInfo = sprintf('** Folgender Newsletter wurde soeben%s an den Verteiler "%s" versendet:**\r\n------------------------------------------\r\n\r\n%s', $strSentByTxt, $title, $html);
        $arrInfomailRecipients = explode(',', $objNewsletterChannel->__get('infomailRecipients'));

        foreach ($arrInfomailRecipients as $infomailRecipient) {
            $objInfomail = parent::generateEmailObject($objNewsletter, $this->arrAttachments);

            $arrInfoRecipient = [
                'email' => $infomailRecipient,
                'firstname' => 'VORNAME',
                'vorname' => 'VORNAME',
                'lastname' => 'NACHNAME',
                'nachname' => 'NACHNAME',
                'salutationPersonal' => 'LIEBE/LIEBER',
                'anrede_persoenlich' => 'LIEBE/LIEBER',
            ];

            if ('dev' === System::getContainer()->getParameter('kernel.environment')) {
                $arrInfoRecipient['email'] = 'mail@alexteuscher.de';
            }

            parent::sendNewsletter($objInfomail, $objNewsletter, $arrInfoRecipient, $textInfo, $htmlInfo, $css);

            if (TL_MODE === 'BE') {
                echo 'Sending newsletter-Info to <strong>'.$infomailRecipient.'</strong><br>';
            }
        }

        $this->Database->prepare('UPDATE tl_newsletter SET infomailSent=? WHERE id=?')->execute('1', $objNewsletter->__get('id'));
        $objNewsletter->__set('infomailSent', '1');

        if (TL_MODE === 'BE') {
            Message::addConfirmation('Info-Mail wurde an '.$objNewsletter->__get('info_to').' gesendet.');
        }
    }
}
