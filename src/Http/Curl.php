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

use FloatPHP\Classes\Filesystem\{Stringify, File};
use \CurlHandle, \CurlMultiHandle;

/**
 * Advanced cURL manipulation.
 */
final class Curl
{
    /**
     * @access public
     * @var int cURL option
     */
    public const URL            = CURLOPT_URL;
    public const HEADER         = CURLOPT_HEADER;
    public const NOBODY         = CURLOPT_NOBODY;
    public const HTTPHEADER     = CURLOPT_HTTPHEADER;
    public const HEADERFUNC     = CURLOPT_HEADERFUNCTION;
    public const WRITEFUNC      = CURLOPT_WRITEFUNCTION;
    public const TIMEOUT        = CURLOPT_TIMEOUT;
    public const CONNECTTIMEOUT = CURLOPT_CONNECTTIMEOUT;
    public const POST           = CURLOPT_POST;
    public const POSTFIELDS     = CURLOPT_POSTFIELDS;
    public const CUSTOMREQUEST  = CURLOPT_CUSTOMREQUEST;
    public const VERIFYHOST     = CURLOPT_SSL_VERIFYHOST;
    public const VERIFYPEER     = CURLOPT_SSL_VERIFYPEER;
    public const RETURNTRANSFER = CURLOPT_RETURNTRANSFER;
    public const FOLLOWLOCATION = CURLOPT_FOLLOWLOCATION;
    public const MAXREDIRS      = CURLOPT_MAXREDIRS;
    public const ENCODING       = CURLOPT_ENCODING;
    public const USERAGENT      = CURLOPT_USERAGENT;
    public const FILE           = CURLOPT_FILE;

    /**
     * @access public
     * @var int cURL response
     */
    public const EFFECTIVEURL  = CURLINFO_EFFECTIVE_URL;
    public const TOTALTIME     = CURLINFO_TOTAL_TIME;
    public const HTTPCODE      = CURLINFO_HTTP_CODE;
    public const OK            = CURLM_OK;
    public const INTERNALERROR = CURLM_INTERNAL_ERROR;
    public const BADHANDLE     = CURLM_BAD_HANDLE;
    public const BADEASYHANDLE = CURLM_BAD_EASY_HANDLE;
    public const OUTOFMEMORY   = CURLM_OUT_OF_MEMORY;
    public const ALREADYADDED  = CURLM_ADDED_ALREADY;

    /**
     * @access private
     * @var array $responseStatus
     * @var array $responseHeader
     * @var string $responseBody
     */
    private static $responseStatus = [];
    private static $responseHeader = [];
    private static $responseBody = '';

    /**
     * Init cURL handle.
     *
     * @access public
     * @param string $url
     * @return CurlHandle|false
     */
    public static function init(?string $url = null) : CurlHandle|false
    {
        return curl_init($url);
    }

    /**
     * Execute cURL handle.
     *
     * @access public
     * @param CurlHandle $handle
     * @return string|bool
     */
    public static function exec(CurlHandle $handle) : string|bool
    {
        return curl_exec($handle);
    }

    /**
     * Execute cURL handle (Alias with response and close).
     *
     * @access public
     * @param CurlHandle $handle
     * @return string
     */
    public static function execute(CurlHandle $handle) : string
    {
        self::return($handle); // Force return
        $response = (string)self::exec($handle);
        self::close($handle);
        return $response;
    }

    /**
     * Set cURL single option.
     *
     * @access public
     * @param CurlHandle $handle
     * @param int $option
     * @param mixed $value
     * @return bool
     */
    public static function setOpt(CurlHandle $handle, int $option, mixed $value) : bool
    {
        return curl_setopt($handle, $option, $value);
    }

    /**
     * Set cURL single option (Alias with option name).
     *
     * @access public
     * @param CurlHandle $handle
     * @param string $option
     * @param mixed $value
     * @return bool
     */
    public static function setOption(CurlHandle $handle, string $option, mixed $value) : bool
    {
        $option = Stringify::uppercase($option);
        $class = self::class;
        $const = "{$class}::{$option}";
        return self::setOpt($handle, $const, $value);
    }

