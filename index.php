<?php
#
# Web Interface for MDTest
#
# MDTest Web Interface
# Copyright (c) 2007 Michel Fortin
# <http://michelf.ca/>
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
# 
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
#

$impl = isset($_GET['impl']) ? $_GET['impl'] : "l-markdown.php";
$raw = isset($_GET['raw']);
$diff = isset($_GET['diff']);

header("Content-Type: text/html; charset=utf-8");

?>
<!DOCTYPE html>

<html>
<head>
<title>MDTest</title>
<style>
	body { font-family: Palatino, "Palatino Linotype", serif; margin: 1ex 1em; }
	form { background: #eed; margin: -1ex -1em 0 -1em; padding: 1ex 1em; }
	h1 { font-style: italic; }
	label { font: 80% "Lucida Grande", Tahoma, sans-serif; margin-right: 1em; }
	input[type=submit] { min-width: 4em; }
</style>
</head>
<body>

<form>
<div>
	<label>Implementation:
		<select name="impl">
<?php
		$all_options = array();
		$files = glob(dirname(__FILE__). "/Implementations/*");
		foreach ($files as $file) {
			$name = htmlspecialchars(basename($file));
			if (is_executable($file) && !is_dir($file) && substr($file, -4, 4) != '.php') {
				// Script
				$value = "s-$name";
				$all_options[] = $value;
			} else if (!is_dir($file) && substr($file, -4, 4) == '.php') {
				// PHP Lib
				$value = "l-$name";
				$all_options[] = $value;
			} else if (!is_dir($file) && substr($file, -8, 8) == '.phpcall') {
				// PHP Call
				$funcname = file_get_contents($file);
				$value = "f-$funcname";
				$all_options[] = $value;
			} else {
				continue;
			}
			
			$selected = $impl == $value;
			if ($selected)  $selected = " selected";
			echo "\t\t\t<option value=\"$value\"$selected>$name</option>\n";
		}
?>
		</select>
	</label>
	<label><input type="checkbox" name="raw"<?php echo isset($_GET['raw']) ? " checked" : "" ?> /> Compare raw output</label> 
	<label><input type="checkbox" name="diff"<?php echo isset($_GET['diff']) ? " checked" : "" ?> /> Show diff</label>
	<input type="submit" value="Test">
</div>
</form>

<h1>MDTest Results</h1>

<pre><?php

$options = "";

# Checking that value of $impl is in $all_options to protect against
# function call injection attacks.
if ($impl && array_search($impl, $all_options) !== FALSE) {
	if ($impl{0} == 'f') {
		$impl_arg = escapeshellarg(substr($impl, 2));
		$options .= "-f $impl_arg";
	} else {
		$impl_arg = escapeshellarg(
			dirname(__FILE__). "/Implementations/". substr($impl, 2));
		if ($impl{0} == 's') {
			$options .= "-s $impl_arg";
		} else if ($impl{0} == 'l') {
			$options .= "-l $impl_arg";
		} else if ($impl{0} == 'f') {
			$options .= "-f $impl_arg";
		}
	}
}
if ($diff)  $options .= " -d";
if ($raw)  $options .= " -r";

ob_start('htmlspecialchars', 64);
system("'". dirname(__FILE__). "/mdtest.php' $options");
ob_end_flush();
?></pre>

<p><small>MDTest<br />
Copyright © 2007-2013 <a href="http://michelf.ca/">Michel Fortin</a></small></p>

<p><small>Derived from Markdown Test<br />
Copyright © 2004 <a href="http://daringfireball.net/">John Gruber</a></small></p>

</body>
</html>
