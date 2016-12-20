# SCIM filter parser

The PHP parser for SCIM filter. SCIM stands for System for Cross-domain Identity Management and more details can be
found on http://www.simplecloud.info/ website.

[![Author](http://img.shields.io/badge/author-@tmilos-blue.svg?style=flat-square)](https://twitter.com/tmilos77)
[![Build Status](https://travis-ci.org/tmilos/scim-filter-parser.svg?branch=master)](https://travis-ci.org/tmilos/scim-fitrer-parser)
[![Coverage Status](https://coveralls.io/repos/github/tmilos/scim-filter-parser/badge.svg?branch=master)](https://coveralls.io/github/tmilos/scim-filter-parser?branch=master)
[![License](https://img.shields.io/packagist/l/tmilos/scim-filter-parser.svg)](https://packagist.org/packages/tmilos/scim-filter-parser)


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

