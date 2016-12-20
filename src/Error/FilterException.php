<?php

/*
 * This file is part of the tmilos/scim-filter-parser package.
 *
 * (c) Milos Tomic <tmilos@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Tmilos\ScimFilterParser\Error;

class FilterException extends \RuntimeException
{
    /**
     * @param string     $message
     * @param \Exception $previous
     *
     * @return FilterException
     */
    public static function syntaxError($message, $previous = null)
    {
        return new static('[Syntax Error] '.$message, 0, $previous);
    }

    /**
     * @param string $message
     *
     * @return FilterException
     */
    public static function filterError($message)
    {
        return new static($message);
    }
}
