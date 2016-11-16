<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Common\Testing;

use QL\MCP\Common\GUID;

/**
 * Test for extending GUID and changing its default formatting for `__toString` and `jsonSerialize`.
 */
class GUIDExtendedMock extends GUID
{
    const READABLE = self::STANDARD;
}
