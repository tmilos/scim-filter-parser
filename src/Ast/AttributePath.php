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

class AttributePath extends Node
{
    /** @var string */
    public $schema;

    /** @var string[] */
    public $attributeNames = [];

    /**
     * @param string $string
     *
     * @return AttributePath
     */
    public static function fromString($string)
    {
        $string = trim($string);
        if (!$string) {
            throw new \InvalidArgumentException('Empty attribute path');
        }

        $colonPos = strrpos($string, ':');
        if ($colonPos !== false) {
            $schema = substr($string, 0, $colonPos);
            $path = substr($string, $colonPos + 1);
        } else {
            $schema = null;
            $path = $string;
        }

        $parts = explode('.', $path);
        $attributePath = new static();
        $attributePath->schema = $schema;
        foreach ($parts as $part) {
            $attributePath->add($part);
        }

        return $attributePath;
    }

    public function add($attributeName)
    {
        $firstLetter = strtolower(substr($attributeName, 0, 1));
        if ($firstLetter < 'a' || $firstLetter > 'z') {
            throw new \InvalidArgumentException(sprintf('Invalid attribute name "%s"', $attributeName));
        }

        $this->attributeNames[] = $attributeName;
    }

    public function __toString()
    {
        if ($this->schema) {
            return $this->schema.' : '.implode('.', $this->attributeNames);
        }

        return implode('.', $this->attributeNames);
    }

    public function dump()
    {
        return [
            'AttributePath' => $this->__toString(),
        ];
    }
}
