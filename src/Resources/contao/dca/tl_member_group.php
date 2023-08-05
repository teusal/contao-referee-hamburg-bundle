<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

use Contao\ArrayUtil;
use Contao\BackendUser;
use Contao\DataContainer;
use Contao\Image;
use Contao\StringUtil;
use Teusal\ContaoRefereeHamburgBundle\Library\Addressbook\AddressbookSynchronizer;
use Teusal\ContaoRefereeHamburgBundle\Library\Member\BSAMemberGroup;
use Teusal\ContaoRefereeHamburgBundle\Library\Newsletter\BSANewsletter;
use Teusal\ContaoRefereeHamburgBundle\Model\BsaGruppenmitgliederModel;
use Teusal\ContaoRefereeHamburgBundle\Model\BsaSchiedsrichterModel;

/*
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_member_group']['fields']['beobachtung_beobachterauswahl'] = [
    'filter' => true,
    'inputType' => 'checkbox',
    'eval' => [],
    'sql' => "char(1) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_member_group']['fields']['beobachtung_aktivieren'] = [
    'filter' => true,
    'inputType' => 'checkbox',
    'eval' => ['submitOnChange' => true],
    'sql' => "char(1) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_member_group']['fields']['beobachtung_ordner_name'] = [
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'maxlength' => 10, 'tl_class' => 'clr'],
    'sql' => "varchar(10) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_member_group']['fields']['beobachtung_gruppen_name'] = [
    'inputType' => 'select',
    'options' => ['VSA', 'Leistungskader', 'Nachwuchskader', 'Oldies'],
    'eval' => ['mandatory' => true, 'maxlength' => 25, 'includeBlankOption' => true, 'tl_class' => 'w50'],
    'sql' => "varchar(25) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_member_group']['fields']['beobachtung_gruppen_name_kurz'] = [
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'maxlength' => 50, 'tl_class' => 'w50'],
    'sql' => "varchar(50) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_member_group']['fields']['email_aktivieren'] = [
    'inputType' => 'checkbox',
    'eval' => ['submitOnChange' => true],
    'sql' => "char(1) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_member_group']['fields']['email'] = [
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'maxlength' => 100, 'rgxp' => 'email'],
    'sql' => "varchar(100) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_member_group']['fields']['automatik'] = [
    'filter' => true,
    'inputType' => 'select',
    'options_callback' => [BSAMemberGroup::class, 'getAllAutomaticOptions'],
    'reference' => &$GLOBALS['TL_LANG']['tl_member_group']['options'],
    'eval' => ['helpwizard' => true, 'includeBlankOption' => true, 'blankOptionLabel' => 'Manuelle Verwaltung', 'unique' => true],
    'save_callback' => [
        [tl_bsa_member_group::class, 'changeAutomatik'],
    ],
    'sql' => "varchar(30) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_member_group']['fields']['image_anzeigen'] = [
    'inputType' => 'checkbox',
    'eval' => ['submitOnChange' => true],
    'sql' => "char(1) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_member_group']['fields']['image'] = [
    'inputType' => 'fileTree',
    'eval' => ['mandatory' => true, 'fieldType' => 'radio', 'files' => true, 'filesOnly' => true, 'extensions' => 'jpg,gif,jpeg,png', 'path' => 'files/Bilder/Schiedsrichter/web/Gruppenfotos'],
    'sql' => 'binary(16) NULL',
];
$GLOBALS['TL_DCA']['tl_member_group']['fields']['image_print_verlinken'] = [
    'inputType' => 'checkbox',
    'eval' => ['submitOnChange' => true],
    'sql' => "char(1) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_member_group']['fields']['image_print'] = [
    'inputType' => 'fileTree',
    'eval' => ['mandatory' => true, 'fieldType' => 'radio', 'files' => true, 'filesOnly' => true, 'extensions' => 'jpg,gif,jpeg,png', 'path' => 'files/Bilder/Schiedsrichter/druck/Gruppenfotos'],
    'sql' => 'binary(16) NULL',
];
$GLOBALS['TL_DCA']['tl_member_group']['fields']['sync_addressbook'] = [
    'inputType' => 'checkbox',
    'eval' => ['submitOnChange' => true],
    'sql' => "char(1) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_member_group']['fields']['addressbook_token_id'] = [
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'maxlength' => 50, 'nospace' => true, 'tl_class' => 'long'],
    'sql' => "varchar(50) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_member_group']['fields']['veranstaltung_include_as_filter'] = [
    'filter' => true,
    'inputType' => 'checkbox',
    'eval' => [],
    'sql' => "char(1) NOT NULL default ''",
];

/*
 * Change config
 */
