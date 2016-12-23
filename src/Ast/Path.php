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

class Path extends Node
{
    /** @var AttributePath */
    private $attributePath;

    /** @var ValuePath */
    private $valuePath;

    /**
     * @param AttributePath $attributePath
     *
     * @return Path
     */
    public static function fromAttributePath(AttributePath $attributePath)
    {
        return new static($attributePath, null);
    }

    /**
     * @param ValuePath          $valuePath
     * @param AttributePath|null $attributePath
     *
     * @return Path
     */
    public static function fromValuePath(ValuePath $valuePath, AttributePath $attributePath = null)
    {
        return new static($attributePath, $valuePath);
    }

    /**
     * @param AttributePath $attributePath
     * @param ValuePath     $valuePath
     */
    private function __construct(AttributePath $attributePath = null, ValuePath $valuePath = null)
    {
        $this->attributePath = $attributePath;
        $this->valuePath = $valuePath;
    }

    public function dump()
    {
        if (!$this->valuePath) {
            return [
                'Path' => $this->attributePath->dump(),
            ];
        } elseif (!$this->attributePath) {
            return [
                'Path' => $this->valuePath->dump(),
            ];
        } else {
            return array_merge($this->valuePath->dump(), $this->attributePath->dump());
        }
    }
}
