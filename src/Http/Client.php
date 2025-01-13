<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Http Component
 * @version    : 1.4.x
 * @copyright  : (c) 2018 - 2024 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Http;

use FloatPHP\Classes\Filesystem\{Stringify, Arrayify, Converter, TypeCheck, Json};
use FloatPHP\Classes\Server\System;
use FloatPHP\Exceptions\Classes\ClientException;

/**
 * Advanced HTTP request client (cURL|Stream).
 */
class Client
{
    /**
     * @access public
     */
    public const GET       = 'GET';
    public const POST      = 'POST';
    public const HEAD      = 'HEAD';
    public const PUT       = 'PUT';
    public const PATCH     = 'PATCH';
    public const OPTIONS   = 'OPTIONS';
    public const DELETE    = 'DELETE';
    public const TIMEOUT   = 10;
    public const REDIRECT  = 3;
    public const PATTERN   = [
        'status'    => '/^\s*HTTP\/\d+(\.\d+)?\s+(?P<code>\d+)\s*(?P<message>.*)?\r?\n?$/',
        'attribute' => '/^\s*(?P<name>[a-zA-Z0-9\-]+)\s*:\s*(?P<value>.*?)\s*(?:\r?\n|$)/',
        'location'  => "/Location:\s*(.+)/i"
    ];
    public const USERAGENT = [
        'Mozilla/5.0',
        '(X11; Linux x86_64)',
        'AppleWebKit/537.36',
        '(KHTML, like Gecko)',
        'Chrome/114.0.5735.199',
        'Safari/537.36'
    ];

    /**
     * @access protected
     */
    protected $method;
    protected $params = [];
    protected $header = [];
    protected $body = [];
    protected $response = [];
    protected $baseUrl;
    protected $url;
    protected $gateway;
    protected const CURL   = 'Curl';
    protected const STREAM = 'Stream';

    /**
     * @access protected
     * @var array $pattern Parser pattern
     */
    private static $pattern = [];

    /**
     * Init client.
     *
     * @access public
     * @param ?string $baseUrl
     * @param array $params
     */
    public function __construct(?string $baseUrl = null, array $params = [])
    {
        $this->baseUrl = $baseUrl;
        $this->params = self::getParams($params);
        $this->setGateway();
        self::setPattern();
    }

    /**
     * Make Http request.
     *
     * @access public
     * @param string $method
     * @param array $body
     * @param array $header
     * @param ?string $url
     * @return object
     */
    public function request(string $method, array $body = [], array $header = [], ?string $url = null) : self
    {
        // Reset client
        $this->reset();

        // Set method
        $this->setMethod($method);

        // Set body
        $this->setBody($body);

        // Set header
        $this->setHeader($header);

        // Set URL
        $this->setUrl($url);

        // Execute request
        $this->execute();

        return $this;
    }

    /**
     * Make Http GET request.
     *
     * @access public
     * @param ?string $url
     * @param array $body
     * @param array $header
     * @return object
     */
    public function get(?string $url = null, array $body = [], array $header = []) : self
    {
        return $this->request(self::GET, $body, $header, $url);
    }

    /**
     * Make Http POST request.
     *
     * @access public
     * @param ?string $url
     * @param array $body
     * @param array $header
     * @return object
     */
    public function post(?string $url = null, array $body = [], array $header = []) : self
    {
        return $this->request(self::POST, $body, $header, $url);
    }

    /**
     * Make Http HEAD request.
     *
     * @access public
     * @param ?string $url
     * @param array $body
     * @param array $header
     * @return object
     */
    public function head(?string $url = null, array $body = [], array $header = []) : self
    {
        return $this->request(self::HEAD, $body, $header, $url);
    }

    /**
     * Make Http PUT request.
     *
     * @access public
     * @param ?string $url
     * @param array $body
     * @param array $header
     * @return object
     */
    public function put(?string $url = null, array $body = [], array $header = []) : self
    {
        return $this->request(self::PUT, $body, $header, $url);
    }

    /**
     * Make Http PATCH request.
     *
     * @access public
     * @param ?string $url
     * @param array $body
     * @param array $header
     * @return object
     */
    public function patch(?string $url = null, array $body = [], array $header = []) : self
    {
        return $this->request(self::PATCH, $body, $header, $url);
    }

    /**
     * Make Http DELETE request.
     *
     * @access public
     * @param ?string $url
     * @param array $body
     * @param array $header
     * @return object
     */
    public function delete(?string $url = null, array $body = [], array $header = []) : self
    {
        return $this->request(self::DELETE, $body, $header, $url);
    }

    /**
     * Set request method.
     *
     * @access public
     * @param string $method
     * @return object
     */
    public function setMethod(string $method) : self
    {
        $this->method = Stringify::uppercase($method);
        return $this;
    }

