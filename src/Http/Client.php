<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Http Component
 * @version    : 1.3.x
 * @copyright  : (c) 2018 - 2024 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Http;

use FloatPHP\Classes\Filesystem\{Stringify, Arrayify, Json};

/**
 * Advanced HTTP client (cURL or Steam).
 */
class Client
{
    /**
     * @access protected
     * @var array $request
     * @var array $response
     * @var string $method
     * @var string $url
     * @var int $timeout
     */
    protected $request = [];
    protected $response = [];
    protected $method = 'GET';
    protected $url;
    protected $timeout = 5;

    /**
     * @access public
     * @param string $url
     * @param int $timeout
     */
    public function __construct(string $url = '', int $timeout = 5)
    {
        $this->url = $url;
        $this->timeout = $timeout;
    }

    /**
     * Make HTTP request.
     *
     * @access public
     * @param string $method
     * @param array $body
     * @param array $header
     * @param string $url
     * @return object
     */
    public function request(string $method, array $body = [], array $headers = [], string $url = '') : self
    {
        // Init client
        $this->init();

        // Set method
        $this->method = Stringify::lowercase($method);

        // Set body
        $this->setBody($body);

        // Set headers
        $this->setHeaders($headers);

        // Set url
        $this->setUrl($url);

        // Execute request
        $this->execute();

        return $this;
    }

    /**
     * Make HTTP GET request.
     *
     * @access public
     * @param array $body
     * @param array $headers
     * @param string $url
     * @return object
     */
    public function get(array $body = [], array $headers = [], string $url = '') : self
    {
        return $this->request('GET', $body, $headers, $url);
    }

    /**
     * Make HTTP POST request.
     *
     * @access public
     * @param array $body
     * @param array $header
     * @param string $url
     * @return object
     */
    public function post(array $body = [], array $headers = [], string $url = '') : self
    {
        return $this->request('POST', $body, $headers, $url);
    }

    /**
     * Get response.
     * 
     * @access public
     * @return array
     */
    public function getResponse() : array
    {
        return [
            'status' => $this->response['status'],
            'header' => $this->response['header'],
            'body'   => $this->response['body']
        ];
    }

    /**
     * Get response status.
     * 
     * @access public
     * @return array
     */
    public function getStatus() : array
    {
        return $this->response['status'];
    }

    /**
     * Get response status code.
     * 
     * @access public
     * @return mixed
     */
    public function getStatusCode() : mixed
    {
        if ( isset($this->response['status']['statusCode']) ) {
            return intval($this->response['status']['statusCode']);
        }
        return false;
    }

    /**
     * Get response header.
     * 
     * @access public
     * @return array
     */
    public function getHeader() : array
    {
        return $this->response['header'];
    }

    /**
     * Set request header.
     * 
     * @access public
     * @param array $header
     * @return void
     */
    public function setHeaders(array $header = []) : void
    {
        $this->request['header'] = Arrayify::merge($this->request['header'], $header);
    }

    /**
     * Set request body.
     * 
     * @access public
     * @param array $body
     * @return void
     */
    public function setBody(array $body = []) : void
    {
        $this->request['body'] = $body;
    }

    /**
     * Set request url.
     * 
     * @access public
     * @param string $url
     * @return void
     */
    public function setUrl(string $url = '') : void
    {
        if ( !empty($url) ) {
            $this->url = $url;
        }
    }

    /**
     * Set request timeout.
     * 
     * @access public
     * @param int $timeout
     * @return void
     */
    public function setTimout(int $timeout = 5) : void
    {
        $this->timeout = $timeout;
    }

    /**
     * Get response body.
     *
     * @access public
     * @param bool $decode
     * @return mixed
     */
    public function getBody(bool $decode = false) : mixed
    {
        $body = $this->response['body'] ?? '';
        return $decode ? Json::decode($body, isArray: true) : $body;
    }

