<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\DataType;

/**
 * A US address
 *
 * @api
 */
class USAddress
{
    /**
     * @var string
     */
    private $street1;

    /**
     * @var string
     */
    private $street2;

    /**
     * @var string
     */
    private $city;

    /**
     * @var string
     */
    private $state;

    /**
     * @var string
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
