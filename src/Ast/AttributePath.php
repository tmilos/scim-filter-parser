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

    public function add($attributeName)
    {
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
