<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Common;

use PHPUnit\Framework\TestCase;
use ReflectionClass;

class OpaquePropertyTest extends TestCase
{
    public function setUp()
    {
        $this->resetNoise();
    }

    public function testValueNotDisplayedWhenStringified()
    {
        $prop = new OpaqueProperty('testsecret');

        $this->assertSame('[opaque property]', (String) $prop);
    }

    public function testDebugInfo()
    {
        $prop = new OpaqueProperty('testsecret');

        $expected = [
            'value' => '[opaque property]',
            'bytes' => 10
        ];

        $this->assertSame($expected, $prop->__debugInfo());
    }

    public function testVarDumpedWithoutDebugInfo()
    {
        $expected = <<<'VARDUMP'
  ["value"]=>
  string(17) "[opaque property]"
  ["bytes"]=>
  int(10)
VARDUMP;
        $expectedWithXDebug = <<<'VARDUMP'
  private $value =>
  string(10)
VARDUMP;

        $notrandom = 'O2yqbji4P1s4zEOnhp6EvedjmOJi34J4g9fo9nZvsyUw2sssoKSHU3lET2vh3ORiLFmgO/xgomJyTRPwW0eiUi7xWkXzA36UlZcIIs1I44qzwaEYOZXd9RFo+pG2Hff3htzoEV3eA6MT/Wx6/c+4sWKR2NtHg7U2+XK09uaf43c=';
        $this->resetNoise(unpack('C*', base64_decode($notrandom)));

        $prop = new OpaqueProperty('testsecret');

        ob_start();
        var_dump($prop);
        $vardumped = trim(ob_get_clean());

        if (extension_loaded('xdebug')) {
            // NOTE: xdebug now seems to ignore __debugInfo
            // https://bugs.xdebug.org/view.php?id=1777
            $expected = $expectedWithXDebug;
        }

        $x = strpos($vardumped, $expected);

        $this->assertStringContainsString($expected, $vardumped);
    }

    /**
     * @dataProvider providerStrings
     */
    public function testOpaqueValueInputAndOutputAreEqual($testString)
    {
        $prop = new OpaqueProperty($testString);
        $this->assertSame($testString, $prop->getValue());
    }

    /**
     * @dataProvider providerStrings
     */
    public function testStoredValueIsNotEqual($testString)
    {
        $prop = new OpaqueProperty($testString);
        $reflect = new ReflectionClass($prop);
        $value = $reflect->getProperty('value');

        $this->assertNotSame($testString, $value);
    }

    public function testExtendedOpaqueUsesDifferentKey()
    {
        $testString = 'test value';
        $original = new OpaqueProperty($testString);
        $extended = new ExtendedOpaqueProperty($testString);

        $reflect = new ReflectionClass($original);
        $prop = $reflect->getProperty('noise');
        $prop->setAccessible(true);
        $originalNoise = $prop->getValue();

        $reflect = new ReflectionClass($extended);
        $prop = $reflect->getProperty('noise');
        $prop->setAccessible(true);
        $extendedNoise = $prop->getValue();

        $this->assertNotSame($originalNoise, $extendedNoise);
        $this->assertSame($original->getValue(), $extended->getValue());
    }

    public function providerStrings()
    {
        return [
            ['derp herp in the kerp'],
            ["Let me absolve you

Of the past that controls you
"],
            ['HELLO,!#Ïðèâåò,user!'],
            ['🏀 📄 🚧 🔸 ✅ 🚀 ⏰ 🚨'],
            ['㐀 㐁 㐂 㐃 㐄 㐅 㐆 㐇 㐈 㐉 㐊 㐋 㐌 㐍 㐎 㐏 㐐 㐑 㐒 㐓 㐔 㐕 㐖 㐗 㐘 㐙 㐚 㐛 㐜 㐝 㐞 㐟 㐠 㐡 㐢 㐣 㐤'],
            ['𐌀 𐌁 𐌂 𐌃 𐌄 𐌅 𐌆 𐌇 𐌈 𐌉 𐌊 𐌋 𐌌 𐌍 𐌎 𐌏 𐌐 𐌑 𐌒 𐌓 𐌔 𐌕 𐌖 𐌗 𐌘 𐌙 𐌚 𐌛 𐌜 𐌝 𐌞 𐌠 𐌡 𐌢 𐌣'],
            ['℀ ℁ ℂ ℃ ℄ ℅ ℆ ℇ ℈ ℉ ℊ ℋ ℌ ℍ ℎ ℏ ℐ ℑ ℒ ℓ ℔ ℕ № ℗ ℘ ℙ ℚ ℛ ℜ ℝ ℞ ℟ ℠ ℡ ™ ℣ ℤ ℥ Ω ℧ ℨ ℩ K Å ℬ ℭ ℮ ℯ ℰ ℱ Ⅎ ℳ ℴ ℵ ℶ ℷ ℸ ℹ ℺ ℽ ℾ ℿ ⅀ ⅁ ⅂ ⅃ ⅄ ⅅ ⅆ ⅇ ⅈ ⅉ ⅊ ⅋']
        ];
    }

    private function resetNoise($noise = null)
    {
        $reflect = new ReflectionClass(OpaqueProperty::CLASS);
        $prop = $reflect->getProperty('noise');
        $prop->setAccessible(true);
        $prop->setValue($noise);
    }
}

class ExtendedOpaqueProperty extends OpaqueProperty
{
    protected static $noise;
}

