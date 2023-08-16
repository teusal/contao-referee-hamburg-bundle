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
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * This class filters transports while adding by backend users emailaddress.
 */
class AvailableTransports extends \Contao\CoreBundle\Mailer\AvailableTransports
{
    private const USER_TRANSPORT = 'user_transport';
    private const SYSTEM_TRANSPORT = 'system_transport';

    /**
     * constructor.
     */
    /**
     * @phpstan-ignore-next-line
     */
    public function __construct(TranslatorInterface $translator = null)
    {
        // unset translator to prevent messages of missing translations
        parent::__construct(null);
    }

    /**
     * checks whether the tranport exists by name or not.
     *
     * @param string $name the name of the transport
     *
     * @return bool true, if there is a known configuration in the system for a specified name
     */
    public function existsTransport(string $name): bool
    {
        return \array_key_exists($name, $this->getTransports());
    }

    /**
     * Returns the available transports as options suitable for widgets.
     *
     * @phpstan-ignore-next-line
     *
     * @return array<string, array<string, string>>
     */
    public function getTransportOptions(): array
    {
        $options = [
            self::USER_TRANSPORT => [],
            self::SYSTEM_TRANSPORT => [],
        ];

        foreach (parent::getTransportOptions() as $name => $label) {
            if (false === strpos($name, '@')) {
                $key = self::SYSTEM_TRANSPORT;
            } else {
                $key = self::USER_TRANSPORT;
            }
            $options[$key][$name] = $label;
        }

        return $options;
    }

    /**
     * Returns the available transports by system as options suitable for widgets.
     *
     * @return array<string, array<string, string>>
     */
    public function getSystemTransportOptions(): array
    {
        $options = $this->getTransportOptions();
        unset($options[self::USER_TRANSPORT]);

        return $options;
    }

    /**
     * Returns the available transports by user as options suitable for widgets.
     *
     * @return array<string, array<string, string>>
     */
    public function getUserTransportOptions(): array
    {
        $options = $this->getTransportOptions();
        unset($options[self::SYSTEM_TRANSPORT]);

        return $options;
    }

    /**
     * Returns the available transports by system and the cuttent logged in backend user as options suitable for widgets.
     *
     * @return array<string, array<string, string>>
     */
    public function getSystemAndBackendUserTransportOptions(): array
    {
        if (!\defined('TL_MODE') || TL_MODE !== 'BE') {
            return $this->getSystemTransportOptions();
        }

        $user = BackendUser::getInstance();

        $options = $this->getTransportOptions();

        foreach (array_keys($options[self::USER_TRANSPORT]) as $name) {
            if ($name !== $user->email) {
                unset($options[self::USER_TRANSPORT][$name]);
            }
        }

        return $options;
    }

    /**
     * checks whether the transport is a system transport or not.
     *
     * @param string $name The name of the transport
     *
     * @return bool true if it is a system transport
     */
    public function isSystemTransport(string $name): bool
    {
        return false === strpos($name, '@');
    }

    /**
     * checks whether the transport is an user transport or not.
     *
     * @param string $name the name of the transport
     *
     * @return bool true if it is an user transport
     */
    public function isUserTransport(string $name): bool
    {
        return !$this->isSystemTransport($name);
    }
}
