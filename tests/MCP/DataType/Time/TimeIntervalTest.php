<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\DataType\Time;

use PHPUnit_Framework_TestCase;

class TimeIntervalTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group DataType
     * @group Time
     * @covers MCP\DataType\Time\TimeInterval
     * @covers MCP\DataType\Time\TimeUtil
     */
    public function testIntervalSpecIsSavedFromConstruction()
    {
        $input = 'P2W';
        $tint = new TimeInterval($input);
        $this->assertSame($input, $tint->intervalSpec());
    }

    /**
     * @group DataType
     * @group Time
     * @covers MCP\DataType\Time\TimeInterval
     * @covers MCP\DataType\Time\TimeUtil
     */
    public function testBadIntervalSpecThrowsException()
    {
        $this->setExpectedException('\MCP\DataType\Time\Exception');
        $input = 'bad interval format';
        new TimeInterval($input);
    }

    /**
     * @group DataType
     * @group Time
     * @covers MCP\DataType\Time\TimeInterval
     * @covers MCP\DataType\Time\TimeUtil
     */
    public function testBadFormatSpecThrowsException()
    {
        $this->setExpectedException('\PHPUnit_Framework_Error_Warning');
        $input = array();
        $tint = new TimeInterval('P2W');
        $tint->format($input);
    }

    /**
     * @group DataType
     * @group Time
     * @covers MCP\DataType\Time\TimeInterval
     * @covers MCP\DataType\Time\TimeUtil
     */
    public function testGoodFormatterSpec()
    {
        $expected = 'every 14 days';
        $input = 'every %d days';
        $tint = new TimeInterval('P2W');
        $actual = $tint->format($input);
        $this->assertSame($expected, $actual);
    }

    /**
     * @group DataType
     * @group Time
     * @covers MCP\DataType\Time\TimeInterval
     * @covers MCP\DataType\Time\TimeUtil
     */
    public function testIntervalSpecWithInvert()
    {
        $expected = '-1 year';
        $input = '%r%y year';
        $tint = new TimeInterval('P1Y-I');
        $actual = $tint->format($input);
        $this->assertSame($expected, $actual);
    }

    /**
     * @group DataType
     * @group Time
     * @covers MCP\DataType\Time\TimeInterval
     * @covers MCP\DataType\Time\TimeUtil
     */
    public function testIntervalSpecWithDays()
    {
        $expected = '1 year or 365 days';
        $input = '%y year or %a days';
        $tint = new TimeInterval('P1Y-365D');
        $actual = $tint->format($input);
        $this->assertSame($expected, $actual);
    }

    /**
     * @group DataType
     * @group Time
     * @covers MCP\DataType\Time\TimeInterval
     * @covers MCP\DataType\Time\TimeUtil
     */
    public function testIntervalSpecWithInvertAndDays()
    {
        $expected = '-2 years or -730 days';
        $input = '%r%y years or %r%a days';
        $tint = new TimeInterval('P2Y-I730D');
        $actual = $tint->format($input);
        $this->assertSame($expected, $actual);
    }

    /**
     * @group DataType
     * @group Time
     * @covers MCP\DataType\Time\TimeInterval
     * @covers MCP\DataType\Time\TimeUtil
     */
    public function testIntervalSpecIgnoresBadDays()
    {
        $expected = '2 years or (unknown) days';
        $input = '%y years or %a days';
        $tint = new TimeInterval('P2Y-730');
        $actual = $tint->format($input);
        $this->assertSame($expected, $actual);
    }
}
