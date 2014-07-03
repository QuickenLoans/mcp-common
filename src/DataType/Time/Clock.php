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
}
