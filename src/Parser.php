<?php

/*
 * This file is part of the tmilos/scim-filter-parser package.
 *
 * (c) Milos Tomic <tmilos@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Tmilos\ScimFilterParser;

use Tmilos\Lexer\Config\LexerArrayConfig;
use Tmilos\Lexer\Lexer;
use Tmilos\Lexer\Token;
use Tmilos\ScimFilterParser\Ast\Tokens;

class Parser
{
    /** @var Lexer */
    private $lexer;

    /** @var Version */
    private $version;

    /** @var bool */
    private $inValuePath;

    public function __construct()
    {
        $this->lexer = new Lexer(new LexerArrayConfig(LexerConfigFactory::getConfig()));
        $this->version = Version::V2();
    }

    /**
     * @return Version
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param Version $version
     */
    public function setVersion(Version $version)
    {
        $this->version = $version;
    }

    /**
     * @param string $input
     *
     * @return Ast\Node
     */
    public function parse($input)
    {
        $this->inValuePath = false;
        $this->lexer->setInput($input);
        $this->lexer->moveNext();

        return $this->disjunction();
    }

    /**
     * @return Ast\Term|Ast\Disjunction
     */
    private function disjunction()
    {
        /** @var Ast\Term[] $terms */
        $terms = [];
        $terms[] = $this->conjunction();

        if ($this->lexer->isNextToken(Tokens::T_SP)) {
            $nextToken = $this->lexer->glimpse();
            if ($nextToken && $nextToken->is(Tokens::T_OR)) {
                $this->match(Tokens::T_SP);
                $this->match(Tokens::T_OR);
                $this->match(Tokens::T_SP);
                $terms[] = $this->conjunction();
            }
        }

        if (count($terms) == 1) {
            return $terms[0];
        }

        return new Ast\Disjunction($terms);
    }

    /**
     * @return Ast\Conjunction|Ast\Factor
     */
    private function conjunction()
    {
        $factors = [];
        $factors[] = $this->factor();

        if ($this->lexer->isNextToken(Tokens::T_SP)) {
            $nextToken = $this->lexer->glimpse();
            if ($nextToken && $nextToken->is(Tokens::T_AND)) {
                $this->match(Tokens::T_SP);
                $this->match(Tokens::T_AND);
                $this->match(Tokens::T_SP);
                $factors[] = $this->factor();
            }
        }

        if (count($factors) == 1) {
            return $factors[0];
        }

        return new Ast\Conjunction($factors);
    }

    /**
     * @return Ast\Filter
     */
    private function factor()
    {
        if ($this->lexer->isNextToken(Tokens::T_NOT)) {
            // not ( filter )
            $this->match(Tokens::T_NOT);
            $this->match(Tokens::T_SP);
            $this->match(Tokens::T_PAREN_OPEN);
            $filter = $this->disjunction();
            $this->match(Tokens::T_PAREN_CLOSE);

            return new Ast\Negation($filter);
        } elseif ($this->lexer->isNextToken(Tokens::T_PAREN_OPEN)) {
            // ( filter )
            $this->match(Tokens::T_PAREN_OPEN);
            $filter = $this->disjunction();
            $this->match(Tokens::T_PAREN_CLOSE);

            return $filter;
        }

        if ($this->version->equals(Version::V2()) && !$this->inValuePath) {
            $tokenAfterAttributePath = $this->lexer->peekWhileTokens([Tokens::T_ATTR_NAME, Tokens::T_DOT]);
            $this->lexer->resetPeek();
            if ($tokenAfterAttributePath->is(Tokens::T_BRACKET_OPEN)) {
                return $this->valuePath();
            }
        }

        return $this->comparisionExpression();
    }

    /**
     * @return Ast\ValuePath
     */
    private function valuePath()
    {
        $attributePath = $this->attributePath();
        $this->match(Tokens::T_BRACKET_OPEN);
        $this->inValuePath = true;
        $filter = $this->disjunction();
        $this->match(Tokens::T_BRACKET_CLOSE);
        $this->inValuePath = false;

        return new Ast\ValuePath($attributePath, $filter);
    }

    /**
     * @return Ast\ComparisonExpression
     */
    private function comparisionExpression()
    {
        $attributePath = $this->attributePath();
        $this->match(Tokens::T_SP);

        $operator = $this->comparisonOperator();

        $compareValue = null;
        if ($operator != 'pr') {
            $this->match(Tokens::T_SP);
            $compareValue = $this->compareValue();
        }

        return new Ast\ComparisonExpression($attributePath, $operator, $compareValue);
    }

    /**
     * @param Ast\AttributePath $attributePath
     *
     * @return Ast\AttributePath
     */
    private function attributePath(Ast\AttributePath $attributePath = null)
    {
        $this->match(Tokens::T_ATTR_NAME);

        if (!$attributePath) {
            $attributePath = new Ast\AttributePath();
        }
        $attributePath->add($this->lexer->getToken()->getValue());

        if ($this->lexer->isNextToken(Tokens::T_DOT)) {
            $this->match(Tokens::T_DOT);
            $this->attributePath($attributePath);
        }

        return $attributePath;
    }

    /**
     * @return string
     */
    private function comparisonOperator()
    {
        if (!$this->lexer->isNextTokenAny(Tokens::compareOperators())) {
            $this->syntaxError('comparision operator');
        }
        $this->match($this->lexer->getLookahead()->getName());

        return $this->lexer->getToken()->getValue();
    }

    /**
     * @return mixed
     */
    private function compareValue()
    {
        if (!$this->lexer->isNextTokenAny(Tokens::compareValues())) {
            $this->syntaxError('comparison value');
        }
        $this->match($this->lexer->getLookahead()->getName());

        $value = json_decode($this->lexer->getToken()->getValue());
        if (preg_match(
                '/^(\\d\\d\\d\\d)-(\\d\\d)-(\\d\\d)T(\\d\\d):(\\d\\d):(\\d\\d)(?:\\.\\d+)?Z$/D',
                $value,
                $matches
            )) {
            $year = intval($matches[1]);
            $month = intval($matches[2]);
            $day = intval($matches[3]);
            $hour = intval($matches[4]);
            $minute = intval($matches[5]);
            $second = intval($matches[6]);
            // Use gmmktime because the timestamp will always be given in UTC.
            $ts = gmmktime($hour, $minute, $second, $month, $day, $year);

            $value = new \DateTime('@'.$ts, new \DateTimeZone('UTC'));
        }

        return $value;
    }

    private function match($tokenName)
    {
        if (!$this->lexer->getLookahead() || !$this->lexer->getLookahead()->is($tokenName)) {
            $this->syntaxError($tokenName);
        }

        $this->lexer->moveNext();
    }

    private function syntaxError($expected = '', Token $token = null)
    {
        if (null === $token) {
            $token = $this->lexer->getLookahead();
        }
        if ($token) {
            $offset = $token->getOffset();
        } elseif ($this->lexer->getToken()) {
            $offset = $this->lexer->getToken()->getOffset();
        } else {
            $offset = strlen($this->lexer->getInput());
        }

        $message = "line 0, col {$offset}: Error: ";
        $message .= ($expected !== '') ? "Expected {$expected}, got " : 'Unexpected ';
        $message .= ($token === null) ? 'end of string.' : "'{$token->getValue()}'";

        throw Error\FilterException::syntaxError($message, Error\FilterException::filterError($this->lexer->getInput()));
    }
}
