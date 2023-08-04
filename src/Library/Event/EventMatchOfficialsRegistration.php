<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

namespace Teusal\ContaoRefereeHamburgBundle\Library\Event;

use Contao\Environment;
use Contao\Input;
use Contao\Message;
use Contao\StringUtil;

/**
 * Class EventMatchOfficialsRegistration.
 */
class EventMatchOfficialsRegistration extends AbstractEventParticipiantHandler
{
    /**
     * Searching the matches in the schedules and entering the referees at the event.
     */
    public function execute(): string
    {
        if ('spiele' !== Input::get('key')) {
            return '';
        }

        // register match officials
        if ('match_officials_registration' === Input::post('FORM_SUBMIT')) {
            $arrToSave = Input::post('sr');

            if (empty($arrToSave)) {
                Message::addInfo('Es wurden keine neuen Spieloffiziellen eingetragen.');
            } else {
                foreach ($arrToSave as $toSave) {
                    $exist = $this->Database->prepare('SELECT * FROM tl_bsa_teilnehmer WHERE pid=? AND sr_id=?')
                        ->execute($this->objEvent->id, $toSave)
                    ;

                    if ($exist->next()) {
                        Message::addError('Ein Eintrag für "'.$exist->__get('sr').'" existiert bereits. Es wurde daher kein neuer Eintrag angelegt.');
                    } else {
                        $this->Database->prepare("INSERT INTO tl_bsa_teilnehmer (pid, tstamp, sr_id, sr, typ) SELECT ?, ?, id, name_rev, 's' FROM tl_bsa_schiedsrichter WHERE id = ?")
                            ->execute($this->objEvent->id, time(), $toSave)
                        ;
                    }
                }
                Message::addConfirmation(\count($arrToSave).' Spieloffizielle wurden eingetragen.');
            }

            $this->reload();
        }

        $startDate = $this->objEvent->datum;
        $endDate = strtotime('+1 DAY', (int) $startDate);

        $arrMatches = $this->Database->prepare("SELECT * FROM tl_bsa_spiel WHERE datum>? AND datum<? AND abgesetzt='' AND (sr_id<>0 OR sra1_id<>0 OR sra2_id<>0 OR 4off_id<>0 OR pate_id<>0)")
            ->execute($startDate, $endDate)
            ->fetchAllAssoc()
        ;

        if (empty($arrMatches)) {
            Message::addInfo('Es gibt keine angesetzten Schiedsrichter an diesem Tag.');
        }

        $strHTML = '
'.Message::generate().'
'.$this->getBackButton().'
'.$this->getHeader().'

<form action="'.StringUtil::ampersand(Environment::get('request')).'" id="match_officials_registration_form" class="tl_form tl_edit_form" method="post">
    <div class="tl_formbody_edit">
        <input type="hidden" name="FORM_SUBMIT" value="match_officials_registration" />
        <input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">

        <fieldset class="tl_tbox nolegend">
';

        $enableButton = false;

        foreach ($arrMatches as $match) {
            $strHTML .= '
            <div class="widget" style="min-height: 0px;">
                <h3>'.$match['heimmannschaft'].' - '.$match['gastmannschaft'].'</h3>
';

            if ($match['sr_id']) {
                $enableButton = $enableButton || !$this->isAlreadyRegistered($match['sr_id']);
                $strHTML .= '                <div>
                    <input type="checkbox" name="sr[]" id="sr_'.$match['sr_id'].'" value="'.$match['sr_id'].'" checked="checked" class="tl_checkbox" '.($this->isAlreadyRegistered($match['sr_id']) ? 'disabled="disabled" ' : ' ').'/>
                    <label for="sr_'.$match['sr_id'].'">'.$match['sr_name'].', '.$match['sr_vorname'].' (SR)</label>
                </div>
';
            }

            if ($match['sra1_id']) {
                $enableButton = $enableButton || !$this->isAlreadyRegistered($match['sra1_id']);
                $strHTML .= '                <div>
                <input type="checkbox" name="sr[]" id="sr_'.$match['sra1_id'].'" value="'.$match['sra1_id'].'" checked="checked" class="tl_checkbox" '.($this->isAlreadyRegistered($match['sra1_id']) ? 'disabled="disabled" ' : ' ').'/>
                <label for="sr_'.$match['sra1_id'].'">'.$match['sra1_name'].', '.$match['sra1_vorname'].' (SRA1)</label>
            </div>
';
            }

            if ($match['sra2_id']) {
                $enableButton = $enableButton || !$this->isAlreadyRegistered($match['sra2_id']);
                $strHTML .= '                <div>
                <input type="checkbox" name="sr[]" id="sr_'.$match['sra2_id'].'" value="'.$match['sra2_id'].'" checked="checked" class="tl_checkbox" '.($this->isAlreadyRegistered($match['sra2_id']) ? 'disabled="disabled" ' : ' ').'/>
                <label for="sr_'.$match['sra2_id'].'">'.$match['sra2_name'].', '.$match['sra2_vorname'].' (SRA2)</label>
            </div>
';
            }

            if ($match['4off_id']) {
                $enableButton = $enableButton || !$this->isAlreadyRegistered($match['4off_id']);
                $strHTML .= '            <div>
                <input type="checkbox" name="sr[]" id="sr_'.$match['4off_id'].'" value="'.$match['4off_id'].'" checked="checked" class="tl_checkbox" '.($this->isAlreadyRegistered($match['4off_id']) ? 'disabled="disabled" ' : ' ').'/>
                <label for="sr_'.$match['4off_id'].'">'.$match['4off_name'].', '.$match['4off_vorname'].' (4. Off.)</label>
            </div>
';
            }

            if ($match['pate_id']) {
                $enableButton = $enableButton || !$this->isAlreadyRegistered($match['pate_id']);
                $strHTML .= '            <div>
                <input type="checkbox" name="sr[]" id="sr_'.$match['pate_id'].'" value="'.$match['pate_id'].'" checked="checked" class="tl_checkbox" '.($this->isAlreadyRegistered($match['pate_id']) ? 'disabled="disabled" ' : ' ').'/>
                <label for="sr_'.$match['pate_id'].'">'.$match['pate_name'].', '.$match['pate_vorname'].' (Pate)</label>
            </div>
';
            }
            $strHTML .= '            </div>
';
        }

        $strHTML .= '        </fieldset>
    </div>

    <div class="tl_formbody_submit">
        <div class="tl_submit_container">
            <button type="submit" name="save" id="save" class="tl_submit" accesskey="s"'.($enableButton ? '' : ' disabled').'>Teilnahme(n) an der Sitzung speichern</button>
        </div>
    </div>

</form>
';

        return $strHTML;
    }
}