    /**
     * Set cURL URL.
     *
     * @access public
     * @param CurlHandle $handle
     * @param string $url
     * @return bool
     */
    public static function setUrl(CurlHandle $handle, string $url) : bool
    {
        return self::setOpt($handle, self::URL, $url);
    }

    /**
     * Set cURL timeoout.
     *
     * @access public
     * @param CurlHandle $handle
     * @param int $timeoout
     * @return bool
     */
    public static function setTimeout(CurlHandle $handle, int $timeoout) : bool
    {
        return self::setOpt($handle, self::TIMEOUT, $timeoout);
    }

    /**
     * Set cURL max redirections.
     *
     * @access public
     * @param CurlHandle $handle
     * @param int $max
     * @return bool
     */
    public static function setRedirect(CurlHandle $handle, int $max) : bool
    {
        return self::setOpt($handle, self::MAXREDIRS, $max);
    }

    /**
     * Set cURL encoding.
     *
     * @access public
     * @param CurlHandle $handle
     * @param string $encoding
     * @return bool
     */
    public static function setEncoding(CurlHandle $handle, string $encoding) : bool
    {
        return self::setOpt($handle, self::TIMEOUT, $encoding);
    }

    /**
     * Set User-Agent.
     *
     * @access public
     * @param CurlHandle $handle
     * @param string $ua
     * @return bool
     */
    public static function setUserAgent(CurlHandle $handle, string $ua) : bool
    {
        return self::setOpt($handle, self::USERAGENT, $ua);
    }

    /**
     * Set cURL HTTP method.
     *
     * @access public
     * @param CurlHandle $handle
     * @param string $method
     * @return bool
     */
    public static function setMethod(CurlHandle $handle, string $method) : bool
    {
        return self::setOpt($handle, self::CUSTOMREQUEST, $method);
    }

    /**
     * Set cURL POST method.
     *
     * @access public
     * @param CurlHandle $handle
     * @return bool
     */
    public static function setPost(CurlHandle $handle) : bool
    {
        return self::setOpt($handle, self::POST, true);
    }

    /**
     * Set cURL POST data.
     *
     * @access public
     * @param CurlHandle $handle
     * @param mixed $data
     * @return bool
     */
    public static function setPostData(CurlHandle $handle, mixed $data) : bool
    {
        return self::setOpt($handle, self::POSTFIELDS, $data);
    }

    /**
     * Set cURL body return.
     *
     * @access public
     * @param CurlHandle $handle
     * @param bool $status
     * @return bool
     */
    public static function return(CurlHandle $handle, bool $status = true) : bool
    {
        return self::setOpt($handle, self::RETURNTRANSFER, $status);
    }

    /**
     * Set cURL redirection follow.
     *
     * @access public
     * @param CurlHandle $handle
     * @param bool $status
     * @return bool
     */
    public static function follow(CurlHandle $handle, bool $status = true) : bool
    {
        return self::setOpt($handle, self::FOLLOWLOCATION, $status);
    }

    /**
     * Verify cURL host.
     *
     * @access public
     * @param CurlHandle $handle
     * @param bool $status
     * @return bool
     */
    public static function verifyHost(CurlHandle $handle, bool $status = true) : bool
    {
        $status = $status == true ? 2 : false;
        return self::setOpt($handle, self::VERIFYHOST, $status);
    }

    /**
     * Verify cURL peer.
     *
     * @access public
     * @param CurlHandle $handle
     * @param bool $status
     * @return bool
     */
    public static function verifyPeer(CurlHandle $handle, bool $status = true) : bool
    {
        return self::setOpt($handle, self::VERIFYPEER, $status);
    }

    /**
     * Set cURL header in reponse.
     *
     * @access public
     * @param CurlHandle $handle
     * @param bool $status
     * @return bool
     */
    public static function headerIn(CurlHandle $handle, bool $status = true) : bool
    {
        return self::setOpt($handle, self::HEADER, $status);
    }

