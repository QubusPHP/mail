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

namespace Qubus\Mail;

use Closure;
use Qubus\Config\ConfigContainer;
use Qubus\Exception\Exception;
use Qubus\Mail\Transport\Driver;
use Qubus\Mail\Transport\Sendmail;
use Qubus\Mail\Transport\Smtp;

class Mailer
{
    /**
     * The currently active Swift Mailer driver.
     */
    protected Smtp|Sendmail $driver;

    /**
     * Create a new Swift Mailer driver instance.
     *
     * @param string $driver
     * @param ConfigContainer $config
     * @return Mailer
     * @throws Exception
     */
    public function factory(string $driver, ConfigContainer $config): static
    {
        $this->driver = match ($driver) {
            'smtp' => new Smtp($config),
            'sendmail' => new Sendmail($config),
            default => throw new Exception("Swift Mailer Driver {$driver} is not supported."),
        };

        return $this;
    }

    /**
     * Send message.
     *
     * @param Closure|null $callback
     * @return Driver
     */
    public function send(?Closure $callback = null): Driver
    {
        $instance = $this->driver;

        // If a closure is passed, the closure will be used to modify
        // the current message.
        if ($callback !== null) {
            $callback($instance);
        }

        // Now that the message has been prepared, send it.
        return $instance->send();
    }
}
