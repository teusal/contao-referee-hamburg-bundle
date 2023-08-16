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
 * Class BSAEmail.
 */
class BSAEmail extends AbstractEmail
{
    /**
     * Konstruktor.
     */
    public function __construct()
    {
        parent::__construct();
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
        return [];
    }
}
