# Change Log
All notable changes to this project will be documented in this file. See
[keepachangelog.com](http://keepachangelog.com) for reference.

## [1.0.0] - 2015-12-??

## Added
- Add **OpaqueProperty**.
    - Please note this requires a secure CSPRNG such as **PHP7** or `paragonie/random_compat`.
- Add **ByteString** utility to consistently get length and offsets of byte strings.
- Add **MemoryLogger** from `ql/mcp-panthor` **TestLogger** to help introspect log messages in unit tests.

## Changed
- Changed name from **MCP Core** to **MCP Common** and namespace changed to `QL\MCP\Common\...`.

## [MCP Core 1.0.3] - 2015-08-11
