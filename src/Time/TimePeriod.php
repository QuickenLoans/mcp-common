<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Common\Time;

use DateInterval;
use DatePeriod;
use Iterator;
use IteratorIterator;

/**
 * A time period.
 *
 * Acts as an interator, which will return a TimePoint for each time in the period.
 *
 * Usage:
 *
 * ```php
 * use MCP\Common\Time\TimeInterval;
 * use MCP\Common\Time\TimePeriod;
 * use MCP\Common\Time\TimePoint;
 *
 * $start = new TimePoint(2012, 1, 1, 0, 0, 0, 'America/Detroit');
 * $interval = new TimeInterval('P1W');
 * $recurrences = 5;
 *
 * $period = TimePeriod::createWithRecurrences($start, $interval, $recurrences);
 * foreach ($period as $timePoint) {
 *     echo $timePoint->format("Y-m-d\n", 'America/Detroit');
 * }
 *
 * // Output
 * // 2012-01-01
 * // 2012-01-08
 * // 2012-01-15
 * // 2012-01-22
 * // 2012-01-29
 * ```
 *
 * @see http://php.net/manual/en/class.dateperiod.php
 */
class TimePeriod implements Iterator
{
    use TimeUtil;

    /**
     * @type DatePeriod
     */
    private $datePeriod;

    /**
     * @type IteratorIterator
     */
    private $iterator;

    /**
     * @param TimePoint $start
     * @param TimeInterval $interval
     * @param int $recurrences
     * @param int $options
     *
     * @return TimePeriod
     */
    public static function createWithRecurrences(TimePoint $start, TimeInterval $interval, $recurrences, $options = 0)
    {
        $cur = $start;
        if ($options & DatePeriod::EXCLUDE_START_DATE) {
            $recurrences = $recurrences + 1;
        }
        for ($i = 0; $i < $recurrences; $i++) {
            $cur = $cur->add($interval);
        }
        return new self($start, $interval, $cur, $options);
    }

    /**
     * @param TimePoint $start
     * @param TimeInterval $interval
     * @param TimePoint $end
     * @param int $options
     */
    public function __construct(TimePoint $start, TimeInterval $interval, TimePoint $end, $options = 0)
    {
        $startDt = $this->timePointToDateTime($start);
        $intervalDi = new DateInterval($interval->intervalSpec());
        $endDt = $this->timePointToDateTime($end);
        $this->datePeriod = new DatePeriod($startDt, $intervalDi, $endDt, $options);
        $this->iterator = new IteratorIterator($this->datePeriod);
    }

    /**
     * @return TimePoint
     */
    public function current()
    {
        $date = $this->iterator->current();
        return $this->dateTimeToTimePoint($date);
    }

    /**
     * @return null
     */
    public function next()
    {
        return $this->iterator->next();
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->iterator->key();
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->iterator->valid();
    }

    /**
     * @return null
     */
    public function rewind()
    {
        return $this->iterator->rewind();
    }
}
