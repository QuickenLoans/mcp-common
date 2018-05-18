<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Common\Time;

use PHPUnit\Framework\TestCase;
use QL\MCP\Common\Exception;

class TimePointTest extends TestCase
{
    public function testConstructingInvalidTimeThrowsException()
    {
        $this->expectException(Exception::class);

        new TimePoint(10193, 1, 1, 0, 0, 0, 'UTC');
    }

    public function testFormatWithDefaults()
    {
        $expected = '1983-12-15 00:00:00.000000';

        $tp = new TimePoint(1983, 12, 15);

        $actual = $tp->format('Y-m-d H:i:s.u', 'UTC');
        $this->assertSame($expected, $actual);
    }

    public function testFormatStringPassesThrough()
    {
        $expected = '1983-12-15 14:02:42';

        $tp = new TimePoint(1983, 12, 15, 9, 2, 42, 'America/Detroit');

        $actual = $tp->format('Y-m-d H:i:s', 'UTC');
        $this->assertSame($expected, $actual);
    }

    public function testModifyMethodCreatesNewTimePoint()
    {
        $expected = '2050-12-15 14:02:42';

        $tp = new TimePoint(1983, 12, 15, 9, 2, 42, 'America/Detroit');

        $tp = $tp->modify('+67 years');
        $actual = $tp->format('Y-m-d H:i:s', 'UTC');
        $this->assertSame($expected, $actual);
    }

    public function testModifyWithBadStringThrowsException()
    {
        $this->expectException(Exception::class);

        $tp = new TimePoint(1945, 7, 16, 5, 29, 45, 'America/Denver');
        $tp->modify('bad string');
    }

    public function testCompareNormalizesTimezones()
    {
        $expected = 1;

        $first = new TimePoint(1876, 3, 10, 0, 0, 0, 'America/Detroit');
        $second = new TimePoint(1876, 3, 10, 0, 0, 0, 'UTC');

        $actual = $first->compare($second);
        $this->assertSame($expected, $actual);
    }

    public function testCompareSameTimePointsShouldShowAsEqual()
    {
        $expected = 0;

        $first = new TimePoint(1969, 7, 20, 20, 17, 0, 'UTC');
        $second = new TimePoint(1969, 7, 20, 20, 17, 0, 'UTC');

        $actual = $first->compare($second);
        $this->assertSame($expected, $actual);
    }

    public function testCompareCloseTimePointsShouldShowNotEqual()
    {
        $expected = -1;

        $first = new TimePoint(1986, 1, 26, 11, 38, 0, 'America/Detroit');
        $second = new TimePoint(1986, 1, 26, 11, 39, 13, 'America/Detroit');

        $actual = $first->compare($second);
        $this->assertSame($expected, $actual);
    }

    public function testAddingFourHoursDateInterval()
    {
        $expected = new TimePoint(1775, 4, 19, 10, 0, 0, 'America/Detroit');
        $start = new TimePoint(1775, 4, 19, 6, 0, 0, 'America/Detroit');

        $int = new TimeInterval('PT4H');

        $actual = $start->add($int);
        $this->assertSame($expected->format('Y-m-d H:i:s', 'America/Detroit'), $actual->format('Y-m-d H:i:s', 'America/Detroit'));
    }

    public function testAddingOneDayDateInterval()
    {
        $expected = new TimePoint(1963, 11, 22, 11, 30, 0, 'America/Detroit');
        $start = new TimePoint(1963, 11, 22, 12, 30, 0, 'America/Detroit');

        $int = new TimeInterval('PT1H');

        $actual = $start->sub($int);
        $this->assertSame($expected->format('Y-m-d H:i:s', 'America/Detroit'), $actual->format('Y-m-d H:i:s', 'America/Detroit'));
    }

    public function testDiffingTimePointsWithPositiveInterval()
    {
        $expected = new TimeInterval('P0Y10M8DT22H30M0S-313D');

        $start = new TimePoint(2019, 11, 22, 11, 0, 0, 'America/Detroit');
        $diff = new TimePoint(2020, 10, 01, 10, 30, 0, 'America/Detroit');
        $actual = $start->diff($diff);

        $this->assertEquals($expected, $actual);
        $this->assertNotEquals(new TimeInterval('P0Y10M8DT22H30M0S'), $actual);
    }

    public function testDiffingTimePointsWithNegativeInterval()
    {
        $expected = new TimeInterval('P1Y10M8DT22H31M0S-I677D');

        $start = new TimePoint(2051, 10, 01, 10, 30, 0, 'America/Detroit');
        $diff = new TimePoint(2049, 11, 22, 11, 59, 0, 'America/Detroit');
        $actual = $start->diff($diff);

        $this->assertEquals($expected, $actual);
        $this->assertNotEquals(new TimeInterval('P1Y10M8DT22H31M0S'), $actual);
    }

    public function testDiffingTimePointsWithSameDate()
    {
        $expected = new TimeInterval('P0Y0M0DT6H13M0S');

        $start = new TimePoint(2009, 7, 6, 11, 0, 0, 'America/Detroit');
        $diff = new TimePoint(2009, 7, 6, 17, 13, 0, 'America/Detroit');
        $actual = $start->diff($diff);

        $this->assertEquals($expected, $actual);
    }

    public function testDiffingTimePointsWithSameTimestamp()
    {
        $expected = new TimeInterval('P0Y0M0DT0H0M0S');

        $start = new TimePoint(1999, 9, 9, 3, 0, 0, 'America/Detroit');
        $diff = new TimePoint(1999, 9, 9, 3, 0, 0, 'America/Detroit');
        $actual = $start->diff($diff);

        $this->assertEquals($expected, $actual);
    }

    public function testJsonSerialize()
    {
        $timepoint = new TimePoint(2015, 10, 10, 10, 10, 0, 'UTC');
        $output = json_encode($timepoint);

        $this->assertEquals('"2015-10-10T10:10:00Z"', $output);
        $this->assertEquals('2015-10-10T10:10:00Z', json_decode($output, true));
    }

    public function testToString()
    {
        $timepoint = new TimePoint(2015, 10, 10, 10, 10, 0, 'UTC');
        $output = (string) $timepoint;

        $this->assertEquals('2015-10-10T10:10:00Z', $output);
    }

    public function testMicroseconds()
    {
        $timepoint = new TimePoint(2015, 10, 10, 10, 10, 0, 'UTC', 123456);
        $output = $timepoint->format('Y-m-d H:i:s.u', 'UTC');

        $this->assertEquals('2015-10-10 10:10:00.123456', $output);
    }
}
