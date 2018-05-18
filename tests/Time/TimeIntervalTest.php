<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Common\Time;

use PHPUnit\Framework\TestCase;
use QL\MCP\Common\Exception;

class TimeIntervalTest extends TestCase
{
    public function testIntervalSpecIsSavedFromConstruction()
    {
        $input = 'P2W';

        $tint = new TimeInterval($input);
        $this->assertSame($input, $tint->intervalSpec());
    }

    public function testBadIntervalSpecThrowsException()
    {
        $this->expectException(Exception::class);

        $input = 'bad interval format';
        new TimeInterval($input);
    }

    public function testGoodFormatterSpec()
    {
        $expected = 'every 14 days';
        $input = 'every %d days';
        $tint = new TimeInterval('P2W');
        $actual = $tint->format($input);
        $this->assertSame($expected, $actual);
    }

    public function testIntervalSpecWithInvert()
    {
        $expected = '-1 year';
        $input = '%r%y year';
        $tint = new TimeInterval('P1Y-I');
        $actual = $tint->format($input);
        $this->assertSame($expected, $actual);
    }

    public function testIntervalSpecWithDays()
    {
        $expected = '1 year or 365 days';
        $input = '%y year or %a days';
        $tint = new TimeInterval('P1Y-365D');
        $actual = $tint->format($input);
        $this->assertSame($expected, $actual);
    }

    public function testIntervalSpecWithInvertAndDays()
    {
        $expected = '-2 years or -730 days';
        $input = '%r%y years or %r%a days';
        $tint = new TimeInterval('P2Y-I730D');
        $actual = $tint->format($input);
        $this->assertSame($expected, $actual);
    }

    public function testIntervalSpecIgnoresBadDays()
    {
        $expected = '2 years or (unknown) days';
        $input = '%y years or %a days';
        $tint = new TimeInterval('P2Y-730');
        $actual = $tint->format($input);
        $this->assertSame($expected, $actual);
    }
}
