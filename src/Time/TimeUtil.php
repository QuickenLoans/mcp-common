<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Common\Time;

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
            // DateTime::createFromFormat() will create an invalid DateTimeZone object when the timezone is an offset
            // (-05:00, for example) instead of a named timezone. By recreating the DateTimeZone object, we can expose
            // this problem and ensure that a valid DateTimeZone object is always set.
            $timezone = new DateTimeZone($date->getTimezone()->getName());
        } catch (Exception $e) {
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
