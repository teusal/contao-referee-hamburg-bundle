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
use Contao\File;
use Contao\FileUpload;
use Contao\Input;
use Contao\Message;
use Contao\StringUtil;
use Contao\System;

/**
 * Class EventParticipiantImport.
 */
class EventParticipiantImport extends AbstractEventParticipiantHandler
{
    /**
     * Function to import csv data. Each data set of the file will be registered as an event participiant.
     */
    public function execute(): string
    {
        if ('import' !== Input::get('key')) {
            return '';
        }

        /** @var FileUpload $objUploader */
        $objUploader = new FileUpload();

        $isRulesTest = 'regelarbeit' === $this->objEvent->eventGroup;

        // Datei einlesen und Daten importieren
        if ('participiant_import' === Input::post('FORM_SUBMIT')) {
            $arrUploaded = $objUploader->uploadTo('system/tmp');

            if (empty($arrUploaded)) {
                Message::addError('Bitte wählen Sie eine csv-Datei aus.');
                $this->reload();
            }

            $arrRefereeIds = [];

            foreach ($arrUploaded as $strCsvFile) {
                $objFile = new File($strCsvFile);

                if ('csv' !== $objFile->extension) {
                    Message::addError(sprintf($GLOBALS['TL_LANG']['ERR']['filetype'], $objFile->extension));
                    $this->reload();
                }

                $content = $objFile->getContent();
                File::putContent($strCsvFile, $content);

                $resFile = $objFile->handle;

                $arrRows = [];

                while (($arrRow = @fgetcsv($resFile, null, ';')) !== false) {
                    $arrRows[] = $isRulesTest ? $arrRow : array_map('utf8_encode', $arrRow);
                }

                $arrHeader = array_shift($arrRows);

                if ($isRulesTest) {
                    if (!\in_array('Teilnehmer', $arrHeader, true)) {
                        Message::addError('Teilnehmer wurde nicht als Spalte in der Datei '.$objFile->basename.' gefunden. Der Import wird abgebrochen.');
                        $this->reload();
                    }

                    if (!\in_array('Summe', $arrHeader, true)) {
                        Message::addError('Summe wurde nicht als Spalte in der Datei '.$objFile->basename.' gefunden. Der Import wird abgebrochen.');
                        $this->reload();
                    }
                } else {
                    if (!\in_array('Vorname', $arrHeader, true)) {
                        Message::addError('Vorname wurde nicht als Spalte in der Datei '.$objFile->basename.' gefunden. Der Import wird abgebrochen.');
                        $this->reload();
                    }

                    if (!\in_array('Name', $arrHeader, true)) {
                        Message::addError('Name wurde nicht als Spalte in der Datei '.$objFile->basename.' gefunden. Der Import wird abgebrochen.');
                        $this->reload();
                    }
                }

                $arrParticipants = [];

                foreach ($arrRows as $arrRow) {
                    $arrParticipants[] = array_combine($arrHeader, $arrRow);
                }

                foreach ($arrParticipants as $arrParticipant) {
                    if ($isRulesTest) {
                        $nameRev = $arrParticipant['Teilnehmer'];
                        $query = 'SELECT * FROM tl_bsa_referee WHERE nameReverse=? AND deleted=?';
                        $params = ['nameReverse' => $arrParticipant['Teilnehmer'], 'deleted' => false];
                    } else {
                        $nameRev = $arrParticipant['Name'].', '.$arrParticipant['Vorname'];
                        $query = 'SELECT * FROM tl_bsa_referee WHERE lastname=? AND firstname=? AND deleted=?';
                        $params = ['lastname' => $arrParticipant['Name'], 'firstname' => $arrParticipant['Vorname'], 'deleted' => false];
                    }

                    $arrReferee = $this->Database->prepare($query)
                        ->execute($params)
                        ->fetchAllAssoc()
                    ;

                    if (!\is_array($arrReferee) || empty($arrReferee)) {
                        $params['deleted'] = true;
                        $arrReferee = $this->Database->prepare($query)
                            ->execute($params)
                            ->fetchAllAssoc()
                        ;
                    }

                    if (!\is_array($arrReferee) || empty($arrReferee)) {
                        echo 'Kein Schiedsrichter zu '.$nameRev.' gefunden.<br/>';
                        Message::addError('Kein Schiedsrichter zu '.$nameRev.' gefunden.');
                    } elseif (\count($arrReferee) > 1) {
                        Message::addError('Mehrere Schiedsrichter zu '.$nameRev.' gefunden.');
                    } else {
                        $arrReferee = array_shift($arrReferee);
                        $arrReferee['csv_import_data'] = $arrParticipant;
                        $arrRefereeIds[$arrReferee['id']] = $arrReferee;
                    }
                }
            }

            if ($isRulesTest) {
                $maxPoints = (float) ($this->objEvent->type);

                foreach ($arrRefereeIds as $refereeId => $referee) {
                    $reachedPoints = (float) ($referee['csv_import_data']['Summe']);

                    if (0.0 === $reachedPoints) {
                        Message::addError('Teilnehmer '.$referee['nameReverse'].' wird ausgelassen, da 0 Punkte erreicht wurden, wie eine Nicht-Bearbeitung aussieht.');
                        unset($arrRefereeIds[$refereeId]);
                    } elseif ($reachedPoints > $maxPoints) {
                        Message::addError('Teilnehmer '.$referee['nameReverse'].' wird ausgelassen, da mit '.$reachedPoints.' Punkten mehr Punkte erreicht wären, als möglich sind.');
                        unset($arrRefereeIds[$refereeId]);
                    }
                }
            }

            $arrNewReferee = [];
            $arrExistingReferee = [];
            $arrRemovedReferee = [];

            foreach ($this->arrAlreadyRegisteredParticipiants as $refereeId) {
                if (!\array_key_exists($refereeId, $arrRefereeIds)) {
                    $arrRemovedReferee[] = $refereeId;
                }
            }

            foreach ($arrRefereeIds as $refereeId => $referee) {
                if ($this->isAlreadyRegistered($refereeId)) {
                    $arrExistingReferee[$refereeId] = $referee;
                } else {
                    $arrNewReferee[$refereeId] = $referee;
                }
            }

            if (!empty($arrNewReferee)) {
                foreach ($arrNewReferee as $refereeId => $referee) {
                    $this->Database->prepare('INSERT INTO tl_bsa_event_participiant (pid, tstamp, refereeId, refereeNameReverse, type) SELECT ?, ?, id, nameReverse, ? FROM tl_bsa_referee WHERE id = ?')
                        ->execute($this->objEvent->id, time(), ($isRulesTest ? (float) ($referee['csv_import_data']['Summe']) : 'a'), $refereeId)
                    ;
                }
                Message::addConfirmation(\count($arrNewReferee).' Teilnehmer wurde(n) hinzugefügt.');
            }

            if (!empty($arrExistingReferee)) {
                $updates = 0;

                if ($isRulesTest && 'overwrite' === Input::post('update_existing')) {
                    foreach ($arrExistingReferee as $refereeId => $referee) {
                        $update = $this->Database->prepare('UPDATE tl_bsa_event_participiant SET type = ? WHERE pid = ? AND refereeId = ?')
                            ->execute((float) ($referee['csv_import_data']['Summe']), $this->objEvent->id, $refereeId)
                        ;

                        if ($update->__get('affectedRows') > 0) {
                            $updates += $update->__get('affectedRows');
                            $this->Database->prepare('UPDATE tl_bsa_event_participiant SET tstamp = ? WHERE pid = ? AND refereeId = ?')
                                ->execute(time(), $this->objEvent->id, $refereeId)
                            ;
                        }
                    }
                }

                if ($updates > 0) {
                    Message::addConfirmation($updates.' Teilnehmer wurde(n) aktualisiert.');
                }

                if (\count($arrExistingReferee) - $updates > 0) {
                    Message::addInfo(\count($arrExistingReferee) - $updates.' Teilnehmer wurde(n) ohne Änderungen übergangen.');
                }
            }

            if (!empty($arrRemovedReferee)) {
                if ('delete' === Input::post('delete_not_in_list')) {
                    foreach ($arrRemovedReferee as $refereeId) {
                        $this->Database->prepare('DELETE FROM tl_bsa_event_participiant WHERE pid = ? AND refereeId = ?')
                            ->execute($this->objEvent->id, $refereeId)
                        ;
                    }
                    Message::addConfirmation(\count($arrRemovedReferee).' Teilnehmer wurde(n) gelöscht.');
                } else {
                    Message::addInfo(\count($arrRemovedReferee).' Teilnehmer wurde(n) ignoriert, obwohl kein Datensatz in der csv-Datei war.');
                }
            }

            System::setCookie('BE_PAGE_OFFSET', 0, 0);
            $this->reload();
        }

        return '
'.Message::generate().'
'.$this->getBackButton().'
'.$this->getHeader().'

<form action="'.StringUtil::ampersand(Environment::get('request')).'" id="anwesenheit_import_form" class="tl_form tl_edit_form" method="post" enctype="multipart/form-data">
    <div class="tl_formbody_edit">
        <input type="hidden" name="FORM_SUBMIT" value="participiant_import" />
        <input type="hidden" name="REQUEST_TOKEN" value="'.System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue().'">

        <fieldset class="tl_tbox nolegend">
            <div class="widget">
                <h3>'.($isRulesTest ? 'DFB Online Lernen' : 'DFBnet Lehrgang').'-Export (csv-Datei) auswählen</h3>
                '.$objUploader->generateMarkup().'
                <p class="tl_help tl_tip">Wählen Sie die Datei aus, die zuvor aus '.('regelarbeit' === $this->objEvent->eventGroup ? 'den Ergebnissen einer Schulung im DFB Online Lernen' : 'einem einzelnen Lehrgang im DFBnet').' exportiert wurde.</p>
            </div>
        </fieldset>

        <fieldset class="tl_tbox nolegend">
'.($isRulesTest ? '            <div class="widget">
                <h3>Aktualisierung?</h3>
                <input type="checkbox" name="update_existing" id="update_existing" value="overwrite"'.(null === Input::post('update_existing') || 'overwrite' === Input::post('update_existing') ? ' checked="checked"' : '').' class="tl_checkbox" />
                <label for="update_existing">Ergebnisse aktualisieren?</label>
                <p class="tl_help">Ergebnisse der Teilnehmer, die bereits in dieser '.$GLOBALS['TL_LANG']['MOD'][$this->objEvent->eventGroup][0].' erfasst sind, werden mit den Daten aus der csv-Datei überschrieben.</p>
            </div>
' : '').'
            <div class="widget">
                <h3>Löschen?</h3>
                <input type="checkbox" name="delete_not_in_list" id="delete_not_in_list" value="delete"'.('delete' === Input::post('delete_not_in_list') ? ' checked="checked"' : '').' class="tl_checkbox" />
                <label for="delete_not_in_list">Teilnehmer löschen?</label>
                <p class="tl_help">Alle Teilnehmer, die bereits in dieser '.$GLOBALS['TL_LANG']['MOD'][$this->objEvent->eventGroup][0].' erfasst aber nicht in der csv-Liste enthalten sind, werden gelöscht.</p>
            </div>
        </fieldset>
    </div>

    <div class="tl_formbody_submit">
        <div class="tl_submit_container">
            <button type="submit" name="save" id="save" class="tl_submit" accesskey="s">Teilnahme(n) an der '.$GLOBALS['TL_LANG']['MOD'][$this->objEvent->eventGroup][0].' importieren</button>
        </div>
    </div>

</form>
</div>';
    }
}
