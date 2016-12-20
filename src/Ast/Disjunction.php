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
    /** @var Term[] */
    private $terms = [];

    /**
     * @param Term[] $terms
     */
    public function __construct(array $terms = [])
    {
        foreach ($terms as $term) {
            $this->add($term);
        }
    }

    /**
     * @param Term $term
     */
    public function add(Term $term)
    {
        $this->terms[] = $term;
    }

    /**
     * @return Term[]
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
