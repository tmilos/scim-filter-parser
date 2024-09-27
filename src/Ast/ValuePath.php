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

class ValuePath extends Factor
{
    /** @var AttributePath */
    private $attributePath;

    /** @var Filter */
    private $filter;

    /**
     * @param AttributePath $attributePath
     * @param Filter        $filter
     */
    public function __construct(AttributePath $attributePath, Filter $filter)
    {
        $this->attributePath = $attributePath;
        $this->filter = $filter;
    }

    public function __toString()
    {
        return sprintf('%s[%s]', $this->attributePath, $this->filter);
    }

    public function dump()
    {
        return [
            'ValuePath' => [
                $this->attributePath->dump(),
                $this->filter->dump(),
            ],
        ];
    }
}
