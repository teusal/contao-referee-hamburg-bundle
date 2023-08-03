<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

namespace Teusal\ContaoRefereeHamburgBundle\Library\Email;

/**
 * Class LoginEmail.
 */
class LoginEmail extends AbstractEmail
{
    protected static $login = [
        ['colspan', '<h1 style="margin-top: 15px; font-weight: bold;">Login Ersetzungen</h1>'],
        ['#LOGIN_USERNAME#', 'Der Benutzername des Login eines Schiedsrichter'],
        ['#LOGIN_NEW_PASSWORD#', 'Das neu erzeugte Passwort zu diesem Login'],
    ];

    /**
     * Konstruktor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    final public function setLogin($username, $password): void
    {
        $this->replacementValues['LOGIN']['USERNAME'] = $username;
        $this->replacementValues['LOGIN']['NEW_PASSWORD'] = $password;
    }

    protected function getSubjectReferenceAddons()
    {
        return [];
    }

    protected function getTextReferenceAddons()
    {
        return static::$login;
    }
}
