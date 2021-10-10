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

use FloatPHP\Classes\Security\Tokenizer;
use \InvalidArgumentException;
use \RuntimeException;

class Mail
{
    /**
     * @access protected
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
     * @param void
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Get instance.
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
     * Resets all properties to initial state.
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
        $this->uid = Tokenizer::getUniqueId();
        return $this;
    }

    /**
     * Set To.
     *
     * @access public
     * @param string $email
     * @param string $name
     * @return object
     */
    public function setTo($email, $name = null) : object
    {
        $this->to[] = $this->formatHeader((string)$email,(string)$name);
        return $this;
    }

    /**
     * Get To.
     *
     * @access public
     * @param void
     * @return array
     */
    public function getTo() : array
    {
        return (array)$this->to;
    }

    /**
     * Set From.
     *
     * @access public
     * @param string $email
     * @param string $name
     * @return object
     */
    public function setFrom($email, $name = null) : object
    {
        $this->addMailHeader('From',(string)$email,(string)$name);
        return $this;
    }

    /**
     * Set Cc.
     *
     * @access public
     * @param array $pairs
     * @return object
     */
    public function setCc(array $pairs) : object
    {
        return $this->addMailHeaders('Cc',$pairs);
    }

    /**
     * Set Bcc.
     *
     * @access public
     * @param array $pairs
     * @return object
     */
    public function setBcc(array $pairs) : object
    {
        return $this->addMailHeaders('Bcc',$pairs);
    }

    /**
     * Set ReplyTo.
     *
     * @access public
     * @param string $email
     * @param string $name
     * @return object
     */
    public function setReplyTo($email, $name = null) : object
    {
        return $this->addMailHeader('Reply-To',$email,$name);
    }

    /**
     * Set Html.
     *
     * @access public
     * @param void
     * @return object
     */
    public function setHtml() : object
    {
        return $this->addGenericHeader(
            'Content-Type','text/html; charset="utf-8"'
        );
    }

    /**
     * Set subject.
     *
     * @access public
     * @param string $subject
     * @return object
     */
    public function setSubject($subject) : object
    {
        $this->subject = $this->encodeUtf8(
            $this->filterSubject((string)$subject)
        );
        return $this;
    }

    /**
     * Get subject.
     *
     * @access public
     * @param void
     * @return string
     */
    public function getSubject() : string
    {
        return (string)$this->subject;
    }

    /**
     * Set message.
     *
     * @access public
     * @param string $message
     * @return object
     */
    public function setMessage($message) : object
    {
        $this->message = str_replace("\n.","\n..",(string)$message);
        return $this;
    }

    /**
     * Get message.
     *
     * @access public
     * @param void
     * @return string
     */
    public function getMessage() : string
    {
        return (string)$this->message;
    }

    /**
     * Add attachment.
     *
     * @access public
     * @param string $path
     * @param string $filename
     * @param null $data
     * @return object
     */
    public function addAttachment($path, $filename = null, $data = null) : object
    {
        $filename = empty($filename) ? basename($path) : $filename;
        $data = empty($data) ? $this->getAttachmentData($path) : $data;
        $this->attachments[] = [
            'path' => $path,
            'file' => $filename,
            'data' => chunk_split(base64_encode($data))
        ];
        return $this;
    }

    /**
     * Get attachment data.
     *
     * @access public
     * @param string $path
     * @return string
     */
    public function getAttachmentData($path) : string
    {
        $filesize = filesize($path);
        $handle = fopen($path, "r");
        $attachment = fread($handle, $filesize);
        fclose($handle);
        return (string)$attachment;
    }

    /**
     * Add mail header.
     *
     * @access public
     * @param string $header
     * @param string $email
     * @param string $name
     * @return object
     */
    public function addMailHeader($header, $email, $name = null) : object
    {
        $address = $this->formatHeader((string)$email,(string)$name);
        $this->headers[] = sprintf('%s: %s',(string)$header,$address);
        return $this;
    }

