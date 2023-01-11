<?php
/**
 * @author     : JIHAD SINNAOUR
 * @package    : FloatPHP
 * @subpackage : Classes Http Component
 * @version    : 1.0.1
 * @category   : PHP framework
 * @copyright  : (c) 2017 - 2023 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://www.floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Http;

use FloatPHP\Classes\Filesystem\{
    Stringify, Arrayify, Json
};

class Client
{
    /**
     * @access private
     * @var array $request
     * @var array $response
     * @var string $method
     * @var string $url
     * @var int $timout
     */
    private $request = [];
    private $response = [];
    private $method = 'get';
    private $url = '';
    private $timout = 5;

    /**
     * @param string $url
     */
    public function __construct(string $url = '')
    {
        $this->setUrl($url);
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
    public function request(string $method = 'POST', array $body = [], array $header = [], string $url = '') : object
    {
        // Init client
        $this->init();
        // Set method
        $this->method = Stringify::lowercase($method);
        // Set body
        $this->setBody($body);
        // Set header
        $this->setHeader($header);
        // Set url
        $this->setUrl($url);
        // Prepare request
        $this->prepare();

        return $this;
    }

    /**
     * Make HTTP POST request.
     *
     * @access public
     * @param array $body
     * @param array $header
     * @param string $url
     * @return string
     */
    public function post(array $body = [], array $header = [], string $url = '') : string
    {
        // Init client
        $this->init();
        // Set method
        $this->method = 'post';
        // Set body
        $this->setBody($body);
        // Set header
        $this->setHeader($header);
        // Set url
        $this->setUrl($url);
        // Prepare request
        $this->prepare();

        return $this->getBody();
    }

    /**
     * Make HTTP GET request.
     *
     * @access public
     * @param array $body
     * @param array $header
     * @param string $url
     * @return string
     */
    public function get(array $body = [], array $header = [], string $url = '') : string
    {
        // Init client
        $this->init();
        // Set method
        $this->method = 'get';
        // Set body
        $this->setBody($body);
        // Set header
        $this->setHeader($header);
        // Set url
        $this->setUrl($url);
        // Prepare request
        $this->prepare();

        return $this->getBody();
    }

    /**
     * Get response.
     * 
     * @access public
     * @param void
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
     * @param void
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
     * @param void
     * @return mixed
     */
    public function getStatusCode()
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
     * @param void
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
    public function setHeader(array $header = [])
    {
        $this->request['header'] = Arrayify::merge($this->request['header'],$header);
    }

    /**
     * Set request body.
     * 
     * @access public
     * @param array $body
     * @return void
     */
    public function setBody(array $body = [])
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
    public function setUrl(string $url = '')
    {
        if ( !empty($url) ) {
            $this->url = $url;
        }
    }

    /**
     * Set request timout.
     * 
     * @access public
     * @param int $timout
     * @return void
     */
    public function setTimout(int $timout = 5)
    {
        $this->timout = $timout;
    }
    
    /**
     * Get response body.
     * 
     * @access public
     * @param bool $json
     * @return mixed
     */
    public function getBody($json = false)
    {
        if ( $json ) {
           return Json::decode($this->response['body'],true);
        }
        return $this->response['body'];
    }

    /**
     * Track url.
     * 
     * @access public
     * @param string $url
     * @param bool $parse
     * @return string
     */
    public function trackUrl($url = '', $parse = true)
    {
        // Set url
        if ( empty($url) ) {
            $url = $this->url;
        }

        // Init handler
        $handler = curl_init();
        curl_setopt_array($handler,[
            CURLOPT_URL            => $url,
            CURLOPT_HEADER         => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_HTTPHEADER     => []
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
            $parts = parse_url($track);
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
     * @access private
     * @param void
     * @return void
     */
    private function init()
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
     * Prepare request.
     * 
     * @access private
     * @param void
     * @return void
     */
    private function prepare()
    {
        // Init curl
        $handler = curl_init();
        curl_setopt($handler,CURLOPT_URL,$this->url);
        if ( $this->request['header'] ) {
            curl_setopt($handler,CURLOPT_HTTPHEADER,$this->request['header']);
        }
        curl_setopt($handler,CURLOPT_HEADERFUNCTION,[$this,'catchHeader']);
        curl_setopt($handler,CURLOPT_WRITEFUNCTION,[$this,'catchBody']);

        // Additional options
        curl_setopt($handler,CURLOPT_TIMEOUT,$this->timout);
        if ( $this->method == 'post' ) {
            curl_setopt($handler,CURLOPT_POST,true);
            curl_setopt($handler,CURLOPT_POSTFIELDS,$this->request['body']);

        } elseif ( $this->method == 'put' ) {
            curl_setopt($handler,CURLOPT_CUSTOMREQUEST,'PUT');
            curl_setopt($handler,CURLOPT_POSTFIELDS,$this->request['body']);

        } else {
            curl_setopt($handler,CURLOPT_CUSTOMREQUEST,Stringify::uppercase($this->method));
        }

        if ( !Server::isSSL() ) {
            curl_setopt($handler,CURLOPT_SSL_VERIFYHOST,false);
            curl_setopt($handler,CURLOPT_SSL_VERIFYPEER,false);
        }

        // Execute request
        curl_exec($handler);
        curl_close($handler);
    }

    /**
     * Process incoming response header.
     * 
     * @access private
     * @param object $handler
     * @param string $header
     * @return int
     */
    private function catchHeader($handler, string $header) : int
    {
        // Parse HTTP status
        if ( $this->response['status'] == null ) {
            $regex = '/^\s*HTTP\s*\/\s*(?P<protocolVersion>\d*\.\d*)\s*(?P<statusCode>\d*)\s(?P<reasonPhrase>.*)\r\n/';
            preg_match($regex,$header,$matches);
            foreach (['protocolVersion','statusCode','reasonPhrase'] as $part) {
                if ( isset($matches[$part]) ) {
                    $this->response['status'][$part] = $matches[$part];
                }
            }
        }
        // Digest HTTP header attributes
        $regex = '/^\s*(?P<attributeName>[a-zA-Z0-9-]*):\s*(?P<attributeValue>.*)\r\n/';
        preg_match($regex,$header,$matches);
        if ( isset($matches['attributeName']) ) {
            $this->response['header'][$matches['attributeName']] = isset($matches['attributeValue']) ? $matches['attributeValue'] : null;
        }
        return strlen($header);
    }

    /**
     * Process incoming response body.
     *
     * @access private
     * @param object $handler
     * @param string $body
     * @return int
     */
    private function catchBody($handler, string $body) : int
    {
        $this->response['body'] .= $body;
        return strlen($body);
    }
}
