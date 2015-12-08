<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Common;

/**
 * This represents a Microsoft .NET GUID
 *
 * Note that a Microsoft .NET GUID is the same as an RFC 4122 UUID, standard
 * variant, 4th algorithm (see chapter 4.4 of the RFC for details).
 *
 * ```php
 * namespace MCP\Common;
 *
 * $guid = GUID::create();
 * echo $guid; // outputs something like {4577267B-AE54-4C03-8C86-E628D5D3695A}
 * ```
 *
 * @api
 */
class GUID
{
    /**
     * @type string[]
     */
    private static $searchChars = array('{', '-', '}', 'a', 'b', 'c', 'd', 'e', 'f');

    /**
     * @type string[]
     */
    private static $replaceChars = array('', '', '', 'A', 'B', 'C', 'D', 'E', 'F');

    /**
     * @type string
     */
    private $guid;

    /**
     * Creates a GUID object from a 'hex string'
     *
     * This accepts a strings such as "{9a39ed24-1752-4459-9ac2-6b0e8f0dcec7}" and generates a GUID object. Note that
     * this validates the input string and if validation fails it will return null.
     *
     * @param string $hexString
     *
     * @return GUID|null
     */
    public static function createFromHex($hexString)
    {
        $hexString = str_replace(static::$searchChars, static::$replaceChars, $hexString);
        if (!preg_match('/^[0-9A-Fa-f]{32}$/', $hexString)) {
            return null;
        }

        $bin = pack('H*', $hexString);
        if (!static::validate($bin)) {
            return null;
        }

        return new static($bin);
    }

    /**
     * @param string $base64String
     *
     * @return GUID|null
     */
    public static function createFromBase64($base64String)
    {
        $bin = base64_decode($base64String, true);
        if (false === $bin) {
            return null;
        }

        if (!static::validate($bin)) {
            return null;
        }

        return new static($bin);
    }

    /**
     * @param string $binString
     *
     * @return GUID|null
     */
    public static function createFromBin($binString)
    {
        if (!static::validate($binString)) {
            return null;
        }

        return new static($binString);
    }

    /**
     * Creates a new GUID
     *
     * @return GUID
     */
    public static function create()
    {
        $guid = pack(
            'nnnnnnnn',
            mt_rand(0x0000, 0xFFFF),
            mt_rand(0x0000, 0xFFFF),
            mt_rand(0x0000, 0xFFFF),
            mt_rand(0x4000, 0x4FFF),
            mt_rand(0x8000, 0xBFFF),
            mt_rand(0x0000, 0xFFFF),
            mt_rand(0x0000, 0xFFFF),
            mt_rand(0x0000, 0xFFFF)
        );

        return new static($guid);
    }

    /**
     * Validates the given string is a correct byte stream for a GUID
     *
     * This does some seemingly crazy things, but basically it validates that the given value will be within the set
     * of possible GUID's that GUID::create() can produce.
     *
     * @param string $guid
     *
     * @return bool
     */
    private static function validate($guid)
    {
        if (strlen($guid) !== 16) {
            return false;
        }

        $byte = $guid[6];
        $byte = (ord($byte) & 0xF0) >> 4;

        if ($byte !== 4) {
            return false;
        }

        $byte = $guid[8];
        $byte = (ord($byte) & 0xC0);

        if ($byte !== 0x80) {
            return false;
        }

        return true;
    }

    /**
     * @param string $bin
     */
    private function __construct($bin)
    {
        $this->guid = $bin;
    }

    /**
     * Outputs a 'human readable' version of the GUID string
     *
     * The formatting will currently output something like "{74EC705A-AD08-42A6-BCC5-5B9F93FAB0F4}" as the GUID
     * representation.
     *
     * @return string
     */
    public function asHumanReadable()
    {
        $hexStr = $this->asHex();
        $part1 = substr($hexStr, 0, 8);
        $part2 = substr($hexStr, 8, 4);
        $part3 = substr($hexStr, 12, 4);
        $part4 = substr($hexStr, 16, 4);
        $part5 = substr($hexStr, 20);

        return sprintf('{%s-%s-%s-%s-%s}', $part1, $part2, $part3, $part4, $part5);
    }

    /**
     * Outputs the guid as a straight hex string
     *
     * The string will look something like "74EC705AAD0842A6BCC55B9F93FAB0F4".
     *
     * @return string
     */
    public function asHex()
    {
        $normalizer = function ($val) {
            return str_pad(strtoupper(dechex($val)), 2, '0', STR_PAD_LEFT);
        };

        $out = unpack('C*', $this->guid);
        $out = array_map($normalizer, $out);
        $out = implode('', $out);

        return $out;
    }

    /**
     * Returns the GUID as a binary string
     *
     * @return string
     */
    public function asBin()
    {
        return $this->guid;
    }

    /**
     * Returns the guid as a base64 encoded string
     *
     * @return string
     */
    public function asBase64()
    {
        return rtrim(base64_encode($this->guid), '=');
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->asHumanReadable();
    }
}