    /**
     * Add mail headers.
     *
     * @access public
     * @param string $header
     * @param array $pairs
     * @return object
     * @throws InvalidArgumentException
     */
    public function addMailHeaders($header, array $pairs) : object
    {
        if ( count($pairs) === 0 ) {
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
     * Add generic header.
     *
     * @access public
     * @param string $header
     * @param mixed  $value
     * @return object
     */
    public function addGenericHeader($header, $value) : object
    {
        $this->headers[] = sprintf(
            '%s: %s',
            (string)$header,
            (string)$value
        );
        return $this;
    }

    /**
     * Get headers.
     *
     * @access public
     * @param void
     * @return array
     */
    public function getHeaders() : array
    {
        return (array)$this->headers;
    }

    /**
     * Set additional parameters.
     *
     * @access public
     * @param string $params
     * @return object
     */
    public function setParameters($params = '') : object
    {
        $this->params = (string)$params;
        return $this;
    }

    /**
     * Get additional parameters.
     *
     * @access public
     * @param void
     * @return string
     */
    public function getParameters() : string
    {
        return (string)$this->params;
    }

    /**
     * Set message number of characters.
     *
     * @access public
     * @param int $wrap
     * @return object
     */
    public function setWrap($wrap = 78) : object
    {
        $wrap = (int)$wrap;
        if ($wrap < 1) {
            $wrap = 78;
        }
        $this->wrap = $wrap;
        return $this;
    }

    /**
     * Get wrap.
     *
     * @access public
     * @param void
     * @return int
     */
    public function getWrap() : int
    {
        return (int)$this->wrap;
    }
    
    /**
     * Send mail.
     *
     * @access public
     * @param void
     * @return bool
     * @throws RuntimeException
     */
    public function send() : bool
    {
        $to = $this->getMailForSend();
        $headers = $this->getHeadersForSend();
        if ( empty($to) ) {
            throw new RuntimeException(
                'Unable to send, no To address has been set.'
            );
        }
        if ( $this->hasAttachments() ) {
            $message  = $this->assembleAttachmentBody();
            $headers .= PHP_EOL . $this->assembleAttachmentHeaders();
        } else {
            $message = $this->getWrapMessage();
        }
        return mail($to,$this->subject,$message,$headers,$this->params);
    }

    /**
     * Check attachments.
     *
     * @access protected
     * @param void
     * @return bool
     */
    protected function hasAttachments() : bool
    {
        return !empty($this->attachments);
    }

    /**
     * Assemble attachment headers.
     *
     * @access protected
     * @param void
     * @return string
     */
    protected function assembleAttachmentHeaders() : string
    {
        $head = [];
        $head[] = "MIME-Version: 1.0";
        $head[] = "Content-Type: multipart/mixed; boundary=\"{$this->uid}\"";
        return join(PHP_EOL,$head);
    }

    /**
     * Assemble attachment body.
     *
     * @access protected
     * @param void
     * @return string
     */
    protected function assembleAttachmentBody() : string
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
     * Get attachment mime template.
     *
     * @access protected
     * @param array $attachment
     * @return string
     */
    protected function getAttachmentMimeTemplate($attachment) : string
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
     * Format header.
     *
     * @access protected
     * @param string $email
     * @param string $name
     * @return string
     */
    protected function formatHeader($email, $name = null) : string
    {
        $email = $this->filterEmail((string)$email);
        if ( empty($name) ) {
            return $email;
        }
        $name = $this->encodeUtf8($this->filterName((string) $name));
        return sprintf('"%s" <%s>',$name,$email);
    }

    /**
     * Encode Utf8.
     *
     * @access protected
     * @param string $value
     * @return string
     */
    protected function encodeUtf8($value) : string
    {
        $value = trim($value);
        if ( preg_match('/(\s)/',$value) ) {
            return $this->encodeUtf8Words($value);
        }
        return $this->encodeUtf8Word($value);
    }

    /**
     * Encode Utf8 Word.
     *
     * @access protected
     * @param string $value
     * @return string
     */
    protected function encodeUtf8Word($value) : string
    {
        return sprintf('=?UTF-8?B?%s?=',base64_encode($value));
    }

    /**
     * Encode Utf8 Words.
     *
     * @access protected
     * @param string $value
     * @return string
     */
    protected function encodeUtf8Words($value) : string
    {
        $words = explode(' ',$value);
        $encoded = [];
        foreach ($words as $word) {
            $encoded[] = $this->encodeUtf8Word($word);
        }
        return join($this->encodeUtf8Word(' '),$encoded);
    }

    /**
     * Filter email.
     *
     * @access protected
     * @param string $email
     * @return string
     */
    protected function filterEmail($email) : string
    {
        $rule = [
            "\r" => '',
            "\n" => '',
            "\t" => '',
            '"'  => '',
            ','  => '',
            '<'  => '',
            '>'  => ''
        ];
        $email = strtr($email,$rule);
        $email = filter_var($email,FILTER_SANITIZE_EMAIL);
        return (string)$email;
    }

    /**
     * Filter name.
     *
     * @access protected
     * @param string $name
     * @return string
     */
    protected function filterName($name) : string
    {
        $rule = [
            "\r" => '',
            "\n" => '',
            "\t" => '',
            '"'  => "'",
            '<'  => '[',
            '>'  => ']',
        ];
        $filtered = filter_var(
            $name,
            FILTER_SANITIZE_STRING,
            FILTER_FLAG_NO_ENCODE_QUOTES
        );
        return trim(strtr($filtered,$rule));
    }

    /**
     * Filter subject.
     *
     * @access protected
     * @param string $subject
     * @return string
     */
    protected function filterSubject($subject) : string
    {
        return filter_var($subject,FILTER_UNSAFE_RAW,FILTER_FLAG_STRIP_LOW);
    }

    /**
     * Get headers for send.
     *
     * @access protected
     * @param void
     * @return string
     */
    protected function getHeadersForSend() : string
    {
        if ( empty($this->headers) ) {
            return '';
        }
        return join(PHP_EOL,$this->headers);
    }

    /**
     * Get mail for send.
     *
     * @access protected
     * @param void
     * @return string
     */
    protected function getMailForSend() : string
    {
        if ( empty($this->to) ) {
            return '';
        }
        return join(', ',$this->to);
    }

    /**
     * Get wrap message.
     *
     * @access protected
     * @param void
     * @return string
     */
    protected function getWrapMessage() : string
    {
        return wordwrap($this->message,$this->wrap);
    }
}
