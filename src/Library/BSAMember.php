<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

namespace Teusal\ContaoRefereeHamburgBundle\Library;

use Contao\System;

/**
 * Class BSAMember
 */
class BSAMember extends System
{
	/**
	 * Konstruktor
	 */
	public function __construct() {
		parent::__construct();
		$this->loadDataContainer('tl_member');
		$this->import('tl_member');
		$this->import('Database');
		$this->import('BackendUser', 'User');
		$this->import('MemberCreator');
		$this->import('BSAMemberGroup');
	}

	/**
	 * Liefert das Array mit angelegten Logins
	 */
	public function executeSubmitSchiedsrichter ($var) {
		$intId = 0;
		if($var instanceof \DataContainer) {
			$intId = $var->id;
		}
		else {
			$intId = intval($var);
		}

		// Den Schiedsrichter laden
		$objSR = \BsaSchiedsrichterModel::findSchiedsrichter($intId);

		if(!isset($objSR)) {
			throw new \Exception('Schiedsrichter zu ID '.$intId.' nicht gefunden!');
		}

		// Die personenbezogenen Daten an den Login tl_member �bernehmen
		$this->setPersonalData($objSR);

		// nur Obleute oder aktive Schiedsrichter sollen automatisch verwaltete Logins haben
		$needsLogin = $this->needsLogin($objSR->id);

		// existierenden Login tl_member laden
		$objMember = \MemberModel::findOneBy('schiedsrichter', $objSR->id);

		if($objSR->__get('deleted') || !$needsLogin) {
			// Mitglied deaktivieren
			if(isset($objMember) && !$objMember->__get('disable')) {
				// Login deaktivieren
				$this->tl_member->toggleVisibility($objMember->id, false);
				// aus allen Gruppen entfernen
				$this->BSAMemberGroup->deleteFromGroups($objSR->id);
			}
		}
		else {
			if(!isset($objMember) && $needsLogin) {
				// Login anlegen
				$this->MemberCreator->createLoginIfNeeded($objSR->id);
				if($GLOBALS['TL_CONFIG']['bsa_import_login_send_mail']) {
					$this->MemberCreator->sendNotificationMails($objSR->id);
				}

				// Den neuen Login laden
				$objMember = \MemberModel::findOneBy('schiedsrichter', $objSR->id);
			}

			if(isset($objMember) && $objMember->__get('disable')) {
				// Login aktivieren
				$this->tl_member->toggleVisibility($objMember->id, true);
			}

			// Die Automatik-Grupen verwalten
			$this->BSAMemberGroup->handleAutomaticGroups($objSR->id);
		}
	}

	/**
	 * Setzt Vor-, Nachname und E-Mail am Login tl_member
	 */
	private function needsLogin($intID) {
		return \BsaSchiedsrichterModel::isVereinsschiedsrichter($intID) || \BsaVereinObmannModel::isVereinsobmann($intID);
	}

	/**
	 * Setzt Vor-, Nachname und E-Mail am Login tl_member
	 */
	private function setPersonalData($objSR) {
		$objMember = \MemberModel::findOneBy('schiedsrichter', $objSR->id);
		if(isset($objMember)) {
			$objMember->__set('firstname', $objSR->__get('vorname'));
			$objMember->__set('lastname', $objSR->__get('nachname'));
			if(strlen($objSR->__get('email'))) {
				$objMember->__set('email', $objSR->__get('email'));
			}
			$objMember->save();
		}
	}

	/**
	 * Liefert das Array mit angelegten Logins
	 */
	public function getCreatedLogins() {
		return $this->MemberCreator->getCreatedLogins();
	}

	/**
	 * Pr�ft alle Schiedsrichter und vereinslose Personen ob sie einen neuen Login ben�tigen und legt den Zugang an.
	 */
	public function createNeededLogins() {
		if(\Input::get('key') != 'createNeeded') {
			return;
		}

		$redirectUrl = str_replace('&key=createNeeded', '', \Environment::get('request'));

		if(!$GLOBALS['TL_CONFIG']['bsa_import_login_create']) {
			\Message::addError($GLOBALS['TL_LANG']['ERROR']['login_create_inactive']);
			$this->redirect($redirectUrl);
		}

		$arrSR = $this->Database->prepare('SELECT tl_bsa_schiedsrichter.id, tl_bsa_schiedsrichter.name_rev FROM tl_bsa_schiedsrichter LEFT JOIN tl_member ON tl_bsa_schiedsrichter.id = tl_member.schiedsrichter WHERE tl_bsa_schiedsrichter.email!=? AND tl_bsa_schiedsrichter.deleted=? AND tl_member.id IS NULL ORDER BY name_rev')
		                        ->execute('', '')
		                        ->fetchAllAssoc();

		if(empty($arrSR)) {
			\Message::addInfo($GLOBALS['TL_LANG']['INFO']['login_create_not_required']);
			$this->redirect($redirectUrl);
		}

		$arrLoginNames = array();
		foreach($arrSR AS $sr) {
			$this->executeSubmitSchiedsrichter($sr['id']);
			if(is_array($this->MemberCreator->getCreatedLogins()) && array_key_exists($sr['id'], $this->MemberCreator->getCreatedLogins())) {
				$arrLoginNames[] = $sr['name_rev'];
				if($GLOBALS['TL_CONFIG']['bsa_import_login_send_mail']) {
					$this->MemberCreator->sendNotificationMails($sr['id']);
				}
			}
			else {
				\Message::addError(sprintf($GLOBALS['TL_LANG']['ERROR']['login_create_error'], $sr['name_rev']));
			}
		}

		\Message::addConfirmation(sprintf($GLOBALS['TL_LANG']['INFO']['login_created'], count($arrLoginNames), implode("; ", $arrLoginNames)));
		$this->redirect($redirectUrl);
	}
};
?>