    /**
     * Set cURL request header.
     *
     * @access public
     * @param CurlHandle $handle
     * @param array $header
     * @return bool
     */
    public static function setHeader(CurlHandle $handle, array $header) : bool
    {
        return self::setOpt($handle, self::HTTPHEADER, $header);
    }

    /**
     * Set cURL response header callback.
     * Force including header response.
     *
     * @access public
     * @param CurlHandle $handle
     * @param mixed $callback
     * @return bool
     */
    public static function setHeaderCallback(CurlHandle $handle, $callback = null) : bool
    {
        if ( !$callback ) {
            $callback = [self::class, 'parseHeader'];
        }
        return self::setOpt($handle, self::HEADERFUNC, $callback);
    }

    /**
     * Set cURL response body callback.
     *
     * @access public
     * @param CurlHandle $handle
     * @param mixed $callback
     * @return bool
     */
    public static function setBodyCallback(CurlHandle $handle, $callback = null) : bool
    {
        if ( !$callback ) {
            $callback = [self::class, 'parseBody'];
        }
        return self::setOpt($handle, self::WRITEFUNC, $callback);
    }

    /**
     * Set cURL array of options.
     *
     * @access public
     * @param CurlHandle $handle
     * @param array $options
     * @return bool
     */
    public static function setOptions(CurlHandle $handle, array $options) : bool
    {
        return curl_setopt_array($handle, $options);
    }

    /**
     * Get cURL handle last info.
     *
     * @access public
     * @param CurlHandle $handle
     * @param ?int $option
     * @return mixed
     */
    public static function getInfo(CurlHandle $handle, ?int $option = null) : mixed
    {
        return curl_getinfo($handle, $option);
    }

    /**
     * Get cURL error code.
     *
     * @access public
     * @param CurlHandle $handle
     * @return int
     */
    public static function getErrorCode(CurlHandle $handle) : int
    {
        return curl_errno($handle);
    }

    /**
     * Get cURL error message.
     *
     * @access public
     * @param CurlHandle $handle
     * @return string
     */
    public static function getErrorMessage(CurlHandle $handle) : string
    {
        return curl_error($handle);
    }

