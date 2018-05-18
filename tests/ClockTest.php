<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Common;

use DateTime;
use PHPUnit\Framework\TestCase;
use QL\MCP\Common\Time\TimePoint;

class ClockTest extends TestCase
{
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

    public function testConstructorInvalidTimeArgumentReturnsNull()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid current datetime UTC.');

        $clock = new Clock('asdf', 'UTC');
    }

    public function testInvalidTimeZoneArgumentThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid timezone asdfasdafasdf');

        new Clock('now', 'asdfasdafasdf');
    }

    public function testNoTimeZoneGivenDefaultsToUTC()
    {
        $expected = new TimePoint(2001, 3, 30, 0, 0, 0, 'UTC');
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
    public function testFromDateTime()
    {
        $clock = new Clock();
        $output = $clock->fromDateTime(new DateTime());

        $this->assertInstanceOf(TimePoint::class, $output);
    }

    public function testLiveTimeWithMicroseconds()
    {
        $clock = new Clock();
        $time = $clock->read();

        $output = $time->format('u', 'UTC');

        $this->assertStringMatchesFormat('%d', $output);
        $this->assertNotSame('000000', $output); // The odds of this are pretty rare. Just need to test thats it not default.
    }

    /**
     * @dataProvider fromStringData
     */
    public function testFromString($input, $expected)
    {
        $clock = new Clock();
        $output = $clock->fromString($input);

        if (is_string($expected)) {
            $this->assertInstanceOf(TimePoint::class, $output);
            $this->assertEquals($expected, $output->format('Y-m-d H:i:s.u e', 'UTC'));
        } else {
            $this->assertEquals($expected, $output);
        }
    }

    public function fromStringData()
    {
        return [
            // simple UTC implied
            [
                '2015-12-15T10:10:00Z',
                '2015-12-15 10:10:00.000000 UTC'
            ],
            // with fractional second precision
            [
                '2015-12-15T10:10:00.500000Z',
                '2015-12-15 10:10:00.500000 UTC'
            ],
            // iso 8601 no seconds
            [
                '2015-12-15T10:10UTC',
                '2015-12-15 10:10:00.000000 UTC'
            ],
            // offset to UTC
            [
                '2015-12-15T10:10:00-04:00',
                '2015-12-15 14:10:00.000000 UTC'
            ],
            // invalid, no timezone
            [
                '2015-12-15T10:10:00',
                null
            ],
            // invalid, no time
            [
                '2015-12-15',
                null
            ],
            // invalid, bad timezone
            [
                '2015-12-15T10:10:00BUTT',
                null
            ]
        ];
    }

    public function testFromStringNoTimezoneToDefault()
    {
        $clock = new Clock('now', 'UTC');

        $input = '2015-12-15T10:10:00';
        $expected = '2015-12-15 10:10:00.000000 UTC';
        $output = $clock->fromString($input, 'Y-m-d\TH:i:s');

        $this->assertEquals($expected, $output->format('Y-m-d H:i:s.u e', 'UTC'));
    }

    public function testFromStringManualFormat()
    {
        $format = 'Y-m-d\TH:i:sP';
        $input = '2015-12-10T10:10:00Z';
        $expected = '2015-12-10 10:10:00.000000 UTC';

        $clock = new Clock();
        $output = $clock->fromString($input, $format);

        $this->assertInstanceOf(TimePoint::class, $output);
        $this->assertEquals($expected, $output->format('Y-m-d H:i:s.u e', 'UTC'));
    }
}
