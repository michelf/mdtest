MDTest
======

Markdown/Markdown Extra Test Suite


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

	./mdtest.php -l path/to/markdown.php -f Markdown

This will make MDTest import the `markdown.php` file and call the `Markdown` global function.

For external scripts to execute, instead of passing a library file name and a function to call, you pass the path to the script instead:

	./mdtest.php -s path/to/parser-executable

By default, MDTest will run tests from all folders with a `.testsuite` extension it can find in the current directory.

Use the `-h` option to get a complete list of available options.

### Web Interface

The `index.php` file provides a rudimentary web interface for MDTest. It let you choose from a list of implementations in the Implementaiton folder and displays the output of the test script.


Bugs
----

Please file bugs in the Github issue tracker.


Copyright
---------

MDTest  
Copyright (c) 2007-2013 Michel Fortin  
<http://www.michelf.com/>

Derived from Markdown Test  
Copyright (c) 2004 John Gruber  
<http://daringfireball.net/projects/markdown/>

Includes PHP Diff  
Copyright (c) 2003 Daniel Unterberger  
Copyright (c) 2005 Nils Knappmeier  


License
-------

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
