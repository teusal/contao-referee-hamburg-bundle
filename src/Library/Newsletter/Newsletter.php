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
use Contao\Database\Result;
use Contao\DataContainer;
use Contao\Email;
use Contao\FrontendUser;
use Contao\Message;
use Contao\NewsletterChannelModel;
use Contao\NewsletterModel;
use Contao\StringUtil;
use Contao\System;
use Teusal\ContaoRefereeHamburgBundle\Library\Email\AbstractEmail;
use Teusal\ContaoRefereeHamburgBundle\Library\Mailer\AvailableTransports;
use Teusal\ContaoRefereeHamburgBundle\Library\SRHistory;
use Teusal\ContaoRefereeHamburgBundle\Model\RefereeModel;

/**
 * Class Newsletter.
 */
class Newsletter extends \Contao\Newsletter
{
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

        /** @var AvailableTransports $availableTransports */
        $availableTransports = System::getContainer()->get('contao.mailer.available_transports');

        if (empty($objNewsletter->mailerTransport)) {
            Message::addError('Es wurde kein Mailer-Transport in der E-Mail angegeben. Bitte überprüfen Sie die Einstellungen.');
            $this->redirect($this->getReferer(true));
        }

        if (!$availableTransports->existsTransport($objNewsletter->mailerTransport)) {
            Message::addError('Der Mailer-Transport "'.$objNewsletter->mailerTransport.'" ist im System nicht definiert. Bitte wenden Sie sich an einen Administrator.');
            $this->redirect($this->getReferer(true));
        }

        if ($availableTransports->isUserTransport($objNewsletter->mailerTransport) && (TL_MODE !== 'BE' || null === $this->User || $this->User->email !== $objNewsletter->mailerTransport)) {
            Message::addError('Sie sind nicht berechtigt, den Mailer-Transport '.$this->User->email.' "'.$objNewsletter->mailerTransport.'" zu verwenden.<br />Bitte ändern Sie gegebenenfalls den Mailer-Transport in der E-Mail, um diese selbst zu versenden.');
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

        $returnValue = str_replace('<tr class="row_2">', '<tr class="row_2" style="display: none;">', $returnValue);
        $returnValue = str_replace('<tr class="row_1">', '<tr class="row_2">', $returnValue);
        $returnValue = str_replace('<div class="preview_text">', '<div class="preview_text" style="display: none;">', $returnValue);
        $returnValue = str_replace('<div class="preview_html">', '<div class="preview_html preview_text">', $returnValue);

        if (null !== $availableTransports->getTransport($objNewsletter->mailerTransport)->getFrom()) {
            $indexOpen = strpos($returnValue, '<tr class="row_0">');
            $indexClose = strpos($returnValue, '</tr>', $indexOpen);
            $replace = '<tr class="row_0">
    <td class="col_0">'.$GLOBALS['TL_LANG']['tl_newsletter']['from'].'</td>
    <td class="col_1">'.StringUtil::specialchars($availableTransports->getTransport($objNewsletter->mailerTransport)->getFrom()).'</td>
  </tr>';

            $returnValue = substr_replace($returnValue, $replace, $indexOpen, $indexClose - $indexOpen + 5);
        }

        return $returnValue;
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

        // send infomails if the option is activated
        if ($objNewsletterChannel->__get('sendInfomail') && !$objNewsletter->__get('infomailSent') && !isset($_GET['preview'])) {
            $this->sendInfomails($objNewsletterChannel, $objNewsletter, $html, $css);
        }

        // setting testdata for any testmail
        if (TL_MODE === 'BE' && isset($_GET['preview'])) {
            $email = $arrRecipient['email'];
            $arrRecipient = AbstractEmail::getRefereeForTestmail();
            $arrRecipient['email'] = $email;
        }

        // remove friendly part from the email address
        $arrRecipient['email'] = StringUtil::splitFriendlyEmail($arrRecipient['email'])[0];

        // replace email in dev environment
        if ('dev' === System::getContainer()->getParameter('kernel.environment')) {
            $arrRecipient['email'] = 'mail@alexteuscher.de';
        }

        // Determine personal salutation based on gender
        switch ($arrRecipient['gender']) {
            case 'male':
                $arrRecipient['$salutationPersonnel'] = 'Lieber';
                break;

            case 'female':
                $arrRecipient['$salutationPersonnel'] = 'Liebe';
                break;

            default:
                $arrRecipient['$salutationPersonnel'] = 'Liebe/Lieber';
                break;
        }

        // extend recipient array, translate keys
        $arrRecipient['salutation'] = $arrRecipient['salutationPersonal'];
        $arrRecipient['anrede'] = $arrRecipient['salutationPersonal'];
        $arrRecipient['anrede_persoenlich'] = $arrRecipient['salutationPersonal'];
        $arrRecipient['firstname'] = $arrRecipient['firstname'] ?: $arrRecipient['firstname'];
        $arrRecipient['lastname'] = $arrRecipient['lastname'] ?: $arrRecipient['lastname'];
        $arrRecipient['street'] = $arrRecipient['street'] ?: $arrRecipient['street'];
        $arrRecipient['straße'] = $arrRecipient['street'] ?: $arrRecipient['straße'];
        $arrRecipient['postal'] = $arrRecipient['postal'] ?: $arrRecipient['postal'];
        $arrRecipient['city'] = $arrRecipient['city'] ?: $arrRecipient['city'];
        $arrRecipient['telefon'] = $arrRecipient['phone'] ?: $arrRecipient['telefon'];
        $arrRecipient['phone1'] = $arrRecipient['phone'] ?: $arrRecipient['phone1'];
        $arrRecipient['handy'] = $arrRecipient['mobile'] ?: $arrRecipient['handy'];
        $arrRecipient['mobile'] = $arrRecipient['mobile'] ?: $arrRecipient['mobile'];

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

        $objSR = RefereeModel::findByPk($arrRecipient['refereeId']);

        if (!isset($objSR)) {
            return null;
        }

        return $this->Database->prepare('SELECT tl_bsa_referee.email FROM tl_bsa_referee JOIN tl_bsa_club_chairman ON (tl_bsa_referee.id = tl_bsa_club_chairman.chairman OR tl_bsa_referee.id = tl_bsa_club_chairman.viceChairman1 OR tl_bsa_referee.id = tl_bsa_club_chairman.viceChairman2) WHERE tl_bsa_club_chairman.clubId = ? AND tl_bsa_referee.email <> ? AND tl_bsa_referee.email <> ?')
            ->execute($objSR->__get('clubId'), $objSR->__get('email'), '')
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
                'firstname' => 'VORNAME',
                'lastname' => 'NACHNAME',
                'lastname' => 'NACHNAME',
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
