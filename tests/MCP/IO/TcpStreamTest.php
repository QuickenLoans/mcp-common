<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\IO;

use MCP\DataType\IPv4Address;
use PHPUnit_Framework_TestCase;
use ReflectionProperty;

class TcpStreamTest extends PHPUnit_Framework_TestCase
{
    private $fOpenCalls;
    private $fReadCalls;
    private $fWriteCalls;
    private $fCloseCalls;
    private $internalStream;
    private $ipObj;

    public function setUp()
    {
        $this->fOpenCalls = array();
        $this->fReadCalls = array();
        $this->fWriteCalls = array();
        $this->fCloseCalls = array();
        $this->internalStream = null;
        $this->ipObj = IPv4Address::create('68.184.211.4');
    }

    /**
     * @group IO
     * @group TCP
     * @covers MCP\IO\TcpStream
     * @expectedException UnexpectedValueException
     * @expectedExceptionMessage 70000
     */
    public function testFailsConstructingWithBadPort()
    {
        $inputIp = IPv4Address::create('68.184.211.4');
        $inputPort = 70000;
        $config = $this->configFixture(1.0, 2.0, 'Never', 'Never', 'Never', 'Never');
        new TcpStream($inputIp, $inputPort, $config);
    }

    /**
     * @group IO
     * @group TCP
     * @covers MCP\IO\TcpStream
     */
    public function testOpenReturnsTrueOnSuccess()
    {
        $inputIp = '68.184.211.4';
        $inputPort = 20;

        $expectedOpenReturn = true;
        $expectedConnectUrl = sprintf('tcp://%s:%d', $inputIp, $inputPort);

        $config = $this->configFixture(2.0, 5.0, 'Success', 'Never', 'Never', 'Never');
        $stream = new TcpStream($this->ipObj, $inputPort, $config);
        $actual = $stream->open();

        // assert open returned true
        $this->assertTrue($expectedOpenReturn, $actual);
        // assert underlying open function was called once
        $this->assertSame(1, count($this->fOpenCalls));
        // assert underlying open function was called with the correct 'host url'
        $this->assertSame($expectedConnectUrl, $this->fOpenCalls[0][0]);
        // assert underlying open function was called with the correct timeout
        $this->assertSame(2.0, $this->fOpenCalls[0][3]);
    }

    /**
     * @group IO
     * @group TCP
     * @covers MCP\IO\TcpStream
     * @expectedException LogicException
     */
    public function testOpenThrowsExceptionIfCalledAfterAlreadyOpen()
    {
        $config = $this->configFixture(2.0, 5.0, 'Success', 'Never', 'Never', 'Never');
        $stream = new TcpStream($this->ipObj, 21, $config);
        $stream->open();
        $stream->open();
    }

    /**
     * @group IO
     * @group TCP
     * @covers MCP\IO\TcpStream
     * @expectedException RuntimeException
     */
    public function testOpenThrowsExceptionOnFailure()
    {
        $config = $this->configFixture(2.0, 5.0, 'Failure', 'Never', 'Never', 'Never');
        $stream = new TcpStream($this->ipObj, 22, $config);
        $stream->open();
    }

    /**
     * @group IO
     * @group TCP
     * @covers MCP\IO\TcpStream
     */
    public function testReadReturnsAllDataOnStreamIfByteLimitIsGreaterThanDataByteLength()
    {
        $inputData = 'this is the data';
        $expected = $inputData;
        $config = $this->configFixture(2.0, 5.0, 'Success', 'Success', 'Never', 'Never');
        $stream = new TcpStream($this->ipObj, 26, $config);
        $stream->open();
        fwrite($this->internalStream, $inputData);
        rewind($this->internalStream);
        $actual = $stream->read(1024);
        $this->assertSame($expected, $actual);
    }

    /**
     * @group IO
     * @group TCP
     * @covers MCP\IO\TcpStream
     */
    public function testReadReturnsOnlyMaxOfDataIndicatedByByteLimit()
    {
        $inputData = 'this is the data coming through the stream';
        $expected = 'this is the data';
        $config = $this->configFixture(2.0, 5.0, 'Success', 'Success', 'Never', 'Never');
        $stream = new TcpStream($this->ipObj, 27, $config);
        $stream->open();
        fwrite($this->internalStream, $inputData);
        rewind($this->internalStream);
        $actual = $stream->read('16'); // this is a string on purpose, it should be allowed!
        $this->assertSame($expected, $actual);
    }

