<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Http Component Tests
 * @version    : 1.5.x
 * @copyright  : (c) 2018 - 2025 Jihad Sinnaour <me@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Tests\Classes\Http;

use PHPUnit\Framework\TestCase;
use FloatPHP\Classes\Http\Status;

/**
 * Status class tests.
 */
final class StatusTest extends TestCase
{
    /**
     * Test status constants exist.
     */
    public function testStatusConstantsExist(): void
    {
        $this->assertTrue(defined('FloatPHP\Classes\Http\Status::TYPES'));
        $this->assertIsArray(Status::TYPES);
    }

    /**
     * Test common HTTP status codes.
     */
    public function testCommonStatusCodes(): void
    {
        $types = Status::TYPES;
        
        // Test 2xx Success codes
        $this->assertEquals('OK', $types[200]);
        $this->assertEquals('Created', $types[201]);
        $this->assertEquals('Accepted', $types[202]);
        $this->assertEquals('No Content', $types[204]);
        
        // Test 3xx Redirection codes
        $this->assertEquals('Moved Permanently', $types[301]);
        $this->assertEquals('Found', $types[302]);
        $this->assertEquals('Not Modified', $types[304]);
        
        // Test 4xx Client Error codes
        $this->assertEquals('Bad Request', $types[400]);
        $this->assertEquals('Unauthorized', $types[401]);
        $this->assertEquals('Forbidden', $types[403]);
        $this->assertEquals('Not Found', $types[404]);
        $this->assertEquals('Method Not Allowed', $types[405]);
        
        // Test 5xx Server Error codes
        $this->assertEquals('Internal Server Error', $types[500]);
        $this->assertEquals('Bad Gateway', $types[502]);
        $this->assertEquals('Service Unavailable', $types[503]);
    }

    /**
     * Test informational status codes (1xx).
     */
    public function testInformationalStatusCodes(): void
    {
        $types = Status::TYPES;
        
        $this->assertEquals('Continue', $types[100]);
        $this->assertEquals('Switching Protocols', $types[101]);
    }

    /**
     * Test success status codes (2xx).
     */
    public function testSuccessStatusCodes(): void
    {
        $types = Status::TYPES;
        
        $this->assertEquals('OK', $types[200]);
        $this->assertEquals('Created', $types[201]);
        $this->assertEquals('Accepted', $types[202]);
        $this->assertEquals('Non-Authoritative Information', $types[203]);
        $this->assertEquals('No Content', $types[204]);
        $this->assertEquals('Reset Content', $types[205]);
        $this->assertEquals('Partial Content', $types[206]);
    }

    /**
     * Test redirection status codes (3xx).
     */
    public function testRedirectionStatusCodes(): void
    {
        $types = Status::TYPES;
        
        $this->assertEquals('Multiple Choices', $types[300]);
        $this->assertEquals('Moved Permanently', $types[301]);
        $this->assertEquals('Found', $types[302]);
        $this->assertEquals('See Other', $types[303]);
    }

    /**
     * Test client error status codes (4xx).
     */
    public function testClientErrorStatusCodes(): void
    {
        $types = Status::TYPES;
        
        $this->assertArrayHasKey(400, $types);
        $this->assertArrayHasKey(401, $types);
        $this->assertArrayHasKey(403, $types);
        $this->assertArrayHasKey(404, $types);
        $this->assertArrayHasKey(405, $types);
        $this->assertArrayHasKey(406, $types);
        $this->assertArrayHasKey(408, $types);
        $this->assertArrayHasKey(409, $types);
        $this->assertArrayHasKey(410, $types);
        $this->assertArrayHasKey(413, $types);
        $this->assertArrayHasKey(414, $types);
        $this->assertArrayHasKey(415, $types);
        $this->assertArrayHasKey(422, $types);
        $this->assertArrayHasKey(429, $types);
    }

    /**
     * Test server error status codes (5xx).
     */
    public function testServerErrorStatusCodes(): void
    {
        $types = Status::TYPES;
        
        $this->assertArrayHasKey(500, $types);
        $this->assertArrayHasKey(501, $types);
        $this->assertArrayHasKey(502, $types);
        $this->assertArrayHasKey(503, $types);
        $this->assertArrayHasKey(504, $types);
        $this->assertArrayHasKey(505, $types);
    }

