<?php

/**
 * Qubus\Mail
 *
 * @link       https://github.com/QubusPHP/mail
 * @copyright  2023
 * @author     Joshua Parker <joshua@joshuaparker.dev>
 * @license    https://opensource.org/licenses/mit-license.php MIT License
 */

declare(strict_types=1);

namespace Qubus\Mail;

use Qubus\Exception\Exception;

interface Transport
{
    /**
     * Send messages using SMTP.
     *
     * @return static
     * @throws Exception
     */
    public function withSmtp(): static;

    /**
     * Send messages using PHP's native mail() function.
     *
     * @return static
     */
    public function withMail(): static;

    /**
     * Send messages using Sendmail.
     *
     * @return static
     */
    public function withSendmail(): static;

    /**
     * Send messages using qmail.
     *
     * @return static
     */
    public function withQmail(): static;
}
