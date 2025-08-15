<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Http Component
 * @version    : 1.5.x
 * @copyright  : (c) 2018 - 2025 Jihad Sinnaour <me@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file is a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Http;

use FloatPHP\Classes\Filesystem\{Stringify, TypeCheck, File};
use FloatPHP\Classes\Security\{Tokenizer, Sanitizer};
use \InvalidArgumentException;
use \RuntimeException;

/**
 * Advanced mail manipulation.
 */
class Mail
{
    /**
     * @access protected
     * @var int $wrap
     * @var array $to
     * @var string $subject
     * @var string $body
     * @var array $headers
     * @var string $params
     * @var array $attachments
     * @var string $uid
     */
    protected $wrap = 78;
    protected $to = [];
    protected $subject;
    protected $body;
    protected $headers = [];
    protected $params = '';
    protected $attachments = [];
    protected $uid;
    protected $isHtml = false;

    protected const FROM  = 'From';
    protected const CC    = 'Cc';
    protected const BCC   = 'Bcc';
    protected const REPLY = 'Reply-To';
    protected const HTML  = 'text/html; charset="utf-8"';

    /**
     * Set UID.
     *
     * @access public
     * @param string $email, Sender
     * @param string $name, Sender
     */
    public function __construct(?string $email = null, ?string $name = null)
    {
        $this->uid = Tokenizer::getUniqueId();
        if ( $email ) {
            $this->from($email, $name);
        }
    }

    /**
     * Set receiver(s).
     *
     * @access public
     * @param string $email
     * @param string $name
     * @return object
     */
    public function to(string $email, ?string $name = null) : self
    {
        $this->to[] = $this->formatAddressHeader($email, $name);
        return $this;
    }

    /**
     * Set sender.
     *
     * @access public
     * @param string $email
     * @param string $name
     * @return object
     */
    public function from(string $email, ?string $name = null) : self
    {
        $this->setAddressHeader(static::FROM, $email, $name);
        return $this;
    }

    /**
     * Set Reply-To.
     *
     * @access public
     * @param string $email
     * @param string $name
     * @return object
     */
    public function replyTo(string $email, ?string $name = null) : self
    {
        $this->setAddressHeader(static::REPLY, $email, $name);
        return $this;
    }

    /**
     * Set CC (Carbon Copy).
     *
     * @access public
     * @param array $pair
     * @return object
     */
    public function setCc(array $pair) : self
    {
        $this->setAddressPairHeader(static::CC, $pair);
        return $this;
    }

    /**
     * Set BCC (Blind Carbon Copy).
     *
     * @access public
     * @param array $pair
     * @return object
     */
    public function setBcc(array $pair) : self
    {
        $this->setAddressPairHeader(static::BCC, $pair);
        return $this;
    }

    /**
     * Send body as HTML.
     *
     * @access public
     * @return object
     */
    public function asHtml() : self
    {
        $this->addHeader('Content-Type', static::HTML);
        $this->isHtml = true;
        return $this;
    }

    /**
     * Set subject.
     *
     * @access public
     * @param string $subject
     * @return object
     */
    public function setSubject(string $subject) : self
    {
        $this->subject = Sanitizer::sanitizeMail($subject, 'subject');
        return $this;
    }

    /**
     * Set body (message).
     *
     * @access public
     * @param string $body
     * @return object
     */
    public function setBody(string $body) : self
    {
        $escape = ($this->isHtml) ? false : true;
        $this->body = Sanitizer::sanitizeMail($body, 'body', $escape);
        return $this;
    }

    /**
     * Add content to body.
     *
     * @access public
     * @param string $content
     * @param bool $break
     * @return object
     */
    public function addContent(string $content, bool $break = true) : self
    {
        $escape = ($this->isHtml) ? false : true;
        $content = Sanitizer::sanitizeMail($content, 'body', $escape);

        if ( $break ) {
            $this->addBreak();
        }

        $this->body .= $content;
        return $this;
    }

    /**
     * Add break to body.
     *
     * @access public
     * @return object
     */
    public function addBreak() : self
    {
        $this->body .= "\n";
        return $this;
    }

    /**
     * Set attachment(s).
     *
     * @param string $path 
     * @param string $name.
     * @param string $data
     * @return object
     */
    public function setAttachment(string $path, ?string $name = null, ?string $data = null) : self
    {
        $name = $name ?: Stringify::basename($path);
        $data = $data ?: $this->getAttachmentData($path);
        $data = Stringify::chunk(Tokenizer::base64($data));
        $this->attachments[] = [
            'path' => $path,
            'file' => $name,
            'data' => $data
        ];
        return $this;
    }

    /**
     * Set additional mail parameters.
     *
     * @param string $params
     * @return object
     */
    public function setParams(string $params) : self
    {
        $this->params = (string)$params;
        return $this;
    }

    /**
     * Set body words wrapper.
     *
     * @param int $wrap
     * @return object
     */
    public function wrap(int $wrap) : self
    {
        $this->wrap = ($wrap > 1)
            ? $wrap : $this->wrap;
        return $this;
    }