    /**
     * @group IO
     * @group TCP
     * @covers MCP\IO\TcpStream
     */
    public function testReadReturnsFalseIfNoDataIsRead()
    {
        $config = $this->configFixture(2.0, 5.0, 'Success', 'Failure', 'Never', 'Never');
        $stream = new TcpStream($this->ipObj, 27, $config);
        $stream->open();
        $expected = false;
        $actual = $stream->read();
        $this->assertSame($expected, $actual);
    }

    /**
     * Big Fat Warning!
     *
     * This test is very brittle as it uses reflection to edit a private
     * property to trick the object into thinking that a stream has timed out.
     * It would be very easy for this test to break as a false positive. If you
     * need to change this test, make sure it achieves testing that read()
     * throws a RuntimeException if the read fails because of a timeout.
     *
     * @group IO
     * @group TCP
     * @covers MCP\IO\TcpStream
     * @expectedException RuntimeException
     */
    public function testReadThrowsExceptionOnTimeout()
    {
        $config = $this->configFixture(2.0, 5.0, 'Success', 'Failure', 'Never', 'Never');
        $stream = new TcpStream($this->ipObj, 27, $config);
        $stream->open();
        $rprop = new ReflectionProperty('MCP\IO\TcpStream', 'isTimedOut');
        $rprop->setAccessible(true);
        $rprop->setValue($stream, true);
        $stream->read();
    }

    /**
     * @group IO
     * @group TCP
     * @covers MCP\IO\TcpStream
     * @expectedException LogicException
     */
    public function testReadThrowsExceptionIfCalledAfterStreamOpenedAndClosed()
    {
        $config = $this->configFixture(2.0, 5.0, 'Success', 'Never', 'Never', 'Success');
        $stream = new TcpStream($this->ipObj, 28, $config);
        $stream->open();
        $stream->close();
        $stream->read();
    }

    /**
     * @group IO
     * @group TCP
     * @covers MCP\IO\TcpStream
     * @expectedException LogicException
     */
    public function testReadThrowsExceptionIfOpenWasNotSuccessful()
    {
        $config = $this->configFixture(2.0, 5.0, 'Never', 'Never', 'Never', 'Never');
        $stream = new TcpStream($this->ipObj, 23, $config);
        $stream->read();
    }

    /**
     * @group IO
     * @group TCP
     * @covers MCP\IO\TcpStream
     * @expectedException InvalidArgumentException
     */
    public function testReadThrowsExceptionIfInputtedBytesNegativeInteger()
    {
        $config = $this->configFixture(2.0, 5.0, 'Never', 'Never', 'Never', 'Never');
        $stream = new TcpStream($this->ipObj, 23, $config);
        $stream->read(-5);
    }

    /**
     * @group IO
     * @group TCP
     * @covers MCP\IO\TcpStream
     * @expectedException InvalidArgumentException
     */
    public function testReadThrowsExceptionIfInputtedBytesNotScalar()
    {
        $config = $this->configFixture(2.0, 5.0, 'Never', 'Never', 'Never', 'Never');
        $stream = new TcpStream($this->ipObj, 23, $config);
        $stream->read(array());
    }

    /**
     * @group IO
     * @group TCP
     * @covers MCP\IO\TcpStream
     * @expectedException InvalidArgumentException
     */
    public function testReadThrowsExceptionIfInputtedBytesDoesNotLookLikeInteger()
    {
        $config = $this->configFixture(2.0, 5.0, 'Never', 'Never', 'Never', 'Never');
        $stream = new TcpStream($this->ipObj, 23, $config);
        $stream->read(2.5);
    }

    /**
     * @group IO
     * @group TCP
     * @covers MCP\IO\TcpStream
     * @expectedException InvalidArgumentException
     */
    public function testReadThrowsExceptionIfInputtedBytesIsZero()
    {
        $config = $this->configFixture(2.0, 5.0, 'Never', 'Never', 'Never', 'Never');
        $stream = new TcpStream($this->ipObj, 23, $config);
        $stream->read(0);
    }

    /**
     * @group IO
     * @group TCP
     * @covers MCP\IO\TcpStream
     */
    public function testWriteReturnsAmountOfBytesWritten()
    {
        $inputData = 'this is data to write to the stream';
        $expected = strlen($inputData);
        $config = $this->configFixture(2.0, 5.0, 'Success', 'Never', 'Success', 'Never');
        $stream = new TcpStream($this->ipObj, 23, $config);
        $stream->open();
        $actual = $stream->write($inputData);

        // assert fwrite() was called once
        $this->assertSame(1, count($this->fWriteCalls));
        // assert fwrite() was called with the right arguments
        $this->assertSame($this->internalStream, $this->fWriteCalls[0][0]);
        $this->assertSame($inputData, $this->fWriteCalls[0][1]);
        // assert write() returned with what was expected
        $this->assertSame($expected, $actual);
    }

