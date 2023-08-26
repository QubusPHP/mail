<?php

/**
 * Qubus\Mail
 *
 * @link       https://github.com/QubusPHP/mail
 * @copyright  2020
 * @author     Joshua Parker <joshua@joshuaparker.dev>
 * @license    https://opensource.org/licenses/mit-license.php MIT License
 */

declare(strict_types=1);

namespace Qubus\Mail\Transport;

use Qubus\Config\ConfigContainer;
use Qubus\Exception\Exception;
use Swift_SmtpTransport;

class Smtp extends Driver
{
    /**
     * Register the Swift Mailer message and transport instances.
     *
     * @return void
     * @throws Exception
     */
    public function __construct(ConfigContainer $config)
    {
        $this->transport = (new Swift_SmtpTransport())
            ->setHost($config->getConfigKey('mailer.smtp.host'))
            ->setPort($config->getConfigKey('mailer.smtp.port'))
            ->setEncryption($config->getConfigKey('mailer.smtp.encryption'))
            ->setUsername($config->getConfigKey('mailer.smtp.username'))
            ->setPassword($config->getConfigKey('mailer.smtp.password'))
            ->setAuthMode($config->getConfigKey('mailer.smtp.authmode'));
    }
}
