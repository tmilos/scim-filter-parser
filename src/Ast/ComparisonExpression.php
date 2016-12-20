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

class ComparisonExpression extends Factor
{
    /** @var AttributePath */
    public $attributePath;

    /** @var string */
    public $operator;

    /** @var mixed */
    public $compareValue;

    /**
     * @param AttributePath $attributePath
     * @param string        $operator
     * @param mixed         $compareValue
     */
    public function __construct(AttributePath $attributePath, $operator, $compareValue = null)
    {
        $this->attributePath = $attributePath;
        $this->operator = $operator;
        $this->compareValue = $compareValue;
    }

    public function __toString()
    {
        if ($this->operator === 'pr') {
            return sprintf('%s %s', $this->attributePath, $this->operator);
        } else {
            return sprintf('%s %s %s', $this->attributePath, $this->operator, $this->compareValue instanceof \DateTime ? $this->compareValue->format('Y-m-d\TH:i:s\Z') : $this->compareValue);
        }
    }

    public function dump()
    {
        return [
            'ComparisonExpression' => (string) $this,
        ];
    }
}
