<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\DataType;

use PHPUnit_Framework_TestCase;

class HttpUrlTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group DataType
     * @group HTTP
     * @covers MCP\DataType\HttpUrl
     * @dataProvider urlData
     */
    public function testUrlProtocolParsesAsExpected($input, $protocol, $secure, $host, $port, $path, $query, $full)
    {
        $url = HttpUrl::create($input);
        $this->assertSame($protocol, $url->protocol());
    }

    /**
     * @group DataType
     * @group HTTP
     * @covers MCP\DataType\HttpUrl
     * @dataProvider urlData
     */
    public function testUrlSecureReturnsAsExpected($input, $protocol, $secure, $host, $port, $path, $query, $full)
    {
        $url = HttpUrl::create($input);
        $this->assertSame($secure, $url->secure());
    }

    /**
     * @group DataType
     * @group HTTP
     * @covers MCP\DataType\HttpUrl
     * @dataProvider urlData
     */
    public function testUrlHostParsesAsExpected($input, $protocol, $secure, $host, $port, $path, $query, $full)
    {
        $url = HttpUrl::create($input);
        $this->assertSame($host, $url->host());
    }

    /**
     * @group DataType
     * @group HTTP
     * @covers MCP\DataType\HttpUrl
     * @dataProvider urlData
     */
    public function testUrlPortParsesAsExpected($input, $protocol, $secure, $host, $port, $path, $query, $full)
    {
        $url = HttpUrl::create($input);
        $this->assertSame($port, $url->port());
    }

    /**
     * @group DataType
     * @group HTTP
     * @covers MCP\DataType\HttpUrl
     * @dataProvider urlData
     */
    public function testUrlPathParsesAsExpected($input, $protocol, $secure, $host, $port, $path, $query, $full)
    {
        $url = HttpUrl::create($input);
        $this->assertSame($path, $url->path());
    }

    /**
     * @group DataType
     * @group HTTP
     * @covers MCP\DataType\HttpUrl
     * @dataProvider urlData
     */
    public function testUrlQueryParsesAsExpected($input, $protocol, $secure, $host, $port, $path, $query, $full)
    {
        $url = HttpUrl::create($input);
        $this->assertSame($query, $url->queryData());
    }

    /**
     * @group DataType
     * @group HTTP
     * @covers MCP\DataType\HttpUrl
     * @dataProvider urlData
     */
    public function testUrlMostlyReturnsOriginalAsString($input, $protocol, $secure, $host, $port, $path, $query, $full)
    {
        $url = HttpUrl::create($input);
        $this->assertSame($full, $url->asString());
    }

    /**
     * @group DataType
     * @group HTTP
     * @covers MCP\DataType\HttpUrl
     * @dataProvider urlErrors
     */
    public function testUrlCreationFailsWithInvalidUrl($input)
    {
        $url = HttpUrl::create($input);
        $this->assertNull($url);
    }

    /**
     * @group DataType
     * @group HTTP
     * @covers MCP\DataType\HttpUrl
     * @dataProvider urlPaths
     */
    public function testUrlEncodingNormalization($input, $path, $segments)
    {
        $url = HttpUrl::create($input);
        $this->assertSame($path, $url->path());
    }

    /**
     * @group DataType
     * @group HTTP
     * @covers MCP\DataType\HttpUrl
     * @dataProvider urlPaths
     */
    public function testUrlSegmentDecoding($input, $path, $segments)
    {
        $url = HttpUrl::create($input);
        $this->assertSame($segments, $url->segments());
    }

    public function urlData()
    {
        return array(
            //     input,                                            ->protocol(), ->secure(), ->host(),            ->port(), ->path(),             ->queryData()                      ->asString()
            array( '/this/is/a/path',                                null,         null,       null,                null,     '/this/is/a/path',    null,                              '/this/is/a/path'                                ),
            array( 'http://example.com/url/path',                    'http',       false,      'example.com',       80,       '/url/path',          null,                              'http://example.com/url/path'                    ),
            array( 'https://a.example.com/path/to/file?n1=v1&n2=v2', 'https',      true,       'a.example.com',     80,       '/path/to/file',      array('n1' => 'v1', 'n2' => 'v2'), 'https://a.example.com/path/to/file?n1=v1&n2=v2' ),
            array( 'http://awesome/?',                               'http',       false,      'awesome',           80,       '/',                  array(),                           'http://awesome/?'                               ),
            array( '/reviews/state/mi?limit=10&offset=&debug',       null,         null,       null,                null,     '/reviews/state/mi',  array('limit' => '10', 'offset' => '', 'debug' => ''), '/reviews/state/mi?limit=10&offset&debug'),
            array( 'https://dom/?dup=1&dup=2&dup=3',                 'https',      true,       'dom',               80,       '/',                  array('dup' => '3'),               'https://dom/?dup=3'                             ),
            array( '//example/relative/abs/path',                    null,         null,       'example',           80,       '/relative/abs/path', null,                              '//example/relative/abs/path'                    ),
            array( 'http://example.com',                             'http',       false,      'example.com',       80,       '/',                  null,                              'http://example.com/'                            ),
            array( 'http://domain:8080/',                            'http',       false,      'domain',            8080,     '/',                  null,                              'http://domain:8080/'                            ),
            array( 'http://1-800-FLOWERS.COM',                       'http',       false,      '1-800-FLOWERS.COM', 80,       '/',                  null,                              'http://1-800-FLOWERS.COM/'                      ),
        );
    }

    public function urlPaths()
    {
        return array(
            //    input,                                     ->path(),                                  ->segments()
            array('/%00%01%02%03/%04%05%06%07/%08%09%0a%0b', '/%00%01%02%03/%04%05%06%07/%08%09%0A%0B', array(chr( 0).chr( 1).chr( 2).chr( 3), chr( 4).chr( 5).chr( 6).chr( 7), chr( 8).chr( 9).chr(10).chr(11))),
            array('/%0c%0d%0e%0f/%10%11%12%13/%14%15%16%17', '/%0C%0D%0E%0F/%10%11%12%13/%14%15%16%17', array(chr(12).chr(13).chr(14).chr(15), chr(16).chr(17).chr(18).chr(19), chr(20).chr(21).chr(22).chr(23))),
            array('/%18%19%1a%1b/%1c%1d%1e%1f',              '/%18%19%1A%1B/%1C%1D%1E%1F',              array(chr(24).chr(25).chr(26).chr(27), chr(28).chr(29).chr(30).chr(31))                                 ),
            array('/%20%21%22%23/%24%25%26%27/%28%29%2a%2B', '/%20%21%22%23/%24%25%26%27/%28%29%2A%2B', array(' !"#', "$%&'", '()*+')),
            array('/%2c%2D%2e%2F/%30%31%32%33/%34%35%36%37', '/%2C-.%2F/0123/4567',                     array(',-./', '0123', '4567')),
            array('/%38%39%3a%3B/%3c%3D%3e%3F/%40%41%42%43', '/89%3A%3B/%3C%3D%3E%3F/%40ABC',           array('89:;', '<=>?', '@ABC')),
            array('/%44%45%46%47/%48%49%4A%4b/%4C%4d%4E%4f', '/DEFG/HIJK/LMNO',                         array('DEFG', 'HIJK', 'LMNO')),
            array('/%50%51%52%53/%54%55%56%57/%58%59%5a%5b', '/PQRS/TUVW/XYZ%5B',                       array('PQRS', 'TUVW', 'XYZ[')),
            array('/%5c%5d%5e%5f/%60%61%62%63/%64%65%66%67', '/%5C%5D%5E_/%60abc/defg',                 array('\]^_', '`abc', 'defg')),
            array('/%68%69%6a%6b/%6c%6d%6e%6f/%70%71%72%73', '/hijk/lmno/pqrs',                         array('hijk', 'lmno', 'pqrs')),
            array('/%74%75%76%77/%78%79%7a%7b/%7c%7d%7e%7f', '/tuvw/xyz%7B/%7C%7D~%7F',                 array('tuvw', 'xyz{', '|}~' . chr(127))),
        );
    }

    public function urlErrors()
    {
        return array(
            array('http://example /'),
            array('http://example!/'),
            array('http://example"/'),
            array('http://example#/'),
            array('http://example$/'),
            array('http://example%/'),
            array('http://example&/'),
            array('http://example\'/'),
            array('http://example(/'),
            array('http://example)/'),
            array('http://example*/'),
            array('http://example+/'),
            array('http://example../'), // note that two periods right next to each other are not allowed!
            array('http://example,/'),
            array('http://example;/'),
            array('http://example</'),
            array('http://example>/'),
            array('http://example=/'),
            array('http://example?/'),
            array('http://example@/'),
            array('http://example[/'),
            array('http://example]/'),
            array('http://example^/'),
            array('http://example_/'),
            array('http://example`/'),
            array('http://example{/'),
            array('http://example|/'),
            array('http://example}/'),
            array('http://example~/'),
            array('dump://something/'),
            array('://blah/'),
            array('htt://example/'),
            array('http:/awef/'),
            array('/url/path here/'),
            array('/url/path"here/'),
            array('/url/path%here/'),
            array('/url/path<here/'),
            array('/url/path>here/'),
            array('/url/path[here/'),
            array('/url/path]here/'),
            array('/url/path\here/'),
            array('/url/path^here/'),
            array('/url/path`here/'),
            array('/url/path{here/'),
            array('/url/path}here/'),
            array('/url/path|here/'),
            array('relative/url/not/accepted'),
            array('#only-a-fragment'),
            array('?wut=wat'),
            array(':4242/asdf/fdsa'),
        );
    }
}
