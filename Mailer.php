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

use PHPMailer\PHPMailer\PHPMailer;
use Qubus\Exception\Exception;

interface Mailer extends Headers, Addresses, Transport
{
    /**
     * Sets message type to HTML or plaintext.
     *
     * @param bool $isHtml True for HTML mode.
     * @return static
     */
    public function withHtml(bool $isHtml = false): static;

    /**
     * Add an attachment from a path on the filesystem.
     *
     * @param string $path Path to the attachment
     * @param string $name Overrides the attachment name
     * @param string $encode File encoding (see $Encoding)
     * @param string $type MIME type, e.g. `image/jpeg`; determined automatically from $path if not specified
     * @param string $disposition Disposition to use.
     * @return static
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function withAttachment(
        string $path,
        string $name = '',
        string $encode = PHPMailer::ENCODING_BASE64,
        string $type = '',
        string $disposition = 'attachment'
    ): static;

    /**
     * Set Mail Body configuration
     *
     * Format email message Body, this can be an external template html file with a copy
     * of a plain-text like template.txt or HTML/plain-text string.
     *
     * This method can be used by passing a template file HTML name and an associative array
     * with the values that can be parsed into the file HTML by the key KEY_NAME found in your
     * array to your HTML {{KEY_NAME}}.
     *
     * Other optional ways to format the mail body is available like instead of a template the
     * param $data can be set as an array or string, but param $options['template_name'] must be equal to null.
     *
     * @param array|string $data Contain the values to be parsed in mail body.
     * @param array $options Array of options.
     * @return static
     */
    public function withBody(string|array $data, array $options = []): static;

    /**
     * The plain-text message body.
     *
     * @param string $message
     * @return static
     */
    public function withAltBody(string $message = ''): static;

    /**
     * Save message as eml file.
     *
     * @return bool True if saved successfully, false otherwise.
     * @throws Exception
     */
    public function save(): bool;

    /**
     * Create a message and send it.
     * Uses the sending method specified by $Mailer.
     *
     * @return bool false on error.
     * @throws \PHPMailer\PHPMailer\Exception|Exception
     */
    public function send(): bool;
}
