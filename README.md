About
=====
>MDTest -- Run tests for Markdown implementations

Running the tests
-----------------
```bash
./mdtest.php -l "path/to/markdown-test-wrapper.php" -f "Markdown" -t "Markdown" -t "PHP Markdown" -n
```
```bash
./mdtest.php -l "path/to/markdown-test-wrapper.php" -f "MarkdownExtra" -t "Markdown" -t "PHP Markdown" -t "PHP Markdown Extra" -n
```

From @michelf in [this thread][comment]:
>The wrapper I use looks like this:

```php
<?php

# Install PSR-0-compatible class autoloader
spl_autoload_register(function(\$class){
	require preg_replace('{\\\\|_(?!.*\\\\)}', DIRECTORY_SEPARATOR, ltrim(\$class, '\\')).'.php';
});

function Markdown(\$text)
{
	return Michelf\Markdown::defaultTransform(\$text);
}

function MarkdownExtra(\$text)
{
	return Michelf\MarkdownExtra::defaultTransform(\$text);
}
```

[comment]: https://github.com/michelf/mdtest/pull/3#issuecomment-16228356

Here's a sample script for running the tests against the `lib` (main) branch
in the php-markdown repo.
```bash
#!/bin/bash
git clone https://github.com/michelf/php-markdown.git ../php-markdown
# create a symbolic link to said lib repo
ln -s ../php-markdown php-markdown
# go into the lib repo
cd php-markdown
# install the lib repo's dependencies (autoloader)
composer install
# go back to the test repo
cd -
# create a simple wrapper script
cat > wrapper.php << "End-of-message"
<?php

# Install PSR-0-compatible class autoloader
spl_autoload_register(function($class){
	require preg_replace('{\\\\|_(?!.*\\\\)}', DIRECTORY_SEPARATOR, ltrim($class, '\\')).'.php';
});

# Install the composer autoloader from the php-markdown repo
require 'php-markdown/vendor/autoload.php';

function Markdown($text)
{
	return Michelf\Markdown::defaultTransform($text);
}

function MarkdownExtra($text)
{
	return Michelf\MarkdownExtra::defaultTransform($text);
}
End-of-message

# create a simple Makefile
cat > Makefile << "End-of-message"
markdown:
	./mdtest.php -l "wrapper.php" -f "Markdown" -t "Markdown" -t "PHP Markdown" -n

markdown-extra:
	./mdtest.php -l "wrapper.php" -f "MarkdownExtra" -t "Markdown" -t "PHP Markdown" -t "PHP Markdown Extra" -n

markdown-not-normalized:
	./mdtest.php -l "wrapper.php" -f "Markdown" -t "Markdown" -t "PHP Markdown"

markdown-extra-not-normalized:
	./mdtest.php -l "wrapper.php" -f "MarkdownExtra" -t "Markdown" -t "PHP Markdown" -t "PHP Markdown Extra"
End-of-message
```

Once this is done, run `make markdown` or `make markdown-extra` to run the
respective test suites.

About the code
--------------
MDTest<br>
Copyright (c) 2007 Michel Fortin<br>
<http://www.michelf.com/><br>

Derived from Markdown Test<br>
Copyright (c) 2004 John Gruber<br>
<http://daringfireball.net/projects/markdown/><br>

Includes PHP Diff<br>
Copyright (c) 2003 Daniel Unterberger<br>
Copyright (c) 2005 Nils Knappmeier<br>

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

