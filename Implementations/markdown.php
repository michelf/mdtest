<?php
#
# Markdown  -  A text-to-HTML conversion tool for web writers
#
# PHP Markdown  
# Copyright (c) 2004-2013 Michel Fortin  
# <http://michelf.com/projects/php-markdown/>
#
# Original Markdown  
# Copyright (c) 2004-2006 John Gruber  
# <http://daringfireball.net/projects/markdown/>
#
#
require_once('Michelf/Markdown.php');

function Markdown($text) {
#
# Initialize the parser and return the result of its transform method.
#
	return \Michelf\Markdown::defaultTransform($text);
}
