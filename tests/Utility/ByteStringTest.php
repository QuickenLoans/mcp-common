<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Common\Utility;

use PHPUnit_Framework_TestCase;

class ByteStringTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerStringLengths
     */
    public function testLengthIsCorrect($str, $length)
    {
        $this->assertSame($length, ByteString::strlen($str));
    }

    /**
     * @dataProvider providerOffsets
     */
    public function testOffsetIsCorrect($str, $offset, $expected)
    {
        $this->assertSame($expected, ByteString::substr($str, $offset, 1));
    }

    /**
     * @dataProvider providerMultibyteOffset
     */
    public function testMultibyteOffsetIsCorrect($str, $offset, $length, $expected)
    {
        $this->assertSame($expected, ByteString::substr($str, $offset, $length));
    }

    public function testOffsetWithoutLength()
    {
        $teststring = 'abcdef12345abcdef12345';
        $expected = 'ef12345';

        $this->assertSame($expected, ByteString::substr($teststring, 15));
    }


    public function providerStringLengths()
    {
        return [
            ['🏀📄🚧🚀⏰🚨', 23],
            ["\xf0\x9f\x8f\x80\xf0\x9f\x93\x84\xf0\x9f\x9a\xa7\xf0\x9f\x9a\x80\xe2\x8f\xb0\xf0\x9f\x9a\xa8", 23],
            ['a b c d e f A B C D E F', 23],
            ['𐌀 𐌁 𐌂 𐌃 𐌄 𐌅 𐌆 𐌇 𐌈 𐌉 𐌊 𐌋 𐌌 𐌍 𐌎 𐌏 𐌐 𐌑 𐌒 𐌓 𐌔 𐌕 𐌖 𐌗 𐌘 𐌙 𐌚 𐌛 𐌜 𐌝 𐌞 𐌠 𐌡 𐌢 𐌣', 174],
        ];
    }

    public function providerOffsets()
    {
        return [
            ['🏀📄🚧🚀⏰🚨', 17, "\x8f"],
            ["\xf0\x9f\x8f\x80\xf0\x9f\x93\x84\xf0\x9f\x9a\xa7\xf0\x9f\x9a\x80\xe2\x8f\xb0\xf0\x9f\x9a\xa8", 8, "\xf0"],
            ['a b c d e f A B C D E F', 6, 'd'],
            ['𐌀 𐌁 𐌂 𐌃 𐌄 𐌅 𐌆 𐌇 𐌈 𐌉 𐌊 𐌋 𐌌 𐌍 𐌎 𐌏 𐌐 𐌑 𐌒 𐌓 𐌔 𐌕 𐌖 𐌗 𐌘 𐌙 𐌚 𐌛 𐌜 𐌝 𐌞 𐌠 𐌡 𐌢 𐌣', 50, "\xf0"],
        ];
    }

    public function providerMultibyteOffset()
    {
        return [
            ['🏀📄🚧🚀⏰🚨', 12, 4, "\xf0\x9f\x9a\x80"],
            ['🏀📄🚧🚀⏰🚨', 12, 4, '🚀'],
            ["\xf0\x9f\x8f\x80\xf0\x9f\x93\x84\xf0\x9f\x9a\xa7\xf0\x9f\x9a\x80\xe2\x8f\xb0\xf0\x9f\x9a\xa8", 12, 4, "\xf0\x9f\x9a\x80"],
            ["\xf0\x9f\x8f\x80\xf0\x9f\x93\x84\xf0\x9f\x9a\xa7\xf0\x9f\x9a\x80\xe2\x8f\xb0\xf0\x9f\x9a\xa8", 12, 4, '🚀'],
        ];
    }
}
