# Change Log
All notable changes to this project will be documented in this file. See
[keepachangelog.com](http://keepachangelog.com) for reference.

## [1.1.0] - 2016-11-16

## Added
- **GUID** now supports formatting flags when using `$guid->format($flags = 0);`.
    - The default is lowercase, without hypens or braces.
    - Added `GUID::BRACES`
    - Added `GUID::HYPENATED`
    - Added `GUID::UPPERCASE`
    - Added `GUID::STANDARD`
        - The default if no arguments are provided.
        - Example: `9a39ed24175244599ac26b0e8f0dcec7`
    - Added `GUID::READABLE`
        - Same as `$guid->asHumanReadable()`
        - Example: `{9A39ED24-1752-4459-9AC2-6B0E8F0DCEC7}`

## [1.0.0] - 2015-12-10

## Added
- Add **OpaqueProperty**.
    - Please note this requires a secure CSPRNG such as **PHP7** or `paragonie/random_compat`.
    - Sensitive secrets and passwords should be wrapped in OpaqueProperty while in memory, as this obscures values
      from debug output and stacktraces.
- Add **ByteString** utility to consistently get length and offsets of byte strings.
    - ByteString has equivalents for `strlen` and `substr` for treating strings as bytes, and ignoring multibyte
      character encoding.
    - Should be used by crypto-related functions for safety.
- Add **MemoryLogger** from `ql/mcp-panthor` **TestLogger** to help introspect log messages in unit tests.

## Removed
- Remove **HttpUrl**
    - Please use [ql/uri-template](https://github.com/QuickenLoans/uri-template) or
      [PSR7](https://github.com/php-fig/http-message) UriInterface instead.

## Changed
- Change name from **MCP Core** to **MCP Common**.
- Change namespace from `MCP\Core\...` to `QL\MCP\Common\...`.

## [MCP Core 1.0.3] - 2015-08-11
