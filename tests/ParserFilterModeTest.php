<?php

namespace Tests\Tmilos\ScimFilterParser;

use Tmilos\ScimFilterParser\Error\FilterException;
use Tmilos\ScimFilterParser\Mode;
use Tmilos\ScimFilterParser\Parser;
use Tmilos\ScimFilterParser\Version;

class ParserFilterModeTest extends \PHPUnit_Framework_TestCase
{
    public function parser_provider_v2()
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
                'emails[type eq "work"]',
                [
                    'ValuePath' => [
                        ['AttributePath' => 'emails'],
                        ['ComparisonExpression' => 'type eq work'],
                    ],
                ]
            ],

            [
                'userType eq "Employee" and emails[type eq "work" and value co "@example.com"]',
                [
                    'Conjunction' => [
                        ['ComparisonExpression' => 'userType eq Employee'],
                        [
                            'ValuePath' => [
                                ['AttributePath' => 'emails'],
                                [
                                    'Conjunction' => [
                                        ['ComparisonExpression' => 'type eq work'],
                                        ['ComparisonExpression' => 'value co @example.com'],
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            [
                'emails[type eq "work" and value co "@example.com"] or ims[type eq "xmpp" and value co "@foo.com"]',
                [
                    'Disjunction' => [
                        [
                            'ValuePath' => [
                                ['AttributePath' => 'emails'],
                                [
                                    'Conjunction' => [
                                        ['ComparisonExpression' => 'type eq work'],
                                        ['ComparisonExpression' => 'value co @example.com'],
                                    ]
                                ]
                            ]
                        ],
                        [
                            'ValuePath' => [
                                ['AttributePath' => 'ims'],
                                [
                                    'Conjunction' => [
                                        ['ComparisonExpression' => 'type eq xmpp'],
                                        ['ComparisonExpression' => 'value co @foo.com'],
                                    ]
                                ]
                            ]
                        ]
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
     * @dataProvider parser_provider_v2
     */
    public function test_parser_v2($filterString, array $expectedDump)
    {
        $parser = $this->getParser();
        $node = $parser->parse($filterString);
        $this->assertEquals($expectedDump, $node->dump(), sprintf("\n\n%s\n%s\n\n", $filterString, json_encode($node->dump(), JSON_PRETTY_PRINT)));
    }

    public function error_provider_v2()
    {
        return [
            ['not a valid filter', "[Syntax Error] line 0, col 4: Error: Expected PAREN_OPEN, got 'a'"],
            ['username xx "mike"', "[Syntax Error] line 0, col 9: Error: Expected comparision operator, got 'xx'"],
            ['username eq', "[Syntax Error] line 0, col 9: Error: Expected SP, got end of string."],
            ['username eq ', "[Syntax Error] line 0, col 11: Error: Expected comparison value, got end of string."],
            ['emails[type[value eq "1"]]', "[Syntax Error] line 0, col 11: Error: Expected SP, got '['"],
        ];
    }

    /**
     * @dataProvider error_provider_v2
     */
    public function test_error_v2($filterString, $expectedMessage, $expectedException = FilterException::class)
    {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedMessage);
        $parser = $this->getParser();
        $parser->parse($filterString);
    }

    /**
     * @expectedException \Tmilos\ScimFilterParser\Error\FilterException
     * @expectedExceptionMessage [Syntax Error] line 0, col 6: Error: Expected SP, got '['
     */
    public function test_v1_no_value_path()
    {
        $parser = $this->getParser(Version::V1());
        $parser->parse('emails[type eq "work"]');
    }

    /**
     * @expectedException \Tmilos\ScimFilterParser\Error\FilterException
     * @expectedExceptionMessage [Syntax Error] line 0, col 25: Error: Expected end of input, got '.'
     */
    public function test_throws_error_for_value_path_with_attribute_path_in_filter_mode()
    {
        $parser = $this->getParser();
        $node = $parser->parse('addresses[type eq "work"].streetAddress co "main"');
        var_dump($node->dump());
    }

    /**
     * @param Version $version
     *
     * @return Parser
     */
    private function getParser(Version $version = null)
    {
        $version = $version ?: Version::V2();

        return new Parser(Mode::FILTER(), $version);
    }
}