    /**
     * Test get message method exists.
     */
    public function testGetMessageMethodExists(): void
    {
        $this->assertTrue(method_exists(Status::class, 'getMessage'));
    }

    /**
     * Test get message for valid codes.
     */
    public function testGetMessageForValidCodes(): void
    {
        $message200 = Status::getMessage(200);
        $this->assertEquals('OK', $message200);
        
        $message404 = Status::getMessage(404);
        $this->assertEquals('Not Found', $message404);
        
        $message500 = Status::getMessage(500);
        $this->assertEquals('Internal Server Error', $message500);
    }

    /**
     * Test get message for invalid codes.
     */
    public function testGetMessageForInvalidCodes(): void
    {
        $message = Status::getMessage(999);
        $this->assertNull($message);
        
        $message = Status::getMessage(-1);
        $this->assertNull($message);
    }

    /**
     * Test set status method exists.
     */
    public function testSetStatusMethodExists(): void
    {
        $this->assertTrue(method_exists(Status::class, 'set'));
    }

    /**
     * Test status code ranges.
     */
    public function testStatusCodeRanges(): void
    {
        $types = Status::TYPES;
        
        // Check we have codes in each range
        $hasInformational = false;
        $hasSuccess = false;
        $hasRedirection = false;
        $hasClientError = false;
        $hasServerError = false;
        
        foreach (array_keys($types) as $code) {
            if ($code >= 100 && $code < 200) $hasInformational = true;
            if ($code >= 200 && $code < 300) $hasSuccess = true;
            if ($code >= 300 && $code < 400) $hasRedirection = true;
            if ($code >= 400 && $code < 500) $hasClientError = true;
            if ($code >= 500 && $code < 600) $hasServerError = true;
        }
        
        $this->assertTrue($hasInformational);
        $this->assertTrue($hasSuccess);
        $this->assertTrue($hasRedirection);
        $this->assertTrue($hasClientError);
        $this->assertTrue($hasServerError);
    }

    /**
     * Test status messages are strings.
     */
    public function testStatusMessagesAreStrings(): void
    {
        foreach (Status::TYPES as $code => $message) {
            $this->assertIsInt($code);
            $this->assertIsString($message);
            $this->assertNotEmpty($message);
        }
    }

    /**
     * Test common REST API status codes.
     */
    public function testRestApiStatusCodes(): void
    {
        $types = Status::TYPES;
        
        // Common REST API codes
        $restCodes = [
            200 => 'OK',
            201 => 'Created',
            204 => 'No Content',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            422 => 'Unprocessable Entity',
            500 => 'Internal Server Error'
        ];
        
        foreach ($restCodes as $code => $expectedMessage) {
            $this->assertArrayHasKey($code, $types);
            $this->assertEquals($expectedMessage, $types[$code]);
        }
    }

    /**
     * Test specific HTTP status scenarios.
     */
    public function testSpecificHttpStatusScenarios(): void
    {
        $types = Status::TYPES;
        
        // Authentication related
        $this->assertEquals('Unauthorized', $types[401]);
        $this->assertEquals('Forbidden', $types[403]);
        
        // Content related
        $this->assertEquals('Not Found', $types[404]);
        $this->assertEquals('Gone', $types[410]);
        
        // Server issues
        $this->assertEquals('Internal Server Error', $types[500]);
        $this->assertEquals('Service Unavailable', $types[503]);
        
        // Caching related
        $this->assertEquals('Not Modified', $types[304]);
        
        // Rate limiting
        $this->assertEquals('Too Many Requests', $types[429]);
    }

    /**
     * Test status code completeness.
     */
    public function testStatusCodeCompleteness(): void
    {
        $types = Status::TYPES;
        
        // Ensure we have a reasonable number of status codes
        $this->assertGreaterThan(30, count($types));
        
        // Ensure key status codes exist
        $essentialCodes = [100, 200, 201, 301, 302, 400, 401, 403, 404, 500];
        foreach ($essentialCodes as $code) {
            $this->assertArrayHasKey($code, $types, "Essential status code {$code} should exist");
        }
    }

    /**
     * Test status code validation.
     */
    public function testStatusCodeValidation(): void
    {
        foreach (Status::TYPES as $code => $message) {
            // All codes should be between 100-599
            $this->assertGreaterThanOrEqual(100, $code);
            $this->assertLessThan(600, $code);
            
            // Messages should not be empty
            $this->assertNotEmpty(trim($message));
        }
    }
}
