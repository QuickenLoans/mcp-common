<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Common;

use JsonSerializable;
use QL\MCP\Common\Utility\ByteString;

/**
 * This represents a Microsoft .NET GUID
 *
 * Note that a Microsoft .NET GUID is the same as an RFC 4122 UUID, standard variant, version 4.
 *
 * Usage:
 *
 * ```php
 * namespace QL\MCP\Common;
 *
 * $guid = GUID::create();
 * echo $guid;
 *
 * // {4577267B-AE54-4C03-8C86-E628D5D3695A}
 * ```
 *
 * @see https://en.wikipedia.org/wiki/Universally_unique_identifier#Version_4_.28random.29
 * @see https://www.ietf.org/rfc/rfc4122.txt
 */
class GUID implements JsonSerializable
{
    const STANDARD = 0;
    const HYPENATED = 0x8; // Remains for backwards compatibility
    const HYPHENATED = 0x8;
    const UPPERCASE = 0x10;
    const BRACES = 0x20;

    const READABLE = self::HYPHENATED | self::UPPERCASE | self::BRACES;

    const SEPERATOR_HYPHEN = '-';

    /**
     * @type string[]
     */
    protected static $searchChars = ['{', '-', '}', 'a', 'b', 'c', 'd', 'e', 'f'];

    /**
     * @type string[]
     */
    protected static $replaceChars = ['', '', '', 'A', 'B', 'C', 'D', 'E', 'F'];

    /**
     * @type string
     */
    private $guid;

    /**
     * Creates a GUID object from a "hex string"
     *
     * This accepts a strings such as "{9a39ed24-1752-4459-9ac2-6b0e8f0dcec7}" and generates a GUID object.
     * Note that this validates the input string and if validation fails it will return null.
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
     * Creates a new V4 UUID
     *
     * @return GUID
     */
    public static function create()
    {
        $guid = \random_bytes(16);

        // Reset version byte to version 4 (0100)
        $guid[6] = chr(ord($guid[6]) & 0x0f | 0x40);

        $guid[8] = chr(ord($guid[8]) & 0x3f | 0x80);

        return new static($guid);
    }

    /**
     * Validates the given string is a correct byte stream for a GUID.
     *
     * This does some seemingly crazy things, but basically it validates that the given value will be within the set
     * of possible GUID's that GUID::create() can produce.
     *
     * @param string $guid
     *
     * @return bool
     */
    protected static function validate($guid)
    {
        if (ByteString::strlen($guid) !== 16) {
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
     * Outputs a 'formatted' version of the GUID string
     *
     * The formatting will currently output something like "{74EC705A-AD08-42A6-BCC5-5B9F93FAB0F4}" as the GUID
     * representation.
     *
     * Example:
     *
     * ```php
     * $guid = GUID::createFromHex('9a39ed24-1752-4459-9ac2-6b0e8f0dcec7');
     *
     * echo $guid->format();
     * 9a39ed24175244599ac26b0e8f0dcec7
     *
     * echo $guid->format(GUID::FORMAT_BRACES | GUID::FORMAT_UPPERCASE);
     * {9a39ed24175244599ac26b0e8f0dcec7}
     *
     * echo $guid->format(0);
     * 9a39ed24175244599ac26b0e8f0dcec7
     * ```
     *
     * @param int $format
     *
     * @return string
     */
    public function format($format = self::STANDARD)
    {
        $hexStr = strtolower($this->asHex());

        $parts = [
            substr($hexStr, 0, 8),
            substr($hexStr, 8, 4),
            substr($hexStr, 12, 4),
            substr($hexStr, 16, 4),
            substr($hexStr, 20)
        ];

        if ($format & self::UPPERCASE) {
            $parts = array_map(function($v) {
                return strtoupper($v);
            }, $parts);
        }


        $separator = '';
        if ($format & self::HYPHENATED) {
            $separator = self::SEPERATOR_HYPHEN;
        }

        $formatted = implode($separator, $parts);

        if ($format & self::BRACES) {
            $formatted = sprintf('{%s}', $formatted);
        }

        return $formatted;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->format(static::READABLE);
    }

    /**
     * Serialize as a JSON string in human-readable form.
     *
     * Example:
     *
     * ```php
     * $guid = GUID::createFromHex('9a39ed24-1752-4459-9ac2-6b0e8f0dcec7');
     * echo json_encode($guid);
     *
     * "{9A39ED24-1752-4459-9AC2-6B0E8F0DCEC7}"
     * ```
     *
     * @return string
     */
    public function jsonSerialize()
    {
        return $this->format(static::READABLE);
    }

    /**
     * Outputs a 'human readable' version of the GUID string
     *
     * The formatting will currently output something like "{74EC705A-AD08-42A6-BCC5-5B9F93FAB0F4}" as the GUID
     * representation.
     *
     * Example:
     *
     * ```php
     * $guid = GUID::createFromHex('9a39ed24-1752-4459-9ac2-6b0e8f0dcec7');
     *
     * echo $guid->asHumanReadable();
     * {9A39ED24-1752-4459-9AC2-6B0E8F0DCEC7}
     * ```
     *
     * @param int $format
     *
     * @return string
     */
    public function asHumanReadable()
    {
        return $this->format(static::READABLE);
    }

    /**
     * Outputs the guid as a straight hex string
     *
     * The string will look something like "74EC705AAD0842A6BCC55B9F93FAB0F4".
     *
     * Example:
     *
     * ```php
     * $guid = GUID::createFromHex('9a39ed24-1752-4459-9ac2-6b0e8f0dcec7');
     *
     * echo $guid->asHex();
     * 9A39ED24175244599AC26B0E8F0DCEC7
     * ```
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
}
