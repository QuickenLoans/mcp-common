<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\DataType\Time;

use DateTime;
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

    /**
     * @dataProvider inRangeData
     */
    public function testInRange(TimePoint $expiration, TimePoint $creation = null, $skew = null, $expected)
    {
        $clock = new Clock('2015-10-10 10:00:00', 'UTC');

        $this->assertEquals($expected, $clock->inRange($expiration, $creation, $skew));
    }

    public function inRangeData()
    {
        return [
            // in range
            [
                new TimePoint('2015', '10', '30', '10', '10', '00', 'UTC'),
                null,
                null,
                true
            ],
            [
                new TimePoint('2015', '10', '30', '10', '10', '00', 'UTC'),
                new TimePoint('2015', '10', '10', '8', '00', '00', 'UTC'),
                null,
                true
            ],
            [
                new TimePoint('2015', '10', '30', '10', '10', '00', 'UTC'),
                new TimePoint('2015', '10', '10', '10', '10', '00', 'UTC'),
                '30 minutes',
                true
            ],
            [
                new TimePoint('2015', '10', '10', '12', '00', '00', 'EST'),
                null,
                null,
                true
            ],
            [
                new TimePoint('2015', '10', '10', '09', '50', '00', 'UTC'),
                null,
                '30 minutes',
                true
            ],
            // out of range
            [
                new TimePoint('2015', '10', '9', '10', '10', '00', 'UTC'),
                null,
                null,
                false
            ],
            [
                new TimePoint('2015', '10', '10', '09', '20', '00', 'UTC'),
                null,
                '30 minutes',
                false
            ],
            [
                new TimePoint('2015', '10', '30', '10', '20', '00', 'UTC'),
                new TimePoint('2015', '10', '10', '10', '10', '00', 'UTC'),
                null,
                false
            ]
        ];
    }

    /**
     * Testing of conversion logic in Time Util Test
     */
    public function testFromString()
    {
        $clock = new Clock();
        $output = $clock->fromString('2015-10-10T10:10:00Z');

        $this->assertInstanceOf('MCP\DataType\Time\TimePoint', $output);
    }

    /**
     * Testing of conversion logic in Time Util Test
     *
     * @expectedException Exception
     */
    public function testFromStringFailure()
    {
        $clock = new Clock();
        $output = $clock->fromString('2015-10-10T10:10:00');

        $this->assertInstanceOf('MCP\DataType\Time\TimePoint', $output);
    }

    /**
     * Testing of conversion logic in Time Util Test
     */
    public function testFromDateTime()
    {
        $clock = new Clock();
        $output = $clock->fromDateTime(new DateTime());

        $this->assertInstanceOf('MCP\DataType\Time\TimePoint', $output);
    }
}