    /**
     * Get cURL error (Normalized).
     *
     * @access public
     * @param CurlHandle $handle
     * @return array
     */
    public static function getError(CurlHandle $handle) : array
    {
        return [
            'code'    => self::getErrorCode($handle),
            'message' => self::getErrorMessage($handle)
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
        // Rest cUrl
        self::reset();

        // Extract params
        $params = Client::getParams($params);
        extract($params);

        // Set method
        if ( !$method || $method == Client::POST ) {
            $method = Client::HEAD;
        }

        // Set body
        $url = $body ? Client::getQuery($body, $url) : $url;

        // Init
        $handle = self::init($url);

        // Set options
        self::setOptions($handle, [
            self::HEADER         => true,
            self::NOBODY         => true,
            self::FOLLOWLOCATION => true,
            self::RETURNTRANSFER => true,
            self::MAXREDIRS      => $redirect,
            self::TIMEOUT        => $timeout,
            self::HTTPHEADER     => $header,
            self::CUSTOMREQUEST  => $method,
            self::VERIFYHOST     => $ssl == true ? 2 : false,
            self::VERIFYPEER     => $ssl,
            self::USERAGENT      => $ua
        ]);

        // Get header
        $header = self::execute($handle);

        // Parse HTTP location
        $regex = Client::getPattern('location');
        if ( Stringify::match($regex, $header, $matches) ) {
            $url = trim($matches[1]);
        }

        return $url;
    }

    /**
     * Close cURL handle.
     *
     * @access public
     * @param CurlHandle $handle
     * @return void
     */
    public static function close(CurlHandle $handle) : void
    {
        curl_close($handle);
    }

    /**
     * Init multiple cURL handles.
     *
     * @access public
     * @return CurlMultiHandle
     */
    public static function initMultiple() : CurlMultiHandle
    {
        return curl_multi_init();
    }

    /**
     * Execute multiple cURL.
     *
     * @access public
     * @param CurlMultiHandle $multi
     * @param int $active Running
     * @return int
     */
    public static function execMultiple(CurlMultiHandle $multi, &$active) : int
    {
        return curl_multi_exec($multi, $active);
    }

    /**
     * Execute multiple cURL (Alias with loop).
     *
     * @access public
     * @param CurlMultiHandle $multi
     * @return int
     */
    public static function executeMultiple(CurlMultiHandle $multi) : int
    {
        do {
            $status = self::execMultiple($multi, $active);
            self::selectMultiple($multi);
        } while ($active && $status == self::OK);

        return $status;
    }

    /**
     * Select multiple cURL.
     *
     * @access public
     * @param CurlMultiHandle $multi
     * @param float $timeout
     * @return int
     */
    public static function selectMultiple(CurlMultiHandle $multi, float $timeout = 1.0) : int
    {
        return curl_multi_select($multi, $timeout);
    }

    /**
     * Get multiple cURL error.
     *
     * @access public
     * @param int $code
     * @return ?string
     */
    public static function getMultipleError(int $code) : ?string
    {
        return curl_multi_strerror($code);
    }

    /**
     * Get multiple cURL content.
     *
     * @access public
     * @param CurlHandle $handle
     * @return ?string
     */
    public static function getMultipleContent(CurlHandle $handle) : ?string
    {
        return curl_multi_getcontent($handle);
    }

    /**
     * Add handle to multiple cURL.
     *
     * @access public
     * @param CurlMultiHandle $multi
     * @param CurlHandle $handle
     * @return int 
     */
    public static function addHandle(CurlMultiHandle $multi, CurlHandle $handle) : int
    {
        return curl_multi_add_handle($multi, $handle);
    }

    /**
     * Remove handle from multiple cURL.
     *
     * @access public
     * @param CurlMultiHandle $multi
     * @param CurlHandle $handle
     * @return int 
     */
    public static function removeHandle(CurlMultiHandle $multi, CurlHandle $handle) : int
    {
        return curl_multi_remove_handle($multi, $handle);
    }

    /**
     * Close multiple cURL handle.
     *
     * @access public
     * @param CurlMultiHandle $handle
     * @return void
     */
    public static function closeMultiple(CurlMultiHandle $handle) : void
    {
        curl_multi_close($handle);
    }

    /**
     * Advanced cURL HTTP request.
     *
     * @access public
     * @param string $url
     * @param array $params
     * @return array
     */
    public static function request(string $url, array $params = []) : array
    {
        // Rest cUrl
        self::reset();

        // Extract params
        $params = Client::getParams($params);
        extract($params);

        // Set body
        if ( $body && $method !== Client::POST ) {
            $url = Client::getQuery($body, $url);
        }

        // Init cURL
        $handle = self::init($url);

        // Set options
        self::setHeader($handle, $header);
        self::setTimeout($handle, $timeout);

        if ( $encoding ) {
            self::setEncoding($handle, $encoding);
        }

        if ( $ua ) {
            self::setUserAgent($handle, $ua);
        }

        if ( $ssl === false ) {
            self::verifyHost($handle, false);
            self::verifyPeer($handle, false);
        }

        if ( $method !== Client::POST ) {
            self::setMethod($handle, $method);
        }

        if ( $method == Client::POST || $method == Client::PUT ) {
            if ( $method == self::POST ) {
                self::setPost($handle);
            }
            self::setPostData($handle, $body);
        }

        // Allow redirection follow
        if ( $follow === true ) {
            self::follow($handle);
            self::setRedirect($handle, $redirect);
        }

        // Allow body return
        if ( $return === true ) {
            self::return($handle);

        } else {
            self::setBodyCallback($handle);
        }

        // Include header response
        if ( $headerIn === true ) {
            self::headerIn($handle);

        } else {
            self::setHeaderCallback($handle);
        }

        // Get response
        $error = false;
        $response = self::exec($handle);

        if ( $response === false ) {
            $header = [];
            $status = self::getError($handle);
            $body = Status::getMessage(500);
            $error = true;

        } else {
            $header = self::getResponseHeader();
            $status = self::getResponseStatus();
            $body = $return ? (string)$response : self::getResponseBody();
        }

        // Close handle
        self::close($handle);

        // Return reponse data
        return [
            'error'  => $error,
            'status' => $status,
            'header' => $header,
            'body'   => $body
        ];
    }

    /**
     * Advanced multiple cURL HTTP request.
     *
     * @access public
     * @param array $urls
     * @param array $params cURL params
     * @param array $extra Extra params (Crawl)
     * @return array
     */
    public static function requestMultiple(array $urls, array $params = [], array $extra = []) : array
    {
        $response = [];

        // Init multiple
        $multiple = self::initMultiple();
        $handles = [];

        // Extract params
        $params = Client::getParams($params);
        extract($params);
        unset($params);

        foreach ($urls as $url) {

            // Init
            $handle = self::init($url);

            // Set options
            self::setOptions($handle, [
                self::RETURNTRANSFER => $return,
                self::FOLLOWLOCATION => $follow,
                self::HEADER         => $headerIn,
                self::MAXREDIRS      => $redirect,
                self::TIMEOUT        => $timeout,
                self::HTTPHEADER     => $header,
                self::CUSTOMREQUEST  => $method,
                self::VERIFYHOST     => $ssl == true ? 2 : false,
                self::VERIFYPEER     => $ssl,
                self::USERAGENT      => $ua
            ]);

            self::addHandle($multiple, $handle);
            $handles[$url] = $handle;
        }

        // Execute multiple handles
        self::executeMultiple($multiple);

        // Extract extra params
        $extra = Client::getExtra($extra);
        extract($extra);
        unset($extra);

        foreach ($handles as $url => $handle) {

            // Add to response on success
            if ( $content = (string)self::getMultipleContent($handle) ) {
                $response[] = $url;
            }

            // Save to file
            if ( $path && File::isDir($path) ) {

                $name = Client::parseUrl($url);
                $name = Stringify::slugify($name);
                $temp = Stringify::formatPath("{$path}/{$name}{$ext}");

                if ( $signature ) {
                    $break = Stringify::break();
                    $content .= "{$break}{$signature}";
                }

                File::w($temp, $content);
            }

            self::removeHandle($multiple, $handle);
            self::close($handle);
        }

        self::closeMultiple($multiple);
        unset($handles);

        return $response;
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
     * Parse response header.
     *
     * @access public
     * @param CurlHandle $handle
     * @param string $header
     * @return int
     */
    public static function parseHeader(CurlHandle $handle, string $header) : int
    {
        // Parse status
        $regex = Client::getPattern('status');
        if ( Stringify::match($regex, $header, $matches) ) {
            $code = $matches['code'] ?? -1;
            $code = (int)$code;
            $message = $matches['message'] ?? '';
            if ( empty($message) ) {
                $message = Status::getMessage($code);
            }
            self::$responseStatus = ['code' => $code, 'message' => $message];
        }

        // Get header attributes (multi-line)
        $regex = Client::getPattern('attribute');
        if ( Stringify::match($regex, $header, $matches) ) {
            if ( isset($matches['name']) && isset($matches['value']) ) {
                $name = $matches['name'];
                $value = trim($matches['value']);
                if ( isset(self::$responseHeader[$name]) ) {
                    self::$responseHeader[$name] .= "\n{$value}";

                } else {
                    self::$responseHeader[$name] = $value;
                }
            }
        }

        return strlen($header);
    }

    /**
     * Parse response body.
     *
     * @access public
     * @param CurlHandle $handle
     * @param string $body
     * @return int
     */
    public static function parseBody(CurlHandle $handle, string $body) : int
    {
        self::$responseBody .= $body;
        return strlen($body);
    }

    /**
     * Reset cURL.
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
