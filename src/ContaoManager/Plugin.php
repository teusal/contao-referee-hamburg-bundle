<?php

declare(strict_types=1);

/*
 * This file is part of [package name].
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

namespace Teusal\ContaoRefereeHamburgBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Teusal\ContaoRefereeHamburgBundle\ContaoRefereeHamburgBundle;

class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(ContaoRefereeHamburgBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class]),
        ];
    }
}
