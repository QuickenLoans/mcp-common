# MCP Core #

This package mostly provides shared code that is used by most other MCP
packages.

## Classes ##

- GUID
- HttpUrl
- IPv4Address
- USAddress
- Clock
- TimePoint
- TimeInterval
- TimePeriod

### GUID ###

This class represents a Microsoft .NET GUID. Note that a Microsoft .NET GUID is
the same as an RFC 4122 UUID, standard variant, 4th algorithm (see chapter 4.4
of the RFC for details).

#### Create A New Random GUID ####

```php
<?php
use MCP\DataType\GUID;

$guid = GUID::create();
echo $guid; // outputs something like "{4577267B-AE54-4C03-8C86-E628D5D3695A}"
```

#### GUID Creation From Existing Data ####

All of the following create calls create calls result in the *same thing*.

```php
use MCP\DataType\GUID;

$guid0 = GUID::createFromBin(base64_decode('T/l0arruT6OXO7O3QOOBKg=='));
$guid1 = GUID::createFromHex('4FF9746ABAEE4FA3973BB3B740E3812A');
$guid2 = GUID::createFromHex('4ff9746abaee4fa3973bb3b740e3812a');
$guid3 = GUID::createFromHex('4FF9746A-BAEE-4FA3-973B-B3B740E3812A');
$guid4 = GUID::createFromHex('4ff9746a-baee-4fa3-973b-b3b740e3812a');
$guid5 = GUID::createFromHex('{4FF9746ABAEE4FA3973BB3B740E3812A}');
$guid6 = GUID::createFromHex('{4ff9746abaee4fa3973bb3b740e3812a}');
$guid7 = GUID::createFromHex('{4FF9746A-BAEE-4FA3-973B-B3B740E3812A}');
$guid8 = GUID::createFromHex('{4ff9746a-baee-4fa3-973b-b3b740e3812a}');
$guid9 = GUID::createFromBase64('T/l0arruT6OXO7O3QOOBKg==');

```

#### GUID Output Functions ####

```php
$guid = GUID::createFromHex('{0C875FFC-61AB-4A75-A4AF-5F89ADCE0D63}');

$guid->asHex();           // '0C875FFC61AB4A75A4AF5F89ADCE0D63'
$guid->asBin();           // pack('H*', '0C875FFC61AB4A75A4AF5F89ADCE0D63')
$guid->asBase64();        // 'DIdf/GGrSnWkr1+Jrc4NYw'
$guid->asHumanReadable(); // '{0C875FFC-61AB-4A75-A4AF-5F89ADCE0D63}'
echo $guid;               // '{0C875FFC-61AB-4A75-A4AF-5F89ADCE0D63}'
```

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

Represents an IPv4 address value.

#### IPv4Address Creation Functions ####

Assume for the sake of example that www.example.com resolves to the IP address
168.23.11.48.

```php
use MCP\DataType\IPv4Address;

$ipNoHost = IPv4Address::create('168.23.11.48');
$ipWithHost = IPv4Address::createFromHostString('www.example.com');

$ipNoHost->asInt();          // 2820410160
$ipNoHost->asString();       // '168.23.11.48'
$ipNoHost->originalHost();   // null
$ipWithHost->asInt();        // 2820410160
$ipWithHost->asString();     // '168.23.11.48'
$ipWithHost->originalHost(); // 'www.example.com'

// On 32 bit systems, asInt() works differently due to using ip2long() under
// the hood.
$ipNoHost->asInt();          // -1474557136
$ipWithHost->asInt();        // -1474557136
```

### USAddress ###

Represents a US Address. This has no validation on it and is simply meant to
make an address a 'type'. Validating an address should be done by an external
service.

```php
use MCP\DataType\USAddress;

$addr = new USAddress('310 W 6th St', 'Apt 206', 'Royal Oak', 'MI', '48067');

$addr->street1(); // '310 W 6th St'
$addr->street2(); // 'Apt 206'
$addr->city();    // 'Royal Oak'
$addr->state();   // 'MI'
$addr->zip();     // '48067'
```
