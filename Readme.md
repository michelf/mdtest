MDTest
======

Markdown/Markdown Extra Test Suite

by Michel Fortin  
<http://michelf.ca/>

derived from Markdown Test by John Gruber
<http://daringfireball.net/>


Introduction
------------

MDTest is a Markdown test suite derived from the older MarkdownTest by 
John Gruber. MDTest is primarily used for the developement of PHP Markdown
but is strucutred in a way that can test and benchmark various implementations.


Test Suites
-----------

MDTest splits its tests into "mdtest" folders. The MDTest command line tool
will run the test for the specified test suite if specified, or all of them
if unspecified.

### Markdown.mdtest

The *Markdown* testsuite folder contains the original Markdown test suite from
MarkdownTest. It was written by John Gruber to test Markdown.pl.

### PHP Markdown.mdtest

The *PHP Markdown* testsuite folder contains complementary tests for 
PHP Markdown not included in the Markdown test suite.

### PHP Markdown Extra.mdtest

The *PHP Markdown Extra* testsuite folder contains tests for features added
to PHP Markdown Extra and not present in regular Markdown.


Requirement
-----------

This library package should be run with PHP 5.3 or later.


Usage
-----

### Command Line

To run MDTest you need a Markdown parser. You can supply the parser as a function to call after loading a PHP library or as an executable script taking input from STDIN and emiting its output to STDOUT.

For instance, if you are testing PHP Markdown, use this command:

	./mdtest.php -n -l path/to/markdown.php -f Markdown

This will make MDTest import the `markdown.php` file and call the `Markdown` global function.

For external scripts to execute, instead of passing a library file name and a function to call, you pass the path to the script instead:

	./mdtest.php -n -s path/to/parser-executable

By default, MDTest will run tests from all folders with a `.testsuite` extension it can find in the current directory.

Use the `-h` option to get a complete list of available options.

### Web Interface

The `index.php` file provides a rudimentary web interface for MDTest. It let you choose from a list of implementations in the Implementaiton folder and displays the output of the test script.


Bugs
----

Please fill bugs in the Github issue tracker.
