<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Common;

use PHPUnit\Framework\TestCase;
use QL\MCP\Common\Testing\GUIDExtendedMock;

class GUIDTest extends TestCase
{
    /**
     * @dataProvider validGuids
     */
    public function testAllInputHexFormatsConstructedGuidCorrectly($inputHex, $inputB64, $binary, $outputB64, $outputHex, $outputHuman)
    {
        $expected = $outputHex;
        $guid = GUID::createFromHex($inputHex);
        $this->assertInstanceOf(GUID::class, $guid);

        $actual = $guid->asHex();
        $this->assertSame($expected, $actual);
    }

    /**
     * @dataProvider validGuids
     */
    public function testAllBase64InputStringsConstructGuidCorrectly($inputHex, $inputB64, $testAllBinaryStringsConstructGuidCorrectly, $outputB64, $outputHex, $outputHuman)
    {
        $expected = $outputHex;
        $guid = GUID::createFromBase64($inputB64);
        $this->assertInstanceOf(GUID::class, $guid);

        $actual = $guid->asHex();
        $this->assertSame($expected, $actual);
    }

    /**
     * @dataProvider validGuids
     */
    public function testAllBinaryStringsConstructGuidCorrectly($inputHex, $inputB64, $binary, $outputB64, $outputHex, $outputHuman)
    {
        $expected = $outputHex;
        $guid = GUID::createFromBin($binary);
        $this->assertInstanceOf(GUID::class, $guid);

        $actual = $guid->asHex();
        $this->assertSame($expected, $actual);
    }

    /**
     * @dataProvider validGuids
     */
    public function testAllGuidsNormalizeBase64Output($inputHex, $inputB64, $binary, $outputB64, $outputHex, $outputHuman)
    {
        $expected = $outputB64;
        $guid = GUID::createFromBin($binary);
        $this->assertInstanceOf(GUID::class, $guid);

        $actual = $guid->asBase64();
        $this->assertSame($expected, $actual);
    }

    /**
     * @dataProvider validGuids
     */
    public function testAllGuidsNormalizeHumanReadableOutput($inputHex, $inputB64, $binary, $outputB64, $outputHex, $outputHuman)
    {
        $expected = $outputHuman;
        $guid = GUID::createFromBin($binary);
        $this->assertInstanceOf(GUID::class, $guid);

        $actual = $guid->asHumanReadable();
        $this->assertSame($expected, $actual);
    }

    /**
     * @dataProvider validGuids
     */
    public function testAllGuidsNormalizeToStringOutput($inputHex, $inputB64, $binary, $outputB64, $outputHex, $outputHuman)
    {
        $expected = $outputHuman;
        $guid = GUID::createFromBin($binary);
        $this->assertInstanceOf(GUID::class, $guid);

        $actual = (string) $guid;
        $this->assertSame($expected, $actual);
    }


    /**
     * @dataProvider validGuids
     */
    public function testAllGuidsNormalizeBinaryOutput($inputHex, $inputB64, $binary, $outputB64, $outputHex, $outputHuman)
    {
        $expected = $binary;
        $guid = GUID::createFromBin($binary);
        $this->assertInstanceOf(GUID::class, $guid);

        $actual = $guid->asBin();
        $this->assertSame($expected, $actual);
    }

    /**
     * @dataProvider invalidGuids
     */
    public function testCreatingFromHexStringCatchesInvalidGuids($hex, $base64, $bin)
    {
        $guid = GUID::createFromHex($hex);
        $this->assertNull($guid);
    }

    /**
     * @dataProvider invalidGuids
     */
    public function testCreatingFromBase64StringCatchesInvalidGuids($hex, $base64, $bin)
    {
        $guid = GUID::createFromBase64($base64);
        $this->assertNull($guid);
    }

    /**
     * @dataProvider invalidGuids
     */
    public function testCreatingFromBinaryStringCatchesInvalidGuids($hex, $base64, $bin)
    {
        $guid = GUID::createFromBin($bin);
        $this->assertNull($guid);
    }

    /**
     * This is a weird test in that if a bug exists, it MIGHT find it.  Because
     * of the random nature of GUID's you might consider either upping the
     * counter or just running this test many times if you suspect there is a
     * bug in the code this covers.
     */
    public function testNewlyCreatedGuidsAreValid()
    {
        for ($i = 0; $i < 64; $i++) {
            $newguid = GUID::create();
            $guid = GUID::createFromBin($newguid->asBin());
            $msg = 'GUID::create() seems to be producing invalid GUIDs:' . $newguid->asBin() . "\n";
            $this->assertNotNull($guid, $msg);
        }
    }

