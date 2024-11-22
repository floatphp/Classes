<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Security Component
 * @version    : 1.3.x
 * @copyright  : (c) 2018 - 2024 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Security;

use FloatPHP\Classes\Filesystem\{Stringify, Validator};

/**
 * Built-in sanitizer class.
 * @see Inspired by https://developer.wordpress.org/apis/security/
 */
final class Sanitizer
{
    /**
     * Escape URL (output).
     *
     * [ENT_QUOTES : 3].
     *
     * @access public
     * @param string $url
     * @param bool $html, Escape
     * @return string
     */
    public static function escapeUrl(string $string, bool $html = false) : string
    {
        // Format spaces
        $string = Stringify::formatSpace($string);

        // Strip breaks
        $string = Stringify::stripBreak($string);

        // Filter URL
        $string = Stringify::filter($string, 'url');

        // Escape HTML in URL
        if ( $html ) {
            $string = self::escapeHTML($string);
        }

        return $string;
    }

    /**
     * Escape HTML (output).
     *
     * [ENT_QUOTES : 3].
     *
     * @access public
     * @param string $string
     * @param int $flags
     * @param mixed $encode
     * @return string
     */
    public static function escapeHTML(string $string, int $flags = 3, $encode = 'UTF-8') : string
    {
        return htmlspecialchars($string, $flags, $encode);
    }

    /**
     * Escape XML (output).
     *
     * [ENT_QUOTES : 3].
     * [ENT_XML1   : 16].
     *
     * @access public
     * @param string $string
     * @return string
     */
    public static function escapeXML(string $string) : string
    {
        return self::escapeHTML($string, 3 | 16, 'UTF-8');
    }

    /**
     * Escape JS (output).
     *
     * [ENT_NOQUOTES : 0].
     *
     * @access public
     * @param string $string
     * @return string
     */
    public static function escapeJS(string $string) : string
    {
        // Encode special characters
        $string = self::escapeHTML($string, 0, 'UTF-8');

        // Escape backslashes and single quotes
        $string = Stringify::replace('\\', '\\\\', $string);
        $string = Stringify::replace("'", "\\'", $string);

        // Escape newline characters
        $string = Stringify::replace("\n", '\\n', $string);
        $string = Stringify::replace("\r", '\\r', $string);

        return $string;
    }

    /**
     * Escape SQL (output).
     *
     * @access public
     * @param string $string
     * @return string
     */
    public static function escapeSQL(string $string) : string
    {
        // Format spaces
        $string = Stringify::formatSpace($string);

        // Escape single quotes
        $string = Stringify::replace("'", "\\'", $string);

        return $string;
    }

    /**
     * Sanitize text field.
     * [input].
     *
     * @access public
     * @param string $string
     * @return string
     */
    public static function sanitizeText(string $string) : string
    {
        // Format spaces
        $string = Stringify::formatSpace($string);

        // Strip breaks
        $string = Stringify::stripBreak($string);

        // Strip tags
        $string = Stringify::stripTag($string);

        // Escape HTML
        $string = self::escapeHTML($string);

        return $string;
    }

    /**
     * Sanitize textarea field.
     * [input].
     *
     * @access public
     * @param string $string
     * @return string
     */
    public static function sanitizeTextarea(string $string) : string
    {
        // Format spaces
        $string = Stringify::formatSpace($string);

        // Strip tags
        $string = self::escapeHTML($string);

        return $string;
    }

    /**
     * Sanitize email address.
     * [input].
     *
     * @access public
     * @param string $string
     * @return string
     */
    public static function sanitizeEmail(string $string) : string
    {
        // Format spaces
        $string = Stringify::formatSpace($string);

        // Strip breaks
        $string = Stringify::stripBreak($string);

        // Filter email
        $string = Stringify::filter($string, 'email');

        // Validate
        if ( !Validator::isEmail($string) ) {
            $string = '';
        }

        return $string;
    }

    /**
     * Sanitize name (username).
     * [input].
     *
     * @access public
     * @param string $string
     * @param bool $strict
     * @return string
     */
    public static function sanitizeName(string $string, bool $strict = true) : string
    {
        // Format spaces
        $string = Stringify::formatSpace($string);

        // Strip breaks
        $string = Stringify::stripBreak($string);

        // Strip special characters
        $string = Stringify::stripChar($string);

        return $string;
    }

    /**
     * Sanitize URL (URL toolkit).
     * [input].
     *
     * @access public
     * @param string $url
     * @param array $protocols
     * @return string
     * @todo
     */
    public static function sanitizeUrl(string $string, array $protocols = ['https']) : string
    {
        // Format spaces
        $string = Stringify::formatSpace($string);

        // Strip breaks
        $string = Stringify::stripBreak($string);

        // Filter URL
        $string = Stringify::filter($string, 'url');

        // Format protocol
        if ( !Stringify::contains($string, 'https') ) {
            $string = '' . "https://{$string}";
        }

        return $string;
    }

    /**
     * Sanitize mail parts (name, subject, body, attachment).
     * [input].
     *
     * @access public
     * @param string $string
     * @param string $type
     * @param bool $escape
     * @return string
     */
    public static function sanitizeMail(string $string, string $type = 'body', bool $escape = true) : string
    {
        // Format spaces
        $string = Stringify::formatSpace($string);

        // Filter text
        $string = Stringify::filter($string, 'text');

        // Escape HTML
        if ( $escape ) {
            $string = self::escapeHTML($string);
        }

        // Strip breaks and tags
        if ( $type !== 'body' ) {
            $string = Stringify::stripBreak($string);
            $string = Stringify::stripTag($string);

        } else {
            $string = Stringify::replace("\n.", "\n..", $string);
        }

        // Format UTF-8
        if ( $type == 'name' || $type == 'subject' ) {
            $string = sprintf('=?UTF-8?B?%s?=', Tokenizer::base64($string));
        }

        return $string;
    }

    /**
     * Sanitize mail body.
     * [input].
     *
     * @access public
     * @param string $string
     * @return string
     */
    public static function sanitizeBody(string $string) : string
    {
        return quoted_printable_encode($string);
    }

    /**
     * Sanitize HTML content (XSS).
     * [input].
     *
     * @access public
     * @param string $string
     * @param mixed $html
     * @param array $protocols
     * @return string
     * @todo
     */
    public static function sanitizeHTML(string $string, $html = 'post', ?array $protocols = null) : string
    {
        return $string;
    }

    /**
     * Sanitize filename.
     * [input].
     *
     * @access public
     * @param string $string
     * @todo
     */
    public static function sanitizeFilename(string $string) : string
    {
        return $string;
    }

    /**
     * Sanitize mime type.
     * [input].
     *
     * @access public
     * @param string $string
     * @return string
     * @todo
     */
    public static function sanitizeMimeType(string $string) : string
    {
        return $string;
    }
}
