<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\IO;

/**
 * @api
 */
interface IStreamable
{
    /**
     * Opens the stream
     *
     * @return boolean
     */
    public function open();

    /**
     * Reads a number of bytes from the stream
     *
     * @param int|null $bytes
     * @return string
     */
    public function read($bytes = null);

    /**
     * Writes the given data to the stream
     *
     * @param string $data
     * @return int
     */
    public function write($data);

    /**
     * @return boolean
     */
    public function close();
}