    /**
     * Set request header.
     *
     * @access public
     * @param array $header
     * @return object
     */
    public function setHeader(array $header = []) : self
    {
        $this->header = Arrayify::merge(
            $this->header,
            $header
        );
        return $this;
    }

    /**
     * Set request body (Data).
     *
     * @access public
     * @param array $body
     * @return object
     */
    public function setBody(array $body = []) : self
    {
        $this->body = Arrayify::merge(
            $this->body,
            $body
        );
        return $this;
    }

    /**
     * Set request URL (Append to base URL).
     *
     * @access public
     * @param ?string $url
     * @return object
     */
    public function setUrl(?string $url = null) : self
    {
        $url = (string)$url;
        $baseUrl = (string)$this->baseUrl;
        $this->url = Stringify::formatPath("{$baseUrl}/{$url}", true);
        return $this;
    }

    /**
     * Set request timeout.
     *
     * @access public
     * @param int $timeout
     * @return object
     */
    public function setTimeout(int $timeout) : self
    {
        $this->params['timeout'] = $timeout;
        return $this;
    }

    /**
     * Set request max redirections.
     *
     * @access public
     * @param int $redirect
     * @return object
     */
    public function setRedirect(int $redirect) : self
    {
        $this->params['redirect'] = $redirect;
        return $this;
    }

    /**
     * Set request encoding.
     *
     * @access public
     * @param string $encoding
     * @return object
     */
    public function setEncoding(string $encoding) : self
    {
        $this->params['encoding'] = $encoding;
        return $this;
    }

    /**
     * Set User-Agent.
     *
     * @access public
     * @param ?string $ua
     * @return object
     */
    public function setUserAgent(?string $ua = null) : self
    {
        $this->params['ua'] = $ua;
        return $this;
    }

    /**
     * Allow cURL transfer return.
     *
     * @access public
     * @return object
     */
    public function return() : self
    {
        $this->params['return'] = true;
        return $this;
    }

    /**
     * Allow cURL redirection follow.
     *
     * @access public
     * @return object
     */
    public function follow() : self
    {
        $this->params['follow'] = true;
        return $this;
    }

    /**
     * Allow cURL header in reponse.
     *
     * @access public
     * @return object
     */
    public function headerIn() : self
    {
        $this->params['headerIn'] = true;
        return $this;
    }

    /**
     * Get response data.
     *
     * @access public
     * @return array
     */
    public function getResponse() : array
    {
        return $this->response;
    }

    /**
     * Get response status (code, message).
     *
     * @access public
     * @return array
     */
    public function getStatus() : array
    {
        return $this->getResponse()['status'] ?? [];
    }

    /**
     * Get response status code.
     *
     * @access public
     * @return int
     */
    public function getStatusCode() : int
    {
        return $this->getStatus()['code'] ?? -1;
    }

    /**
     * Get response status message.
     *
     * @access public
     * @return string
     */
    public function getStatusMessage() : string
    {
        return $this->getStatus()['message'] ?? '';
    }

    /**
     * Get response header.
     *
     * @access public
     * @return array
     */
    public function getHeader() : array
    {
        return $this->getResponse()['header'] ?? [];
    }

    /**
     * Get response body.
     *
     * @access public
     * @param bool $decode JSON
     * @return mixed
     */
    public function getBody(bool $decode = true) : mixed
    {
        $body = $this->getResponse()['body'] ?? '';
        if ( $decode ) {
            return Json::decode($body, isArray: true) ?: [];
        }
        return $body;
    }

    /**
     * Check client error (Http|Gateway).
     *
     * @access public
     * @param int $httpCode
     * @return bool
     */
    public function hasError(int $httpCode = 400) : bool
    {
        // Check for Gateway error
        $error = $this->getResponse()['error'] ?? false;
        if ( $error ) return true;

        // Check for Http error
        return $this->getStatusCode() >= $httpCode;
    }

    /**
     * Check curl gateway.
     *
     * @access public
     * @return bool
     */
    public function isCurl() : bool
    {
        return $this->gateway == self::CURL;
    }

    /**
     * Check stream gateway.
     *
     * @access public
     * @return bool
     */
    public function isStream() : bool
    {
        return $this->gateway == self::STREAM;
    }

    /**
     * Get effective URL.
     *
     * @access public
     * @param ?string $url
     * @param bool $parse
     * @return string
     */
    public function getLocation(?string $url = null, bool $parse = true) : string
    {
        // Set url
        $this->setUrl($url);

        // Get location
        $location = $this->gateway::getLocation($this->url, [
            'header'   => $this->header,
            'body'     => $this->body,
            'method'   => $this->method,
            'timeout'  => $this->params['timeout'],
            'redirect' => $this->params['redirect'],
            'encoding' => $this->params['encoding'],
            'ssl'      => Server::isSsl()
        ]);

        return $parse ? self::parseUrl($location) : $location;
    }

