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

class MatchesEmail extends AbstractEmail
{
    /**
     * Matches replacement definitions.
     *
     * @var array<array<string>>
     */
    protected static $matches = [
        ['colspan', '<h1 style="margin-top: 15px; font-weight: bold;">Daten des exportierten Ansetzungen</h1>'],
        ['#ANSETZUNGEN_DATEINAME#', 'Der Dateiname der exportierten Ansetzungen'],
    ];

    /**
     * constructor.
     */
    public function __construct()
    {
        parent::__construct();
        parent::$referee = [];
        parent::$club = [];
    }

    /**
     * setting the filename.
     *
     * @param string $strFilename The filename
     */
    final public function setFilename($strFilename): void
    {
        $this->replacementValues['ANSETZUNGEN']['DATEINAME'] = $strFilename;
    }

    public function sendTo(): bool
    {
        throw new \Exception('Don\'t send this email');
    }

    /**
     * provides additional replacements used in subject.
     *
     * @return array<array<string>>
     */
    protected function getSubjectReferenceAddons()
    {
        return static::$matches;
    }

    /**
     * provides additional replacements used in body text.
     *
     * @return array<array<string>>
     */
    protected function getTextReferenceAddons()
    {
        return array_merge(static::$matches);
    }
}
