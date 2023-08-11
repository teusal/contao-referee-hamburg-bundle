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
use Teusal\ContaoRefereeHamburgBundle\Library\Event\EventMatchOfficialsRegistration;
use Teusal\ContaoRefereeHamburgBundle\Library\Event\EventParticipiantImport;
use Teusal\ContaoRefereeHamburgBundle\Library\Event\EventParticipiantRegistration;
use Teusal\ContaoRefereeHamburgBundle\Library\Geburtstag;
use Teusal\ContaoRefereeHamburgBundle\Library\Mailer\UserTransportValidator;
use Teusal\ContaoRefereeHamburgBundle\Library\Member\BSAMember;
use Teusal\ContaoRefereeHamburgBundle\Library\Newsletter\Newsletter;
use Teusal\ContaoRefereeHamburgBundle\Model\ClubChairmanModel;
use Teusal\ContaoRefereeHamburgBundle\Model\ClubModel;
use Teusal\ContaoRefereeHamburgBundle\Model\EventModel;
use Teusal\ContaoRefereeHamburgBundle\Model\EventParticipiantModel;
use Teusal\ContaoRefereeHamburgBundle\Model\MemberGroupAssignmentMemberModel;
use Teusal\ContaoRefereeHamburgBundle\Model\MemberGroupNewsletterAssignmentModel;
use Teusal\ContaoRefereeHamburgBundle\Model\RefereeModel;
use Teusal\ContaoRefereeHamburgBundle\Model\SeasonModel;
use Teusal\ContaoRefereeHamburgBundle\Model\SportsFacilityClubAssignmentModel;
use Teusal\ContaoRefereeHamburgBundle\Model\SportsFacilityModel;
use Teusal\ContaoRefereeHamburgBundle\Model\SportsFacilityNumberModel;
use Teusal\ContaoRefereeHamburgBundle\Model\WebsiteDataReleaseModel;
use Teusal\ContaoRefereeHamburgBundle\Module\Email\ModuleClubEmail;
use Teusal\ContaoRefereeHamburgBundle\Module\Email\ModuleRefereeEmail;

/*
 * BACK END MENU STRUKTUR
 */
