<?php

namespace MCP\DataType\Time;

use DateTime;
use DateTimeZone;

class TimeUtilTest extends \PHPUnit_Framework_TestCase
{
    use TimeUtil;

    const FORMAT = 'Y-m-d H:i:s e';

    /**
     * @dataProvider DateTimeConvertData
     */
    public function testDateTimeToTimePoint(DateTime $input, $expected)
    {
        $output = $this->dateTimeToTimePoint($input);

        $this->assertInstanceOf('MCP\DataType\Time\TimePoint', $output);

        $timezone = ($input->getTimezone() instanceof DateTimeZone) ? $input->getTimezone()->getName() : date_default_timezone_get();

        $this->assertEquals($expected, $output->format(self::FORMAT, $timezone));
    }

    public function DateTimeConvertData()
    {
        return [
            // No set timezone, DateTime defaults to local
            [
                DateTime::createFromFormat('Y-m-d H:i:s', '2015-12-30 12:56:00'),
                '2015-12-30 12:56:00 '.date_default_timezone_get()
            ],
            // Manually set timezone identifier
            [
                DateTime::createFromFormat('Y-m-d H:i:s e', '2015-12-30 12:56:00 UTC'),
                '2015-12-30 12:56:00 UTC'
            ],
            // Manually set offset
            [
                DateTime::createFromFormat('Y-m-d H:i:s P', '2015-12-30 12:56:00 -04:00'),
                '2015-12-30 16:56:00 UTC'
            ],
            // Manually set shorthand
            [
                DateTime::createFromFormat('Y-m-d H:i:s P', '2015-12-30 12:56:00 CST'),
                '2015-12-30 12:56:00 CST'
            ],
            // Timezone change causes date change
            [
                DateTime::createFromFormat('Y-m-d H:i:s P', '2015-12-15 23:59:00 -02:00'),
                '2015-12-16 01:59:00 UTC'
            ]
        ];
    }

    /**
     * @dataProvider StringToTimePointData
     */
    public function testStringToTimePoint($input, $expected)
    {
        $output = $this->stringToTimePoint($input);

        if (is_string($expected)) {
            $this->assertInstanceOf('MCP\DataType\Time\TimePoint', $output);
            $this->assertEquals($expected, $output->format('Y-m-d H:i:s.u e', 'UTC'));
        } else {
            $this->assertFalse($output);
        }
    }

    public function StringToTimePointData()
    {
        return [
            // simple UTC implied
            [
                '2015-12-15T10:10:00Z',
                '2015-12-15 10:10:00.000000 UTC'
            ],
            // loss of fractional second precision
            [
                '2015-12-15T10:10:00.500000Z',
                '2015-12-15 10:10:00.000000 UTC'
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
                false
            ],
            // invalid, no time
            [
                '2015-12-15',
                false
            ],
            // invalid, bad timezone
            [
                '2015-12-15T10:10:00BUTT',
                false
            ]
        ];
    }

    public function testStringToTimePointManualFormat()
    {
        $format = 'Y-m-d\TH:i:sP';
        $input = '2015-12-10T10:10:00Z';
        $expected = '2015-12-10 10:10:00.000000 UTC';

        $output = $this->stringToTimePoint($input, $format);

        $this->assertInstanceOf('MCP\DataType\Time\TimePoint', $output);
        $this->assertEquals($expected, $output->format('Y-m-d H:i:s.u e', 'UTC'));
    }
}