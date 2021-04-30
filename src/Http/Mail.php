<?php
/**
 * @author    : JIHAD SINNAOUR
 * @package   : FloatPHP
 * @subpackage: Classes Http Component
 * @version   : 1.0.0
 * @category  : PHP framework
 * @copyright : (c) 2017 - 2021 JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link      : https://www.floatphp.com
 * @license   : MIT License
 *
 * This file if a part of FloatPHP Framework
 */

namespace FloatPHP\Classes\Server;

class Mail
{
    /**
     * @access protected
     *
     * @var int $wrap
     * @var array $to
     * @var string $subject
     * @var string $message
     * @var array $headers
     * @var string $params
     * @var array $attachments
     * @var string $uid
     */
    protected $wrap = 78;
    protected $to = [];
    protected $subject;
    protected $message;
    protected $headers = [];
    protected $params;
    protected $attachments = [];
    protected $uid;

    /**
     * Resets the class properties
     * @param void
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Get instance
     *
     * @access public
     * @param void
     * @return object
     */
    public static function instance()
    {
        return new self();
    }

    /**
     * Resets all properties to initial state
     *
     * @access public
     * @param void
     * @return object
     */
    public function reset()
    {
        $this->to = [];
        $this->headers = [];
        $this->subject = null;
        $this->message = null;
        $this->wrap = 78;
        $this->params = null;
        $this->attachments = [];
        $this->uid = Stringify::getUniqueId();
        return $this;
    }

    /**
     * setTo
     *
     * @param string $email
     * @param string $name
     *
     * @return object
     */
    public function setTo($email, $name)
    {
        $this->to[] = $this->formatHeader((string) $email, (string) $name);
        return $this;
    }

    /**
     * getTo
     *
     * Return an array of formatted To addresses.
     *
     * @return array
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * setFrom
     *
     * @param string $email The email to send as from.
     * @param string $name  The name to send as from.
     *
     * @return object
     */
    public function setFrom($email, $name)
    {
        $this->addMailHeader('From', (string) $email, (string) $name);
        return $this;
    }

    /**
     * setCc
     *
     * @param array  $pairs  An array of name => email pairs.
     *
     * @return object
     */
    public function setCc(array $pairs)
    {
        return $this->addMailHeaders('Cc', $pairs);
    }

    /**
     * setBcc
     *
     * @param array  $pairs  An array of name => email pairs.
     *
     * @return object
     */
    public function setBcc(array $pairs)
    {
        return $this->addMailHeaders('Bcc', $pairs);
    }

    /**
     * setReplyTo
     *
     * @param string $email
     * @param string $name
     *
     * @return object
     */
    public function setReplyTo($email, $name = null)
    {
        return $this->addMailHeader('Reply-To', $email, $name);
    }

    /**
     * setHtml
     *
     * @return object
     */
    public function setHtml()
    {
        return $this->addGenericHeader(
            'Content-Type', 'text/html; charset="utf-8"'
        );
    }

    /**
     * setSubject
     *
     * @param string $subject The email subject
     *
     * @return object
     */
    public function setSubject($subject)
    {
        $this->subject = $this->encodeUtf8(
            $this->filterOther((string) $subject)
        );
        return $this;
    }

    /**
     * getSubject function.
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * setMessage
     *
     * @param string $message The message to send.
     *
     * @return object
     */
    public function setMessage($message)
    {
        $this->message = str_replace("\n.", "\n..", (string) $message);
        return $this;
    }

    /**
     * getMessage
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * addAttachment
     *
     * @param string $path The file path to the attachment.
     * @param string $filename The filename of the attachment when emailed.
     * @param null $data
     * 
     * @return object
     */
    public function addAttachment($path, $filename = null, $data = null)
    {
        $filename = empty($filename) ? basename($path) : $filename;
        $data = empty($data) ? $this->getAttachmentData($path) : $data;
        $this->attachments[] = array(
            'path' => $path,
            'file' => $filename,
            'data' => chunk_split(base64_encode($data))
        );
        return $this;
    }

