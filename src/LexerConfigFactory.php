<?php

/*
 * This file is part of the tmilos/scim-filter-parser package.
 *
 * (c) Milos Tomic <tmilos@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Tmilos\ScimFilterParser;

use Tmilos\ScimFilterParser\Ast\Tokens;

class LexerConfigFactory
{
    public static function getConfig()
    {
        return [
            '\\s+' => Tokens::T_SP,
            //'true' => Tokens::T_TRUE,
            //'false' => Tokens::T_FALSE,
            //'null' => Tokens::T_NULL,
            '\\d+(\\.\\d+)?' => Tokens::T_NUMBER,
            '"(?:[^"\\\\]|\\\\.)*"' => Tokens::T_STRING,
            '\\.' => Tokens::T_DOT,
            ':' => Tokens::T_COLON,
            '/' => Tokens::T_SLASH,
            '\\(' => Tokens::T_PAREN_OPEN,
            '\\)' => Tokens::T_PAREN_CLOSE,
            //            'not' => Tokens::T_NOT,
            //            'and' => Tokens::T_AND,
            //            'or' => Tokens::T_OR,
            //            'pr' => Tokens::T_PR,
            //            'eq' => Tokens::T_EQ,
            //            'ne' => Tokens::T_NE,
            //            'co' => Tokens::T_CO,
            //            'sw' => Tokens::T_SW,
            //            'ew' => Tokens::T_EW,
            //            'gt' => Tokens::T_GT,
            //            'lt' => Tokens::T_LT,
            //            'ge' => Tokens::T_GE,
            //            'le' => Tokens::T_LE,
            '[a-z0-9-_]+' => Tokens::T_NAME,
            '\\[' => Tokens::T_BRACKET_OPEN,
            '\\]' => Tokens::T_BRACKET_CLOSE,
        ];
    }
}