    public function testGUIDIsJSONSerializable()
    {
        $guid = GUID::createFromHex('9a39ed24-1752-4459-9ac2-6b0e8f0dcec7');
        $expected = '"{9A39ED24-1752-4459-9AC2-6B0E8F0DCEC7}"';

        $this->assertSame($expected, json_encode($guid));
    }

    public function testCustomFormatted()
    {
        $guid = GUID::createFromHex('9a39ed24-1752-4459-9ac2-6b0e8f0dcec7');

        $this->assertSame('9A39ED24175244599AC26B0E8F0DCEC7', $guid->format(GUID::UPPERCASE));
        $this->assertSame('{9A39ED24175244599AC26B0E8F0DCEC7}', $guid->format(GUID::UPPERCASE | GUID::BRACES));
        $this->assertSame('9a39ed24-1752-4459-9ac2-6b0e8f0dcec7', $guid->format(GUID::HYPHENATED));
        $this->assertSame('9a39ed24175244599ac26b0e8f0dcec7', $guid->format(0));

        $this->assertSame('9a39ed24175244599ac26b0e8f0dcec7', $guid->format(GUID::STANDARD));
        $this->assertSame('{9A39ED24-1752-4459-9AC2-6B0E8F0DCEC7}', $guid->format(GUID::READABLE));
    }

    public function testExtendedGUIDCanChangeDefaultFormatting()
    {
        $guid = GUIDExtendedMock::createFromHex('9a39ed24-1752-4459-9ac2-6b0e8f0dcec7');

        $this->assertSame('9a39ed24175244599ac26b0e8f0dcec7', $guid->asHumanReadable());
        $this->assertSame('9a39ed24175244599ac26b0e8f0dcec7', (string) $guid);
        $this->assertSame('"9a39ed24175244599ac26b0e8f0dcec7"', json_encode($guid));
    }

    /**
     * @return array
     */
    public function validGuids()
    {
        return array(
            //    input hex format,                         input base64 format,        binary,                                         output base64 format,     output hex format,                  output human readable
            array('{0C875FFC-61AB-4A75-A4AF-5F89ADCE0D63}', 'DIdf/GGrSnWkr1+Jrc4NYw==', pack('H*', '0C875FFC61AB4A75A4AF5F89ADCE0D63'), 'DIdf/GGrSnWkr1+Jrc4NYw', '0C875FFC61AB4A75A4AF5F89ADCE0D63', '{0C875FFC-61AB-4A75-A4AF-5F89ADCE0D63}'),
            array('{870e91a0-e54d-477a-85ec-1666d7be3c4c}', 'hw6RoOVNR3qF7BZm1748TA==',  pack('H*', '870E91A0E54D477A85EC1666D7BE3C4C'), 'hw6RoOVNR3qF7BZm1748TA', '870E91A0E54D477A85EC1666D7BE3C4C', '{870E91A0-E54D-477A-85EC-1666D7BE3C4C}'),
            array('{A941FCAAEC34403BA7D0B6D837AF5535}',     'qUH8quw0QDun0LbYN69VNQ',   pack('H*', 'A941FCAAEC34403BA7D0B6D837AF5535'), 'qUH8quw0QDun0LbYN69VNQ', 'A941FCAAEC34403BA7D0B6D837AF5535', '{A941FCAA-EC34-403B-A7D0-B6D837AF5535}'),
            array('{8314700199e14ea4a4d6bf9b8fbef486}',     'gxRwAZnhTqSk1r+bj770hg==', pack('H*', '8314700199E14EA4A4D6BF9B8FBEF486'), 'gxRwAZnhTqSk1r+bj770hg', '8314700199E14EA4A4D6BF9B8FBEF486', '{83147001-99E1-4EA4-A4D6-BF9B8FBEF486}'),
            array('54F45675-9A56-4967-AF24-2F3F0E8468F7',   'VPRWdZpWSWevJC8/DoRo9w==',  pack('H*', '54F456759A564967AF242F3F0E8468F7'), 'VPRWdZpWSWevJC8/DoRo9w', '54F456759A564967AF242F3F0E8468F7', '{54F45675-9A56-4967-AF24-2F3F0E8468F7}'),
            array('33f46a60-9cc7-4a4e-9927-d7e3fc92bbe9',   'M/RqYJzHSk6ZJ9fj/JK76Q',   pack('H*', '33F46A609CC74A4E9927D7E3FC92BBE9'), 'M/RqYJzHSk6ZJ9fj/JK76Q', '33F46A609CC74A4E9927D7E3FC92BBE9', '{33F46A60-9CC7-4A4E-9927-D7E3FC92BBE9}'),
            array('4FF9746ABAEE4FA3973BB3B740E3812A',       'T/l0arruT6OXO7O3QOOBKg==', pack('H*', '4FF9746ABAEE4FA3973BB3B740E3812A'), 'T/l0arruT6OXO7O3QOOBKg', '4FF9746ABAEE4FA3973BB3B740E3812A', '{4FF9746A-BAEE-4FA3-973B-B3B740E3812A}'),
            array('b4ca6d3610eb4bfaa8c52b45441d2e01',       'tMptNhDrS/qoxStFRB0uAQ==', pack('H*', 'B4CA6D3610EB4BFAA8C52B45441D2E01'), 'tMptNhDrS/qoxStFRB0uAQ', 'B4CA6D3610EB4BFAA8C52B45441D2E01', '{B4CA6D36-10EB-4BFA-A8C5-2B45441D2E01}'),
        );
    }

