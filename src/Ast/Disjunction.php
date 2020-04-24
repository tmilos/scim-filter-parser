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

class Disjunction extends Filter
{
    /** @var Filter[] */
    private $terms = [];

    /**
     * @param Filter[] $terms
     */
    public function __construct(array $terms = [])
    {
        foreach ($terms as $term) {
            $this->add($term);
        }
    }

    /**
     * @param Filter $term
     */
    public function add(Filter $term)
    {
        $this->terms[] = $term;
    }

    /**
     * @return Filter[]
     */
    public function getTerms()
    {
        return $this->terms;
    }

    public function __toString()
    {
        return implode(' or ', $this->terms);
    }

    public function dump()
    {
        $arr = [];
        foreach ($this->terms as $term) {
            $arr[] = $term->dump();
        }

        return [
            'Disjunction' => $arr,
        ];
    }
}