$GLOBALS['TL_DCA']['tl_member_group']['config']['ctable'] = ['tl_bsa_gruppenmitglieder', 'tl_bsa_newsletterzuordnung'];
$GLOBALS['TL_DCA']['tl_member_group']['config']['switchToEdit'] = true;
$GLOBALS['TL_DCA']['tl_member_group']['config']['notCopyable'] = true;
$GLOBALS['TL_DCA']['tl_member_group']['config']['enableVersioning'] = false;
$GLOBALS['TL_DCA']['tl_member_group']['config']['ondelete_callback'][] = [tl_bsa_member_group::class, 'executeDelete'];
$GLOBALS['TL_DCA']['tl_member_group']['config']['ondelete_callback'][] = [BSANewsletter::class, 'deleteGruppe'];
$GLOBALS['TL_DCA']['tl_member_group']['config']['onsubmit_callback'][] = [tl_bsa_member_group::class, 'executeSubmit'];
/*
 * disable Versioning
 */
$GLOBALS['TL_DCA']['tl_member_group']['config']['enableVersioning'] = false;
/*
 * Change list
 */
$GLOBALS['TL_DCA']['tl_member_group']['list']['sorting']['panelLayout'] = 'filter;search,limit';
ArrayUtil::arrayInsert($GLOBALS['TL_DCA']['tl_member_group']['list']['operations'], 0, [
    'edit_gruppenmitglieder' => [
        'href' => 'table=tl_bsa_gruppenmitglieder',
        'icon' => 'member.gif',
        'button_callback' => [tl_bsa_member_group::class, 'memberIcon'],
    ],
]);
ArrayUtil::arrayInsert($GLOBALS['TL_DCA']['tl_member_group']['list']['operations'], 1, [
    'edit_newsletterzuordnung' => [
        'href' => 'table=tl_bsa_newsletterzuordnung',
        'icon' => 'bundles/contaonewsletter/send.svg',
    ],
]);
unset($GLOBALS['TL_DCA']['tl_member_group']['list']['operations']['copy']);
/**
 * Change palette.
 */
$bsaPalette = '{legend_beobachtung:hide},beobachtung_beobachterauswahl,beobachtung_aktivieren;';
$GLOBALS['TL_DCA']['tl_member_group']['palettes']['default'] = str_replace('{redirect_legend:hide}', $bsaPalette.'{redirect_legend:hide}', $GLOBALS['TL_DCA']['tl_member_group']['palettes']['default']);
$GLOBALS['TL_DCA']['tl_member_group']['palettes']['__selector__'][] = 'beobachtung_aktivieren';
$bsaPalette = '{legend_email:hide},email_aktivieren;';
$GLOBALS['TL_DCA']['tl_member_group']['palettes']['default'] = str_replace('{redirect_legend:hide}', $bsaPalette.'{redirect_legend:hide}', $GLOBALS['TL_DCA']['tl_member_group']['palettes']['default']);
$GLOBALS['TL_DCA']['tl_member_group']['palettes']['__selector__'][] = 'email_aktivieren';
$bsaPalette = '{legend_automatik:hide},automatik;{legend_image:hide},image_anzeigen;{addressbook_legend},sync_addressbook;';
$GLOBALS['TL_DCA']['tl_member_group']['palettes']['default'] = str_replace('{redirect_legend:hide}', $bsaPalette.'{redirect_legend:hide}', $GLOBALS['TL_DCA']['tl_member_group']['palettes']['default']);
$GLOBALS['TL_DCA']['tl_member_group']['palettes']['__selector__'][] = 'image_anzeigen';
$GLOBALS['TL_DCA']['tl_member_group']['palettes']['__selector__'][] = 'image_print_verlinken';
$GLOBALS['TL_DCA']['tl_member_group']['palettes']['__selector__'][] = 'sync_addressbook';
$bsaPalette = '{legend_veranstaltung:hide},veranstaltung_include_as_filter;';
$GLOBALS['TL_DCA']['tl_member_group']['palettes']['default'] = str_replace('{redirect_legend:hide}', $bsaPalette.'{redirect_legend:hide}', $GLOBALS['TL_DCA']['tl_member_group']['palettes']['default']);
/*
 * Change subpalettes
 */
