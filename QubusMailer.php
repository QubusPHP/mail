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
use Qubus\Config\ConfigContainer;
use stdClass;

class QubusMailer extends PHPMailer implements Mailer
{
    protected ?string $templatePath = null;

    public function __construct(protected ConfigContainer $config)
    {
        parent::__construct(exceptions: true);

        $this->Body = new stdClass();
    }

    /**
     * Set the From and FromName properties.
     *
     * @param string $address
     * @param string $name
     * @param bool $auto Whether to also set the Sender address, defaults to true.
     *
     * @return QubusMailer
     * @throws Exception
     */
    public function withFrom(string $address, string $name = '', bool $auto = true): self
    {
        $new = clone $this;
        $new->setFrom($address, $name, $auto);

        return $new;
    }

    /**
     * Add a "To" address.
     *
     * @param string|array $address The email address(es) to send to.
     *
     * @return QubusMailer
     * @throws Exception
     */
    public function withTo(string|array $address): self
    {
        $new = clone $this;
        $new->clearAddresses();
        $new->withAddresses(type: 'to', addresses: $address);

        return $new;
    }

    /**
     * Add a "CC" address.
     *
     * @param string|array $address The email address(es) to send to.
     * @return QubusMailer
     * @throws Exception
     */
    public function withCc(string|array $address): self
    {
        $new = clone $this;
        $new->clearCCs();
        $new->withAddresses(type: 'cc', addresses: $address);

        return $new;
    }

    /**
     * Add a "BCC" address.
     *
     * @param string|array $address The email address(es) to send to.
     * @return QubusMailer
     * @throws Exception
     */
    public function withBcc(string|array $address): self
    {
        $new = clone $this;
        $new->clearBCCs();
        $new->withAddresses(type: 'bcc', addresses: $address);

        return $new;
    }

    /**
     * Add a "Reply-To" address.
     *
     * @param string|array $address The email address(es) to reply to.
     * @return QubusMailer
     * @throws Exception
     */
    public function withReplyTo(string|array $address): self
    {
        $new = clone $this;
        $new->clearReplyTos();
        $new->withAddresses(type: 'Reply-To', addresses: $address);

        return $new;
    }

