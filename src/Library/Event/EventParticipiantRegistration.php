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
use Teusal\ContaoRefereeHamburgBundle\Model\BsaVereinModel;
use Teusal\ContaoRefereeHamburgBundle\Model\BsaVereinObmannModel;

/**
 * Class EventParticipiantRegistration.
 */
class EventParticipiantRegistration extends AbstractEventParticipiantHandler
{
    /**
     * Query participiants of a club in the GUI and register them to the specified event.
     */
    public function execute(): string
    {
        if ('besucher' !== Input::get('key')) {
            return '';
        }

        if ('true' === Input::get('start')) {
            $this->Session->set('clubId', '');
            $this->redirect(str_replace('&start=true', '', Environment::get('request')));
        }

        $clubId = $this->Session->get('clubId');

        if (Input::post('clubId')) {
            $this->Session->set('clubId', Input::post('clubId'));

            if ($clubId !== $this->Session->get('clubId')) {
                $this->reload();
            }
        }

        $clubId = $this->Session->get('clubId');
        $typ = Input::post('typ');
        $arrClubs = $this->Database->execute('SELECT id, name_kurz FROM tl_bsa_verein WHERE anzeigen=1 ORDER BY name_kurz')
            ->fetchAllAssoc()
        ;

        // register participiant
        if ('participiant_registration' === Input::post('FORM_SUBMIT')) {
            $submitMode = Input::post('SUBMIT_MODE');

            if ('reset' === $submitMode) {
                $this->Session->set('clubId', '');
            }

            if ('next' === $submitMode) {
                for ($i = \count($arrClubs); $i >= 0; --$i) {
                    if ($arrClubs[$i - 1]['id'] === $clubId) {
                        if ($i === \count($arrClubs)) {
                            $this->Session->set('clubId', '');
                        } else {
                            $this->Session->set('clubId', $arrClubs[$i]['id']);
                        }
                        break;
                    }
                }
            }

            $objClub = BsaVereinModel::findByPk($clubId);

            $arrToSave = Input::post('sr');

            if (empty($arrToSave)) {
                Message::addInfo('Es wurden keine neuen Teilnehmer für den Verein '.$objClub->name_kurz.' eingetragen.');
            } else {
                foreach ($arrToSave as $toSave) {
                    $exist = $this->Database->prepare('SELECT * FROM tl_bsa_teilnehmer WHERE pid=? AND sr_id=?')
                        ->execute($this->objEvent->id, $toSave)
                    ;

                    if ($exist->next()) {
                        Message::addError('Ein Eintrag für "'.$exist->sr.'" existiert bereits. Es wurde daher kein neuer Eintrag angelegt.');
                    } else {
                        $this->Database->prepare('INSERT INTO tl_bsa_teilnehmer (pid, tstamp, sr_id, sr, typ) SELECT ?, ?, id, name_rev, ? FROM tl_bsa_schiedsrichter WHERE id = ?')
                            ->execute($this->objEvent->id, time(), $typ, $toSave)
                        ;
                    }
                }
                Message::addConfirmation(\count($arrToSave).' Teilnehmer wurden für den Verein '.$objClub->name_kurz.' eingetragen.');
            }
            $this->reload();
        }

        $strHTML = '
'.Message::generate().'
'.$this->getBackButton().'
'.$this->getHeader().'

<form action="'.StringUtil::ampersand(Environment::get('request')).'" id="participiant_registration_form" class="tl_form tl_edit_form" method="post">
    <div class="tl_formbody_edit">
        <input type="hidden" name="FORM_SUBMIT" id="FORM_SUBMIT" value="participiant_registration" />
        <input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">
        <input type="hidden" name="SUBMIT_MODE" id="SUBMIT_MODE" value="next" />

';

        $strHTML .= '        <fieldset class="tl_tbox nolegend">
            <div class="w50 clr widget" style="min-height: 0px;">
                <h3>Verein:</h3>
                <select class="tl_select" name="clubId" onChange="document.getElementById(\'FORM_SUBMIT\').value=\'\'; document.getElementById(\'participiant_registration_form\').submit()">
                    <option value="0">bitte einen Verein wählen</option>
';

        foreach ($arrClubs as $club) {
            $strHTML .= '                    <option value="'.$club['id'].'"'.($clubId === $club['id'] ? ' selected="selected"' : '').'>'.$club['name_kurz'].'</option>
';
        }

        $strHTML .= '                </select>
            </div>
        </fieldset>

';

        if ($clubId) {
            $strHTML .= '        <fieldset class="tl_tbox nolegend">
            <div class="w50 clr widget" style="min-height: 0px;">
                <h3>Typ:</h3>
                <select class="tl_select" name="typ">
';

            foreach ($GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['typen'] as $key => $value) {
                $strHTML .= '                    <option value="'.$key.'"'.($key === $typ ? ' selected="selected"' : '').'>'.$value.'</option>
';
            }

            $strHTML .= '                </select>
            </div>
        </fieldset>

';

            $strHTML .= '        <fieldset class="tl_tbox nolegend">
            <div class="widget" style="min-height: 0px;">
                <h3>Schiedsrichter</h3>
';

            $objChairman = BsaVereinObmannModel::findOneBy('verein', $clubId);
            $sql = '';

            if (isset($objChairman)) {
                if ($objChairman->__get('obmann')) {
                    $sql .= ' OR id='.$objChairman->__get('obmann');
                }

                if ($objChairman->__get('stellv_obmann_1')) {
                    $sql .= ' OR id='.$objChairman->__get('stellv_obmann_1');
                }

                if ($objChairman->__get('stellv_obmann_2')) {
                    $sql .= ' OR id='.$objChairman->__get('stellv_obmann_2');
                }
            }

            $arrReferees = $this->Database->prepare('SELECT * FROM tl_bsa_schiedsrichter WHERE (verein=?'.$sql.') AND deleted=? ORDER BY name_rev')
                ->execute($clubId, '')
                ->fetchAllAssoc()
            ;

            foreach ($arrReferees as $referee) {
                $strHTML .= '                <div>
                    <input type="checkbox" name="sr[]" id="sr_'.$referee['id'].'" value="'.$referee['id'].'" class="tl_checkbox" '.($this->isAlreadyRegistered($referee['id']) ? 'checked="checked" disabled="disabled" ' : ' ').'/>
                    <label for="sr_'.$referee['id'].'">'.$referee['name_rev'].'</label>
                </div>
';
            }

            $strHTML .= '            </div>
        </fieldset>

';
        }

        $strHTML .= '    </div>
';

        $strHTML .= '
    <div class="tl_formbody_submit">
        <div class="tl_submit_container">
            <button type="submit" name="save1" id="save1" class="tl_submit" accesskey="s"'.($clubId ? '' : ' disabled').'>Teilnahme(n) speichern + Weiter</button>
            <button type="submit" name="save2" id="save2" class="tl_submit" onclick="document.getElementById(\'SUBMIT_MODE\').value=\'reset\';"'.($clubId ? '' : ' disabled').'>Teilnahme(n) speichern + Verein zurücksetzen</button>
        </div>
    </div>
';

        $strHTML .= '
</form>';

        return $strHTML;
    }
}