    /**
     * @return array
     */
    public function invalidGuids()
    {
        return array(
            //    hex string,                             base64 string,            binary string
            array('9e222abb-4324-0577-a4de-ca7b33fd8390', 'niIqu0MkBXek3sp7M/2DkA==', pack('H*', '9E222ABB43240577A4DECA7B33FD8390')), // not psudo-random guid
            array('9ac8058f-0078-15ca-84ee-8644aeceefc4', 'msgFjwB4FcqE7oZErs7vxA==', pack('H*', '9AC8058F007815CA84EE8644AECEEFC4')), // not psudo-random guid
            array('9a39ed24-1752-2459-9ac2-6b0e8f0dcec7', 'mjntJBdSJFmawmsOjw3Oxw==', pack('H*', '9A39ED24175224599AC26B0E8F0DCEC7')), // not psudo-random guid
            array('74ec705a-ad08-32a6-bcc5-5b9f93fab0f4', 'dOxwWq0IMqa8xVufk/qw9A==', pack('H*', '74EC705AAD0832A6BCC55B9F93FAB0F4')), // not psudo-random guid
            array('d7db9a3b-adf8-5916-b541-10d77a20678c', '19uaO634WRa1QRDXeiBnjA==', pack('H*', 'D7DB9A3BADF85916B54110D77A20678C')), // not psudo-random guid
            array('decaa466-4cab-6142-9fd7-b2f88b375fc7', '3sqkZkyrYUKf17L4izdfxw==', pack('H*', 'DECAA4664CAB61429FD7B2F88B375FC7')), // not psudo-random guid
            array('2bcb2093-b0bd-760d-83a2-9af17124cc91', 'K8sgk7C9dg2DoprxcSTMkQ==', pack('H*', '2BCB2093B0BD760D83A29AF17124CC91')), // not psudo-random guid
            array('754e083a-4779-805c-95df-9ba2ae87d3ea', 'dU4IOkd5gFyV35uirofT6g==', pack('H*', '754E083A4779805C95DF9BA2AE87D3EA')), // not psudo-random guid
            array('7c629b8c-8ee9-9524-8c27-5859f7d89e3a', 'fGKbjI7plSSMJ1hZ99ieOg==', pack('H*', '7C629B8C8EE995248C275859F7D89E3A')), // not psudo-random guid
            array('b725f3ae-1ec6-a792-8aa0-b590803011d0', 'tyXzrh7Gp5KKoLWQgDAR0A==', pack('H*', 'B725F3AE1EC6A7928AA0B590803011D0')), // not psudo-random guid
            array('e7eb8647-7d67-bf15-b1c8-c4a3f0ef332c', '5+uGR31nvxWxyMSj8O8zLA==', pack('H*', 'E7EB86477D67BF15B1C8C4A3F0EF332C')), // not psudo-random guid
            array('ebb23fcc-93a2-cc40-ad7b-7f77005bb54e', '67I/zJOizECte393AFu1Tg==', pack('H*', 'EBB23FCC93A2CC40AD7B7F77005BB54E')), // not psudo-random guid
            array('9d74085f-6fa3-d15f-8d19-4f1f5bde973d', 'nXQIX2+j0V+NGU8fW96XPQ==', pack('H*', '9D74085F6FA3D15F8D194F1F5BDE973D')), // not psudo-random guid
            array('672dbe48-5fce-e3e3-a901-3940318b314d', 'Zy2+SF/O4+OpATlAMYsxTQ==', pack('H*', '672DBE485FCEE3E3A9013940318B314D')), // not psudo-random guid
            array('f2fdac34-0cd8-f3d8-b89f-9b969cf49a13', '8v2sNAzY89i4n5uWnPSaEw==', pack('H*', 'F2FDAC340CD8F3D8B89F9B969CF49A13')), // not psudo-random guid
            array('b7fb7bd0-3a4e-4479-0c87-671f33870029', 't/t70DpORHkMh2cfM4cAKQ==', pack('H*', 'B7FB7BD03A4E44790C87671F33870029')), // not standard UUID variant
            array('984c689e-3f9c-44c8-12cb-4c801ae758a6', 'mExonj+cRMgSy0yAGudYpg==', pack('H*', '984C689E3F9C44C812CB4C801AE758A6')), // not standard UUID variant
            array('181c8846-9d9e-41ae-22c0-dd2ca36820d5', 'GByIRp2eQa4iwN0so2gg1Q==', pack('H*', '181C88469D9E41AE22C0DD2CA36820D5')), // not standard UUID variant
            array('5681e6bd-d545-4473-31d9-e5d5b8848e05', 'VoHmvdVFRHMx2eXVuISOBQ==', pack('H*', '5681E6BDD545447331D9E5D5B8848E05')), // not standard UUID variant
            array('47583191-9cd7-4d6f-4137-e09b8c97a280', 'R1gxkZzXTW9BN+CbjJeigA==', pack('H*', '475831919CD74D6F4137E09B8C97A280')), // not standard UUID variant
            array('0b64bb5b-ce19-4436-5c6c-2ff432713226', 'C2S7W84ZRDZcbC/0MnEyJg==', pack('H*', '0B64BB5BCE1944365C6C2FF432713226')), // not standard UUID variant
            array('cc762570-ffd1-418e-6a19-897ae58d41b1', 'zHYlcP/RQY5qGYl65Y1BsQ==', pack('H*', 'CC762570FFD1418E6A19897AE58D41B1')), // not standard UUID variant
            array('f11debf9-82e4-4736-716a-71207cf50ea6', '8R3r+YLkRzZxanEgfPUOpg==', pack('H*', 'F11DEBF982E44736716A71207CF50EA6')), // not standard UUID variant
            array('c32d41aa-cca7-46e2-ceec-ef64cb813676', 'wy1BqsynRuLO7O9ky4E2dg==', pack('H*', 'C32D41AACCA746E2CEECEF64CB813676')), // not standard UUID variant
            array('4c2ca366-5a76-4c2e-df65-9384177273cc', 'TCyjZlp2TC7fZZOEF3JzzA==', pack('H*', '4C2CA3665A764C2EDF659384177273CC')), // not standard UUID variant
            array('b81d1641-0765-4085-ea17-ac0f91ebd6b3', 'uB0WQQdlQIXqF6wPkevWsw==', pack('H*', 'B81D164107654085EA17AC0F91EBD6B3')), // not standard UUID variant
            array('9650bc2b-c7fe-4495-f919-7f4f6a16af4e', 'llC8K8f+RJX5GX9PahavTg==', pack('H*', '9650BC2BC7FE4495F9197F4F6A16AF4E')), // not standard UUID variant
            array('ZZ50bc2b-c7fe-4495-f919-7f4f6a16af4e', 'DIdf___',                  pack('H*', '0C875FFC61AB4A75A4AF5F89ADCE0D6382734')),
            array('adfec787-23cc-72ee-aa74-89----------', 'llC8K8f+RJX5GX9PahavTggl', pack('H*', '0C875FFC61AB4A75A4AF5F89ADCE')),
            array('a{}{}{}7-23cc-72ee-aa74-89----------', 'llC8K8f+RJX5GX',           pack('H*', '0C875FFC61AB4A75A4AF5F89ADCE')),
        );
    }
}
