<?php

/**
 * Qubus\Mail
 *
 * @link       https://github.com/QubusPHP/mail
 * @copyright  2020 Joshua Parker <josh@joshuaparker.blog>
 * @license    https://opensource.org/licenses/mit-license.php MIT License
 *
 * @since      1.0.0
 */

declare(strict_types=1);

namespace Qubus\Mail\Transport;

use Qubus\Config\ConfigContainer;
use Swift_SendmailTransport;

class Sendmail extends Driver
{
    /**
     * Register the Swift Mailer message and transport instances.
     *
     * @return void
     */
    public function __construct(ConfigContainer $config)
    {
        $this->transport = new Swift_SendmailTransport($config->getConfigKey('mailer.sendmail.command'));
    }
}
