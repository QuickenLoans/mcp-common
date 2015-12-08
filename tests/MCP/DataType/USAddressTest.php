<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\DataType;

use PHPUnit_Framework_TestCase;

class USAddressTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group DataType
     * @group Address
     * @covers MCP\DataType\USAddress
     */
    public function testStreetAddress1ReturnsFromGetter()
    {
        $input = 'mystreet1';
        $expected = $input;
        $obj = new USAddress($input, null, null, null, null);
        $actual = $obj->street1();

        $this->assertSame($expected, $actual);
    }

    /**
     * @group DataType
     * @group Address
     * @covers MCP\DataType\USAddress
     */
    public function testStreetAddress2ReturnsFromGetter()
    {
        $input = 'mystreet2';
        $expected = $input;
        $obj = new USAddress(null, $input, null, null, null);
        $actual = $obj->street2();

        $this->assertSame($expected, $actual);
    }

    /**
     * @group DataType
     * @group Address
     * @covers MCP\DataType\USAddress
     */
    public function testCityReturnsFromGetter()
    {
        $input = 'mycity';
        $expected = $input;
        $obj = new USAddress(null, null, $input, null, null);
        $actual = $obj->city();

        $this->assertSame($expected, $actual);
    }

    /**
     * @group DataType
     * @group Address
     * @covers MCP\DataType\USAddress
     */
    public function testStateReturnsFromGetter()
    {
        $input = 'mystate';
        $expected = $input;
        $obj = new USAddress(null, null, null, $input, null);
        $actual = $obj->state();

        $this->assertSame($expected, $actual);
    }

    /**
     * @group DataType
     * @group Address
     * @covers MCP\DataType\USAddress
     */
    public function testZipReturnsFromGetter()
    {
        $input = 'myzip';
        $expected = $input;
        $obj = new USAddress(null, null, null, null, $input);
        $actual = $obj->zip();

        $this->assertSame($expected, $actual);
    }
}
