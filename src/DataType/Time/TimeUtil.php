<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\DataType\Time;

use Exception;
use DateInterval;
use DateTime;
use DateTimeZone;

/**
 * Shared code for time classes
 *
 * Do not use this trait in your code! It is an implementation detail of this library and may change without notice.
 * If you need functionality provided here, use the Clock instead.
 *
 * @internal
 */
trait TimeUtil
{
    /**
     * @param TimePoint $date
     * @return \DateTime
     */
    private function timePointToDateTime(TimePoint $date)
    {
        $parserFormat = '@^(\d+)-(\d+)-(\d+)-(\d+)-(\d+)-(\d+)$@';
        $parsableFormat = 'Y-n-j-G-i-s';
        $inputFormat = '%04d-%02d-%02d %02d:%02d:%02d';
        $tz = new DateTimeZone('UTC');
        preg_match($parserFormat, $date->format($parsableFormat, 'UTC'), $t);
        return new DateTime(sprintf($inputFormat, $t[1], $t[2], $t[3], $t[4], $t[5], $t[6]), $tz);
    }

    /**
     * Convert a formatted string to a TimePoint
     *
     * Either set the format manually using the date() compatible format parameter, or this method will attempt to
     * parse the input string using the following supported formats.
     *
     *  - UTC Indicator (Y-m-d\TH:i:s\Z, example 2015-12-15T10:10:00Z)
     *  - RFC 3339 (Y-m-d\TH:i:sP, example 2015-12-15T10:10:00+00:00)
     *  - RFC 3339 with fractional seconds (Y-m-d\TH:i:s.uP, example 2015-12-15T10:10:10.000000+00:00)
     *  - ISO 8601 (Y-m-d\TH:i:sO, example 2015-12-15T10:10:00+0000)
     *  - ISO 8601 with fractional seconds and period (Y-m-d\TH:i:s.uO, example 2015-12-15T10:10:00.000000+0000)
     *  - ISO 8601 with fractional seconds and comma (Y-m-d\TH:i:s,uO, example 2015-12-15T10:10:00.000000+0000)
     *  - ISO 8601 with no seconds (Y-m-d\TH:iO, example 2015-12-15T10:10:00+0000)
     *
     * @param string $input
     * @param string $format
     * @return TimePoint|false
     */
    private function stringToTimePoint($input, $format = null)
    {
        if ($format === null) {

            $formats = [
                'Y-m-d\TH:i:sP',        // RFC 3339
                'Y-m-d\TH:i:s.uP',      // RFC 3339 with fractional seconds (lost precision)
                'Y-m-d\TH:i:sO',        // ISO 8601
                'Y-m-d\TH:i:s.uO',      // ISO 8601 with fractional seconds and period (lost precision)
                'Y-m-d\TH:i:s,uO',      // ISO 8601 with fractional seconds and comma (lost precision)
                'Y-m-d\TH:iO',          // ISO 8601 with no seconds
            ];

            do {
                $datetime = DateTime::createFromFormat(array_shift($formats), $input);
            } while (!$datetime instanceof DateTime && count($formats) > 0);

        } else {
            $datetime = DateTime::createFromFormat($format, $input);
        }

        return ($datetime instanceof DateTime) ? $this->dateTimeToTimePoint($datetime) : false;
    }

    /**
     * Convert a DateTime to a TimePoint
     *
     * Note that, if the DateTime includes fractional seconds, that precision will be lost as the TimePoint object
     * does not support the inclusion of fractional seconds.
     *
     * @param DateTime $date
     * @return TimePoint
     */
    private function dateTimeToTimePoint(DateTime $date)
    {
        // missing or offset only timezone? correct to UTC
        if (!$date->getTimezone() instanceof DateTimeZone || preg_match('#^[+-]{1}[0-9]{2}:?[0-9]{2}$#', $date->getTimezone()->getName())) {
            $date->setTimezone(new DateTimeZone('UTC'));
        }

        try {
            $timezone = new DateTimeZone($date->getTimezone()->getName());
        } catch (Exception $e) {
            // catch any remaining invalid timezone cases (this "should never" happen)
            // @codeCoverageIgnoreStart
            $date->setTimezone(new DateTimeZone('UTC'));
            // @codeCoverageIgnoreEnd
        }

        return new TimePoint(
            $date->format('Y'),
            $date->format('m'),
            $date->format('d'),
            $date->format('H'),
            $date->format('i'),
            $date->format('s'),
            $date->getTimezone()->getName()
        );
    }

    /**
     * Create a Dateinterval spec from DateInterval properties
     * 
     * @param DateInterval $interval
     * @return string
     */
    private function dateIntervalToIntervalSpec(DateInterval $interval)
    {
        $date = sprintf('%uY%uM%uD', $interval->y, $interval->m, $interval->d);
        $time = sprintf('%uH%uM%uS', $interval->h, $interval->i, $interval->s);

        // build extra spec
        $extra = '';
        if ($interval->invert) {
            $extra .= 'I';
        }

        if ($interval->days) {
            $extra .= $interval->days . 'D';
        }

        if (strlen($extra) > 0) {
            $extra = '-' . $extra;
        }

        return sprintf('P%sT%s%s', $date, $time, $extra);
    }
}
