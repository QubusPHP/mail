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
use PHPMailer\PHPMailer\PHPMailer;

interface Headers
{
    /**
     * The envelope sender of the message.
     *
     * This will usually be turned into a Return-Path header by the receiver,
     * and is the address that bounces will be sent to.
     *
     * @param string $sender
     * @return static
     */
    public function withSender(string $sender = ''): static;

    /**
     * The Subject of the message.
     *
     * @param string $subject
     * @return static
     */
    public function withSubject(string $subject = ''): static;

    /**
     * Email priority.
     *
     * Options: null (default), 1 = High, 3 = Normal, 5 = low.
     * When null, the header is not set at all.
     *
     * @param int|null $priority
     * @return static
     */
    public function withPriority(?int $priority = null): static;

    /**
     * The character set of the message.
     *
     * @param string $charset The character set of the message.
     * @return static
     */
    public function withCharset(string $charset = PHPMailer::CHARSET_ISO88591): static;

    /**
     * Add a custom header.
     *
     * $name value can be overloaded to contain
     * both header name and value (name:value).
     *
     * @param string $name Custom header name.
     * @param string|null $value Header value.
     * @return static
     * @throws Exception
     */
    public function withCustomHeader(string $name, ?string $value = null): static;

    /**
     * The MIME Content-type of the message.
     *
     * @param string $contentType
     * @return static
     */
    public function withContentType(string $contentType = PHPMailer::CONTENT_TYPE_PLAINTEXT): static;

    /**
     * What to put in the X-Mailer header.
     *
     * Options: An empty string for PHPMailer default,
     * whitespace/null for none, or a string to use.
     *
     * @param string|null $xmailer
     * @return static
     */
    public function withXMailer(?string $xmailer = ''): static;
}