ArrayUtil::arrayInsert($GLOBALS['BE_MOD'], 0, [
    'bsa' => [
        'global_settings' => [
            'tables' => ['tl_bsa_global_settings'],
        ],
        'email_settings' => [
            'tables' => ['tl_settings'],
        ],
        'season' => [
            'tables' => ['tl_bsa_season'],
        ],
    ],
    'bsa_sportplatz' => [
        'sporthalle' => [
            'tables' => ['tl_bsa_sports_facility', 'tl_bsa_sports_facility_number'],
        ],
        'sportplatz' => [
            'tables' => ['tl_bsa_sports_facility', 'tl_bsa_sports_facility_number'],
        ],
        'sportplatz_zuordnung' => [
            'tables' => ['tl_bsa_sports_facility_club_assignment'],
        ],
    ],
    'bsa_verein_schiedsrichter' => [
        'verein' => [
            'tables' => ['tl_bsa_club'],
        ],
        'obmann' => [
            'tables' => ['tl_bsa_club_chairman'],
        ],
        'vereinslos' => [
            'tables' => ['tl_bsa_referee'],
        ],
        'schiedsrichter' => [
            'tables' => ['tl_bsa_referee'],
        ],
        'freigaben' => [
            'tables' => ['tl_bsa_website_data_release'],
        ],
        'schiedsrichter_historie' => [
            'tables' => ['tl_bsa_referee_history'],
        ],
    ],
    'bsa_member' => [
        'member_settings' => [
            'tables' => ['tl_bsa_member_settings'],
        ],
        'groups' => [
            'tables' => ['tl_member_group', 'tl_bsa_member_group_member_assignment', 'tl_bsa_member_group_newsletter_assignment'],
        ],
        'logins' => [
            'tables' => ['tl_member'],
            'createNeeded' => [BSAMember::class, 'createNeededLogins'],
        ],
        'external_logins' => [
            'tables' => $GLOBALS['BE_MOD']['accounts']['member']['tables'],
        ],
    ],
    'bsa_veranstaltung' => [
        'sitzung' => [
            'tables' => ['tl_bsa_event', 'tl_bsa_event_participiant'],
            'spiele' => [EventMatchOfficialsRegistration::class, 'execute'],
            'besucher' => [EventParticipiantRegistration::class, 'execute'],
            'import' => [EventParticipiantImport::class, 'execute'],
        ],
        'obleute' => [
            'tables' => ['tl_bsa_event', 'tl_bsa_event_participiant'],
            'besucher' => [EventParticipiantRegistration::class, 'execute'],
            'import' => [EventParticipiantImport::class, 'execute'],
        ],
        'training' => [
            'tables' => ['tl_bsa_event', 'tl_bsa_event_participiant'],
            'spiele' => [EventMatchOfficialsRegistration::class, 'execute'],
            'besucher' => [EventParticipiantRegistration::class, 'execute'],
            'import' => [EventParticipiantImport::class, 'execute'],
        ],
        'regelarbeit' => [
            'tables' => ['tl_bsa_event', 'tl_bsa_event_participiant'],
            'import' => [EventParticipiantImport::class, 'execute'],
        ],
        'coaching' => [
            'tables' => ['tl_bsa_event', 'tl_bsa_event_participiant'],
            'import' => [EventParticipiantImport::class, 'execute'],
        ],
        'lehrgang' => [
            'tables' => ['tl_bsa_event', 'tl_bsa_event_participiant'],
            'import' => [EventParticipiantImport::class, 'execute'],
        ],
        'helsen' => [
            'tables' => ['tl_bsa_event', 'tl_bsa_event_participiant'],
        ],
        'sonstige' => [
            'tables' => ['tl_bsa_event', 'tl_bsa_event_participiant'],
        ],
        'export_veranstaltungen' => [
            'callback' => 'ModuleExportVeranstaltungen',
        ],
    ],
    'bsa_mailing' => [
        'geburtstagsmail_settings' => [
            'tables' => ['tl_bsa_geburtstagsmail_setting'],
        ],
        'simple_mail' => [
            'callback' => ModuleRefereeEmail::class,
        ],
        'verein_mail' => [
            'callback' => ModuleClubEmail::class,
        ],
        'bsa_newsletter' => &$GLOBALS['BE_MOD']['content']['newsletter'],
    ],
    'bsa_ansetzungen' => [
        'bsa_tauschboerse_settings' => [
            'tables' => ['tl_bsa_tauschboerse_settings'],
        ],
        'bsa_export_ansetzungen_settings' => [
            'tables' => ['tl_bsa_export_ansetzungen_settings'],
        ],
        'bsa_spiele' => [
            'tables' => ['tl_bsa_spiel'],
        ],
        'edit_vereinsansetzungen' => [
            'callback' => 'ModuleEditVereinsansetzungen',
        ],
        'export_ansetzungen' => [
            'callback' => 'ModuleExportAnsetzungen',
        ],
        'export_ansetzungen_statistiken' => [
            'callback' => 'ModuleExportAnsetzungenStatistiken',
        ],
    ],
    'bsa_beobachtungen' => [
        'bsa_beobachtung_settings' => [
            'tables' => ['tl_bsa_beobachtung_settings'],
        ],
        'beobachtung' => [
            'tables' => ['tl_bsa_beobachtung'],
            'beo_erfassung' => ['ImportBeobachtung', 'executeImport'],
            'create_by_nr' => ['tl_bsa_beobachtung', 'askSpielnummer'],
            'export' => ['ExportBeobachtung', 'exportXLS'],
        ],
        'beobachtung_ausgang' => [
            'tables' => ['tl_bsa_beobachtung_ausgang'],
            'bestaetigung' => ['tl_bsa_beobachtung_ausgang', 'executeBestaetigung'],
            'beo_erfassung' => ['ImportBeobachtungAusgang', 'executeImport'],
        ],
        'export_beobachtungen' => [
            'callback' => 'ModuleExportBeobachtungen',
        ],
        'export_beobachter' => [
            'callback' => 'ModuleExportBeobachter',
        ],
    ],
    'bsa_dfbnet' => [
        'bsa_dfbnet_settings' => [
            'tables' => ['tl_bsa_dfbnet_settings'],
        ],
        'dfbnet_import_ansetzungen' => [
            'callback' => 'ModuleDFBnetAnsetzungenImport',
        ],
        'dfbnet_import_hallenrunden' => [
            'callback' => 'ModuleDFBnetHalleImport',
        ],
        'dfbnet_import_schiedsrichter' => [
            'callback' => 'ModuleDFBnetSchiedsrichterImport',
        ],
    ],
    'bsa_anwaerter' => [
        'bsa_anwaerterlehrgang_settings' => [
            'tables' => ['tl_bsa_anwaerterlehrgang_settings'],
        ],
        'bsa_lehrgang' => [
            'tables' => ['tl_bsa_lehrgang', 'tl_bsa_anwaerter'],
            'einladung_pdf' => ['tl_bsa_anwaerter', 'downloadEinladung'],
            'mail' => ['LehrgangSelectMailer', 'process'],
            'mail_bestaetigung' => ['LehrgangBestaetigungMailer', 'process'],
            'mail_bestaetigung_obmann' => ['LehrgangBestaetigungObmannMailer', 'process'],
            'mail_vorabinfo' => ['LehrgangVorabinfoMailer', 'process'],
            'mail_einladung' => ['LehrgangEinladungMailer', 'process'],
            'mail_einladung_all' => ['LehrgangEinladungAllMailer', 'process'],
            'export' => ['tl_bsa_anwaerter', 'exportXLS'],
            'select_edit_result' => ['LehrgangSelectEditor', 'process'],
            'edit_result' => ['LehrgangResultEditor', 'process'],
            'finish_result' => ['LehrgangFinishEditor', 'process'],
            'createGroup' => ['tl_bsa_anwaerter', 'createGroup'],
        ],
    ],
]);
ArrayUtil::arrayInsert(
    $GLOBALS['BE_MOD']['content'],
    0,
    [
        'bsa_calendar_settings' => [
            'tables' => ['tl_bsa_calendar_settings'],
        ],
    ]
);
$GLOBALS['BE_MOD']['content']['newsletter']['send'] = [Newsletter::class, 'send'];
/*
 * FRONT END MENU STRUKTUR
 */
