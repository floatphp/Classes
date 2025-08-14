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
use FloatPHP\Classes\Http\Xml;

/**
 * Xml class tests.
 */
final class XmlTest extends TestCase
{
    /**
     * Test XML parse method exists.
     */
    public function testXmlParseMethodExists(): void
    {
        $this->assertTrue(method_exists(Xml::class, 'parse'));
    }

    /**
     * Test simple XML parsing.
     */
    public function testSimpleXmlParsing(): void
    {
        $xmlString = '<?xml version="1.0" encoding="UTF-8"?>
            <root>
                <name>Test</name>
                <value>123</value>
            </root>';
        
        $result = Xml::parse($xmlString);
        $this->assertNotFalse($result);
    }

    /**
     * Test XML parsing with attributes.
     */
    public function testXmlParsingWithAttributes(): void
    {
        $xmlString = '<?xml version="1.0" encoding="UTF-8"?>
            <root>
                <item id="1" type="test">
                    <name>Test Item</name>
                    <value>100</value>
                </item>
                <item id="2" type="sample">
                    <name>Sample Item</name>
                    <value>200</value>
                </item>
            </root>';
        
        $result = Xml::parse($xmlString);
        $this->assertNotFalse($result);
    }

    /**
     * Test XML parsing with CDATA.
     */
    public function testXmlParsingWithCdata(): void
    {
        $xmlString = '<?xml version="1.0" encoding="UTF-8"?>
            <root>
                <description><![CDATA[This is a test description with <special> characters & symbols.]]></description>
                <content><![CDATA[
                    <h1>HTML Content</h1>
                    <p>This is a paragraph with <strong>bold</strong> text.</p>
                ]]></content>
            </root>';
        
        $result = Xml::parse($xmlString);
        $this->assertNotFalse($result);
    }

    /**
     * Test XML parsing with namespaces.
     */
    public function testXmlParsingWithNamespaces(): void
    {
        $xmlString = '<?xml version="1.0" encoding="UTF-8"?>
            <root xmlns:test="http://example.com/test">
                <test:item>
                    <test:name>Namespaced Item</test:name>
                    <test:value>500</test:value>
                </test:item>
            </root>';
        
        $result = Xml::parse($xmlString);
        $this->assertNotFalse($result);
    }

    /**
     * Test XML parsing with different options.
     */
    public function testXmlParsingWithDifferentOptions(): void
    {
        $xmlString = '<?xml version="1.0" encoding="UTF-8"?>
            <root>
                <item>Test Value</item>
            </root>';
        
        // Test with default options
        $result1 = Xml::parse($xmlString);
        $this->assertNotFalse($result1);
        
        // Test with custom options
        $result2 = Xml::parse($xmlString, LIBXML_NOCDATA);
        $this->assertNotFalse($result2);
    }

    /**
     * Test invalid XML parsing.
     */
    public function testInvalidXmlParsing(): void
    {
        $invalidXml = '<root><item>Unclosed tag</root>';
        
        $result = Xml::parse($invalidXml);
        $this->assertFalse($result);
    }

    /**
     * Test empty XML parsing.
     */
    public function testEmptyXmlParsing(): void
    {
        $result = Xml::parse('');
        $this->assertFalse($result);
    }

    /**
     * Test malformed XML parsing.
     */
    public function testMalformedXmlParsing(): void
    {
        $malformedXml = '<?xml version="1.0"?><root><item><name>Test</name><value>123</item></root>';
        
        $result = Xml::parse($malformedXml);
        $this->assertFalse($result);
    }

    /**
     * Test XML with special characters.
     */
    public function testXmlWithSpecialCharacters(): void
    {
        $xmlString = '<?xml version="1.0" encoding="UTF-8"?>
            <root>
                <text>Special chars: &lt; &gt; &amp; &quot; &apos;</text>
                <unicode>Unicode: ñ ü ß € 中文 العربية</unicode>
            </root>';
        
        $result = Xml::parse($xmlString);
        $this->assertNotFalse($result);
    }

    /**
     * Test large XML parsing.
     */
    public function testLargeXmlParsing(): void
    {
        $largeXml = '<?xml version="1.0" encoding="UTF-8"?><root>';
        
        // Generate large XML with many items
        for ($i = 1; $i <= 100; $i++) {
            $largeXml .= "<item id=\"{$i}\"><name>Item {$i}</name><value>" . ($i * 10) . "</value></item>";
        }
        
        $largeXml .= '</root>';
        
        $result = Xml::parse($largeXml);
        $this->assertNotFalse($result);
    }

