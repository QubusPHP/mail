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

use PHPMailer\PHPMailer\Exception;

interface Addresses
{
    /**
     * Set the From and FromName properties.
     *
     * @param string $address
     * @param string $name
     * @param bool $auto Whether to also set the Sender address, defaults to true.
     * @return static
     * @throws Exception
     */
    public function withFrom(string $address, string $name = '', bool $auto = true): static;

    /**
     * Add a "To" address.
     *
     * @param string|array $address The email address(es) to send to.
     * @return static
     * @throws Exception
     */
    public function withTo(string|array $address): static;

    /**
     * Add a "CC" address.
     *
     * @param string|array $address The email address(es) to send to.
     * @return static
     * @throws Exception
     */
    public function withCc(string|array $address): static;

    /**
     * Add a "BCC" address.
     *
     * @param string|array $address The email address(es) to send to.
     * @return static
     * @throws Exception
     */
    public function withBcc(string|array $address): static;

    /**
     * Add a "Reply-To" address.
     *
     * @param string|array $address The email address(es) to reply to.
     * @return static
     * @throws Exception
     */
    public function withReplyTo(string|array $address): static;
}