ArrayUtil::arrayInsert(
    $GLOBALS['FE_MOD'],
    0,
    [
        'bsa' => [
            'ansetzungen_statistik' => 'ModuleAnsetzungenStatistik',
            'bsa_spiele' => 'ModuleAnsetzungenListe',
            'bsa_lehrgang' => 'ModuleLehrgangList',
            'bsa_lehrgang_add_anm' => 'ModuleLehrgangAddAnmeldung',
            'bsa_lehrgang_add_res' => 'ModuleLehrgangAddReservation',
            'bsa_lehrgang_upd_anw' => 'ModuleLehrgangEditAnwaerter',
            'bsa_lehrgang_del_anw' => 'ModuleLehrgangDeleteAnwaerter',
            'beobachtung_export' => 'ModuleExportBeobachtungenObleute',
            'beobachtung_show' => 'ModuleShowBeobachtung',
            'bsa_geburtstag' => 'ModuleBSAGeburtstag',
            'bsa_kontakt' => 'ModuleBSAKontakt',
            'bsa_member_group' => 'ModuleMemberGroup',
            'bsa_newsletter_send' => 'ModuleSendNewsletter',
            'bsa_tauschboerse_add' => 'ModuleBSATauschboerseAdd',
            'bsa_tauschboerse_edit' => 'ModuleBSATauschboerseEdit',
            'bsa_tauschboerse_show' => 'ModuleBSATauschboerseShow',
            'veranstaltung_export' => 'ModuleExportVeranstaltungenObleute',
            'bsa_schiedsrichter' => 'ModuleSchiedsrichter',
            'bsa_vereine' => 'ModuleVerein',
            'bsa_freigaben' => 'ModuleFreigabenBearbeitung',
        ],
    ]
);

