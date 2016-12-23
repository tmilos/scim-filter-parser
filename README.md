# SCIM filter parser

The PHP parser for SCIM filter. SCIM stands for System for Cross-domain Identity Management and more details can be
found on http://www.simplecloud.info/ website.

[![Author](http://img.shields.io/badge/author-@tmilos-blue.svg?style=flat-square)](https://twitter.com/tmilos77)
[![Build Status](https://travis-ci.org/tmilos/scim-filter-parser.svg?branch=master)](https://travis-ci.org/tmilos/scim-filter-parser)
[![Coverage Status](https://coveralls.io/repos/github/tmilos/scim-filter-parser/badge.svg?branch=master)](https://coveralls.io/github/tmilos/scim-filter-parser?branch=master)
[![Quality Score](https://img.shields.io/scrutinizer/g/tmilos/scim-filter-parser.svg?style=flat-square)](https://scrutinizer-ci.com/g/tmilos/scim-filter-parser)
[![License](https://img.shields.io/packagist/l/tmilos/scim-filter-parser.svg)](https://packagist.org/packages/tmilos/scim-filter-parser)
[![Packagist Version](https://img.shields.io/packagist/v/tmilos/scim-filter-parser.svg?style=flat-square)](https://packagist.org/packages/tmilos/scim-filter-parser)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/cc1043a0-daa9-481b-9840-109bdb43543b/mini.png)](https://insight.sensiolabs.com/projects/cc1043a0-daa9-481b-9840-109bdb43543b)


# Usage

```php
<?php
$parser = new Parser();
$node = $parser->parse('userType eq "Employee" and (emails co "example.com" or emails.value co "example.org")');
/*
walk the node...

Conjunction = {
    ComparisonExpression => userType eq Employee
    Disjunction => {
        ComparisonExpression => emails co example.com
        ComparisonExpression => emails.value co example.org
    }
}
*/
```

For more details look at the [unit tests](tests/ParserTest.php).


# SCIM version

SCIM filter between versions v1 and v2 remained almost the same, with the difference that v2 introduced new ValuePath syntax.
Parser is by default in v2 mode, and you can switch it to v1 with ``Parser::setVersion(Version::V1())`` when it will throw syntax
errors when brackets are encountered.

