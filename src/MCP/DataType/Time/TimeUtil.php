<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\DataType\Time;

use DateInterval;
use DateTime;
use DateTimeZone;

/**
 * Shared code for time classes
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
     * @param DateTime $date
     * @return TimePoint
     */
    private function dateTimeToTimePoint(DateTime $date)
    {
        $parserFormat = '@^(\d+)-(\d+)-(\d+)-(\d+)-(\d+)-(\d+)$@';
        $parsableFormat = 'Y-n-j-G-i-s';
        preg_match($parserFormat, $date->format($parsableFormat), $t);
        $t[5] = ltrim($t[5], '0');
        $t[6] = ltrim($t[6], '0');
        $t[1] = (int) $t[1];
        $t[2] = (int) $t[2];
        $t[3] = (int) $t[3];
        $t[4] = (int) $t[4];
        $t[5] = (int) $t[5];
        $t[6] = (int) $t[6];

        return new TimePoint(
            $t[1],
            $t[2],
            $t[3],
            $t[4],
            $t[5],
            $t[6],
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
