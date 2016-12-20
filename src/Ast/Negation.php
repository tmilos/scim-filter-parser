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

class Negation extends Factor
{
    /** @var Filter */
    public $filter;

    /**
     * @param Filter $filter
     */
    public function __construct(Filter $filter)
    {
        $this->filter = $filter;
    }

    /**
     * @return Filter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    public function __toString()
    {
        return sprintf('not (%s)', $this->filter);
    }

    public function dump()
    {
        return [
            'Negation' => $this->filter->dump(),
        ];
    }
}