    /**
     * @param string $type Type of the recipient (to, cc, bcc or Reply-To)
     * @param mixed $addresses Email address or array of email addresses.
     * @return bool True on success, false if addresses not valid.
     * @throws Exception
     */
    private function withAddresses(string $type, string|array $addresses): bool
    {
        if (!is_array($addresses)) {
            $addresses = (array) $addresses;
        }

        $result = true;

        foreach ($addresses as $key => $value) {
            if (is_int($key)) {
                $r = $this->addAnAddress($type, $value);
            } else {
                $r = $this->addAnAddress($type, $key, $value);
            }
            if ($result && !$r) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * The envelope sender of the message.
     *
     * This will usually be turned into a Return-Path header by the receiver,
     * and is the address that bounces will be sent to.
     *
     * @param string $sender
     * @return QubusMailer
     */
    public function withSender(string $sender = ''): self
    {
        $new = clone $this;
        $new->Sender = $sender;

        return $new;
    }

    /**
     * The Subject of the message.
     *
     * @param string $subject
     * @return QubusMailer
     */
    public function withSubject(string $subject = ''): self
    {
        $new = clone $this;
        $new->Subject = $subject;

        return $new;
    }

    /**
     * Sets message type to HTML or plaintext.
     *
     * @param bool $isHtml True for HTML mode.
     * @return QubusMailer
     */
    public function withHtml(bool $isHtml = false): self
    {
        $new = clone $this;
        $new->isHTML($isHtml);

        return $new;
    }

    /**
     * Email priority.
     *
     * Options: null (default), 1 = High, 3 = Normal, 5 = low.
     * When null, the header is not set at all.
     *
     * @param int|null $priority
     * @return QubusMailer
     */
    public function withPriority(?int $priority = null): self
    {
        $new = clone $this;
        $new->Priority = $priority;

        return $new;
    }

    /**
     * The character set of the message.
     *
     * @param string $charset
     * @return QubusMailer
     */
    public function withCharset(string $charset = self::CHARSET_ISO88591): self
    {
        $new = clone $this;
        $new->CharSet = $charset;

        return $new;
    }

    /**
     * Add a custom header.
     *
     * $name value can be overloaded to contain
     * both header name and value (name:value).
     *
     * @param string $name Custom header name
     * @param string|null $value Header value
     *
     * @return QubusMailer
     * @throws Exception
     */
    public function withCustomHeader(string $name, ?string $value = null): self
    {
        $new = clone $this;
        $new->clearCustomHeaders();
        $new->addCustomHeader($name, $value);

        return $new;
    }

    /**
     * The MIME Content-type of the message.
     *
     * @param string $contentType
     * @return QubusMailer
     */
    public function withContentType(string $contentType = self::CONTENT_TYPE_PLAINTEXT): self
    {
        $new = clone $this;
        $new->ContentType = $contentType;

        return $new;
    }

    /**
     * Add an attachment from a path on the filesystem.
     *
     * @param string $path Path to the attachment
     * @param string $name Overrides the attachment name
     * @param string $encode File encoding (see $Encoding)
     * @param string $type MIME type, e.g. `image/jpeg`; determined automatically from $path if not specified
     * @param string $disposition Disposition to use.
     *
     * @return QubusMailer
     * @throws Exception
     */
    public function withAttachment(
        string $path,
        string $name = '',
        string $encode = self::ENCODING_BASE64,
        string $type = '',
        string $disposition = 'attachment'
    ): self {
        $new = clone $this;
        $new->clearAttachments();
        $new->addAttachment($path, $name, $encode, $type, $disposition);

        return $new;
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
     * @param array|string $data Contain the values to be parsed in mail body.
     * @param array $options Array of options.
     * @return QubusMailer
     */
    public function withBody(string|array $data, array $options = []): self
    {
        $new = clone $this;

        $defaultOptions = [
            'template_path'    => null,
            'template_name'    => null,
            'template_options' => [],
        ];

        $options = array_merge($defaultOptions, $options);

        $templatePath = null === $new->templatePath ? $options['template_path'] : $new->templatePath;

        if (! is_array($data) && $options['template_name'] === null) {
            $new->Body = $data;
            return $new;
        } elseif (is_array($data) && $options['template_name'] === null) {
            $new->Body = implode('<br>  ', $data);
            return $new;
        } else {
            $templatePath = $options['template_path']
            ? $options['template_path'] . '/' . $options['template_name']
            : $options['template_name'];

            if (! file_exists($templatePath)) {
                //'none template message found in: ' .$options['template_name'];
                $new->Body = sprintf('Template (%s) not found.', $options['template_name']);
                return $new;
            } else {
                $new->Body = file_get_contents($templatePath);

                if (preg_match('/\.txt$/', $options['template_name'])) {
                    $new->Body = $new->Body;
                } else {
                    $templateTextPath = preg_replace(
                        pattern: '/\.[html|php|htm|phtml]+$/',
                        replacement: '.txt',
                        subject: $templatePath
                    );

                    if (file_exists($templateTextPath)) {
                        $new->Body = file_get_contents($templateTextPath);
                    }
                }
            }

            $data = is_array($data) ? $data : [$data];
            $data = array_merge($data, $options['template_options']);

            foreach ($data as $key => $value) {
                $new->Body = str_replace("{{" . $key . "}}", $value, $new->Body);
            }
        }

        return $new;
    }

    /**
     * The plain-text message body.
     *
     * @param string $message
     * @return QubusMailer
     */
    public function withAltBody(string $message = ''): self
    {
        $new = clone $this;
        $new->AltBody = $message;

        return $new;
    }

    /**
     * What to put in the X-Mailer header.
     *
     * Options: An empty string for PHPMailer default, whitespace/null for none, or a string to use.
     *
     * @param string|null $xmailer
     * @return QubusMailer
     */
    public function withXMailer(?string $xmailer = ''): self
    {
        $new = clone $this;
        $new->XMailer = $xmailer;

        return $new;
    }

    /**
     * Send messages using SMTP.
     *
     * @throws \Qubus\Exception\Exception
     */
    public function withSmtp(): self
    {
        $new = clone $this;
        $new->isSMTP();
        $new->Host = $this->config->getConfigKey(key: 'mailer.smtp.host');
        $new->Port = $this->config->getConfigKey(key: 'mailer.smtp.port');
        $new->SMTPSecure = $this->config->getConfigKey(key: 'mailer.smtp.encryption');
        $new->SMTPAuth = $this->config->getConfigKey(key: 'mailer.smtp.auth');
        $new->AuthType = $this->config->getConfigKey(key: 'mailer.smtp.authmode');
        $new->Username = $this->config->getConfigKey(key: 'mailer.smtp.username');
        $new->Password = $this->config->getConfigKey(key: 'mailer.smtp.password');

        return $new;
    }

    /**
     * Send messages using PHP's native mail() function.
     *
     * @return QubusMailer
     */
    public function withIsMail(): self
    {
        $new = clone $this;
        $new->isMail();

        return $new;
    }

    /**
     * Send messages using Sendmail.
     *
     * @return QubusMailer
     */
    public function withIsSendmail(): self
    {
        $new = clone $this;
        $new->isSendmail();

        return $new;
    }

    /**
     * Send messages using qmail.
     *
     * @return QubusMailer
     */
    public function withIsQmail(): self
    {
        $new = clone $this;
        $new->isQmail();

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function save(): bool
    {
        $fileName = $this->config->getConfigKey(key: 'mailer.emlfile');
        try {
            $file = fopen(filename: $fileName, mode: 'w+');
            fwrite(stream: $file, data: $this->getSentMIMEMessage());
            fclose(stream: $file);

            return true;
        } catch (Exception $e) {
            $this->setError(msg: $e->getMessage());

            return false;
        }
    }

    /**
     * Create a message and send it.
     * Uses the sending method specified by $Mailer.
     *
     * @return bool false on error.
     * @throws Exception|\Qubus\Exception\Exception
     */
    public function send(): bool
    {
        try {
            //prepare the message
            if (!$this->preSend()) {
                return false;
            }

            //in debug mode, save message as a file
            if ($this->config->getConfigKey(key: 'mailer.debug')) {
                return $this->save();
            } else {
                return $this->postSend();
            }
        } catch (Exception | \Qubus\Exception\Exception $e) {
            $this->mailHeader = '';
            $this->setError($e->getMessage());
            if ($this->exceptions) {
                throw $e;
            }
            return false;
        }
    }
}