/*
 * CONTENT ELEMENTS
 */
$GLOBALS['TL_CTE']['links']['backlink'] = 'ContentBacklink';

/*
 * HOOKS
 */
$GLOBALS['TL_HOOKS']['getSearchablePages'][] = ['ListingIndexer', 'getSearchablePages'];
$GLOBALS['TL_HOOKS']['getSearchablePages'][] = ['AnsetzungenStatistikIndexer', 'getSearchablePages'];
$GLOBALS['TL_HOOKS']['getSearchablePages'][] = ['LehrgangIndexer', 'getSearchablePages'];
$GLOBALS['TL_HOOKS']['getSearchablePages'][] = ['SchiedsrichterIndexer', 'getSearchablePages'];
$GLOBALS['TL_HOOKS']['getSystemMessages'][] = [Geburtstag::class, 'getSystemMessages'];
$GLOBALS['TL_HOOKS']['getSystemMessages'][] = [UserTransportValidator::class, 'getSystemMessages'];
$GLOBALS['TL_HOOKS']['removeRecipient'][] = ['SRHistory', 'unsubscribeNewsletterToSRHistory'];

/*
 * CRON JOBS
 */
// $GLOBALS['TL_CRON']['daily'][] = ['LehrgangReservierungDeleter', 'doDelete'];
// $GLOBALS['TL_CRON']['daily'][] = ['EventAdder', 'addTraining'];
// $GLOBALS['TL_CRON']['daily'][] = ['EventAdder', 'addSitzung'];
// $GLOBALS['TL_CRON']['daily'][] = ['AnsetzungenImportFileDeleter', 'processDelete'];
// $GLOBALS['TL_CRON']['daily'][] = ['SchiedsrichterImportFileDeleter', 'processDelete'];
$GLOBALS['TL_CRON']['daily'][] = [Geburtstag::class, 'sendInfoMail'];
$GLOBALS['TL_CRON']['daily'][] = [Geburtstag::class, 'sendMail'];
// $GLOBALS['TL_CRON']['daily'][] = ['BSAMemberGroup', 'updateOnBirthday'];

/*
 * MODELS
 */
$GLOBALS['TL_MODELS'] = [
    // 'tl_bsa_anwaerter' => BSAAnwaerterModel::class,
    // 'tl_bsa_beobachtung' => BSABeobachtungModel::class,
    // 'tl_bsa_beobachtung_ausgang' => BSABeobachtungAusgangModel::class,
    'tl_bsa_website_data_release' => WebsiteDataReleaseModel::class,
    'tl_bsa_member_group_member_assignment' => MemberGroupAssignmentMemberModel::class,
    'tl_bsa_member_group_newsletter_assignment' => MemberGroupNewsletterAssignmentModel::class,
    // 'tl_bsa_lehrgang' => BSALehrgangModel::class,
    // 'tl_bsa_member_group_newsletter_assignment' => BSANewsletterzuordnungModel::class,
    'tl_bsa_referee' => RefereeModel::class,
    // 'tl_bsa_referee_history' => BsaRefereeHistoryModel::class,
    'tl_bsa_season' => SeasonModel::class,
    // 'tl_bsa_spiel' => BSASpielModel::class,
    'tl_bsa_sports_facility' => SportsFacilityModel::class,
    'tl_bsa_sports_facility_number' => SportsFacilityNumberModel::class,
    'tl_bsa_sports_facility_club_assignment' => SportsFacilityClubAssignmentModel::class,
    // 'tl_bsa_tauschboerse' => BSATauschbörseModel::class,
    'tl_bsa_event_participiant' => EventParticipiantModel::class,
    'tl_bsa_event' => EventModel::class,
    'tl_bsa_club' => ClubModel::class,
    'tl_bsa_club_chairman' => ClubChairmanModel::class,
];
