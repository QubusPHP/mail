<?php

declare(strict_types=1);

namespace Qubus\Mail\Transport;

use Qubus\Config\Collection;
use Swift_SendmailTransport;

class Sendmail extends Driver
{
    /**
     * Register the Swift Mailer message and transport instances.
     *
     * @return void
     */
    public function __construct(Collection $config)
    {
        $this->transport = new Swift_SendmailTransport($config->getConfigKey('mailer.sendmail.command'));
    }
}
