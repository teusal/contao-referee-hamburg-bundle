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
    /**
     * Login replacement definitions.
     *
     * @var array<array<string>>
     */
    protected static $login = [
        ['colspan', '<h1 style="margin-top: 15px; font-weight: bold;">Login Ersetzungen</h1>'],
        ['#LOGIN_USERNAME#', 'Der Benutzername des Login eines Schiedsrichter'],
        ['#LOGIN_NEW_PASSWORD#', 'Das neu erzeugte Passwort zu diesem Login'],
    ];

    /**
     * constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * setting the login informations.
     *
     * @param string $username The username
     * @param string $password The password
     */
    final public function setLogin($username, $password): void
    {
        $this->replacementValues['LOGIN']['USERNAME'] = $username;
        $this->replacementValues['LOGIN']['NEW_PASSWORD'] = $password;
    }

    /**
     * provides additional replacements used in subject.
     *
     * @return array<array<string>>
     */
    protected function getSubjectReferenceAddons()
    {
        return [];
    }

    /**
     * provides additional replacements used in body text.
     *
     * @return array<array<string>>
     */
    protected function getTextReferenceAddons()
    {
        return static::$login;
    }
}
