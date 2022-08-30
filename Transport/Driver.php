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

use stdClass;
use Swift_Attachment;
use Swift_Mailer;
use Swift_Message;
use Swift_Mime_Header;
use Swift_OutputByteStream;
use Swift_Transport;

use function array_diff;
use function array_merge;
use function call_user_func_array;
use function file_exists;
use function file_get_contents;
use function implode;
use function in_array;
use function is_array;
use function is_int;
use function preg_match;
use function preg_replace;
use function sprintf;
use function str_replace;

class Driver
{
    /**
     * The instance of the SwiftMailer message.
     */
    protected ?Swift_Message $swift = null;

    /**
     * The instance of the SwiftMailer transport.
     *
     * @var mixed
     */
    public ?Swift_Transport $transport = null;

    /**
     * The instance of the SwiftNailer mailer.
     */
    protected ?Swift_Mailer $mailer = null;

    /**
     * The email body.
     *
     * @var mixed $body
     */
    public mixed $body;

    /**
     * The number of successfully sent emails.
     */
    public int $result;

    /**
     * The email addresses that the message will be sent to.
     *
     * @var array $emails
     */
    public array $emails = [];

    /**
     * The email addresses that did not successfully receive the message.
     *
     * @var array $failed
     */
    public array $failed = [];

    public ?string $templatePath = null;

    public function __construct()
    {
        $this->body = new stdClass();
    }

    /**
     * Prepare the Swift Message class
     */
    public function swift(): Swift_Message
    {
        if (null === $this->swift) {
            $this->swift = new Swift_Message();
        }

        return $this->swift;
    }

    /**
     * Prepare the Swift Mailer class
     */
    public function mailer(): Swift_Mailer
    {
        if (null === $this->mailer) {
            $this->mailer = new Swift_Mailer($this->transport);
        }

        return $this->mailer;
    }

    /**
     * Set the HTML content type.
     *
     * @return Driver
     */
    public function html(bool $useHtml = true): static
    {
        $contentType = $useHtml ? 'text/html' : 'text/plain';

        $this->swift()->setContentType($contentType);

        return $this;
    }

    /**
     * Set the mail charset.
     *
     * @return Driver
     */
    public function charset(string $encoding = 'utf-8'): static
    {
        $this->swift()->setCharset($encoding);

        return $this;
    }

    /**
     * Set the subject.
     *
     * @param string $subject
     * @return Driver
     */
    public function subject($subject): static
    {
        $this->swift()->setSubject($subject);

        return $this;
    }

    /**
     * Add an email address to the from list.
     *
     * @return Driver
     */
    public function from(string|array $email, ?string $name = null): static
    {
        if (! is_array($email)) {
            $this->swift()->addFrom($email, $name);
        } else {
            $this->swift()->setFrom($email, $name);
        }

        return $this;
    }

    /**
     * Add an email address to reply to.
     *
     * @return Driver
     */
    public function reply(string|array $email, ?string $name = null): static
    {
        $this->swift()->setReplyTo($email, $name);

        return $this;
    }

    /**
     * Add an email address to the list of emails to send the email to.
     *
     * @return Driver
     */
    public function to(string|array $email, ?string $name = null): static
    {
        if (! is_array($email)) {
            $this->swift()->addTo($email, $name);

            $this->emails[] = $email;
        } else {
            foreach ($email as $key => $value) {
                if (is_int($key)) {
                    $this->emails[] = $value;

                    $this->swift()->addTo($value, null);
                } else {
                    $this->swift()->addTo($key, $value);

                    $this->emails[] = $key;
                }
            }
        }

        return $this;
    }

    /**
     * Add an email address to the list of emails the email should be copied to.
     *
     * @return Driver
     */
    public function cc(string|array $email, ?string $name = null): static
    {
        if (! is_array($email)) {
            $this->swift()->addCc($email, $name);

            $this->emails[] = $email;
        } else {
            foreach ($email as $key => $value) {
                if (is_int($key)) {
                    $this->emails[] = $value;

                    $this->swift()->addCc($value, null);
                } else {
                    $this->swift()->addCc($key, $value);

                    $this->emails[] = $key;
                }
            }
        }

        return $this;
    }

    /**
     * Add an email address to the list of emails the email should be
     * blind-copied to.
     *
     * @return Driver
     */
    public function bcc(string|array $email, ?string $name = null): static
    {
        if (! is_array($email)) {
            $this->swift()->addBcc($email, $name);

            $this->emails[] = $email;
        } else {
            foreach ($email as $key => $value) {
                if (is_int($key)) {
                    $this->emails[] = $value;

                    $this->swift()->addBcc($value, null);
                } else {
                    $this->swift()->addBcc($key, $value);

                    $this->emails[] = $key;
                }
            }
        }

        return $this;
    }

