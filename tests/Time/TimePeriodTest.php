<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Common\Time;

use DatePeriod;
use PHPUnit\Framework\TestCase;

class TimePeriodTest extends TestCase
{
    public function testEveryThreeDaysInTwoWeeks()
    {
        $start = new TimePoint(2012, 10, 21, 0, 0, 0, 'UTC');
        $end = new TimePoint(2012, 11, 3, 0, 0, 0, 'UTC');
        $interval = new TimeInterval('P3D');
        $period = new TimePeriod($start, $interval, $end);
        $expected = array(
            new TimePoint(2012, 10, 21, 0, 0, 0, 'UTC'),
            new TimePoint(2012, 10, 24, 0, 0, 0, 'UTC'),
            new TimePoint(2012, 10, 27, 0, 0, 0, 'UTC'),
            new TimePoint(2012, 10, 30, 0, 0, 0, 'UTC'),
            new TimePoint(2012, 11, 2, 0, 0, 0, 'UTC'),
        );

        foreach ($period as $key => $timePoint) {
            $this->assertSame($expected[$key]->format('Y-m-d H:i:s', 'UTC'), $timePoint->format('Y-m-d H:i:s', 'UTC'));
        }
    }

    public function testCreateWithRecurrences()
    {
        $start = new TimePoint(2012, 9, 13, 0, 0, 0, 'UTC');
        $interval = new TimeInterval('P1D');
        $recurrences = 3;
        $expected = array(
            new TimePoint(2012, 9, 13, 0, 0, 0, 'UTC'),
            new TimePoint(2012, 9, 14, 0, 0, 0, 'UTC'),
            new TimePoint(2012, 9, 15, 0, 0, 0, 'UTC'),
        );
        $period = TimePeriod::createWithRecurrences($start, $interval, $recurrences);
        foreach ($period as $key => $timePoint) {
            $this->assertSame($expected[$key]->format('Y-m-d H:i:s', 'UTC'), $timePoint->format('Y-m-d H:i:s', 'UTC'));
        }
    }

    public function testCreateWithRecurrencesSkippingFirst()
    {
        $start = new TimePoint(2012, 9, 13, 0, 0, 0, 'UTC');
        $interval = new TimeInterval('P1D');
        $recurrences = 3;
        $expected = array(
            new TimePoint(2012, 9, 14, 0, 0, 0, 'UTC'),
            new TimePoint(2012, 9, 15, 0, 0, 0, 'UTC'),
            new TimePoint(2012, 9, 16, 0, 0, 0, 'UTC'),
        );

        $period = TimePeriod::createWithRecurrences($start, $interval, $recurrences, DatePeriod::EXCLUDE_START_DATE);

        foreach ($period as $key => $timePoint) {
            $this->assertSame($expected[$key]->format('Y-m-d H:i:s', 'UTC'), $timePoint->format('Y-m-d H:i:s', 'UTC'));
        }
    }
}
