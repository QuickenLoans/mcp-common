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

```php
<?php
use MCP\DataType\GUID;

$guid = GUID::create();
echo $guid; // outputs something like "{4577267B-AE54-4C03-8C86-E628D5D3695A}"
```

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

### Clock ###

Any time an application wants to 'get the current time', it is recommended that
this class is used to do that instead of using the native php date/time
functions or classes. The main reason for this is to facilitate unit testing.
Since interacting with the system clock on a computer can be viewed as 'calling
a service', this is something that should be wrapped and mocked when dealing
with unit tests that rely on clock information.

If your app is diligent about injecting this object into others to get the
current time rather than using native PHP, testing any time-sensitive
application code becomes a trivial matter.

Any MCP package that interacts with the system clock is required to use this
wrapper for the above reason.

Normal use looks like so:

```php
use MCP\DataType\Time\Clock;

$clock = new Clock;
$currentTime = $clock->read();
```

If you want to see how a system operates at a specific "point in time", the
Clock may be "set" to something other than the system time or time zone:

```php
use MCP\DataType\Time\Clock;

// Set a clock up to December 15, 1983 at 9:02pm located in Detroit, MI
$clock = new Clock("1983-12-15 21:02:00", "America/Detroit");
```

One thing to note is that this clock will not change as the program continues
to run. If you're looking for more accurate time measurements, this class would
have to be change to work slightly differently than it is now. In other words,
if your program is dealing with time resolution at or under a few seconds, this
class is not sufficient for that.

### TimeInterval ###

This is a wrapper for the native PHP [DateInterval](http://php.net/DateInterval)
class. It removes the public properties normally available on DateInterval (in
favor of the format() function). The main purpose of this class is to represent
a fixed range of time (ie 1 month, 3 days and 7 hours). It is usually used in
conjunction with TimePoint and TimePeriod for calculations.

```php
use MCP\DataType\Time\TimeInterval;

// The interval spec is the exact same as PHP's DateInterval
$int = new TimeInterval('P1M3DT7H');
```

### TimePeriod ###

Wraps the native PHP [DatePeriod](http://php.net/DatePeriod) class. This class
represents a way to iterate over a set of date/times that occur at fixed
intervals.

The native PHP class has a strange overloaded __construct() on
[DatePeriod](http://php.net/DatePeriod) that is actually not possible to
actually write in the PHP language. As a result, this wrapper exposes one of
the creation options as a public static factory method.

```php
use MCP\DataType\Time\TimeInterval;
use MCP\DataType\Time\TimePeriod;
use MCP\DataType\Time\TimePoint;

$start = new TimePoint(2012, 1, 1, 0, 0, 0, "America/Detroit");
$interval = new TimeInterval('P1W');
$end = new TimePoint(2012, 2, 1, 0, 0, 0, "America/Detroit");
$recurrences = 5;

$period0 = new TimePeriod($start, $interval, $end);
$period1 = TimePeriod::createWithRecurrences($start, $interval, $recurrences);

foreach ($period0 as $timePoint) {
    echo $timePoint->format("Y-m-d\n", "America/Detroit");
}
echo "\n";
foreach ($period1 as $timePoint) {
    echo $timePoint->format("Y-m-d\n", "America/Detroit");
}

/*
The output of the above code is as follows:

2012-01-01
2012-01-08
2012-01-15
2012-01-22
2012-01-29

2012-01-01
2012-01-08
2012-01-15
2012-01-22
2012-01-29
*/
```

Note that $period0 and $period1 are copies of the same time period, just
constructed differently.

### TimePoint ###

This is a wrapper for the native php [DateTime](http://php.net/DateTime) class.
The changes made to the public API for this class are means to a very specific
goal: less sloppiness in time handling in our programs. Some of the alterations
are meant to:

1. Force the creation of a TimePoint to specify a time zone. A time without a
   timezone is a unitless number (remember 6th grade science class!).
2. Force the formatting of a string version of the TimePoint to require a
   viewing time zone. A time without a timezone is a unitless number (remember
   6th grade science class!!).
3. Force the parsing of a time string to happen outside of this class. Parsing
   and holding of parsed data are separate concerns and should be separate
   classes.
4. This class is immutable in that any modification type functions create a new
   copy of the modified state. An instance of TimePoint can never change by
   itself (in contrast to DateTime).

```php
use MCP\DataType\Time\TimeInterval;
use MCP\DataType\Time\TimePoint;

$time = new TimePoint(1999, 3, 31, 18, 15, 0, "America/Detroit");

// returns a -1, 0 or 1 if $time is less than, equal to or greater than the
// argument respectively.
$time->compare(new TimePoint(1983, 12, 15, 21, 2, 0, "America/Detroit")); // -1

// the format string is exactly the same as DateTime->format(). Note the
// *required* timezone argument.
$time->format("Y-m-d H:i:s", "UTC"); // '1999-03-31 23:15:00'

// Note that $time did not change, a copy was created. TimePoint->modify()
// takes the same argument format as DateTime->modify()
$time->modify("+1 day")
     ->format('Y-m-d H:i:s', 'America/Detroit'); // '1999-04-01 18:15:00'

$time->add(new TimeInterval('P2D'))
     ->format('Y-m-d H:i:s', 'America/Detroit'); // '1999-04-02 18:15:00'

$time->sub(new TimeInterval('P2D'))
     ->format('Y-m-d H:i:s', 'America/Detroit'); // '1999-03-29 18:15:00'

$format = '%y years, %m months, %d days, %h hours, %i minutes';
$time->diff(new TimePoint(1983, 12, 15, 21, 2, 0, 'America/Detroit'))
     ->format($format); // '15 years, 3 months, 15 days, 21 hours, 13 minutes'
```
