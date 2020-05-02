<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Common;

use QL\MCP\Common\Utility\ByteString;
use function random_bytes;

/**
 * This wraps string values so they are obscured while in memory, in the case of stacktrace var_dumps, etc.
 *
 * All instances of OpaqueProperty share the same key used to obscure the true values. To use different values, create
 * new child classes that extend OpaqueProperty and redeclare $noise.
 *
 * ```php
 * class ExtendedOpaqueProperty extends OpaqueProperty {
 *   protected static $noise;
 * }
 * ```
 *
 * Usage:
 *
 * ```php
 * use QL\MCP\Common\OpaqueProperty;
 *
 * $secret = new OpaqueProperty('my_secret_token');
 *
 * echo $secret;
 * // [opaque property]
 *
 * echo $secret->getValue();
 * // "my_secret_token"
 * ```
 *
 * Inspiration and reference:
 *
 * @see https://github.com/phacility/libphutil/blob/master/src/error/PhutilOpaqueEnvelope.php
 */
class OpaqueProperty
{
    const SAFE_OUTPUT = '[opaque property]';

    const ERR_INVALID_TYPE = 'Could not obscure property of type "%s". Only strings can be stored.';

    /**
     * Obscured property
     *
     * @type string
     */
    private $value;

    /**
     * Noise used to obscure the property. This is static so it is generated only once per run.
     *
     * @type array
     */
    protected static $noise;

    /**
     * @param string $value
     */
    public function __construct($value)
    {
        if (!is_string($value)) {
            throw new Exception(sprintf(self::ERR_INVALID_TYPE, gettype($value)));
        }

        $this->value = $this->mask($value, self::getNoise());
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->mask($this->value, self::getNoise());
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return self::SAFE_OUTPUT;
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'value' => self::SAFE_OUTPUT,
            'bytes' => ByteString::strlen($this->value),
        ];
    }

    /**
     * @param string $input
     * @param array $noise
     *
     * @return string
     */
    private function mask($input, array $noise)
    {
        $xord = '';

        $input = unpack('C*', $input);
        $inputSize = count($input);
        $noiseSize = count($noise);

        $currentByte = 1;

        // Parse the string byte by byte and xor against noise
        while ($currentByte <= $inputSize) {
            $byte = $input[$currentByte];
            $noiseByte = $noise[($currentByte % $noiseSize) + 1];
            $xordByte = $byte ^ $noiseByte;

            $xord .= chr($xordByte);
            $currentByte++;
        }

        return $xord;
    }

    /**
     * @return array
     */
    protected static function getNoise()
    {
        if (static::$noise === null) {
            $rand = random_bytes(128);
            static::$noise = unpack('C*', $rand);
        }

        return static::$noise;
    }
}