    /**
     * Parse URL (clean).
     *
     * @access public
     * @param string $url
     * @return string
     */
    public static function parseUrl(string $url) : string
    {
        $scheme = Stringify::parseUrl($url, 0);
        $host = Stringify::parseUrl($url, 1);
        $path = Stringify::parseUrl($url, 5);
        return Stringify::formatPath("{$scheme}://{$host}/{$path}");
    }

    /**
     * Check cURL status.
     *
     * @access public
     * @return bool
     */
    public static function hasCurl() : bool
    {
        return TypeCheck::isFunction('curl-init');
    }

    /**
     * Check stream status.
     *
     * @access public
     * @return bool
     */
    public static function hasStream() : bool
    {
        $val = intval(System::getIni('allow-url-fopen'));
        return (bool)$val;
    }

    /**
     * Set Http parser patterns.
     *
     * @access public
     * @param array $pattern
     * @return void
     */
    public static function setPattern(array $pattern = []) : void
    {
        self::$pattern = Arrayify::merge(self::PATTERN, $pattern);
    }

    /**
     * Get Http parser patterns.
     *
     * @access public
     * @param string $name
     * @return string
     */
    public static function getPattern(string $name) : string
    {
        return self::$pattern[$name] ?? '';
    }

    /**
     * Get request body data (query).
     *
     * @access public
     * @param array $body
     * @param string $url
     * @return string
     */
    public static function getQuery(array $body, ?string $url = null) : string
    {
        $query = [];
        foreach ($body as $key => $value) {
            $value = Converter::toString($value);
            if ( TypeCheck::isInt($key) ) {
                $query[$value] = '';

            } else {
                $query[$key] = $value;
            }
        }

        $query = Stringify::buildQuery($query, '', '$', 2);
        $query = Stringify::replace('=&', '&', $query);
        $query = rtrim($query, '=');

        if ( $url && $query ) {
            $query = "{$url}?{$query}";
        }

        return $query;
    }

    /**
     * Format inline Http header.
     *
     * @access public
     * @param array $header
     * @return string
     */
    public static function formatHeader(array $header) : string
    {
        $format = '';
        foreach ($header as $key => $value) {
            $format .= "{$key}: {$value}\r\n";
        }
        return $format;
    }

    /**
     * Get default User-Agent.
     *
     * @access public
     * @return string
     */
    public static function getUserAgent() : string
    {
        return implode(', ', static::USERAGENT);
    }

    /**
     * Get default Http client parameters.
     *
     * @access public
     * @param array $params
     * @return array
     */
    public static function getParams(array $params = []) : array
    {
        return Arrayify::merge([
            'header'   => [],
            'body'     => [],
            'method'   => null,
            'timeout'  => self::TIMEOUT,
            'redirect' => self::REDIRECT,
            'ua'       => self::getUserAgent(),
            'ssl'      => true,
            'encoding' => null,
            'return'   => false,
            'follow'   => false,
            'headerIn' => false
        ], $params);
    }

    /**
     * Get default extra params (crawler).
     *
     * @access public
     * @param array $params
     * @return array
     */
    public static function getExtra(array $params = []) : array
    {
        return Arrayify::merge([
            'path'      => null,
            'signature' => null,
            'ext'       => null
        ], $params);
    }

    /**
     * Execute request.
     *
     * @access protected
     * @return void
     */
    protected function execute() : void
    {
        $this->response = $this->gateway::request($this->url, [
            'header'   => $this->header,
            'body'     => $this->body,
            'method'   => $this->method,
            'timeout'  => $this->params['timeout'],
            'redirect' => $this->params['redirect'],
            'encoding' => $this->params['encoding'],
            'return'   => $this->params['return'],
            'follow'   => $this->params['follow'],
            'headerIn' => $this->params['headerIn'],
            'ua'       => $this->params['ua'],
            'ssl'      => Server::isSsl()
        ]);
    }

    /**
     * Set gateway.
     *
     * @access protected
     * @return void
     * @throws ClientException
     */
    protected function setGateway() : void
    {
        $this->gateway = match (true) {
            self::hasCurl()   => self::CURL,
            self::hasStream() => self::STREAM,
            default           => 'undefined'
        };

        if ( $this->gateway == 'undefined' ) {
            throw new ClientException(
                ClientException::invalidGateway()
            );
        }

        $ns = __NAMESPACE__;
        $gateway = "{$ns}\\{$this->gateway}";
        $this->gateway = new $gateway;
    }

    /**
     * Reset client.
     *
     * @access protected
     * @return void
     */
    protected function reset() : void
    {
        $this->header = [];
        $this->body = [];
        $this->response = [];
    }
}
