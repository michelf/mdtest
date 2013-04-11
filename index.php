<?php
#
# Web Interface for MDTest
#
# MDTest Web Interface
# Copyright (c) 2007 Michel Fortin
# <http://www.michelf.com/>
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
$normalize = isset($_GET['normalize']);
$diff = isset($_GET['diff']);

?>
<!DOCTYPE html>

<html>
<head>
<title></title>
<style>
	body { font-family: Palatino, "Palatino Linotype", serif; margin: 1ex 1em; }
	form { background: #eed; margin: -1ex -1em 0 -1em; padding: 1ex 1em; }
	h1 { font-style: italic; }
	h1 span { font-size: 75%; }
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
		$files = glob(dirname(__FILE__). "/Implementations/*");
		foreach ($files as $file) {
			$name = htmlspecialchars(basename($file));
			if (is_dir($file)) {
				// skip directories
				continue;
			} else if (is_executable($file) && substr($file, -4, 4) != '.php') {
				// Script
				$value = "s-$name";
			} else if (substr($file, -4, 4) == '.php') {
				// PHP Lib
				$value = "l-$name";
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
	<label><input type="checkbox" name="normalize"<?php echo isset($_GET['normalize']) ? " checked" : "" ?> /> Normalize Output</label> 
	<label><input type="checkbox" name="diff"<?php echo isset($_GET['diff']) ? " checked" : "" ?> /> Show differences</label>
	<input type="submit" value="Test">
</div>
</form>

<h1><span>MD</span>Test Results</h1>

<pre><?php

$options = "";

if ($impl) {
	$impl_arg = escapeshellarg(
		dirname(__FILE__). "/Implementations/". substr($impl, 2));
	if ($impl{0} == 's') {
		$options .= "-s $impl_arg";
	} else if ($impl{0} == 'l') {
		$options .= "-l $impl_arg";
	}
}
if ($diff)  $options .= " -d";
if ($normalize)  $options .= " -n";

ob_start('htmlspecialchars', 64);
system("'". dirname(__FILE__). "/mdtest.php' $options");
ob_end_flush();
?></pre>

<p><small>MDTest<br />
Copyright (c) 2007 <a href="http://www.michelf.com/">Michel Fortin</a></small></p>

<p><small>Derived from Markdown Test<br />
Copyright (c) 2004 <a href="http://daringfireball.net/">John Gruber</a></small></p>

</body>
</html>
