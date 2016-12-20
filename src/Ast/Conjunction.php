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

class Conjunction extends Term
{
    /** @var Filter[] */
    private $factors = [];

    /**
     * @param Filter[] $factors
     */
    public function __construct(array $factors = [])
    {
        foreach ($factors as $factor) {
            $this->add($factor);
        }
    }

    /**
     * @param Filter $factor
     */
    public function add(Filter $factor)
    {
        $this->factors[] = $factor;
    }

    /**
     * @return Filter[]
     */
    public function getFactors()
    {
        return $this->factors;
    }

    public function dump()
    {
        $arr = [];
        foreach ($this->factors as $factor) {
            $arr[] = $factor->dump();
        }

        return [
            'Conjunction' => $arr,
        ];
    }

    public function __toString()
    {
        return implode(' and ', $this->factors);
    }
}
