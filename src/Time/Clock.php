<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Common\Time;

use DateTime;
use DateTimeZone;
use Exception as BaseException;

/**
 * Abstract the system clock and provide TimePoint utility methods
 *
 * Usage:
 *
 * ```php
 * use MCP\Common\Time\Clock;
 *
 * $clock = new Clock;
 * $time = $clock->read();
 *
 * var_dump($time);
 * // class MCP\Common\Time\TimePoint#1 {}
 * ```
 */
class Clock
{
    use TimeUtil;

    const ERR_TIMEZONE = 'Invalid timezone %s.';
    const ERR_CURRENT = 'Invalid current datetime %s.';
    const ERR_FORMAT = 'Unable to parse malformed string %s to TimePoint.';

    /**
     * @type string
     */
    private $current;

    /**
     * @type DateTimeZone
     */
    private $timezone;

    /**
     * If an invalid timezone is provided, an exception will be thrown.
     *
     * @param string $current
     * @param string|null $timezone
     *
     * @throws Exception
     */
    public function __construct($current = 'now', $timezone = null)
    {
        $this->current = $current;
        $timezone = ($timezone === null) ? ini_get('date.timezone') : $timezone;

        // ensure that timezone is valid
        try {
            $this->timezone = new DateTimeZone($timezone);
        } catch (BaseException $e) {
            throw new Exception(sprintf(self::ERR_TIMEZONE, $timezone));
        }

        // ensure that current time is valid and that clock can be read
        if ($this->read() === null) {
            throw new Exception(sprintf(self::ERR_CURRENT, $timezone));
        }
    }

    /**
     * Get the current TimePoint
     *
     * @return TimePoint|null
     */
    public function read()
    {
        try {
            $datetime = new DateTime($this->current, $this->timezone);
        } catch (BaseException $e) {
            return null;
        }

        return $this->fromDateTime($datetime);
    }

    /**
     * Get a TimePoint from a DateTime object
     *
     * Note that, if the DateTime contains fractional seconds, that precision will be lost as the TimePoint object
     * does not currently support fractional seconds.
     *
     * @param DateTime $datetime
     *
     * @return TimePoint
     */
    public function fromDateTime(DateTime $datetime)
    {
        return $this->dateTimeToTimePoint($datetime);
    }

    /**
     * Get a TimePoint from a formatted string
     *
     * The format parameter should be a date() compatible string. If it is not provided this method will attempt to
     * parse in input with one of the following supported formats.
     *
     *  - UTC Implied (2015-12-10T10:10:00Z)
     *  - RFC 3339 (2015-12-10T10:10:00+04:00, 2015-12-10T10:10:00.000000+04:00)
     *  - ISO 8601 (2015-12-10T10:10:00+0000, 2015-12-10T10:10:00.000000+0000, 2015-12-10T10:10:00,000000+0000, etc)
     *
     * Note that, if a timezone is not specified in the input string, the clock's timezone will be used.
     *
     * @param string $input
     * @param string|null $format
     *
     * @return TimePoint|null
     */
    public function fromString($input, $format = null)
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
                $datetime = DateTime::createFromFormat(array_shift($formats), $input, $this->timezone);
            } while (!$datetime instanceof DateTime && count($formats) > 0);

        } else {
            $datetime = DateTime::createFromFormat($format, $input, $this->timezone);
        }

        return ($datetime instanceof DateTime) ? $this->fromDateTime($datetime) : null;
    }

    /**
     * Check that the current time is within a specified range
     *
     * Optionally, a strtotime() compatible skew modifier may be provided. This modifier allows for a certain amount
     * of clock skew between systems when performing the range check.
     *
     * @param TimePoint $expiration
     * @param TimePoint|null $creation
     * @param string|null $skew
     *
     * @throws Exception
     *
     * @return bool
     */
    public function inRange(TimePoint $expiration, TimePoint $creation = null, $skew = null)
    {
        $now = $this->read();

        if ($skew !== null) {
            if ($creation instanceof TimePoint) {
                $creation = $creation->modify(sprintf('-%s', $skew));
            }

            $expiration = $expiration->modify(sprintf('+%s', $skew));
        }

        return $now->compare($expiration) !== 1 && (!$creation instanceof TimePoint || $now->compare($creation) !== -1);
    }
}
