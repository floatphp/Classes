<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Security Component
 * @version    : 1.5.x
 * @copyright  : (c) 2018 - 2025 Jihad Sinnaour <me@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file is a part of FloatPHP Framework.
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
    public static function escapeUrl(string $url, bool $html = false) : string
    {
        // Input validation
        if ( trim($url) === '' ) {
            return '';
        }

        // Format spaces
        $url = Stringify::formatSpace($url);

        // Strip breaks
        $url = Stringify::stripBreak($url);

        // Filter URL
        $url = Stringify::filter($url, 'url');

        // Escape HTML in URL
        if ( $html ) {
            $url = self::escapeHTML($url);
        }

        return $url;
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
        // Input validation
        if ( trim($string) === '' ) {
            return '';
        }
        
        // Validate flags parameter
        if ( $flags < 0 || $flags > 15 ) {
            $flags = 3; // Default to ENT_QUOTES
        }

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
        // Input validation
        if ( trim($string) === '' ) {
            return '';
        }

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
        // Input validation
        if ( trim($string) === '' ) {
            return '';
        }

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
        // Input validation
        if ( trim($string) === '' ) {
            return '';
        }

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
        // Input validation
        if ( trim($string) === '' ) {
            return '';
        }

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
        // Input validation
        if ( trim($string) === '' ) {
            return '';
        }

        // Format spaces
        $string = Stringify::formatSpace($string);

        // Strip breaks
        $string = Stringify::stripBreak($string);

        // Strip special characters
        $string = Stringify::stripChar($string);

        // Apply strict mode filtering
        if ( $strict ) {
            // Allow only alphanumeric characters and underscores
            $string = preg_replace('/[^a-zA-Z0-9_]/', '', $string);
        }

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
     */
    public static function sanitizeHTML(string $string, $html = 'post', ?array $protocols = null) : string
    {
        // Input validation
        if ( trim($string) === '' ) {
            return '';
        }

        // Set default allowed protocols
        if ( $protocols === null ) {
            $protocols = ['http', 'https', 'mailto'];
        }

        // Basic HTML sanitization
        $allowedTags = '<p><br><strong><em><u><ol><ul><li><a><img>';
        
        if ( $html === 'comment' ) {
            // More restrictive for comments
            $allowedTags = '<p><br><strong><em>';
        } elseif ( $html === 'basic' ) {
            // Very basic HTML only
            $allowedTags = '<p><br>';
        }

        // Strip dangerous tags but keep allowed ones
        $string = strip_tags($string, $allowedTags);

        // Remove dangerous attributes and protocols
        $string = preg_replace('/on\w+\s*=\s*["\'][^"\']*["\']/i', '', $string);
        $string = preg_replace('/javascript\s*:/i', '', $string);
        $string = preg_replace('/vbscript\s*:/i', '', $string);
        $string = preg_replace('/data\s*:/i', '', $string);

        // Validate protocols in href and src attributes
        foreach ($protocols as $protocol) {
            $string = preg_replace('/href\s*=\s*["\'](?!' . preg_quote($protocol) . ':)[^"\']*["\']/i', '', $string);
            $string = preg_replace('/src\s*=\s*["\'](?!' . preg_quote($protocol) . ':)[^"\']*["\']/i', '', $string);
        }

        return $string;
    }

    /**
     * Sanitize filename.
     * [input].
     *
     * @access public
     * @param string $string
     * @return string
     */
    public static function sanitizeFilename(string $string) : string
    {
        // Input validation
        if ( trim($string) === '' ) {
            return '';
        }

        // Length validation
        if ( strlen($string) > 255 ) {
            $string = substr($string, 0, 255);
        }

        // Remove path traversal attempts
        $string = str_replace(['../', '.\\', '../'], '', $string);

        // Remove dangerous characters
        $string = preg_replace('/[<>:"|?*\x00-\x1f\x7f]/', '', $string);

        // Remove leading/trailing dots and spaces
        $string = trim($string, '. ');

        // Prevent reserved Windows filenames
        $reserved = ['CON', 'PRN', 'AUX', 'NUL', 'COM1', 'COM2', 'COM3', 'COM4', 'COM5', 'COM6', 'COM7', 'COM8', 'COM9', 'LPT1', 'LPT2', 'LPT3', 'LPT4', 'LPT5', 'LPT6', 'LPT7', 'LPT8', 'LPT9'];
        
        $baseName = pathinfo($string, PATHINFO_FILENAME);
        if ( in_array(strtoupper($baseName), $reserved) ) {
            $string = 'file_' . $string;
        }

        // Ensure filename is not empty after sanitization
        if ( trim($string) === '' ) {
            $string = 'file.txt';
        }

        return $string;
    }

    /**
     * Sanitize mime type.
     * [input].
     *
     * @access public
     * @param string $string
     * @return string
     */
    public static function sanitizeMimeType(string $string) : string
    {
        // Input validation
        if ( trim($string) === '' ) {
            return '';
        }

        // Convert to lowercase for consistency
        $string = strtolower(trim($string));

        // Validate basic MIME type format (type/subtype)
        if ( !preg_match('/^[a-z][a-z0-9]*[a-z0-9\-\.]\/[a-z0-9][a-z0-9\-\.]*[a-z0-9]$/', $string) ) {
            return '';
        }

        // Define allowed MIME types for security
        $allowedTypes = [
            'text/plain', 'text/html', 'text/css', 'text/javascript', 'text/csv',
            'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
            'application/pdf', 'application/json', 'application/xml',
            'application/zip', 'application/x-zip-compressed',
            'application/msword', 'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'audio/mpeg', 'audio/wav', 'audio/ogg',
            'video/mp4', 'video/webm', 'video/ogg'
        ];

        // Check if MIME type is in allowed list
        if ( !in_array($string, $allowedTypes) ) {
            return '';
        }

        return $string;
    }
}
