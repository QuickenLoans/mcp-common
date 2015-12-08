<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Common\Time;

use DateInterval;
use Exception as BaseException;

/**
 * Represents a time interval such as "2 weeks".
 *
 * This wraps the DateInterval class for this namespace.
 *
 * Usage:
 *
 * ```php
 * use QL\MCP\Common\Time\TimeInterval;
 *
 * // The interval spec is the exact same as PHP's DateInterval
 * $interval = new TimeInterval('P1M3DT7H');
 *
 * echo $interval->format("%d days %h hours\n");
 *
 * // "3 days 7 hours"
 * ```
 *
 * @see http://php.net/manual/en/class.dateinterval.php
 */
class TimeInterval
{
    /**
     * @type DateInterval
     */
    private $dateInterval;

    /**
     * @type string
     */
    private $intervalSpec;

    /**
     * @param string $timeIntervalSpec
     *
     * @throws Exception
     */
    public function __construct($timeIntervalSpec)
    {
        list($originalSpec, $extraSpec) = $this->parseSpec($timeIntervalSpec);

        try {
            $this->dateInterval = new DateInterval($originalSpec);
            $this->addNegativeInterval($extraSpec);
            $this->addDays($extraSpec);

        } catch (BaseException $e) {
            throw new Exception('Bad interval spec: ' . $e->getMessage(), 0, $e);
        }

        $this->intervalSpec = $timeIntervalSpec;
    }

    /**
     * @param string $formatSpec
     *
     * @return string
     */
    public function format($formatSpec)
    {
        return $this->dateInterval->format($formatSpec);
    }

    /**
     * Returns the original spec string that was used to create this interval
     *
     * @return string
     */
    public function intervalSpec()
    {
        return $this->intervalSpec;
    }

    /**
     * Add negative interval property that is lost when constructing a new DateInterval
     *
     * @param string $extraSpec
     *
     * @return null
     */
    private function addNegativeInterval($extraSpec)
    {
        if (stripos($extraSpec, 'i') === 0) {
            $this->dateInterval->invert = 1;
        }
    }

    /**
     * Add days property that is lost when constructing a new DateInterval
     *
     * @param string $extraSpec
     *
     * @return null
     */
    private function addDays($extraSpec)
    {
        if (preg_match('/(\d+)D$/', $extraSpec, $matches)) {
            $days = end($matches);

            $this->dateInterval = DateInterval::__set_state(
                array(
                    'y' => $this->dateInterval->y,
                    'm' => $this->dateInterval->m,
                    'd' => $this->dateInterval->d,
                    'h' => $this->dateInterval->h,
                    'i' => $this->dateInterval->i,
                    's' => $this->dateInterval->s,
                    'invert' => $this->dateInterval->invert,
                    'days' => $days
                )
            );
        }
    }

    /**
     * Parse the spec into the standard DateInterval spec, and our extra data spec
     *
     * @param string
     *
     * @return array
     */
    private function parseSpec($intervalSpec)
    {
        $spec = explode('-', $intervalSpec, 2);
        if (count($spec) === 2) {
            return $spec;

        } else {
            return array($intervalSpec, '');
        }
    }
}
