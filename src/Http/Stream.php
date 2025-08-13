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
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Http;

use FloatPHP\Classes\Filesystem\{Stringify, Arrayify, File, Json, Exception};

/**
 * Advanced HTTP stream manipulation.
 */
final class Stream
{
    /**
     * @access public
     */
    public const HTTP = 'http';
    public const FTP  = 'ftp';
    public const SSH  = 'ssh2';
    public const PHAR = 'phar';
    public const PHP  = 'php';
    public const FILE = 'file';
    public const GLOB = 'glob';
    public const DATA = 'data';
    public const TCP  = 'tcp';
    public const UDP  = 'udp';
    public const SSL  = 'ssl';
    public const TLS  = 'tls';

    /**
     * @access private
     * @var string $transport
     * @var array $responseStatus
     * @var array $responseHeader
     * @var string $responseBody
     */
    private static $transport = self::HTTP;
    private static $responseStatus = [];
    private static $responseHeader = [];
    private static $responseBody = '';

    /**
     * Set stream transport.
     *
     * @access public
     * @param string $transport
     * @return void
     */
    public static function setTransport(string $transport) : void
    {
        self::$transport = $transport;
    }

    /**
     * Create stream context.
     *
     * @access public
     * @param ?array $options
     * @param ?array $params
     * @return mixed
     */
    public static function create(?array $options = null, ?array $params = null) : mixed
    {
        return stream_context_create($options, $params);
    }

    /**
     * Get stream wrappers.
     *
     * @access public
     * @return array
     */
    public static function getWrappers() : array
    {
        return stream_get_wrappers();
    }

    /**
     * Get stream socket transports.
     *
     * @access public
     * @return array
     */
    public static function getTransports() : array
    {
        return stream_get_transports();
    }

    /**
     * Set stream context parameters.
     *
     * @access public
     * @param resource $context
     * @param array $params
     * @return bool
     */
    public static function setParams($context, array $params) : bool
    {
        return stream_context_set_params($context, $params);
    }

    /**
     * Execute stream context using file.
     *
     * @access public
     * @param string $url
     * @param array $options
     * @param mixed $context
     * @return mixed
     */
    public static function exec(string $url, array $options, &$context = null) : mixed
    {
        $options = Stringify::undash($options);
        $context = self::create($options);
        return File::r($url, false, $context);
    }

    /**
     * Execute stream context (Alias with status).
     *
     * @access public
     * @param string $url
     * @param array $options
     * @return bool
     */
    public static function execute(string $url, array $options) : bool
    {
        self::reset();
        $response = self::exec($url, $options, $context);

        // Set header
        self::parseHeader($url, $context);

        // Set body
        self::$responseBody = (string)$response;

        return $response !== false;
    }

    /**
     * Advanced stream HTTP request.
     *
     * @access public
     * @param string $url
     * @param array $params
     * @return array
     */
    public static function request(string $url, array $params = []) : array
    {
        // Extract params
        $params = Client::getParams($params);
        extract($params);

        // Set body
        $body = Client::getQuery($body);

        // Set User-Agent
        if ( $ua ) {
            $header['User-Agent'] = $ua;
        }

        // Set header
        $header = Client::formatHeader($header);

        // Set options
        $options = [
            self::$transport => [
                'method'          => $method,
                'header'          => $header,
                'content'         => $body,
                'timeout'         => $timeout,
                'max-redirects'   => $redirect,
                'follow-location' => $follow == true ? 1 : 0
            ],
            self::SSL        => [
                'verify-peer'      => $ssl,
                'verify-peer-name' => $ssl
            ]
        ];

        // Get response
        $error = false;
        if ( !self::execute($url, $options) ) {
            $header = [];
            $status = self::getError();
            $body = Status::getMessage(500);
            $error = true;

        } else {
            $header = self::getResponseHeader();
            $status = self::getResponseStatus();
            $body = self::getResponseBody();
        }

        // Return reponse data
        return [
            'error'  => $error,
            'status' => $status,
            'header' => $header,
            'body'   => $body
        ];
    }

    /**
     * Get cURL effective URL (Location).
     *
     * @access public
     * @param string $url
     * @param array $params
     * @return string
     */
    public static function getLocation(string $url, array $params = []) : string
    {
        // Extract params
        $params = Client::getParams($params);
        extract($params);

        // Set method
        if ( !$method || $method == Client::POST ) {
            $method = Client::HEAD;
        }

        // Set body
        $url = $body ? Client::getQuery($body, $url) : $url;

        // Set User-Agent
        if ( $ua ) {
            $header['User-Agent'] = $ua;
        }

        // Set options
        $options = [
            self::$transport => [
                'method'          => $method,
                'header'          => $header,
                'content'         => $body,
                'timeout'         => $timeout,
                'max-redirects'   => $redirect,
                'follow-location' => $follow == true ? 1 : 0
            ],
            self::SSL        => [
                'verify-peer'      => $ssl,
                'verify-peer-name' => $ssl
            ]
        ];

        // Parse HTTP location
        if ( self::execute($url, $options) ) {
            $header = self::getResponseHeader();
            $url = $header['Location'] ?? $url;
            $url = trim($url);
        }

        return $url;
    }