    /**
     * getAttachmentData
     *
     * @param string $path The path to the attachment file.
     *
     * @return string
     */
    public function getAttachmentData($path)
    {
        $filesize = filesize($path);
        $handle = fopen($path, "r");
        $attachment = fread($handle, $filesize);
        fclose($handle);
        return $attachment;
    }

    /**
     * addMailHeader
     *
     * @param string $header The header to add.
     * @param string $email  The email to add.
     * @param string $name   The name to add.
     *
     * @return object
     */
    public function addMailHeader($header, $email, $name = null)
    {
        $address = $this->formatHeader((string) $email, (string) $name);
        $this->headers[] = sprintf('%s: %s', (string) $header, $address);
        return $this;
    }

    /**
     * addMailHeaders
     *
     * @param string $header The header to add.
     * @param array  $pairs  An array of name => email pairs.
     *
     * @return object
     */
    public function addMailHeaders($header, array $pairs)
    {
        if (count($pairs) === 0) {
            throw new InvalidArgumentException(
                'You must pass at least one name => email pair.'
            );
        }
        $addresses = [];
        foreach ($pairs as $name => $email) {
            $name = is_numeric($name) ? null : $name;
            $addresses[] = $this->formatHeader($email, $name);
        }
        $this->addGenericHeader($header, implode(',', $addresses));
        return $this;
    }

    /**
     * Add generic header
     *
     * @param string $header
     * @param mixed  $value
     * @return object
     */
    public function addGenericHeader($header, $value)
    {
        $this->headers[] = sprintf(
            '%s: %s',
            (string) $header,
            (string) $value
        );
        return $this;
    }

    /**
     * Return the headers registered so far as an array
     *
     * @param void
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Set additional parameters
     *
     * @param string $params
     * @return object
     */
    public function setParameters($params)
    {
        $this->params = (string) $params;
        return $this;
    }

    /**
     * Get additional parameters
     *
     * @param void
     * @return string
     */
    public function getParameters()
    {
        return $this->params;
    }

    /**
     * Set The number of characters at which the message will wrap
     *
     * @param int $wrap
     * @return object
     */
    public function setWrap($wrap = 78)
    {
        $wrap = (int) $wrap;
        if ($wrap < 1) {
            $wrap = 78;
        }
        $this->wrap = $wrap;
        return $this;
    }

    /**
     * Get wrap
     *
     * @param void
     * @return int
     */
    public function getWrap()
    {
        return $this->wrap;
    }

    /**
     * Checks if the email has any registered attachments
     *
     * @param void
     * @return bool
     */
    public function hasAttachments()
    {
        return !empty($this->attachments);
    }

    /**
     * assembleAttachment
     *
     * @param void
     * @return string
     */
    public function assembleAttachmentHeaders()
    {
        $head = [];
        $head[] = "MIME-Version: 1.0";
        $head[] = "Content-Type: multipart/mixed; boundary=\"{$this->uid}\"";
        return join(PHP_EOL,$head);
    }

    /**
     * assembleAttachmentBody
     *
     * @param void
     * @return string
     */
    public function assembleAttachmentBody()
    {
        $body = [];
        $body[] = "This is a multi-part message in MIME format.";
        $body[] = "--{$this->uid}";
        $body[] = "Content-type:text/html; charset=\"utf-8\"";
        $body[] = "Content-Transfer-Encoding: 7bit";
        $body[] = "";
        $body[] = $this->message;
        $body[] = "";
        $body[] = "--{$this->uid}";
        foreach ($this->attachments as $attachment) {
            $body[] = $this->getAttachmentMimeTemplate($attachment);
        }
        return implode(PHP_EOL,$body) . '--';
    }

    /**
     * getAttachmentMimeTemplate
     *
     * @param array $attachment
     * @return string
     */
    public function getAttachmentMimeTemplate($attachment)
    {
        $file = $attachment['file'];
        $data = $attachment['data'];
        $head = [];
        $head[] = "Content-Type: application/octet-stream; name=\"{$file}\"";
        $head[] = "Content-Transfer-Encoding: base64";
        $head[] = "Content-Disposition: attachment; filename=\"{$file}\"";
        $head[] = "";
        $head[] = $data;
        $head[] = "";
        $head[] = "--{$this->uid}";
        return implode(PHP_EOL,$head);
    }

