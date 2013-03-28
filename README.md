# MCP Core #

This package mostly provides shared code that is used by most other MCP
packages.

## Classes ##

- HttpUrl
- GUID
- USAddress
- IPv4Address
- Clock
- TimePoint
- TimeInterval
- TimePeriod

### GUID ###

This class represents a Microsoft .NET GUID. Note that a Microsoft .NET GUID is
the same as an RFC 4122 UUID, standard variant, 4th algorithm (see chapter 4.4
of the RFC for details).

#### GUID::create() ####

Creates a new random GUID.

```php
<?php
use MCP\DataType\GUID;
$guid = GUID::create();
echo $guid; // outputs something like "{4577267B-AE54-4C03-8C86-E628D5D3695A}"
```

#### GUID::createFromHex($hexString) ####

TODO Add more complete docs for GUID

### HttpUrl ###

This represents an HTTP URL.

```php
<?php
use MCP\DataType\HttpUrl;
$url = HttpUrl::create('https://example.com:3000/s/a%40a?q=term&l=utf-8');

echo $url->protocol(); // 'https'
echo $url->secure(); // true (returns false if $url->protocol() was 'http')
echo $url->host(); // 'example.com'
echo $url->port(); // 3000
echo $url->path(); // /search/a%40a
echo $url->segments(); // array('s', 'a@a')
echo $url->queryData(); // array('q' => 'term', 'l' => 'utf-8')
```

### IPv4Address ###

Represents an IPv4 address value

```php
use MCP\DataType\IPv4Address;
$ip = IPv4Address::create('68.23.11.48');
$ip->asString();
```