    /**
     * Get response header.
     *
     * @access public
     * @return array
     */
    public static function getResponseHeader() : array
    {
        return self::$responseHeader;
    }

    /**
     * Get response status.
     *
     * @access public
     * @return array
     */
    public static function getResponseStatus() : array
    {
        return self::$responseStatus;
    }

    /**
     * Get response status code.
     *
     * @access public
     * @return int
     */
    public static function getStatusCode() : int
    {
        $code = self::$responseStatus['code'] ?? -1;
        return (int)$code;
    }

    /**
     * Get response status message.
     *
     * @access public
     * @return string
     */
    public static function getStatusMessage() : string
    {
        $code = self::$responseStatus['message'] ?? '';
        return (string)$code;
    }

    /**
     * Get response body.
     *
     * @access public
     * @return string
     */
    public static function getResponseBody() : string
    {
        return self::$responseBody;
    }

    /**
     * Get stream error code.
     *
     * @access public
     * @return int
     */
    public static function getErrorCode() : int
    {
        $error = Exception::getLastError();
        return $error['type'] ?? -1;
    }

    /**
     * Get stream error message.
     *
     * @access public
     * @return string
     */
    public static function getErrorMessage() : string
    {
        $error = Exception::getLastError();
        $error = $error['message'] ?? '';

        if ( Stringify::contains($error, 'operation failed') ) {
            $pattern = '/file_get_contents\((\w+:\/\/\/|\/)\)/i';
            if ( Stringify::match($pattern, $error, $matches) ) {
                $host = $matches[0] ?: 'Empty';
                return 'URL rejected: No host part in the URL';
            }
        }

        if ( Stringify::contains($error, 'getaddrinfo') ) {
            $pattern = '/\b[a-z0-9.-]+\.[a-z]{2,}\b/i';
            if ( Stringify::match($pattern, $error, $matches) ) {
                $host = $matches[0] ?: 'Empty';
                return "Could not resolve host: {$host}";
            }
        }

        if ( Stringify::contains($error, 'No such file or directory') ) {
            $pattern = '/\b[a-z]+(?=:\/\/?)/i';
            if ( Stringify::match($pattern, $error, $matches) ) {
                $protocol = $matches[0] ?: 'Empty';
                return "Protocol not supported: {$protocol}";
            }
            return 'Invalid protocol';
        }

        return 'Failed to open stream';
    }

    /**
     * Get stream error (Normalized).
     *
     * @access public
     * @return array
     */
    public static function getError() : array
    {
        return [
            'code'    => self::getErrorCode(),
            'message' => self::getErrorMessage()
        ];
    }

    /**
     * Get Http response header from context.
     *
     * @access public
     * @param string $url
     * @param bool $as Associative 
     * @param resource $context
     * @return mixed
     */
    public static function getHeader(string $url, bool $as = false, $context = null) : mixed
    {
        return @get_headers($url, $as, $context);
    }

    /**
     * Parse Http response header from context.
     *
     * @access public
     * @param string $url
     * @param resource $context
     * @return int
     */
    public static function parseHeader(string $url, $context = null) : int
    {
        // Get header
        $header = self::getHeader($url, true, $context) ?: [];

        // Parse status
        $status = $header[0] ?? '';
        $regex = Client::getPattern('status');
        if ( Stringify::match($regex, $status, $matches) ) {

            $code = $matches['code'] ?? -1;
            $code = (int)$code;
            $message = $matches['message'] ?? '';
            if ( empty($message) ) {
                $message = Status::getMessage($code);
            }
            self::$responseStatus = ['code' => $code, 'message' => $message];
            unset($header[0]);
        }

        // Set formated header
        self::$responseHeader = $header;

        $header = Json::encode($header);
        return strlen($header);
    }

    /**
     * Check valid stream (path).
     *
     * @access public
     * @param string $path
     * @return bool
     */
    public static function isValid(string $path) : bool
    {
        $scheme = strpos($path, '://');
        if ( $scheme === false ) {
            return false;
        }
        $stream = substr($path, 0, length: $scheme);
        return Arrayify::inArray($stream, self::getWrappers());
    }

    /**
     * Reset stream.
     *
     * @access private
     * @return void
     */
    private static function reset() : void
    {
        self::$responseBody = '';
        self::$responseHeader = [];
        self::$responseStatus = [];
    }
}
