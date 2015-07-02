<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\DataType\Time;

use PHPUnit_Framework_TestCase;

class ClockTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group DataType
     * @group Time
     * @covers MCP\DataType\Time\Clock
     * @covers MCP\DataType\Time\TimeUtil
     */
    public function testConstructorArgumentIsUsedForTimeInterval()
    {
        $tz = 'UTC';
        $time = '2011-11-05 09:02:42';
        $expected = '2011-11-05 05:02:42';
        $clock = new Clock($time, $tz);
        $timePoint = $clock->read();
        $actual = $timePoint->format('Y-m-d H:i:s', 'America/Detroit');
        $this->assertSame($expected, $actual);
    }

    /**
     * @group DataType
     * @group Time
     * @covers MCP\DataType\Time\Clock<extended>
     */
    public function testInvalidTimeArgumentThrowsException()
    {
        $this->setExpectedException('\MCP\DataType\Time\Exception');
        new Clock('asdf', 'UTC');
    }

    /**
     * @group DataType
     * @group Time
     * @covers MCP\DataType\Time\Clock<extended>
     */
    public function testInvalidTimeZoneArgumentThrowsException()
    {
        $this->setExpectedException('\MCP\DataType\Time\Exception');
        new Clock('now', 'asdfasdafasdf');
    }

    /**
     * @group DataType
     * @group Time
     * @covers MCP\DataType\Time\Clock<extended>
     */
    public function testNoTimeZoneGivenPullsFromPHPConfig()
    {
        ini_set('date.timezone', 'America/Detroit');

        $expected = new TimePoint(2001, 3, 30, 0, 0, 0, 'America/Detroit');
        $clock = new Clock('2001-03-30 00:00:00');
        $actual = $clock->read();
        $this->assertSame($expected->compare($actual), 0);
    }
}