$GLOBALS['TL_DCA']['tl_member_group']['subpalettes']['beobachtung_aktivieren'] = 'beobachtung_ordner_name,beobachtung_gruppen_name,beobachtung_gruppen_name_kurz';
$GLOBALS['TL_DCA']['tl_member_group']['subpalettes']['email_aktivieren'] = 'email';
$GLOBALS['TL_DCA']['tl_member_group']['subpalettes']['image_anzeigen'] = 'image,image_print_verlinken';
$GLOBALS['TL_DCA']['tl_member_group']['subpalettes']['image_print_verlinken'] = 'image_print';
$GLOBALS['TL_DCA']['tl_member_group']['subpalettes']['sync_addressbook'] = 'addressbook_token_id';
/*
 * Change fields
 */
$GLOBALS['TL_DCA']['tl_member_group']['fields']['name']['eval']['tl_class'] = 'clr long';

/**
 * Class bsa_member_group.
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_bsa_member_group extends tl_member_group
{
    /**
     * Import the back end user object.
     */
    public function __construct()
    {
        parent::__construct();
        $this->import(BackendUser::class, 'User');
    }

    /**
     * Return the "gruppenmitglieder" button.
     *
     * @param array         $row               Record data
     * @param string|null   $href              Button href
     * @param string        $label             Label
     * @param string        $title             Title
     * @param string|null   $icon              Icon
     * @param string        $attributes        HTML attributes
     * @param string        $table             Table
     * @param array         $rootRecordIds     IDs of all root records
     * @param array         $childRecordIds    IDs of all child records
     * @param bool          $circularReference Whether this is a circular reference of the tree view
     * @param string        $previous          “Previous” label
     * @param string        $next              “Next” label
     * @param DataContainer $dc                Data Container object
     */
    public function memberIcon($row, $href, $label, $title, $icon, $attributes, $table, $rootRecordIds, $childRecordIds, $circularReference, $previous, $next, DataContainer $dc): string
    {
        if (BSAMemberGroup::isVollautomatic($row['automatik'])) {
            return Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon), 'Gruppenmitglieder', 'title="Diese Gruppe wird automatisch verwaltet. Automatik: '.$GLOBALS['TL_LANG']['tl_member_group']['options'][$row['automatik']][0].'"').' ';
        }

        if (BSAMemberGroup::isHalbautomatic($row['automatik'])) {
            $icon = 'user.gif';
        }

        $href .= '&amp;id='.$row['id'];

        return '<a href="'.$this->addToUrl($href).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
    }

    /**
     * whenever an automatic configuration of a group is changed the changes in the group members needs to be done.
     *
     * @param mixed         $varValue Value to be saved
     * @param DataContainer $dc       Data Container object
     *
     * @return mixed
     */
    public function changeAutomatik($varValue, DataContainer $dc)
    {
        if ($varValue !== $dc->__get('activeRecord')->automatik) {
            BSAMemberGroup::clearCachedGroups($varValue);
            BSAMemberGroup::clearCachedGroups($dc->__get('activeRecord')->automatik);

            if (BSAMemberGroup::isVollautomatic($varValue) || BSAMemberGroup::isVollautomatic($dc->__get('activeRecord')->automatik)) {
                $objSR = BsaSchiedsrichterModel::findAll();

                if (isset($objSR)) {
                    while ($objSR->next()) {
                        BSAMemberGroup::handleAutomaticGroups($objSR->id);
                    }
                }
            }
        }

        return $varValue;
    }

    /**
     * handle addressbook synchronization whenever a group is deleted.
     *
     * @param DataContainer $dc     Data Container object
     * @param int           $undoId The ID of the tl_undo database record
     */
    public function executeDelete(DataContainer $dc, $undoId): void
    {
        $objMembers = BsaGruppenmitgliederModel::findBy('pid', $dc->id);

        if (isset($objMembers)) {
            while ($objMembers->next()) {
                AddressbookSynchronizer::executeSubmitSchiedsrichter($objMembers->schiedsrichter, $dc->id);
            }
        }
    }

    /**
     * handle addressbook synchronization whenever the synchronization flag is changed.
     *
     * @param DataContainer $dc Data Container object
     */
    public function executeSubmit(DataContainer $dc): void
    {
        $objMembers = BsaGruppenmitgliederModel::findBy('pid', $dc->id);

        if (isset($objMembers)) {
            while ($objMembers->next()) {
                AddressbookSynchronizer::executeSubmitSchiedsrichter($objMembers->schiedsrichter);
            }
        }
    }
}