    /**
     * Send mail
     *
     * @param void
     * @return bool
     * @throws RuntimeException
     */
    public function send()
    {
        $to = $this->getMailForSend();
        $headers = $this->getHeadersForSend();
        if (empty($to)) {
            throw new \RuntimeException(
                'Unable to send, no To address has been set.'
            );
        }
        if ($this->hasAttachments()) {
            $message  = $this->assembleAttachmentBody();
            $headers .= PHP_EOL . $this->assembleAttachmentHeaders();
        } else {
            $message = $this->getWrapMessage();
        }
        return mail($to, $this->subject, $message, $headers, $this->params);
    }

    /**
     * debug
     *
     * @param void
     * @return string
     */
    public function debug()
    {
        return '<pre>' . print_r($this, true) . '</pre>';
    }

    /**
     * magic _toString function
     *
     * @param void
     * @return string
     */
    public function _toString()
    {
        return print_r($this, true);
    }

    /**
     * Formats a display address for emails according to RFC2822 e.g.
     *
     * @param string $email
     * @param string $name
     * @return string
     */
    public function formatHeader($email, $name = null)
    {
        $email = $this->filterEmail((string) $email);
        if (empty($name)) {
            return $email;
        }
        $name = $this->encodeUtf8($this->filterName((string) $name));
        return sprintf('"%s" <%s>', $name, $email);
    }

    /**
     * Encode Utf8
     *
     * @param string $value
     * @return string
     */
    public function encodeUtf8($value)
    {
        $value = trim($value);
        if (preg_match('/(\s)/', $value)) {
            return $this->encodeUtf8Words($value);
        }
        return $this->encodeUtf8Word($value);
    }

    /**
     * Encode Utf8 Word
     *
     * @param string $value
     * @return string
     */
    public function encodeUtf8Word($value)
    {
        return sprintf('=?UTF-8?B?%s?=', base64_encode($value));
    }

    /**
     * Encode Utf8 Words
     *
     * @param string $value
     * @return string
     */
    public function encodeUtf8Words($value)
    {
        $words = explode(' ', $value);
        $encoded = [];
        foreach ($words as $word) {
            $encoded[] = $this->encodeUtf8Word($word);
        }
        return join($this->encodeUtf8Word(' '), $encoded);
    }

    /**
     * Removes any carriage return, line feed, tab, double quote, comma
     * and angle bracket characters before sanitizing the email address
     *
     * @param string $email
     * @return string
     */
    public function filterEmail($email)
    {
        $rule = array(
            "\r" => '',
            "\n" => '',
            "\t" => '',
            '"'  => '',
            ','  => '',
            '<'  => '',
            '>'  => ''
        );
        $email = strtr($email, $rule);
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        return $email;
    }

    /**
     * Removes any carriage return, line feed or tab characters
     *
     * @param string $name
     * @return string
     */
    public function filterName($name)
    {
        $rule = array(
            "\r" => '',
            "\n" => '',
            "\t" => '',
            '"'  => "'",
            '<'  => '[',
            '>'  => ']',
        );
        $filtered = filter_var(
            $name,
            FILTER_SANITIZE_STRING,
            FILTER_FLAG_NO_ENCODE_QUOTES
        );
        return trim(strtr($filtered, $rule));
    }

    /**
     * Removes ASCII control characters including any carriage return, line
     * feed or tab characters.
     *
     * @param string $data
     * @return string
     */
    public function filterOther($data)
    {
        return filter_var($data,FILTER_UNSAFE_RAW,FILTER_FLAG_STRIP_LOW);
    }

    /**
     * Get headers for send
     *
     * @param void
     * @return string
     */
    public function getHeadersForSend()
    {
        if (empty($this->headers)) {
            return '';
        }
        return join(PHP_EOL,$this->headers);
    }

    /**
     * Get mail for send
     *
     * @param void
     * @return string
     */
    public function getMailForSend()
    {
        if ( empty($this->to) ) {
            return '';
        }
        return join(', ',$this->to);
    }

    /**
     * Get wrap message
     *
     * @param void
     * @return string
     */
    public function getWrapMessage()
    {
        return wordwrap($this->message, $this->wrap);
    }
}
