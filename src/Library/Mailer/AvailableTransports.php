<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

namespace Teusal\ContaoRefereeHamburgBundle\Library\Mailer;

use Contao\BackendUser;
use Contao\CoreBundle\Mailer\TransportConfig;

/**
 * This class filters transports while adding by backend users emailaddress.
 */
class AvailableTransports extends \Contao\CoreBundle\Mailer\AvailableTransports
{
    /**
     * @var array<TransportConfig>
     */
    private array $userTransports = [];
    /**
     * @var array<TransportConfig>
     */
    private array $systemTransports = [];

    public function addTransport(TransportConfig $transportConfig): void
    {
        // add to all known system transports
        if (false === strpos($transportConfig->getName(), '@')) {
            $this->systemTransports[$transportConfig->getName()] = $transportConfig;
        } else {
            $this->userTransports[$transportConfig->getName()] = $transportConfig;
        }
        // filter transport by user users email
        if (!empty(BackendUser::getInstance()->email) && BackendUser::getInstance()->email === $transportConfig->getName()) {
            parent::addTransport($transportConfig);
        } else {
            parent::addTransport($transportConfig);
        }
    }

    /**
     * returns true, if there is a known configuration in the system for a specified name.
     */
    public function existsTransportByEmail(string $email): bool
    {
        return \array_key_exists($email, $this->userTransports) || \array_key_exists($email, $this->systemTransports);
    }

    /**
     * Returns the available transports as options suitable for widgets.
     *
     * @return array<string, string>
     */
    public function getSystemTransportOptions(): array
    {
        $options = [];

        foreach ($this->systemTransports as $name => $config) {
            $label = null !== $this->translator ? $this->translator->trans($name, [], 'mailer_transports') : $name;

            if (null !== ($from = $config->getFrom())) {
                $label .= ' ('.$from.')';
            }

            $options[$name] = htmlentities($label);
        }

        return $options;
    }

    /**
     * Returns the available transports as options suitable for widgets.
     *
     * @return array<string, string>
     */
    public function getUserTransportOptions(): array
    {
        $options = [];

        foreach ($this->userTransports as $name => $config) {
            $label = null !== $this->translator ? $this->translator->trans($name, [], 'mailer_transports') : $name;

            if (null !== ($from = $config->getFrom())) {
                $label .= ' ('.$from.')';
            }

            $options[$name] = htmlentities($label);
        }

        return $options;
    }

    /**
     * Returns the available transports as options suitable for widgets.
     *
     * @return array<string, array<string, string>>
     */
    public function getAllTransportOptions(): array
    {
        return [
            'system_transports' => $this->getSystemTransportOptions(),
            'user_transports' => $this->getUserTransportOptions(),
        ];
    }
}
