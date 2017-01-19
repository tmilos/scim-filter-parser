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

    /** @var Mode */
    private $mode;

    /** @var bool */
    private $inValuePath;

    /**
     * @param Mode    $mode
     * @param Version $version
     */
    public function __construct(Mode $mode = null, Version $version = null)
    {
        $this->lexer = new Lexer(new LexerArrayConfig(LexerConfigFactory::getConfig()));
        $this->version = $version ?: Version::V2();
        $this->mode = $mode ?: Mode::FILTER();
        if ($this->mode->equals(Mode::PATH) && !$this->version->equals(Version::V2)) {
            throw new \InvalidArgumentException('Path mode is available only in SCIM version 2');
        }
    }

    /**
     * @return Mode
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param Mode $mode
     */
    public function setMode(Mode $mode)
    {
        $this->mode = $mode;
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

        $node = null;
        if ($this->mode->equals(Mode::FILTER)) {
            $node = $this->disjunction();
        } else {
            $node = $this->path();
        }

        $this->match(null);

        return $node;
    }

    /**
     * @return Ast\Path
     */
    private function path()
    {
        if ($this->isValuePathIncoming()) {
            $valuePath = $this->valuePath();
            $attributePath = null;
            if ($this->lexer->isNextToken(Tokens::T_DOT)) {
                $this->match(Tokens::T_DOT);
                $attributePath = $this->attributePath();
            }

            return Ast\Path::fromValuePath($valuePath, $attributePath);
        }

        return Ast\Path::fromAttributePath($this->attributePath());
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
            if ($this->isName('or', $nextToken)) {
                $this->match(Tokens::T_SP);
                $this->match(Tokens::T_NAME);
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
            if ($this->isName('and', $nextToken)) {
                $this->match(Tokens::T_SP);
                $this->match(Tokens::T_NAME);
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
        if ($this->isName('not', $this->lexer->getLookahead())) {
            // not ( filter )
            $this->match(Tokens::T_NAME);
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
            if ($this->isValuePathIncoming()) {
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
     * @return Ast\AttributePath
     */
    private function attributePath()
    {
        $string = '';
        $valid = [Tokens::T_NUMBER, Tokens::T_NAME, Tokens::T_COLON, Tokens::T_SLASH, Tokens::T_DOT];
        $stopping = [Tokens::T_SP, Tokens::T_BRACKET_OPEN];

        while (true) {
            $token = $this->lexer->getLookahead();
            if (!$token) {
                break;
            }
            $isValid = in_array($token->getName(), $valid);
            $isStopping = in_array($token->getName(), $stopping);
            if ($isStopping) {
                break;
            }
            if (!$isValid) {
                $this->syntaxError('attribute path');
            }
            $string .= $token->getValue();
            $this->lexer->moveNext();
        }

        if (!$string) {
            $this->syntaxError('attribute path');
        }

        $colonPos = strrpos($string, ':');
        if ($colonPos !== false) {
            $schema = substr($string, 0, $colonPos);
            $path = substr($string, $colonPos + 1);
        } else {
            $schema = null;
            $path = $string;
        }

        $parts = explode('.', $path);
        $attributePath = new Ast\AttributePath();
        $attributePath->schema = $schema;
        foreach ($parts as $part) {
            $attributePath->add($part);
        }

        return $attributePath;
    }

    /**
     * @return string
     */
    private function comparisonOperator()
    {
        if (!$this->isName(['pr', 'eq', 'ne', 'co', 'sw', 'ew', 'gt', 'lt', 'ge', 'le'], $this->lexer->getLookahead())) {
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
        if (!$this->lexer->isNextTokenAny([Tokens::T_NAME, Tokens::T_NUMBER, Tokens::T_STRING])) {
            $this->syntaxError('comparison value');
        }
        if ($this->lexer->getLookahead()->is(Tokens::T_NAME) && !$this->isName(['true', 'false', 'null'], $this->lexer->getLookahead())) {
            $this->syntaxError('comparision value');
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

    /**
     * @return bool
     */
    private function isValuePathIncoming()
    {
        $tokenAfterAttributePath = $this->lexer->peekWhileTokens([Tokens::T_NAME, Tokens::T_DOT]);
        $this->lexer->resetPeek();

        return $tokenAfterAttributePath ? $tokenAfterAttributePath->is(Tokens::T_BRACKET_OPEN) : false;
    }

    /**
     * @param string|string[] $value
     * @param Token|null      $token
     *
     * @return bool
     */
    private function isName($value, $token)
    {
        if (!$token) {
            return false;
        }
        if (!$token->is(Tokens::T_NAME)) {
            return false;
        }

        if (is_array($value)) {
            foreach ($value as $v) {
                if (strcasecmp($token->getValue(), $v) === 0) {
                    return true;
                }
            }

            return false;
        }

        return strcasecmp($token->getValue(), $value) === 0;
    }

    private function match($tokenName)
    {
        if (null === $tokenName) {
            if ($this->lexer->getLookahead()) {
                $this->syntaxError('end of input');
            }
        } else {
            if (!$this->lexer->getLookahead() || !$this->lexer->getLookahead()->is($tokenName)) {
                $this->syntaxError($tokenName);
            }

            $this->lexer->moveNext();
        }
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