    /**
     * Track url.
     * 
     * @access public
     * @param string $url
     * @param bool $parse
     * @return string
     */
    public function trackUrl(string $url, bool $parse = true) : string
    {
        // Set url
        if ( empty($url) ) {
            $url = $this->url;
        }

        // Init handler
        $handler = curl_init();
        curl_setopt_array($handler, [
            "CURLOPT_URL"            => $url,
            "CURLOPT_HEADER"         => true,
            "CURLOPT_RETURNTRANSFER" => true,
            "CURLOPT_FOLLOWLOCATION" => true,
            "CURLOPT_SSL_VERIFYHOST" => false,
            "CURLOPT_SSL_VERIFYPEER" => false,
            "CURLOPT_MAXREDIRS"      => 5,
            "CURLOPT_TIMEOUT"        => 0,
            "CURLOPT_CUSTOMREQUEST"  => 'GET',
            "CURLOPT_HTTPHEADER"     => []
        ]);

        // Get response
        $response = curl_exec($handler);
        $track = curl_getinfo($handler, CURLINFO_EFFECTIVE_URL);
        if ( $track == $url ) {
            if ( preg_match("/^window.location.href='(.+)$/im", $response, $matches) ) {
                $track = trim($matches[1]);
            }
        }
        curl_close($handler);

        // Parse url
        if ( $parse ) {
            $parts = Stringify::parseUrl($track);
            if ( isset($parts['query']) ) {
                unset($parts['query']);
            }
            $track = "{$parts['scheme']}://{$parts['host']}{$parts['path']}";
        }

        return (string)$track;
    }

    /**
     * Init client.
     * 
     * @access protected
     * @return void
     */
    protected function init() : void
    {
        $this->request = [
            'header' => [],
            'body'   => []
        ];
        $this->response = [
            'header' => [],
            'status' => [],
            'body'   => null
        ];
    }

    /**
     * Execute request.
     * 
     * @access protected
     * @return void
     */
    protected function execute()
    {
        // Init curl
        $handler = curl_init();

        curl_setopt($handler, CURLOPT_URL, $this->url);
        if ( $this->request['header'] ) {
            curl_setopt($handler, CURLOPT_HTTPHEADER, $this->request['header']);
        }
        curl_setopt($handler, CURLOPT_HEADERFUNCTION, [$this, 'catchHeader']);
        curl_setopt($handler, CURLOPT_WRITEFUNCTION, [$this, 'catchBody']);

        // Additional options
        curl_setopt($handler, CURLOPT_TIMEOUT, $this->timeout);

        if ( $this->method == 'post' ) {
            curl_setopt($handler, CURLOPT_POST, true);
            curl_setopt($handler, CURLOPT_POSTFIELDS, $this->request['body']);

        } elseif ( $this->method == 'put' ) {
            curl_setopt($handler, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($handler, CURLOPT_POSTFIELDS, $this->request['body']);

        } else {
            curl_setopt($handler, CURLOPT_CUSTOMREQUEST, Stringify::uppercase($this->method));
        }

        if ( !Server::isSsl() ) {
            curl_setopt($handler, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($handler, CURLOPT_SSL_VERIFYPEER, false);
        }

        // Execute request
        curl_exec($handler);
        curl_close($handler);
    }

    /**
     * Process incoming response header.
     * 
     * @access protected
     * @param object $handler
     * @param string $header
     * @return int
     */
    protected function catchHeader($handler, string $header) : int
    {
        // Parse HTTP status
        if ( $this->response['status'] == null ) {
            $regex = '/^\s*HTTP\s*\/\s*(?P<protocolVersion>\d*\.\d*)\s*(?P<statusCode>\d*)\s(?P<reasonPhrase>.*)\r\n/';
            preg_match($regex, $header, $matches);
            foreach (['protocolVersion', 'statusCode', 'reasonPhrase'] as $part) {
                if ( isset($matches[$part]) ) {
                    $this->response['status'][$part] = $matches[$part];
                }
            }
        }
        // Digest HTTP header attributes
        $regex = '/^\s*(?P<attributeName>[a-zA-Z0-9-]*):\s*(?P<attributeValue>.*)\r\n/';
        preg_match($regex, $header, $matches);
        if ( isset($matches['attributeName']) ) {
            $this->response['header'][$matches['attributeName']] = $matches['attributeValue'] ?? null;
        }
        return strlen($header);
    }

    /**
     * Process incoming response body.
     *
     * @access protected
     * @param object $handler
     * @param string $body
     * @return int
     */
    protected function catchBody($handler, string $body) : int
    {
        $this->response['body'] .= $body;
        return strlen($body);
    }
}
