<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\DataType\Time;

use DateTime;
use DateTimeZone;
use Exception as BaseException;

/**
 * This class abstracts the system clock
 *
 * This is mainly used for testing purposes since there is no good way to mock
 * the system clock's current time during unit testing.
 *
 * @api
 */
class Clock
{
    use TimeUtil;

    const ERR_FORMAT = 'Unable to parse malformed string %s to TimePoint.';

    /**
     * @var string
     */
    private $currentTime;

    /**
     * @var string
     */
    private $timeZone;

    /**
     * @param string $currentTime
     * @param string|null $timeZone
     * @throws Exception
     */
    public function __construct($currentTime = 'now', $timeZone = null)
    {
        $this->currentTime = $currentTime;
        $this->timeZone = $timeZone;
        $this->read(); // to double check the clock can be read immediately!
    }

    /**
     * Get the current TimePoint
     *
     * @return TimePoint
     * @throws Exception
     */
    public function read()
    {
        if ($this->timeZone) {
            try {
                $tz = new DateTimeZone($this->timeZone);
            } catch (BaseException $e) {
                throw new Exception('Invalid timezone: ' . $this->timeZone, 0, $e);
            }
        } else {
            $tz = new DateTimeZone(ini_get('date.timezone'));
        }

        try {
            $curTime = new DateTime($this->currentTime, $tz);
        } catch (BaseException $e) {
            throw new Exception('Invalid date: ' . $this->currentTime, 0, $e);
        }

        return $this->dateTimeToTimePoint($curTime);
    }

    /**
     * Get a TimePoint from a DateTime object
     *
     * Note that, if the DateTime contains fractional seconds, that precision will be lost as the TimePoint object
     * does not currently support fractional seconds.
     *
     * @param DateTime $datetime
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
     * @param string $input
     * @param string|null $format
     * @return TimePoint
     * @throws Exception
     */
    public function fromString($input, $format = null)
    {
        $result = $this->stringToTimePoint($input, $format);

        if ($result instanceof TimePoint) {
            return $result;
        }

        throw new Exception(sprintf(self::ERR_FORMAT, $input));
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
     * @return bool
     * @throws Exception
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
