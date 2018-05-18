<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Common\Time;

use DateInterval;
use DateTime;
use DateTimeZone;
use Exception as BaseException;
use JsonSerializable;
use QL\MCP\Common\Exception;

/**
 * A point in time.
 *
 * This is used to represent a datetime with the following caveats:
 *
 * - Immutable
 * - When output, users must be explicit about which timezone to use.
 *
 * Usage:
 *
 * ```php
 * use QL\MCP\Common\Time\TimeInterval;
 * use QL\MCP\Common\Time\TimePoint;
 *
 * $time = new TimePoint(1999, 3, 31, 18, 15, 0, 'America/Detroit');
 *
 * // returns a -1, 0 or 1 if $time is less than, equal to or greater than the argument respectively.
 * $time->compare(new TimePoint(1983, 12, 15, 21, 2, 0, 'America/Detroit'));
 * // -1
 *
 * // the format string is exactly the same as DateTime->format(). Note the required timezone argument.
 * $time->format('Y-m-d H:i:s', 'UTC');
 * // '1999-03-31 23:15:00'
 *
 * // Note that $time did not change, a copy was created. TimePoint->modify() takes the same argument format as modify()
 * $time2 = $time->modify('+1 day');
 * $time2->format('Y-m-d H:i:s', 'America/Detroit');
 * // '1999-04-01 18:15:00'
 *
 * $time3 = $time->add(new TimeInterval('P2D'))
 * $time3->format('Y-m-d H:i:s', 'America/Detroit');
 * // '1999-04-02 18:15:00'
 *
 * $time4 = $time->sub(new TimeInterval('P2D'))
 * $time4->format('Y-m-d H:i:s', 'America/Detroit');
 * // '1999-03-29 18:15:00'
 *
 * $time5 = $time->diff(new TimePoint(1983, 12, 15, 21, 2, 0, 'America/Detroit'))
 * $time5->format('%y years, %m months, %d days, %h hours, %i minutes');
 * // '15 years, 3 months, 15 days, 21 hours, 13 minutes'
 * ```
 *
 * @see http://php.net/manual/en/class.datetime.php
 */
class TimePoint implements JsonSerializable
{
    use TimeUtil;

    /**
     * @type DateTime
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
     *
     * @throws Exception
     */
    public function __construct(
        int $year,
        int $month,
        int $day,
        int $hour = 0,
        int $minute = 0,
        int $second = 0,
        string $timezone = 'UTC',
        int $microseconds = 0
    ) {
        $inputFormat = '%04d-%02d-%02d %02d:%02d:%02d.%06d';
        $format = sprintf($inputFormat, $year, $month, $day, $hour, $minute, $second, $microseconds);

        try {
            $tz = new DateTimeZone($timezone);
            $date = new DateTime($format, $tz);
        } catch (BaseException $e) {
            throw new Exception('Error with date format: ' . $e->getMessage(), 0, $e);
        }

        $this->date = $date;
        $this->date->setTimezone(new DateTimeZone('UTC'));
    }

    /**
     * Serialize as a RFC 3339 UTC timezone JSON string
     *
     * @see https://www.ietf.org/rfc/rfc3339.txt
     *
     * Example:
     *
     * ```php
     * $time = new TimePoint(2015, 10, 30, 14, 30, 0, 'America/Detroit');
     * echo json_encode($time);
     *
     * "2015-10-30T18:30:00Z"
     * ```
     *
     * @return string
     */
    public function jsonSerialize()
    {
        return $this->__toString();
    }

    /**
     * Serialize as a RFC 3339 UTC timezone JSON string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->format('Y-m-d\TH:i:s\Z', 'UTC');
    }

    /**
     * Compares two points in time
     *
     * @param TimePoint $timepoint The point in time to compare $this with.
     *
     * @return int Returns -1, 0 or 1 if $this is less than, equal to or greater than $timepoint respectively.
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
     *
     * @return string
     */
    public function format($format, $timezone)
    {
        $date = clone $this->date;
        $date->setTimezone(new DateTimeZone($timezone));
        return $date->format($format);
    }

    /**
     * Modifies the time point similar to DateTime->modify()
     *
     * The main difference is that this does not modify the current object in place as DateTime->modify() does.
     * Instead, this method returns a new TimePoint with the modification applied.
     *
     * @param string $modificationString
     *
     * @throws Exception
     *
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
     *
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
     *
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
     *
     * @return TimeInterval
     */
    public function diff(TimePoint $timepoint)
    {
        $interval = $this->date->diff($timepoint->date);
        $intervalSpec = $this->dateIntervalToIntervalSpec($interval);

        return new TimeInterval($intervalSpec);
    }
}
