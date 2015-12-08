<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Common\Testing;

use PHPUnit_Framework_TestCase;
use stdClass;

class MemoryLoggerTest extends PHPUnit_Framework_TestCase
{
    public function testEmergency()
    {
        $logger = new MemoryLogger;
        $logger->emergency('This is an emergency');

        $this->assertCount(1, $logger->messages);
        $this->assertSame('emergency', $logger->messages[0]['level']);
        $this->assertSame('This is an emergency', $logger->messages[0]['message']);
    }

    public function testAlert()
    {
        $logger = new MemoryLogger;
        $logger->alert('This is an alert');

        $this->assertCount(1, $logger->messages);
        $this->assertSame('alert', $logger->messages[0]['level']);
        $this->assertSame('This is an alert', $logger->messages[0]['message']);
    }

    public function testCritical()
    {
        $logger = new MemoryLogger;
        $logger->critical('This is a critical');

        $this->assertCount(1, $logger->messages);
        $this->assertSame('critical', $logger->messages[0]['level']);
        $this->assertSame('This is a critical', $logger->messages[0]['message']);
    }

    public function testError()
    {
        $logger = new MemoryLogger;
        $logger->error('This is an error');

        $this->assertCount(1, $logger->messages);
        $this->assertSame('error', $logger->messages[0]['level']);
        $this->assertSame('This is an error', $logger->messages[0]['message']);
    }

    public function testWarning()
    {
        $logger = new MemoryLogger;
        $logger->warning('This is a warning');

        $this->assertCount(1, $logger->messages);
        $this->assertSame('warning', $logger->messages[0]['level']);
        $this->assertSame('This is a warning', $logger->messages[0]['message']);
    }

    public function testNotice()
    {
        $logger = new MemoryLogger;
        $logger->notice('This is a notice');

        $this->assertCount(1, $logger->messages);
        $this->assertSame('notice', $logger->messages[0]['level']);
        $this->assertSame('This is a notice', $logger->messages[0]['message']);
    }

    public function testInfo()
    {
        $logger = new MemoryLogger;
        $logger->info('This is an info');

        $this->assertCount(1, $logger->messages);
        $this->assertSame('info', $logger->messages[0]['level']);
        $this->assertSame('This is an info', $logger->messages[0]['message']);
    }

    public function testDebug()
    {
        $logger = new MemoryLogger;
        $logger->debug('This is a debug');

        $this->assertCount(1, $logger->messages);
        $this->assertSame('debug', $logger->messages[0]['level']);
        $this->assertSame('This is a debug', $logger->messages[0]['message']);
    }

    public function testMultipleMessages()
    {
        $logger = new MemoryLogger;
        $logger->info('message 1');
        $logger->error('message 2');

        $this->assertCount(2, $logger->messages);
        $this->assertSame('message 1', $logger->messages[0]['message']);
        $this->assertSame('message 2', $logger->messages[1]['message']);
    }

    public function testContext()
    {
        $context = [
            'a' => 1234,
            'b' => new stdClass
        ];

        $logger = new MemoryLogger;
        $logger->info('message 1', $context);

        $actual = $logger->messages[0]['context'];

        $this->assertSame(1234, $actual['a']);
        $this->assertSame($context['b'], $actual['b']);
    }
}
