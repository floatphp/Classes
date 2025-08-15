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
use FloatPHP\Classes\Http\Mail;

/**
 * Mail class tests.
 */
final class MailTest extends TestCase
{
    private Mail $mail;

    protected function setUp(): void
    {
        $this->mail = new Mail('test@example.com', 'Test Sender');
    }

    /**
     * Test mail initialization.
     */
    public function testMailInitialization(): void
    {
        $mail = new Mail();
        $this->assertInstanceOf(Mail::class, $mail);
    }

    /**
     * Test mail initialization with sender.
     */
    public function testMailInitializationWithSender(): void
    {
        $mail = new Mail('sender@example.com', 'Sender Name');
        $this->assertInstanceOf(Mail::class, $mail);
    }

    /**
     * Test set receiver.
     */
    public function testTo(): void
    {
        $result = $this->mail->to('recipient@example.com', 'Recipient Name');
        $this->assertInstanceOf(Mail::class, $result);
    }

    /**
     * Test set multiple receivers.
     */
    public function testMultipleTo(): void
    {
        $result = $this->mail
            ->to('recipient1@example.com', 'Recipient 1')
            ->to('recipient2@example.com', 'Recipient 2');
        
        $this->assertInstanceOf(Mail::class, $result);
    }

    /**
     * Test set sender.
     */
    public function testFrom(): void
    {
        $result = $this->mail->from('sender@example.com', 'Sender Name');
        $this->assertInstanceOf(Mail::class, $result);
    }

    /**
     * Test set sender without name.
     */
    public function testFromWithoutName(): void
    {
        $result = $this->mail->from('sender@example.com');
        $this->assertInstanceOf(Mail::class, $result);
    }

    /**
     * Test set reply-to.
     */
    public function testReplyTo(): void
    {
        $result = $this->mail->replyTo('reply@example.com', 'Reply Name');
        $this->assertInstanceOf(Mail::class, $result);
    }

    /**
     * Test set CC.
     */
    public function testSetCc(): void
    {
        $cc = ['cc1@example.com' => 'CC Name 1', 'cc2@example.com' => 'CC Name 2'];
        $result = $this->mail->setCc($cc);
        $this->assertInstanceOf(Mail::class, $result);
    }

    /**
     * Test set BCC.
     */
    public function testSetBcc(): void
    {
        $bcc = ['bcc1@example.com' => 'BCC Name 1', 'bcc2@example.com' => 'BCC Name 2'];
        $result = $this->mail->setBcc($bcc);
        $this->assertInstanceOf(Mail::class, $result);
    }

    /**
     * Test send as HTML.
     */
    public function testAsHtml(): void
    {
        $result = $this->mail->asHtml();
        $this->assertInstanceOf(Mail::class, $result);
    }

    /**
     * Test set subject.
     */
    public function testSubject(): void
    {
        $result = $this->mail->setSubject('Test Subject');
        $this->assertInstanceOf(Mail::class, $result);
    }

    /**
     * Test set body.
     */
    public function testBody(): void
    {
        $result = $this->mail->setBody('Test email body content');
        $this->assertInstanceOf(Mail::class, $result);
    }

    /**
     * Test add header.
     */
    public function testAddHeader(): void
    {
        $result = $this->mail->addHeader('X-Custom-Header', 'Custom Value');
        $this->assertInstanceOf(Mail::class, $result);
    }

    /**
     * Test set parameters.
     */
    public function testSetParams(): void
    {
        $result = $this->mail->setParams('-f sender@example.com');
        $this->assertInstanceOf(Mail::class, $result);
    }

    /**
     * Test set wrapper.
     */
    public function testWrap(): void
    {
        $result = $this->mail->wrap(100);
        $this->assertInstanceOf(Mail::class, $result);
    }

    /**
     * Test set wrapper with invalid value.
     */
    public function testWrapWithInvalidValue(): void
    {
        $result = $this->mail->wrap(0); // Should use default
        $this->assertInstanceOf(Mail::class, $result);
    }

    /**
     * Test attachment method exists.
     */
    public function testAttachMethodExists(): void
    {
        $this->assertTrue(method_exists(Mail::class, 'attach'));
    }

    /**
     * Test method chaining.
     */
    public function testMethodChaining(): void
    {
        $result = $this->mail
            ->to('recipient@example.com')
            ->setSubject('Test Subject')
            ->setBody('Test Body')
            ->asHtml()
            ->wrap(120);
        
        $this->assertInstanceOf(Mail::class, $result);
    }

    /**
     * Test mail with complex setup.
     */
    public function testComplexMailSetup(): void
    {
        $mail = new Mail('sender@example.com', 'Sender Name');
        
        $result = $mail
            ->to('recipient@example.com', 'Recipient Name')
            ->replyTo('noreply@example.com', 'No Reply')
            ->setCc(['cc@example.com' => 'CC Name'])
            ->setBcc(['bcc@example.comexample.com' => 'BCC Name'])
            ->setSubject('Complex Test Email')
            ->setBody('<h1>HTML Email</h1><p>This is a test email.</p>')
            ->asHtml()
            ->addHeader('X-Priority', '1')
            ->wrap(78);
        
        $this->assertInstanceOf(Mail::class, $result);
    }

    /**
     * Test empty subject handling.
     */
    public function testEmptySubject(): void
    {
        $result = $this->mail->setSubject('');
        $this->assertInstanceOf(Mail::class, $result);
    }

    /**
     * Test empty body handling.
     */
    public function testEmptyBody(): void
    {
        $result = $this->mail->setBody('');
        $this->assertInstanceOf(Mail::class, $result);
    }

    /**
     * Test special characters in subject.
     */
    public function testSpecialCharactersInSubject(): void
    {
        $result = $this->mail->setSubject('Tëst Sübject with Spëcial Charäcters');
        $this->assertInstanceOf(Mail::class, $result);
    }

    /**
     * Test long body content.
     */
    public function testLongBodyContent(): void
    {
        $longBody = str_repeat('This is a very long email body content. ', 100);
        $result = $this->mail->setBody($longBody);
        $this->assertInstanceOf(Mail::class, $result);
    }

    /**
     * Test HTML body content.
     */
    public function testHtmlBodyContent(): void
    {
        $htmlBody = '
            <html>
                <head><title>Test Email</title></head>
                <body>
                    <h1>Test Email</h1>
                    <p>This is a <strong>test</strong> email with <em>HTML</em> content.</p>
                    <ul>
                        <li>Item 1</li>
                        <li>Item 2</li>
                    </ul>
                </body>
            </html>
        ';
        
        $result = $this->mail->setBody($htmlBody)->asHtml();
        $this->assertInstanceOf(Mail::class, $result);
    }
}
