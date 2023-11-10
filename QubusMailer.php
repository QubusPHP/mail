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
     * @inheritDoc
     */
    public function withFrom(string $address, string $name = '', bool $auto = true): static
    {
        $new = clone $this;
        $new->setFrom($address, $name, $auto);

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function withTo(string|array $address): static
    {
        $new = clone $this;
        $new->clearAddresses();
        $new->withAddresses(type: 'to', addresses: $address);

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function withCc(string|array $address): static
    {
        $new = clone $this;
        $new->clearCCs();
        $new->withAddresses(type: 'cc', addresses: $address);

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function withBcc(string|array $address): static
    {
        $new = clone $this;
        $new->clearBCCs();
        $new->withAddresses(type: 'bcc', addresses: $address);

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function withReplyTo(string|array $address): static
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
     * @inheritDoc
     */
    public function withSender(string $sender = ''): static
    {
        $new = clone $this;
        $new->Sender = $sender;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function withSubject(string $subject = ''): static
    {
        $new = clone $this;
        $new->Subject = $subject;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function withHtml(bool $isHtml = false): static
    {
        $new = clone $this;
        $new->isHTML($isHtml);

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function withPriority(?int $priority = null): static
    {
        $new = clone $this;
        $new->Priority = $priority;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function withCharset(string $charset = self::CHARSET_ISO88591): static
    {
        $new = clone $this;
        $new->CharSet = $charset;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function withCustomHeader(string $name, ?string $value = null): static
    {
        $new = clone $this;
        $new->clearCustomHeaders();
        $new->addCustomHeader($name, $value);

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function withContentType(string $contentType = self::CONTENT_TYPE_PLAINTEXT): static
    {
        $new = clone $this;
        $new->ContentType = $contentType;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function withAttachment(
        string $path,
        string $name = '',
        string $encode = self::ENCODING_BASE64,
        string $type = '',
        string $disposition = 'attachment'
    ): static {
        $new = clone $this;
        $new->clearAttachments();
        $new->addAttachment($path, $name, $encode, $type, $disposition);

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function withBody(string|array $data, array $options = []): static
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
     * @inheritDoc
     */
    public function withAltBody(string $message = ''): static
    {
        $new = clone $this;
        $new->AltBody = $message;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function withXMailer(?string $xmailer = ''): static
    {
        $new = clone $this;
        $new->XMailer = $xmailer;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function withSmtp(): static
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
     * @inheritDoc
     */
    public function withMail(): static
    {
        $new = clone $this;
        $new->isMail();

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function withSendmail(): static
    {
        $new = clone $this;
        $new->isSendmail();

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function withQmail(): static
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