    /**
     * @group IO
     * @group TCP
     * @covers MCP\IO\TcpStream
     * @expectedException LogicException
     */
    public function testWriteThrowsExceptionIfOpenWasNotSuccessful()
    {
        $config = $this->configFixture(2.0, 5.0, 'Never', 'Never', 'Never', 'Never');
        $stream = new TcpStream($this->ipObj, 24, $config);
        $stream->write('some data');
    }

    /**
     * @group IO
     * @group TCP
     * @covers MCP\IO\TcpStream
     * @expectedException LogicException
     */
    public function testWriteThrowsExceptionAfterStreamOpenedAndClosed()
    {
        $config = $this->configFixture(2.0, 5.0, 'Success', 'Never', 'Never', 'Success');
        $stream = new TcpStream($this->ipObj, 28, $config);
        $stream->open();
        $stream->close();
        $stream->write('some data');
    }

    /**
     * @group IO
     * @group TCP
     * @covers MCP\IO\TcpStream
     * @expectedException LogicException
     */
    public function testCloseThrowsExceptionIfOpenWasNotSuccessful()
    {
        $config = $this->configFixture(2.0, 5.0, 'Never', 'Never', 'Never', 'Never');
        $stream = new TcpStream($this->ipObj, 25, $config);
        $stream->close();
    }

    /**
     * @group IO
     * @group TCP
     * @covers MCP\IO\TcpStream
     * @expectedException LogicException
     */
    public function testCloseThrowsExceptionAfterStreamOpenedAndClosed()
    {
        $config = $this->configFixture(2.0, 5.0, 'Success', 'Never', 'Never', 'Success');
        $stream = new TcpStream($this->ipObj, 28, $config);
        $stream->open();
        $stream->close();
        $stream->close();
    }

    public function tearDown()
    {
        if (!is_null($this->internalStream)) {
            fclose($this->internalStream);
        }
    }

    public function mockFOpenSuccess()
    {
        $this->fOpenCalls[] = func_get_args();
        $this->internalStream = fopen('php://memory', 'r+');
        return $this->internalStream;
    }

    public function mockFOpenFailure($remote_socket, &$errno, &$errstr)
    {
        $this->fOpenCalls[] = func_get_args();
        $errno = 61;
        $errstr = 'Connection refused';
        // Note: In the case of a failure, this function actually emits an
        // E_WARNING not an E_USER_WARNING. Since there is no actual way to
        // trigger an E_WARNING from user-space, this will have to do.
        trigger_error('stream_socket_client(): unable to connect to ' . $remote_socket . ' (Connection refused) in [SOME FILE]', E_USER_WARNING);
        return false;
    }

    public function mockFOpenNever()
    {
        throw new \Exception('stream_socket_client() should not have been called.');
    }

    public function mockFReadSuccess($handle, $length)
    {
        $this->fReadCalls[] = func_get_args();
        return fread($handle, $length);
    }

    public function mockFReadFailure()
    {
        $this->fReadCalls[] = func_get_args();
        return 0;
    }

    public function mockFReadNever()
    {
        throw new \Exception('fread() should not have been called.');
    }

    public function mockFWriteSuccess($handle, $data)
    {
        $this->fWriteCalls[] = func_get_args();
        return strlen($data);
    }

    public function mockFWriteFail()
    {
        $this->fWriteCalls[] = func_get_args();
        return 0;
    }

    public function mockFWriteNever()
    {
        throw new \Exception('fwrite() should not have been called.');
    }

    public function mockFCloseSuccess()
    {
        $this->fCloseCalls[] = func_get_args();
        return true;
    }

    public function mockFCloseNever()
    {
        throw new \Exception('fclose() should not have been called.');
    }

    private function configFixture($ctimeout, $ttimeout, $openSuffix, $readSuffix, $writeSuffix, $closeSuffix)
    {
        $mockFOpen = array($this, 'mockFOpen' . $openSuffix);
        $mockFRead = array($this, 'mockFRead' . $readSuffix);
        $mockFWrite = array($this, 'mockFWrite' . $writeSuffix);
        $mockFClose = array($this, 'mockFClose' . $closeSuffix);
        $config = new TcpStreamConfig($ctimeout, $ttimeout, $mockFOpen, $mockFRead, $mockFWrite, $mockFClose);
        return $config;
    }
}
