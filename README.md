[![CircleCI](https://circleci.com/gh/QuickenLoans/mcp-common.svg?style=shield)](https://circleci.com/gh/QuickenLoans/mcp-common)
[![Latest Stable Version](https://img.shields.io/packagist/v/ql/mcp-common.svg?label=stable)](https://packagist.org/packages/ql/mcp-common)
[![GitHub License](https://img.shields.io/github/license/quickenloans-mcp/mcp-common.svg)](https://packagist.org/packages/ql/mcp-common)
![GitHub Language](https://img.shields.io/github/languages/top/quickenloans-mcp/mcp-common.svg)

# MCP Common

This package provides common code, utilities, and data types used by most other MCP or Quicken Loans PHP packages.

## Installation

```
composer require ql/mcp-common ~2.0
```

## Table of Contents

- [GUID](#guid)
- [OpaqueProperty](#opaqueproperty)
- [Clock](#clock)
- **Time**
    - [TimePoint](#timepoint)
    - [TimeInterval](#timeinterval)
    - [TimePeriod](#timeperiod)
- **Utility**
    - [ByteString](#bytestring)

### GUID

This class represents a Microsoft .NET GUID. Note that a Microsoft .NET GUID is the same as an RFC 4122 UUID,
standard variant, 4th algorithm (see chapter 4.4 of [RFC 4122](https://www.ietf.org/rfc/rfc4122.txt) for details).

```php
use QL\MCP\Common\GUID;

$guid = GUID::create();
echo $guid;

// {4577267B-AE54-4C03-8C86-E628D5D3695A}
```

All of the following create calls create calls result in the *same GUID value*.

```php
use QL\MCP\Common\GUID;

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

//----------------

$guid = GUID::createFromHex('{0C875FFC-61AB-4A75-A4AF-5F89ADCE0D63}');

$guid->asHex();                  // '0C875FFC61AB4A75A4AF5F89ADCE0D63'
$guid->asBin();                  // pack('H*', '0C875FFC61AB4A75A4AF5F89ADCE0D63')
$guid->asBase64();               // 'DIdf/GGrSnWkr1+Jrc4NYw'
$guid->asHumanReadable();        // '{0C875FFC-61AB-4A75-A4AF-5F89ADCE0D63}'
$guid->format(GUID::HYPHENATED); // '{0c875ffc-61ab-4a75-a4af-5f89adce0d63}'

echo $guid;                      // '{0C875FFC-61AB-4A75-A4AF-5F89ADCE0D63}'
```

### Clock

Any time an application wants to **get the current time**, it is recommended that this class is used to do that instead
of using the native php date/time functions or classes such as `time` or `date`. The main reason for this is to
facilitate unit testing. Since interacting with the system clock on a computer can be viewed as *calling a service*,
this is something that should be wrapped and mocked when dealing with unit tests that rely on clock information.

If your app is diligent about injecting this object into others to get the current time rather than using native PHP,
testing any time-sensitive application code becomes a trivial matter.

Note:
These Time classes were created before the creation of [DateTimeImmutable](http://php.net/manual/en/class.datetimeimmutable.php).
**DateTimeImmutable** may replace some functionality of these classes in the future.

Usage:

```php
use QL\MCP\Common\Clock;

$clock = new Clock;
$currentTime = $clock->read();

// TimePoint
```

If you want to see how a system operates at a specific **point in time**, the Clock may be set to something other
than the system time or time zone. This is extremely useful for unit testing.

```php
use QL\MCP\Common\Clock;

// Set a clock up to December 15, 1983 at 9:02pm located in Detroit, MI
$clock = new Clock('1983-12-15 21:02:00', 'America/Detroit');
```

One thing to note is that this clock will not change as the program continues to run. If you're looking for more
accurate time measurements, this class would have to be change to work slightly differently than it is now. In other
words, if your program is dealing with time resolution at or under a few seconds, this class is not sufficient for that.

The clock can also be used to parse datetimes from `DateTime` or strings.

```php
use DateTime;
use QL\MCP\Common\Clock;

$clock = new Clock;

$time = $clock->fromDateTime(new DateTime);
var_dump($time);
// class QL\MCP\Common\Time\TimePoint#1 {}

$time = $clock->fromString('2015-12-10T10:30:00+04:00');
var_dump($time);
// class QL\MCP\Common\Time\TimePoint#2 {}

// Check the current time against an expiry, and optionally a created time and clock skew.

$clock = new Clock('2015-09-15 12:00:00', 'UTC');
$expiresAt = new TimePoint(2015, 9, 15, 10, 0, 0, 'America/Detroit');
$createdAt = new TimePoint(2015, 9, 15, 7, 0, 0, 'America/Detroit');
$skew = '30 seconds';

$isValid = $clock->inRange($expiresAt, $createdAt, $skew);
var_dump($isValid);

// bool(true)
```

### TimePoint

This is a wrapper for the native php [DateTime](http://php.net/DateTime) class. The changes made to the public API for
this class are means to a very specific set of goals. These goals are as follows:

1. Force the creation of a TimePoint to specify a time zone.
2. Force the formatting of a string version of the TimePoint to require a viewing time zone.
3. Force the parsing of a time string to happen outside of this class. Parsing and handling errors of parsed data are
   separate concerns and should be separate classes.
4. This class is immutable in that any modification type functions create a new copy of the modified state.

```php
use QL\MCP\Common\Time\TimeInterval;
use QL\MCP\Common\Time\TimePoint;

$time = new TimePoint(1999, 3, 31, 18, 15, 0, 'America/Detroit');

// returns a -1, 0 or 1 if $time is less than, equal to or greater than the argument respectively.
$time->compare(new TimePoint(1983, 12, 15, 21, 2, 0, 'America/Detroit'));
// -1

// the format string is exactly the same as DateTime->format(). Note the required timezone argument.
$time->format('Y-m-d H:i:s', 'UTC');
// '1999-03-31 23:15:00'

// Note that $time did not change, a copy was created. TimePoint->modify() takes the same argument format as modify()
$time2 = $time->modify('+1 day');
$time2->format('Y-m-d H:i:s', 'America/Detroit');
// '1999-04-01 18:15:00'

$time3 = $time->add(new TimeInterval('P2D'))
$time3->format('Y-m-d H:i:s', 'America/Detroit');
// '1999-04-02 18:15:00'

$time4 = $time->sub(new TimeInterval('P2D'))
$time4->format('Y-m-d H:i:s', 'America/Detroit');
// '1999-03-29 18:15:00'

$time5 = $time->diff(new TimePoint(1983, 12, 15, 21, 2, 0, 'America/Detroit'))
$time5->format('%y years, %m months, %d days, %h hours, %i minutes');
// '15 years, 3 months, 15 days, 21 hours, 13 minutes'
```

### TimeInterval

This is a wrapper for the native PHP [DateInterval](http://php.net/DateInterval) class. It removes the public
properties normally available on DateInterval (in favor of the format() function). The main purpose of this class is to
represent a fixed range of time (ie 1 month, 3 days and 7 hours). It is usually used in conjunction with **TimePoint**
and **TimePeriod** for calculations.

The format of time intervals conform to [ISO 8601 Time Intervals](https://en.wikipedia.org/wiki/ISO_8601#Time_intervals).
Also see [DateInterval::__construct](http://php.net/manual/en/dateinterval.construct.php) for more details on
the **interval spec**.

```php
use QL\MCP\Common\Time\TimeInterval;

// The interval spec is the exact same as PHP's DateInterval
$int = new TimeInterval('P1M3DT7H');

echo $interval->format("%d days %h hours\n");
// "3 days 7 hours"
```

### TimePeriod

Wraps the native PHP [DatePeriod](http://php.net/DatePeriod) class. This class represents a way to iterate over a set
of date/times that occur at fixed intervals.

The native PHP class `DatePeriod::__construct()` is overloaded and is actually not possible to write in userland PHP.
As a result, this wrapper exposes one of the creation options as a public static factory method.

```php
use QL\MCP\Common\Time\TimeInterval;
use QL\MCP\Common\Time\TimePeriod;
use QL\MCP\Common\Time\TimePoint;

$start = new TimePoint(2012, 1, 1, 0, 0, 0, "America/Detroit");
$interval = new TimeInterval('P1W');

$end = new TimePoint(2012, 2, 1, 0, 0, 0, "America/Detroit");

$period0 = new TimePeriod($start, $interval, $end);
foreach ($period0 as $timePoint) {
    echo $timePoint->format("Y-m-d\n", "America/Detroit");
}

echo "\n";

$recurrences = 5;

$period1 = TimePeriod::createWithRecurrences($start, $interval, $recurrences);
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

Note that `$period0` and `$period1` are copies of the same time period, just constructed differently.

### OpaqueProperty

Opaque Property is used to obscure secrets while in memory. This is useful to protect sensitive values from debug
output such as stacktraces or mistaken `echo` or `var_dump` commands.

```php
use QL\MCP\Common\OpaqueProperty;

$secret = new OpaqueProperty('my_secret_token');

echo $secret;
// [opaque property]

echo $secret->getValue();
// "my_secret_token"
```

### ByteString

ByteString can be used to get byte offsets in binary strings, or total length in bytes. `strlen` and `substr` provide
this functionality, but can be overridden in systems that use `mbstring` and `mbstring.func_overload`.

This utility protects against that scenario and **always** performs byte-based lengths and offsets, rather than
character-based.

Please note: `substr` can be expensive when doing many offsets or string cuts and using array access is usually
recommended if more performant code is required.

```php
use QL\MCP\Common\Time\ByteString;

$string = 'abcd𐌀𐌁𐌂𐌃';
echo ByteString::strlen($string);
// int(20)

echo ByteString::substr($string, 6, 3);
// "𐌁"
// "f0908c81" in hex
```
