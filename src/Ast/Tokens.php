<?php

/*
 * This file is part of the tmilos/scim-filter-parser package.
 *
 * (c) Milos Tomic <tmilos@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Tmilos\ScimFilterParser\Ast;

abstract class Tokens
{
    const T_SP = 'SP';
    //const T_TRUE = 'TRUE';
    //const T_FALSE = 'FALSE';
    //const T_NULL = 'NULL';
    const T_NUMBER = 'NUMBER';
    const T_STRING = 'STRING';
    const T_NAME = 'NAME';
    const T_DOT = 'DOT';
    const T_COLON = 'COLON';
    const T_SLASH = 'SLASH';
    const T_PAREN_OPEN = 'PAREN_OPEN';
    const T_PAREN_CLOSE = 'PAREN_CLOSE';
    const T_BRACKET_OPEN = ' BRACKET_OPEN';
    const T_BRACKET_CLOSE = ' BRACKET_CLOSE';
//    const T_NOT = 'NOT';
//    const T_AND = 'AND';
//    const T_OR = 'OR';
//
//    const T_PR = 'PR';
//
//    const T_EQ = 'EQ';
//    const T_NE = 'NE';
//    const T_CO = 'CO';
//    const T_SW = 'SW';
//    const T_EW = 'EW';
//    const T_GT = 'GT';
//    const T_LT = 'LT';
//    const T_GE = 'GE';
//    const T_LE = 'LE';
//
//    private static $_compareValue = [self::T_TRUE, self::T_FALSE, self::T_NULL, self::T_NUMBER, self::T_STRING];
//
//    private static $_compareUnaryOperators = [self::T_PR];
//    private static $_compareBinaryOperators = [self::T_EQ, self::T_NE, self::T_CO, self::T_SW, self::T_EW, self::T_GT, self::T_LT, self::T_GE, self::T_LE];
//
//    /**
//     * @return array
//     */
//    public static function _compareValues()
//    {
//        return ['true', 'false', 'null'];
//    }
//
//    /**
//     * @return array
//     */
//    public static function _compareOperators()
//    {
//        return array_merge(static::$_compareUnaryOperators, static::$_compareBinaryOperators);
//    }
}
