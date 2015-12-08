<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Common;

use JsonSerializable;

/**
 * A US address with the following properties:
 * - Street 1
 * - Street 2
 * - City
 * - State
 * - Zip
 *
 * Usage:
 *
 * ```php
 * $address = new USAddress('1 Campus Martius', '', 'Detroit', 'MI', '48226');
 *
 * echo $address->street1() . $address->street2();
 * echo sprintf('%s, %s %d', $address->city(), $address->state(), $address->zip());
 *
 * // 1 Campus Martius
 * // Detroit, MI 48226
 * ```
 */
class USAddress implements JsonSerializable
{
    /**
     * @type string
     */
    private $street1;

    /**
     * @type string
     */
    private $street2;

    /**
     * @type string
     */
    private $city;

    /**
     * @type string
     */
    private $state;

    /**
     * @type string
     */
    private $zip;

    /**
     * @param string $street1
     * @param string $street2
     * @param string $city
     * @param string $state
     * @param string $zip
     */
    public function __construct($street1, $street2, $city, $state, $zip)
    {
        $this->street1 = $street1;
        $this->street2 = $street2;
        $this->city = $city;
        $this->state = $state;
        $this->zip = $zip;
    }

    /**
     * Serialize as a JSON object.
     *
     * Example:
     *
     * ```php
     * $address = new USAddress('1 Campus Martius', '', 'Detroit', 'MI', '48226');
     * echo json_encode($address);
     *
     * {"street1":"1 Campus Martius","street2":"","city":"Detroit","state":"MI","zip":"48226"}
     * ```
     */
    public function jsonSerialize()
    {
        return [
            'street1' => $this->street1(),
            'street2' => $this->street2(),
            'city' => $this->city(),
            'state' => $this->state(),
            'zip' => $this->zip(),
        ];
    }

    /**
     * @return string
     */
    public function street1()
    {
        return $this->street1;
    }

    /**
     * @return string
     */
    public function street2()
    {
        return $this->street2;
    }

    /**
     * @return string
     */
    public function city()
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function state()
    {
        return $this->state;
    }

    /**
     * @return string
     */
    public function zip()
    {
        return $this->zip;
    }
}
