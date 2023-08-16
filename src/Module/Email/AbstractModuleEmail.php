<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

namespace Teusal\ContaoRefereeHamburgBundle\Module\Email;

use Contao\BackendModule;
use Contao\BackendTemplate;
use Contao\BackendUser;
use Contao\CheckBox;
use Contao\Config;
use Contao\CoreBundle\Mailer\TransportConfig;
use Contao\DataContainer;
use Contao\Environment;
use Contao\Input;
use Contao\Message;
use Contao\SelectMenu;
use Contao\StringUtil;
use Contao\System;
use Contao\TextArea;
use Contao\TextField;
use Teusal\ContaoRefereeHamburgBundle\Library\Email\BSAEmail;
use Teusal\ContaoRefereeHamburgBundle\Library\Mailer\AvailableTransports;
use Teusal\ContaoRefereeHamburgBundle\Model\ClubChairmanModel;
use Teusal\ContaoRefereeHamburgBundle\Model\ClubModel;

/**
 * Class SimpleMail.
 */
abstract class AbstractModuleEmail extends BackendModule
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'be_bsa_mail';

    /**
     * clubs.
     *
     * @var array<int, array<string, mixed>>
     */
    protected $arrClubs = [];

    /**
     * @var TransportConfig|null
     */
    private $transport;

    /**
     * @var bool
     */
    private $disabled = false;

    /**
     * Initialize the object.
     *
     * @param DataContainer $dc Data container object
     */
    public function __construct(DataContainer $dc = null)
    {
        parent::__construct($dc);

        // import the backend user
        $this->import(BackendUser::class, 'User');

        // validate an existing mailer transport based on the users emailaddress
        /** @var AvailableTransports $availableTransports */
        $availableTransports = System::getContainer()->get('contao.mailer.available_transports');
        $this->transport = $availableTransports->getTransport($this->User->email);

        if (null === $this->transport) {
            Message::addError('Es wurde keine Konfiguration zum Mailversand für Sie anhand Ihrer E-Mail-Adresse gefunden. Sie können so keine E-Mails versenden. Bitte wenden Sie sich an einen Administrator.');
            $this->disabled = true;
        }

        // load the clubs in an array. the key is set by the id, the value is by nameShort.
        $objClub = ClubModel::findAll(['order' => 'nameShort']);

        if (isset($objClub)) {
            while ($objClub->next()) {
                $this->arrClubs[$objClub->id] = [
                    'number' => $objClub->number,
                    'nameShort' => StringUtil::specialchars($objClub->nameShort),
                    'visible' => $objClub->published,
                ];
            }
        }
        $this->arrClubs[0] = ['number' => '', 'nameShort' => 'vereinslos', 'visible' => false];
    }

    /**
     * Generate module.
     */
    final protected function compile(): void
    {
        $recipients = $this->getRecipientsWidget();
        $carbonCopy = $this->getCarbonCopyWidget();
        $blindCarbonCopy = $this->getBlindCarbonCopyWidget();
        $subject = $this->getSubjectWidget();
        $emailText = $this->getEmailTextWidget();

        if ('send_simple_mail' === Input::post('FORM_SUBMIT')) {
            $recipients->validate();
            $carbonCopy->validate();
            $blindCarbonCopy->validate();
            $subject->validate();
            $emailText->validate();

            $emailTextValue = StringUtil::restoreBasicEntities(StringUtil::decodeEntities($emailText->value));

            if (!$emailText->hasErrors() && $emailTextValue === $this->getDefaultSignature()) {
                $emailText->addError(sprintf($GLOBALS['TL_LANG']['ERR']['mandatory'], $emailText->label));
            }

            if (!$recipients->hasErrors() && !$carbonCopy->hasErrors() && !$subject->hasErrors() && !$emailText->hasErrors()) {
                $recipientData = $this->getRecipientData($recipients->value);

                $arrCC = $this->getRecepientsOfCarbonCopyCheckboxes($carbonCopy, $recipientData['clubId'], $recipientData['email_addresses']);
                $arrBCC = $this->getRecepientsOfCarbonCopyCheckboxes($blindCarbonCopy, $recipientData['clubId'], array_merge($recipientData['email_addresses'], $arrCC));

                $objEmail = new BSAEmail();
                $objEmail->setMailerTransport($this->transport->getName());
                $objEmail->setReferee($recipientData['refereeId']);
                $objEmail->setClub($recipientData['clubId']);
                $objEmail->sendCc($arrCC);
                $objEmail->sendBcc($arrBCC);
                $objEmail->__set('subject', $subject->value);
                $objEmail->__set('html', $emailTextValue);
                $objEmail->sendTo($recipientData['email_addresses']);

                Message::addConfirmation('Die E-Mail wurde erfolgreich versendet: <strong>'.implode(', ', $recipientData['email_addresses']).'</strong>'.(empty($arrCC) ? '' : '<br/>CC: '.implode(', ', $arrCC)).(empty($arrBCC) ? '' : '<br/>BCC: '.implode(', ', $arrBCC)));

                $this->reload();
            }
        }

        if (empty($emailText->value)) {
            $emailText->value = $this->getDefaultSignature();
        }

        $objTinyNews = new BackendTemplate('be_tinyNews');
        $objTinyNews->__set('selector', 'ctrl_body');
        $objTinyNews->__set('readonly', $this->disabled);

        $this->Template->__set('disabled', $this->disabled);
        $this->Template->__set('recipients', $recipients);
        $this->Template->__set('carbonCopy', $carbonCopy);
        $this->Template->__set('blindCarbonCopy', $blindCarbonCopy);
        $this->Template->__set('subject', $subject);
        $this->Template->__set('emailText', $emailText);
        $this->Template->__set('tinyNews', $objTinyNews);
        $this->Template->__set('messages', Message::generate());
        $this->Template->__set('action', StringUtil::ampersand(Environment::get('request')));
        $this->Template->__set('href', $this->getReferer(true));
        $this->Template->__set('title', StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBT']));
        $this->Template->__set('button', $GLOBALS['TL_LANG']['MSC']['backBT']);
    }

    /**
     * returns the options for the widgets carbon copy and blind carbon copy.
     *
     * @return array<string, array<string, string>> the options
     */
    protected function getCarbonCopyOptions()
    {
        return [
            'board_option' => ['value' => 'board', 'label' => 'BSA Vorstand'],
            'chairmans_option' => ['value' => 'chairmans', 'label' => 'Vereinsobmann'],
            'hfv.evpost_option' => ['value' => 'hfv.evpost', 'label' => 'E-Postfach Verein'],
        ];
    }

    /**
     * provides the array of options to be set as recipients.
     *
     * @return array<int, array<string, string>> the list of options
     */
    abstract protected function getRecipientOptions(): array;

    /**
     * provides the array with the data of the to recipients.
     *
     * @param mixed $recipientId the id of the selected recipient
     *
     * @return array<string, mixed> the data of the recipient
     */
    abstract protected function getRecipientData($recipientId): array;

    /**
     * returns the widget for recipients dropdown of all referees as well as clubs chairmans.
     *
     * @return SelectMenu the dropdown
     */
    private function getRecipientsWidget()
    {
        $widget = new SelectMenu();
        $widget->id = 'recipinent';
        $widget->name = 'recipinent';
        $widget->label = 'Empfänger:';
        $widget->options = $this->getRecipientOptions();
        $widget->chosen = !$this->disabled;
        $widget->mandatory = true;
        $widget->disabled = $this->disabled;

        return $widget;
    }

    /**
     * returns the widget for the carbon copy options.
     *
     * @return CheckBox the checkboxes
     */
    private function getCarbonCopyWidget()
    {
        $widget = new CheckBox();
        $widget->id = 'cc';
        $widget->name = 'cc';
        $widget->label = 'CC\'s:';
        $widget->options = array_values($this->getCarbonCopyOptions());
        $widget->multiple = true;
        $widget->disabled = $this->disabled;

        return $widget;
    }

    /**
     * returns the widget for the blind carbon copy options.
     *
     * @return CheckBox the checkboxes
     */
    private function getBlindCarbonCopyWidget()
    {
        $widget = $this->getCarbonCopyWidget();
        $widget->id = 'bcc';
        $widget->name = 'bcc';
        $widget->label = 'BCC\'s:';

        return $widget;
    }

    /**
     * returns the widget for the subject.
     *
     * @return TextField the text field
     */
    private function getSubjectWidget()
    {
        $widget = new TextField();
        $widget->id = 'subject';
        $widget->name = 'subject';
        $widget->label = 'Betreff:';
        $widget->mandatory = true;
        $widget->readonly = $this->disabled;

        return $widget;
    }

    /**
     * returns the widget for email text as html.
     *
     * @return TextArea the textarea
     */
    private function getEmailTextWidget()
    {
        $widget = new TextArea();
        $widget->id = 'body';
        $widget->name = 'body';
        $widget->label = 'Text:';
        $widget->mandatory = true;
        $widget->disabled = $this->disabled;

        return $widget;
    }

    /**
     * getting the email addresses of carbon copy and blind carbon copy fields.
     *
     * @param CheckBox      $objCheckBoxWidget      the carbon copy or blind carbon copy widget
     * @param mixed         $recipientClubId        the id of the recipients club
     * @param array<string> $excludedEmailAddresses the email addresses to be excluded from the result list (i.e. recipients of to-addresses)
     *
     * @return array<string> empty array or list of email addresses
     */
    private function getRecepientsOfCarbonCopyCheckboxes(CheckBox $objCheckBoxWidget, $recipientClubId, array $excludedEmailAddresses)
    {
        $arrCC = [];

        if (empty($objCheckBoxWidget->value)) {
            return $arrCC;
        }

        foreach ($objCheckBoxWidget->value as $cc) {
            switch ($cc) {
                case 'chairmans':
                    $arrChairman = ClubChairmanModel::getEmailAddressesOfChairmans((int) $recipientClubId);

                    foreach ($arrChairman as $chairman) {
                        $arrCC[] = $chairman;
                    }
                    break;

                case 'board':
                    $arrCC[] = 'bsa-'.Config::get('bsa_name').'@hfv.de';
                    break;

                case 'hfv.evpost':
                    $arrCC[] = 'pv03'.$this->arrClubs[$recipientClubId]['number'].'@hfv.evpost.de';
                    break;

                default:
                    throw new \Exception('no recipient implementation for key '.$cc);
            }
        }

        return array_diff($arrCC, $excludedEmailAddresses);
    }

    /**
     * returns the default signatue of the logged in user.
     *
     * @return string default signature
     */
    private function getDefaultSignature(): string
    {
        return '<p>&nbsp;</p>
'.$this->User->__get('signatur_html');
    }
}