    /**
     * Send mail.
     *
     * @return boolean
     * @throws RuntimeException
     */
    public function send() : bool
    {
        if ( !($to = $this->getReceivers()) ) {
            throw new RuntimeException(
                'Unable to send, Undefined receiver address'
            );
        }

        $headers = $this->getHeaders();
        $message = wordwrap($this->body, $this->wrap);

        if ( $this->attachments ) {
            $message = $this->getAttachmentBody();
            $headers .= $this->getAttachmentHeader();
        }

        return mail($to, $this->subject, $message, $headers, $this->params);
    }

    /**
     * Add mail header.
     *
     * @access public
     * @param string $name
     * @param string $value
     * @return object
     */
    public function addHeader(string $name, string $value) : self
    {
        $this->headers[] = sprintf('%s: %s', $name, $value);
        return $this;
    }

    /**
     * Set address header.
     *
     * @access protected
     * @param string $type
     * @param string $email
     * @param string $name
     * @return void
     */
    protected function setAddressHeader(string $type, string $email, ?string $name = null) : void
    {
        $address = $this->formatAddressHeader($email, $name);
        $this->addHeader($type, $address);
    }

    /**
     * Set address pair header.
     *
     * @access protected
     * @param string $type
     * @param array $pairs
     * @return void
     * @throws InvalidArgumentException
     */
    protected function setAddressPairHeader(string $type, array $pairs)
    {
        $address = [];
        foreach ($pairs as $name => $email) {
            if ( TypeCheck::isInt($name) ) {
                $name = null;
            }
            if ( TypeCheck::isString($email) ) {
                $address[] = $this->formatAddressHeader($email, $name);
            }
        }

        if ( !$address ) {
            throw new InvalidArgumentException(
                'Invali mail header pairs'
            );
        }

        $address = implode(',', $address);
        $this->addHeader($type, $address);
    }

    /**
     * Format address header.
     *
     * @access protected
     * @param string $email
     * @param string $name
     * @return string
     * @throws InvalidArgumentException
     */
    protected function formatAddressHeader(string $email, ?string $name = null) : string
    {
        $email = Sanitizer::sanitizeEmail($email);

        if ( empty($email) ) {
            throw new InvalidArgumentException('Invalid email address');
        }

        if ( $name ) {
            $name = Sanitizer::sanitizeMail($name, 'name');
            $email = sprintf('"%s" <%s>', $name, $email);
        }

        return $email;
    }

    /**
     * Get headers.
     *
     * @access protected
     * @return string
     */
    protected function getHeaders() : string
    {
        if ( $this->headers ) {
            $this->headers = join(Stringify::break(), $this->headers);
        }
        return $this->headers;
    }

    /**
     * Get receivers.
     *
     * @access protected
     * @return string
     */
    protected function getReceivers() : string
    {
        return join(', ', $this->to);
    }

    /**
     * Get attachment data.
     *
     * @access protected
     * @param string $path
     * @return mixed
     */
    protected function getAttachmentData(string $path) : mixed
    {
        return File::read($path);
    }

    /**
     * Get attachment header.
     *
     * @access protected
     * @return string
     */
    protected function getAttachmentHeader() : string
    {
        $boundary = 'boundary="' . $this->uid . '"';

        $header = [];
        $header[] = 'MIME-Version: 1.0';
        $header[] = "Content-Type: multipart/mixed; {$boundary}";

        $header = join(Stringify::break(), $header);
        $header = Stringify::break() . $header;

        return $header;
    }

    /**
     * Get attachment body.
     *
     * @access protected
     * @return string
     */
    protected function getAttachmentBody() : string
    {
        $charset = 'charset="utf-8"';

        $body = [];
        $body[] = 'This is a multi-part message in MIME format.';
        $body[] = "--{$this->uid}";
        $body[] = "Content-Type: text/html; {$charset}";
        $body[] = 'Content-Transfer-Encoding: quoted-printable';
        $body[] = '';
        $body[] = Sanitizer::sanitizeBody($this->body);
        $body[] = '';
        $body[] = "--{$this->uid}";

        foreach ($this->attachments as $attachment) {
            $body[] = $this->getAttachmentMimeTemplate($attachment);
        }

        $body = implode(Stringify::break(), $body);
        return "{$body}--";
    }

    /**
     * Get attachment mime template.
     *
     * @access protected
     * @param array $attachment
     * @return string
     */
    protected function getAttachmentMimeTemplate(array $attachment) : string
    {
        $file = $attachment['file'] ?? null;
        $data = $attachment['data'] ?? null;

        $name = 'name="' . $file . '"';
        $filename = 'filename="' . $file . '"';

        $header = [];
        $header[] = "Content-Type: application/octet-stream; {$name}";
        $header[] = "Content-Transfer-Encoding: base64";
        $header[] = "Content-Disposition: attachment; {$filename}";
        $header[] = '';
        $header[] = $data;
        $header[] = '';
        $header[] = "--{$this->uid}";

        $header = implode(Stringify::break(), $header);
        return $header;
    }
}
