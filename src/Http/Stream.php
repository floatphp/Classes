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

use FloatPHP\Classes\Filesystem\{Stringify, Arrayify, File, Json};

/**
 * Advanced Stream manipulation.
 */
final class Stream
{
    /**
     * @access public
     * @var array cURL pattern
     */
    public const PATTERN = [
        'status'    => '/^\s*HTTP\/\d+(\.\d+)?\s+(?P<code>\d+)\s*(?P<message>.*)?\r?\n?$/',
        'attribute' => '/^\s*(?P<name>[a-zA-Z0-9\-]+)\s*:\s*(?P<value>.*?)\s*(?:\r?\n|$)/',
        'location'  => "/^window\.location\.href\s*=\s*['\"]([^'\"]+)['\"]\s*$/im"
    ];

    public function sendRequest(string $url, string $method = 'GET', array $headers = [], ?string $body = null) : array
    {
        $options = [
            'http' => [
                'method'        => strtoupper($method),
                'header'        => $this->formatHeaders($headers),
                'content'       => $body,
                'ignore_errors' => true,
            ]
        ];

        $responseBody = File::r($url, false, $options);
        $statusCode = $this->getStatusCode($http_response_header ?? []);

        return [
            'status_code' => $statusCode,
            'body'        => $responseBody
        ];
    }

    private function formatHeaders(array $headers) : string
    {
        $formattedHeaders = '';
        foreach ($headers as $key => $value) {
            $formattedHeaders .= "$key: $value\r\n";
        }
        return $formattedHeaders;
    }

    private function getStatusCode(array $responseHeaders) : ?int
    {
        if ( isset($responseHeaders[0]) ) {
            if ( preg_match('/HTTP\/[\d\.]+\s+(\d+)/', $responseHeaders[0], $matches) ) {
                return (int)$matches[1];
            }
        }
        return null;
    }
}