    /**
     * Test XML parsing constants.
     */
    public function testXmlParsingConstants(): void
    {
        // Test that XML parsing constants are available
        $this->assertTrue(defined('LIBXML_NOCDATA'));
        $this->assertTrue(defined('LIBXML_VERSION'));
        
        $this->assertIsInt(LIBXML_NOCDATA);
        $this->assertIsInt(LIBXML_VERSION);
    }

    /**
     * Test complex nested XML.
     */
    public function testComplexNestedXml(): void
    {
        $xmlString = '<?xml version="1.0" encoding="UTF-8"?>
            <catalog>
                <book id="1">
                    <title>PHP Programming</title>
                    <author>
                        <name>John Doe</name>
                        <email>john@example.com</email>
                    </author>
                    <publisher>
                        <name>Tech Books</name>
                        <address>
                            <street>123 Main St</street>
                            <city>Anytown</city>
                            <country>USA</country>
                        </address>
                    </publisher>
                    <chapters>
                        <chapter number="1">Introduction</chapter>
                        <chapter number="2">Variables</chapter>
                        <chapter number="3">Functions</chapter>
                    </chapters>
                </book>
            </catalog>';
        
        $result = Xml::parse($xmlString);
        $this->assertNotFalse($result);
    }

    /**
     * Test XML with mixed content.
     */
    public function testXmlWithMixedContent(): void
    {
        $xmlString = '<?xml version="1.0" encoding="UTF-8"?>
            <document>
                <paragraph>
                    This is text with <emphasis>emphasized</emphasis> content
                    and <link href="http://example.com">a link</link>.
                </paragraph>
            </document>';
        
        $result = Xml::parse($xmlString);
        $this->assertNotFalse($result);
    }

    /**
     * Test XML with processing instructions.
     */
    public function testXmlWithProcessingInstructions(): void
    {
        $xmlString = '<?xml version="1.0" encoding="UTF-8"?>
            <?xml-stylesheet type="text/xsl" href="style.xsl"?>
            <root>
                <item>Test</item>
            </root>';
        
        $result = Xml::parse($xmlString);
        $this->assertNotFalse($result);
    }

    /**
     * Test XML with comments.
     */
    public function testXmlWithComments(): void
    {
        $xmlString = '<?xml version="1.0" encoding="UTF-8"?>
            <!-- This is a comment -->
            <root>
                <!-- Another comment -->
                <item>Test</item>
            </root>';
        
        $result = Xml::parse($xmlString);
        $this->assertNotFalse($result);
    }

    /**
     * Test XML validation functionality.
     */
    public function testXmlValidationFunctionality(): void
    {
        // Test if SimpleXML extension is loaded
        $this->assertTrue(extension_loaded('simplexml'), 'SimpleXML extension should be loaded');
        
        // Test if libxml extension is loaded
        $this->assertTrue(extension_loaded('libxml'), 'libxml extension should be loaded');
    }

    /**
     * Test XML encoding handling.
     */
    public function testXmlEncodingHandling(): void
    {
        $xmlString = '<?xml version="1.0" encoding="ISO-8859-1"?>
            <root>
                <text>Text with special chars</text>
            </root>';
        
        $result = Xml::parse($xmlString);
        $this->assertNotFalse($result);
    }

    /**
     * Test XML parse without version declaration.
     */
    public function testXmlParseWithoutVersionDeclaration(): void
    {
        $xmlString = '<root>
                <item>Test</item>
            </root>';
        
        $result = Xml::parse($xmlString);
        $this->assertNotFalse($result);
    }

    /**
     * Test RSS-like XML structure.
     */
    public function testRssLikeXmlStructure(): void
    {
        $xmlString = '<?xml version="1.0" encoding="UTF-8"?>
            <rss version="2.0">
                <channel>
                    <title>Test Feed</title>
                    <description>A test RSS feed</description>
                    <item>
                        <title>First Post</title>
                        <description>Description of first post</description>
                        <link>http://example.com/post1</link>
                        <pubDate>Mon, 15 Aug 2025 12:00:00 GMT</pubDate>
                    </item>
                </channel>
            </rss>';
        
        $result = Xml::parse($xmlString);
        $this->assertNotFalse($result);
    }
}
