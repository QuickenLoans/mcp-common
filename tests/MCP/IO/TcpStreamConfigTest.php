<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\IO;

use PHPUnit_Framework_TestCase;

class TcpStreamConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers MCP\IO\TcpStreamConfig
     * @group IO-TCP
     */
    public function testGetsConnectTimeout()
    {
        $inputs = $this->configObjectDefaults();
        $config = $this->configObjectFixture($inputs);
        $this->assertSame($inputs[0], $config->connectTimeout());
    }

    /**
     * @covers MCP\IO\TcpStreamConfig
     * @expectedException UnexpectedValueException
     * @group IO-TCP
     */
    public function testBadConnectTimeoutThrowsException()
    {
        $inputs = $this->configObjectDefaults();
        $inputs[0] = 'asdf';
        $this->configObjectFixture($inputs);
    }

    /**
     * @covers MCP\IO\TcpStreamConfig
     * @expectedException UnexpectedValueException
     * @group IO-TCP
     */
    public function testBadTransferTimeoutThrowsException()
    {
        $inputs = $this->configObjectDefaults();
        $inputs[1] = 'asdf';
        $this->configObjectFixture($inputs);
    }

    /**
     * @covers MCP\IO\TcpStreamConfig
     * @group IO-TCP
     */
    public function testGetsTransferTimeout()
    {
        $inputs = $this->configObjectDefaults();
        $config = $this->configObjectFixture($inputs);
        $this->assertSame(10.0, $config->transferTimeout());
    }

    /**
     * @covers MCP\IO\TcpStreamConfig
     * @group IO-TCP
     */
    public function testGetsTransferTimeoutSeconds()
    {
        $inputs = $this->configObjectDefaults();
        $config = $this->configObjectFixture($inputs);
        $this->assertSame(10, $config->transferTimeoutSeconds());
    }

    /**
     * @covers MCP\IO\TcpStreamConfig
     * @group IO-TCP
     */
    public function testGetsTransferTimeoutMicroseconds()
    {
        $inputs = $this->configObjectDefaults();
        $config = $this->configObjectFixture($inputs);
        $this->assertSame(0, $config->transferTimeoutMicroSeconds());
    }

    /**
     * @covers MCP\IO\TcpStreamConfig
     * @group IO-TCP
     */
    public function testParsesMicroSecondsFromFloat()
    {
        $inputs = $this->configObjectDefaults();
        $inputs[1] = 2.5;
        $config = $this->configObjectFixture($inputs);
        $this->assertSame(500000, $config->transferTimeoutMicroSeconds());
    }

    /**
     * @covers MCP\IO\TcpStreamConfig
     * @group IO-TCP
     */
    public function testGetsOpenCallable()
    {
        $inputs = $this->configObjectDefaults();
        $config = $this->configObjectFixture($inputs);
        $this->assertSame($inputs[2], $config->openFunc());
    }

    /**
     * @covers MCP\IO\TcpStreamConfig
     * @group IO-TCP
     */
    public function testGetsReadCallable()
    {
        $inputs = $this->configObjectDefaults();
        $config = $this->configObjectFixture($inputs);
        $this->assertSame($inputs[3], $config->readFunc());
    }

    /**
     * @covers MCP\IO\TcpStreamConfig
     * @group IO-TCP
     */
    public function testGetsWriteCallable()
    {
        $inputs = $this->configObjectDefaults();
        $config = $this->configObjectFixture($inputs);
        $this->assertSame($inputs[4], $config->writeFunc());
    }

    /**
     * @covers MCP\IO\TcpStreamConfig
     * @group IO-TCP
     */
    public function testGetsCloseCallable()
    {
        $inputs = $this->configObjectDefaults();
        $config = $this->configObjectFixture($inputs);
        $this->assertSame($inputs[5], $config->closeFunc());
    }

    /**
     * @covers MCP\IO\TcpStreamConfig
     * @group IO-TCP
     */
    public function testConstructorDefaults()
    {
        $config = new TcpStreamConfig(1.0, 2.0);
        $this->assertSame('stream_socket_client', $config->openFunc());
        $this->assertSame('fread', $config->readFunc());
        $this->assertSame('fwrite', $config->writeFunc());
        $this->assertSame('fclose', $config->closeFunc());
    }

    /**
     * @covers MCP\IO\TcpStreamConfig
     * @group IO-TCP
     */
    public function testFactoryPassesInputValues()
    {
        $config = TcpStreamConfig::create(5.0, 30.25);
        $this->assertSame(5.0, $config->connectTimeout());
        $this->assertSame(30, $config->transferTimeoutSeconds());
        $this->assertSame(250000, $config->transferTimeoutMicroSeconds());
        $this->assertSame('stream_socket_client', $config->openFunc());
        $this->assertSame('fread', $config->readFunc());
        $this->assertSame('fwrite', $config->writeFunc());
        $this->assertSame('fclose', $config->closeFunc());
    }

    /**
     * @return array 
     */
    private function configObjectDefaults()
    {
        return array(
            2.0,
            10.0,
            'test_stream_socket_connect',
            'new_fread',
            'new_fwrite',
            'new_fclose'
        );
    }

    /**
     * @param array $config
     * @return TcpStreamConfig 
     */
    private function configObjectFixture(array $config = null)
    {
        if (!$config) {
            $config = $this->configObjectDefaults();
        }
        return new TcpStreamConfig($config[0], $config[1], $config[2], $config[3], $config[4], $config[5]);
    }
}
