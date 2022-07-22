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

namespace Qubus\Mail;

use Closure;
use Qubus\Config\ConfigContainer;
use Qubus\Exception\Exception;
use Qubus\Mail\Transport\Sendmail;
use Qubus\Mail\Transport\Smtp;

class Mailer
{
    /**
     * The currently active Swift Mailer driver.
     *
     * @var string
     */
    protected $driver;

    /**
     * Create a new Swift Mailer driver instance.
     *
     * @param string     $driver
     * @param ConfigContainer $config
     * @return Driver
     */
    public function factory(string $driver, ConfigContainer $config)
    {
        switch ($driver) {
            case 'smtp':
                $this->driver = new Smtp($config);
                break;
            case 'sendmail':
                $this->driver = new Sendmail($config);
                break;
            default:
                throw new Exception("Swift Mailer Driver {$driver} is not supported.");
        }

        return $this;
    }

    /**
     * Send message.
     *
     * @return Driver
     */
    public function send(?Closure $callback = null)
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
