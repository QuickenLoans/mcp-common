<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Common\Time;

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

        $this->assertInstanceOf(TimePoint::CLASS, $output);

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
}