    /**
     * Set the template path.
     */
    public function templatePath(string $path): string
    {
        return $this->templatePath;
    }

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
     * @param array|string $data    Contain the values to be parsed in mail body.
     * @param array        $options Array of options.
     * @return string
     */
    public function body(string|array $data, array $options = []): string
    {
        $defaultOptions = [
            'template_path'    => null,
            'template_name'    => null,
            'template_options' => [],
        ];

        $options = array_merge($defaultOptions, $options);

        $templatePath = null === $this->templatePath ? $options['template_path'] : $this->templatePath;

        if (! is_array($data) && $options['template_name'] === null) {
            return $this->body = $data;
        } elseif (is_array($data) && $options['template_name'] === null) {
            return $this->body = implode('<br>  ', $data);
        } else {
            $templatePath = $options['template_path']
            ? $options['template_path'] . '/' . $options['template_name']
            : $options['template_name'];

            if (! file_exists($templatePath)) {
                //'none template message found in: ' .$options['template_name'];
                return $this->body = sprintf('Template (%s) not found.', $options['template_name']);
            } else {
                $this->body = file_get_contents($templatePath);

                if (preg_match('/\.txt$/', $options['template_name'])) {
                    $this->body = $this->body;
                } else {
                    $templateTextPath = preg_replace('/\.[html|php|htm|phtml]+$/', '.txt', $templatePath);

                    if (file_exists($templateTextPath)) {
                        $this->body = file_get_contents($templateTextPath);
                    }
                }
            }

            $data = is_array($data) ? $data : [$data];
            $data = array_merge($data, $options['template_options']);

            foreach ($data as $key => $value) {
                $this->body = str_replace("{{" . $key . "}}", $value, $this->body);
            }
        }
    }

    /**
     * Prepare the body and send it to the Swiftmailer.
     *
     * @return void
     */
    protected function prepareBody(): void
    {
        $body = $this->body;

        $this->swift()->setBody($body);
    }

    /**
     * Attach a file to the email.
     *
     * @param Swift_OutputByteStream|string $fileData
     * @param string $fileName
     * @param string $mimeType
     * @return Driver
     */
    public function attach(Swift_OutputByteStream|string $fileData, string $fileName = '', string $mimeType = ''): static
    {
        if (file_exists($fileData)) {
            $attachment = Swift_Attachment::fromPath($fileData);
            if ($fileName !== '') {
                $attachment->setFilename($fileName);
            }
            if ($mimeType !== '') {
                $attachment->setContentType($mimeType);
            }
        } else {
            $attachment = new Swift_Attachment($fileData, $fileName, $mimeType);
        }

        $this->swift()->attach($attachment);

        return $this;
    }

    /**
     * Set a custom header
     *
     * @return Swift_Mime_Header|static|null
     */
    public function header(string $header, ?string $value = null): static|null|Swift_Mime_Header
    {
        $headers = $this->swift()->getHeaders();

        if ($value === null) {
            return $headers->get($header);
        } else {
            $headers->addTextHeader($header, $value);

            return $this;
        }
    }

    /**
     * Send the email.
     *
     * @return Driver
     */
    public function send(): static
    {
        // Prepare the body before sending.
        $this->prepareBody();

        // Send the email.
        $this->result = $this->mailer()->send($this->swift(), $this->failed);

        // Clear the Swift_Message instance after email has been sent.
        $this->swift = null;

        return $this;
    }

    /**
     *  Get the number of successfully sent emails.
     */
    public function result(): ?int
    {
        return $this->result;
    }

    /**
     * Check if at least one email was sent. If an email address is provided,
     * this will check if the email was successfully sent to that email
     * address
     */
    public function isSent(?string $email = null): bool
    {
        if (null !== $email) {
            $sent = array_diff($this->emails, $this->failed);

            return in_array($email, $sent);
        } elseif (null !== $this->result) {
            return $this->result > 0;
        }

        return false;
    }

    /**
     * Call a Swiftmailer method.
     *
     * @param string $name
     * @param array $arguments
     * @return Driver
     */
    public function __call(string $name, array $arguments)
    {
        call_user_func_array([$this->swift(), $name], $arguments);

        return $this;
    }
}
