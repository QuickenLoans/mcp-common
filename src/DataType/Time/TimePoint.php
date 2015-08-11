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
use JsonSerializable;
use Exception as BaseException;

/**
 * @api
 */
class TimePoint implements JsonSerializable
{
    use TimeUtil;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @param int $year
     * @param int $month
     * @param int $day
     * @param int $hour
     * @param int $minute
     * @param int $second
     * @param string $timezone
     * @throws Exception
     */
    public function __construct($year, $month, $day, $hour, $minute, $second, $timezone)
    {
        $inputFormat = '%04d-%02d-%02d %02d:%02d:%02d';

        try {
            $tz = new DateTimeZone($timezone);
            $date = new DateTime(sprintf($inputFormat, $year, $month, $day, $hour, $minute, $second), $tz);
        } catch (BaseException $e) {
            throw new Exception('Error with date format: ' . $e->getMessage(), 0, $e);
        }

        $this->date = $date;
        $this->date->setTimeZone(new DateTimeZone('UTC'));
    }

    /**
     * Serialize as a RFC 3339 UTC timezone JSON string
     */
    public function jsonSerialize()
    {
        return $this->format('Y-m-d\TH:i:s\Z', 'UTC');
    }

    /**
     * Compares two points in time
     *
     * @param TimePoint $timepoint The point in time to compare $this with.
     * @return int Returns -1, 0 or 1 if $this is less than, equal to or
     *    greater than $timepoint respectively.
     */
    public function compare(TimePoint $timepoint)
    {
        $first = $this->date;
        $second = $this->timePointToDateTime($timepoint);
        if ($first < $second) {
            return -1;
        }
        if ($first > $second) {
            return 1;
        }
        return 0;
    }

    /**
     * @param string $format
     * @param string $timezone
     * @return string
     */
    public function format($format, $timezone)
    {
        $date = clone $this->date;
        $date->setTimeZone(new DateTimeZone($timezone));
        return $date->format($format);
    }

    /**
     * Modifies the time point similar to DateTime->modify()
     *
     * The main difference is that this does not modify the current object in
     * place as DateTime->modify() does. Instead, this method returns a new
     * TimePoint with the modification applied.
     *
     * @param string $modificationString
     * @throws Exception
     * @return TimePoint
     */
    public function modify($modificationString)
    {
        $date = clone $this->date;
        $result = @$date->modify($modificationString);
        if (!$result) {
            throw new Exception('Bad modify string: ' . $modificationString);
        }
        return $this->dateTimeToTimePoint($date);
    }

    /**
     * @param TimeInterval $interval
     * @return TimePoint
     */
    public function add(TimeInterval $interval)
    {
        $date = clone $this->date;
        $int = new DateInterval($interval->intervalSpec());
        $date->add($int);
        return $this->dateTimeToTimePoint($date);
    }

    /**
     * @param TimeInterval $interval
     * @return TimePoint
     */
    public function sub(TimeInterval $interval)
    {
        $date = clone $this->date;
        $int = new DateInterval($interval->intervalSpec());
        $date->sub($int);
        return $this->dateTimeToTimePoint($date);
    }

    /**
     * @param TimePoint $timepoint
     * @return TimeInterval
     */
    public function diff(TimePoint $timepoint)
    {
        $interval = $this->date->diff($timepoint->date);
        $intervalSpec = $this->dateIntervalToIntervalSpec($interval);

        return new TimeInterval($intervalSpec);
    }
}
