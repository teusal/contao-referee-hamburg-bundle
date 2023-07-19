<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

namespace Teusal\ContaoRefereeHamburgBundle\Tests;

use PHPUnit\Framework\TestCase;
use Teusal\ContaoRefereeHamburgBundle\ContaoRefereeHamburgBundle;

class ContaoRefereeHamburgBundleTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $bundle = new ContaoRefereeHamburgBundle();

        $this->assertInstanceOf('Teusal\ContaoRefereeHamburgBundle\ContaoRefereeHamburgBundle', $bundle);
    }
}
