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

    protected function getSenderNameReferenceAddons()
    {
        return [];
    }

    protected function getSubjectReferenceAddons()
    {
        return [];
    }

    protected function getTextReferenceAddons()
    {
        return [];
    }
}
