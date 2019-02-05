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

    /**
     * @return AttributePath
     */
    public function getAttributePath()
    {
        return $this->attributePath;
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
        return sprintf('%s[%s]', $this->getAttributePath(), $this->getFilter());
    }

    public function dump()
    {
        return [
            'ValuePath' => [
                $this->getAttributePath()->dump(),
                $this->getFilter()->dump(),
            ],
        ];
    }
}
