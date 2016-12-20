<?php

namespace Tests\Tmilos\ScimFilterParser;

use Tmilos\ScimFilterParser\Error\FilterException;
use Tmilos\ScimFilterParser\Parser;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    public function parser_provider()
    {
        return [
            [
                'userName eq "bjensen"',
                ['ComparisonExpression' => 'userName eq bjensen'],
            ],

            [
                'name.familyName co "O\'Malley"',
                ['ComparisonExpression' => 'name.familyName co O\'Malley'],
            ],

            [
                'userName sw "J"',
                ['ComparisonExpression' => 'userName sw J'],
            ],

            [
                'title pr',
                ['ComparisonExpression' => 'title pr'],
            ],

            [
                'meta.lastModified gt "2011-05-13T04:42:34Z"',
                ['ComparisonExpression' => 'meta.lastModified gt 2011-05-13T04:42:34Z'],
            ],

            [
                'title pr and userType eq "Employee"',
                [
                    'Conjunction' => [
                        ['ComparisonExpression' => 'title pr'],
                        ['ComparisonExpression' => 'userType eq Employee'],
                    ]
                ]
            ],

            [
                'title pr or userType eq "Intern"',
                [
                    'Disjunction' => [
                        ['ComparisonExpression' => 'title pr'],
                        ['ComparisonExpression' => 'userType eq Intern'],
                    ]
                ]
            ],

            [
                'schemas eq "urn:ietf:params:scim:schemas:extension:enterprise:2.0:User"',
                ['ComparisonExpression' => 'schemas eq urn:ietf:params:scim:schemas:extension:enterprise:2.0:User'],
            ],

            [
                'userType eq "Employee" and (emails co "example.com" or emails.value co "example.org")',
                [
                    'Conjunction' => [
                        ['ComparisonExpression' => 'userType eq Employee'],
                        [
                            'Disjunction' => [
                                ['ComparisonExpression' => 'emails co example.com'],
                                ['ComparisonExpression' => 'emails.value co example.org'],
                            ]
                        ]
                    ]
                ]
            ],

            [
                'userType ne "Employee" and not (emails co "example.com" or emails.value co "example.org")',
                [
                    'Conjunction' => [
                        ['ComparisonExpression' => 'userType ne Employee'],
                        [
                            'Negation' => [
                                'Disjunction' => [
                                    ['ComparisonExpression' => 'emails co example.com'],
                                    ['ComparisonExpression' => 'emails.value co example.org'],
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            [
                'userType eq "Employee" and (emails.type eq "work")',
                [
                    'Conjunction' => [
                        ['ComparisonExpression' => 'userType eq Employee'],
                        ['ComparisonExpression' => 'emails.type eq work']
                    ]
                ]
            ],

            [
                'username eq "john" and name sw "mike"',
                [
                    'Conjunction' => [
                        ['ComparisonExpression' => 'username eq john'],
                        ['ComparisonExpression' => 'name sw mike']
                    ]
                ]
            ],

            [
                'username eq "john" or name sw "mike"',
                [
                    'Disjunction' => [
                        ['ComparisonExpression' => 'username eq john'],
                        ['ComparisonExpression' => 'name sw mike']
                    ]
                ]
            ],

            [
                'username eq "john" or name sw "mike" and id ew "123"',
                [
                    'Disjunction' => [
                        ['ComparisonExpression' => 'username eq john'],
                        [
                            'Conjunction' => [
                                ['ComparisonExpression' => 'name sw mike'],
                                ['ComparisonExpression' => 'id ew 123'],
                            ]
                        ]
                    ]
                ]
            ],

            [
                'username eq "john" and (name sw "mike" or id ew "123")',
                [
                    'Conjunction' => [
                        ['ComparisonExpression' => 'username eq john'],
                        [
                            'Disjunction' => [
                                ['ComparisonExpression' => 'name sw mike'],
                                ['ComparisonExpression' => 'id ew 123'],
                            ]
                        ]
                    ]
                ]
            ],

            [
                'username eq "john" and not (name sw "mike" or id ew "123")',
                [
                    'Conjunction' => [
                        ['ComparisonExpression' => 'username eq john'],
                        [
                            'Negation' => [
                                'Disjunction' => [
                                    ['ComparisonExpression' => 'name sw mike'],
                                    ['ComparisonExpression' => 'id ew 123'],
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }

    /**
     * @dataProvider parser_provider
     */
    public function test_parser($filterString, array $expectedDump)
    {
        $parser = new Parser();
        $node = $parser->parse($filterString);
        $this->assertEquals($expectedDump, $node->dump(), sprintf("\n\n%s\n%s\n\n", $filterString, json_encode($node->dump(), JSON_PRETTY_PRINT)));
    }

    public function error_provider()
    {
        return [
            ['not a valid filter', "[Syntax Error] line 0, col 4: Error: Expected PAREN_OPEN, got 'a'"],
            ['username xx "mike"', "[Syntax Error] line 0, col 9: Error: Expected comparision operator, got 'xx'"],
            ['username eq', "[Syntax Error] line 0, col 9: Error: Expected SP, got end of string."],
            ['username eq ', "[Syntax Error] line 0, col 11: Error: Expected comparison value, got end of string."],
        ];
    }

    /**
     * @dataProvider error_provider
     */
    public function test_error($filterString, $expectedMessage, $expectedException = FilterException::class)
    {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedMessage);
        $parser = new Parser();
        $parser->parse($filterString);
    }
}
