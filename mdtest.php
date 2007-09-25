#!/usr/bin/php
<?php
#
# MDTest -- Run tests for Markdown implementations
#
# MDTest
# Copyright (c) 2007 Michel Fortin
# <http://www.michelf.com/>
#
# Derived from Markdown Test
# Copyright (c) 2004 John Gruber
# <http://daringfireball.net/projects/markdown/>
#
# Includes PHP Diff
# Copyright (c) 2003 Daniel Unterberger
# Copyright (c) 2005 Nils Knappmeier
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

define( 'MDTEST_VERSION', "1.1" ); # Tue 25 Sep 2007

$version = false;
$test_dirs = null;
$lib = dirname(__FILE__). "/Implementations/markdown.php";
$func = "Markdown";
$script = null;
$normalize = false;
$show_diff = false;


$args = getopt("l:f:s:t:dnvh");

function millisec() {
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec) * 1000;
}

if (isset($args['v'])) {
	echo "$argv[0]: MDTest, version ".MDTEST_VERSION."\n";
	exit;
}
if (in_array('-?', $argv) || isset($args['h'])) {
	echo "\n";
	echo "MDTest Usage\n";
	echo "============\n";
	echo "\n";
	echo "$argv[0] [-dnvh] [-l library_path [-f function]] [-t test_dir]*\n";
	echo "$argv[0] [-dnvh] [-s script_path] [-t test_dir]*  \n";
	echo "\n";
	echo " Options      | Description\n";
	echo " -------      | -----------\n";
	echo " -n           | normalize HTML output before compare\n";
	echo " -d           | show a diff of output vs. expected output\n";
	echo " -l library   | php library to load (like markdown.php)\n";
	echo " -f function  | php function to call (like Markdown)\n";
	echo " -s script    | script to execute (like Markdown.pl)\n";
	echo " -t test_dir  | testsuite directory to use\n";
	echo " -v           | display MDTest version\n";
	echo " -h           | show this help\n";
	echo "\n";
	exit;
}

if (isset($args['l']))  $lib = $args['l'];
if (isset($args['f']))  $func = $args['f'];
if (isset($args['s']))  $script = $args['s'];
if (isset($args['t']))  $test_dirs = $args['t'];
if (isset($args['d']))  $show_diff = true;
if (isset($args['n']))  $normalize = true;

if (isset($args['l']) && isset($args['s'])) {
	exit("$argv[0]: cannot parse with both a script and library.\n");
}
if (isset($args['s']) && isset($args['f'])) {
	exit("$argv[0]: cannot use a function with a script.\n");
}

if (!isset($script)) {
	if (is_array($lib)) {
		exit("$argv[0]: only one library can be specified.\n");
	}
	if (is_array($func)) {
		exit("$argv[0]: only one function can be specified.\n");
	}
	if (!is_file($lib)) {
		exit("$argv[0]: library '$lib' does not exist.\n");
	}
	
	include_once $lib;
	
	if (preg_match('/(.*)(::|->)(.*)/', $func, $matches)) {
		$func = array($matches[1], $matches[3]);
		if (!class_exists($func[0])) {
			exit("$argv[0]: class '$class' is not available.\n");
		}
		if (!is_callable($func)) {
			exit("$argv[0]: method '$func[1]' is not defined for class '$func[0]'.\n");
		}
		if ($matches[2] == '->') {
			$class = $func[0];
			$func[0] = new $class;
		}
	}
	else if (!is_callable($func)) {
		exit("$argv[0]: function '$func' is not available.\n");
	}
} else {
	if (is_array($script)) {
		exit("$argv[0]: only one script can be specified.\n");
	}
	if (!is_file($script)) {
		exit("$argv[0]: script '$script' does not exist.\n");
	}
	if (function_exists('is_executable') && !is_executable($script)) {
		exit("$argv[0]: script '$script' is not executable.\n");
	}
}

if (!is_array($test_dirs)) {
	if ($test_dirs == null) {
		$test_dirs = glob(dirname(__FILE__) . '/*.mdtest', GLOB_ONLYDIR);
	}
	else {
		$test_dirs = array($test_dirs);
	}
}
if (empty($test_dirs)) {
	exit("$argv[0]: no testsuite directory available.");
}
$test_dir_names = array();
foreach ($test_dirs as $key => $test_dir) {
	if (!is_dir($test_dir) && !is_dir("$test_dir.mdtest")) {
		exit("$argv[0]: '$test_dir' is not a directory.\n");
	}
}

if ($normalize && !class_exists('DOMDocument')) {
	exit("$argv[0]: HTML normalization (option \"-n\") requires PHP 5.\n");
}
	
$tests_passed = 0;
$tests_failed = 0;
$tests_all = 0;
$total_time = 0;
$all_times = array();

foreach ($test_dirs as $test_dir) {
	if (!is_dir($test_dir))  $test_dir .= ".mdtest";
	$name = preg_replace('{^.*/|\.mdtest$}', '', $test_dir);
	echo "\n";
	echo "== Test Suite: $name ==\n";
	echo "\n";
	
	$testfiles = glob("$test_dir/*.text");
	if (!$testfiles) {
		echo "$argv[0]: '$test_dir' does not contain any test case.\n";
		continue;
	}
	
	
	foreach ($testfiles as $testfile) {
		$dirname = dirname($testfile);
		$testname = basename($testfile, '.text');
		printf("%-33s ... ", $testname);
		
		// Look for a corresponding HTML or XHTML file:
		if (is_file($resultfile = "$dirname/$testname.html")) {
			$resultformat = 'html';
		} else if (is_file($resultfile = "$dirname/$testname.xhtml")) {
			$resultformat = 'xhtml';
		} else {
			$resultfile = null;
			$resultformat = null;
		}
			
		$tests_all++;
		
		// No result file, benchmark only.
		if (!$resultfile) {
			$t_input = file_get_contents($testfile);
			$start_time = millisec();
			if (!isset($script)) {
				$t_output = call_user_func($func, $t_input);
			} else {
				$t_output = `'$script' '$testfile'`;
			}
			$end_time = millisec();
			
			$proc_time = $end_time - $start_time;
			$all_times[] = $proc_time;
			$total_time += $proc_time;
			
			printf("?      %4d ms\n", $proc_time);
			continue;
		}
		
		$t_input = file_get_contents($testfile);
		$t_result = file_get_contents($resultfile);
		

		$start_time = millisec();
		if (!isset($script)) {
			$t_output = call_user_func($func, $t_input);
		} else {
			$t_output = `'$script' '$testfile'`;
		}
		$end_time = millisec();
		
		if ($normalize) {
			// DOMDocuments
			if ($resultformat == 'xhtml') {
				$doc_result = @DOMDocument::loadXML("<!DOCTYPE html>".
					"<html xmlns='http://www.w3.org/1999/xhtml'>".
					"<body>$t_result</body></html>");
				$doc_output = @DOMDocument::loadXML("<!DOCTYPE html>".
					"<html xmlns='http://www.w3.org/1999/xhtml'>".
					"<body>$t_output</body></html>");
			
				if ($doc_result) {
					normalizeElementContent($doc_result->documentElement, false);
					$n_result = $doc_result->saveXML();
				} else {
					$n_result = '--- Expected Result: XML Parse Error ---';
				}
				if ($doc_output) {
					normalizeElementContent($doc_output->documentElement, false);
					$n_output = $doc_output->saveXML();
				} else {
					$n_output = '--- Output: XML Parse Error ---';
				}
			} else {
				$doc_result = @DOMDocument::loadHTML($t_result);
				$doc_output = @DOMDocument::loadHTML($t_output);
			
				normalizeElementContent($doc_result->documentElement, false);
				normalizeElementContent($doc_output->documentElement, false);
				
				$n_result = $doc_result->saveHTML();
				$n_output = $doc_output->saveHTML();
			}
			
			$n_result = preg_replace('{^.*?<body>|</body>.*?$}is', '', $n_result);
			$n_output = preg_replace('{^.*?<body>|</body>.*?$}is', '', $n_output);
			
			$c_result = $n_result;
			$c_output = $n_output;
		}
		else {
			$c_result = $t_result;
			$c_output = $t_output;
		}
		
		$c_result = trim($c_result) . "\n";
		$c_output = trim($c_output) . "\n";
		
		$proc_time = $end_time - $start_time;
		$all_times[] = $proc_time;
		$total_time += $proc_time;
		
		if ($c_result == $c_output) {
			printf("OK %8d ms\n", $proc_time);
			$tests_passed++;
		}
		else {
			printf("FAILED %4d ms\n", $proc_time);
			$tests_failed++;
			
			if ($show_diff) {
				echo "~~~\n";
				echo PHPDiff($c_result, $c_output, true);
				echo "~~~\n\n";
			}
		}
	}
}

echo "\n";
//echo "$tests_passed passed ";

if (count($all_times)) {

	sort($all_times);

	$average_time = $total_time / ($tests_all);
	$min_time = min($all_times);
	$max_time = max($all_times);

	$quarter1_index = count($all_times) / 4;
	$quarter2_index = count($all_times) / 2;
	$quarter3_index = count($all_times) * 3 / 4;
	$quarter1_time = ($all_times[floor($quarter1_index)] + $all_times[ceil($quarter1_index)]) / 2;
	$median_time = ($all_times[floor($quarter2_index)] + $all_times[ceil($quarter2_index)]) / 2;
	$quarter3_time = ($all_times[floor($quarter3_index)] + $all_times[ceil($quarter3_index)]) / 2;

	printf("%d passed; %d failed.\n\n", $tests_passed, $tests_failed);

	printf("                   Total   Avg.   Min.    Q1.   Med.    Q3.   Max.\n");
	printf("Parse Time (ms): %7d %6d %6d %6d %6d %6d %6d\n",
		$total_time, $average_time, $min_time, $quarter1_time,
		$median_time, $quarter3_time, $max_time);

	printf("Diff. Min. (ms): %7d %6d %6d %6d %6d %6d %6d\n",
		$total_time-($min_time*($tests_all)), $average_time-$min_time, $min_time-$min_time, $quarter1_time-$min_time,
		$median_time-$min_time, $quarter3_time-$min_time, $max_time-$min_time);

}


function normalizeElementContent($element, $whitespace_preserve) {
#
# Normalize content of HTML DOM $element. The $whitespace_preserve 
# argument indicates that whitespace is significant and shouldn't be 
# normalized; it should be used for the content of certain elements like
# <pre> or <script>.
#
	$node_list = $element->childNodes;
	switch (strtolower($element->nodeName)) {
		case 'body':
		case 'div':
		case 'blockquote':
		case 'ul':
		case 'ol':
		case 'dl':
		case 'h1':
		case 'h2':
		case 'h3':
		case 'h4':
		case 'h5':
		case 'h6':
			$whitespace = "\n\n";
			break;
			
		case 'table':
			$whitespace = "\n";
			break;
		
		case 'pre':
		case 'script':
		case 'style':
		case 'title':
			$whitespace_preserve = true;
			$whitespace = "";
			break;
		
		default:
			$whitespace = "";
			break;
	}
	foreach ($node_list as $node) {
		switch ($node->nodeType) {
			case XML_ELEMENT_NODE:
				normalizeElementContent($node, $whitespace_preserve);
				normalizeElementAttributes($node);
				
				switch (strtolower($node->nodeName)) {
					case 'p':
					case 'div':
					case 'hr':
					case 'blockquote':
					case 'ul':
					case 'ol':
					case 'dl':
					case 'li':
					case 'address':
					case 'table':
					case 'dd':
					case 'pre':
					case 'h1':
					case 'h2':
					case 'h3':
					case 'h4':
					case 'h5':
					case 'h6':
						$whitespace = "\n\n";
						break;
					
					case 'tr':
					case 'td':
					case 'dt':
						$whitespace = "\n";
						break;
					
					default:
						$whitespace = "";
						break;
				}
				
				if (($whitespace == "\n\n" || $whitespace == "\n") &&
					$node->nextSibling && 
					$node->nextSibling->nodeType != XML_TEXT_NODE)
				{
					$element->insertBefore(new DOMText($whitespace), $node->nextSibling);
				}
				break;
				
			case XML_TEXT_NODE:
				if (!$whitespace_preserve) {
					if (trim($node->data) == "") {
						$node->data = $whitespace;
					} else {
						$node->data = preg_replace('{\s+}', ' ', $node->data);
					}
				}
				break;
		}
	}
	if (!$whitespace_preserve && 
		($whitespace == "\n\n" || $whitespace == "\n"))
	{
		if ($element->firstChild) {
			if ($element->firstChild->nodeType == XML_TEXT_NODE) {
				$element->firstChild->data = 
					preg_replace('{^\s+}', "\n", $element->firstChild->data);
			} else {
				$element->insertBefore(new DOMText("\n"), $element->firstChild);
			}
		}
		if ($element->lastChild) {
			if ($element->lastChild->nodeType == XML_TEXT_NODE) {
				$element->lastChild->data = 
					preg_replace('{\s+$}', "\n", $element->lastChild->data);
			} else {
				$element->insertBefore(new DOMText("\n"), null);
			}
		}
	}
}


function normalizeElementAttributes($element) {
#
# Sort attributes by name.
#
	// Gather the list of attributes as an array.
	$attr_list = array();
	foreach ($element->attributes as $attr_node) {
		$attr_list[$attr_node->name] = $attr_node;
	}
	
	// Sort attribute list by name.
	ksort($attr_list);

	// Remove then put back each attribute following sort order.
	foreach ($attr_list as $attr_node) {
		$element->removeAttributeNode($attr_node);
		$element->setAttributeNode($attr_node);
	}
}


/**
	Diff implemented in pure php, written from scratch.
	
	Copyright (c) 2003  Daniel Unterberger <diff.phpnet@holomind.de>
	Copyright (c) 2005  Nils Knappmeier next version 
	Copyright (c) 2007  Michel Fortin: Adaptation for MDTest
**/

    
function PHPDiff($old, $new) {
#
# PHPDiff returns the differences between $old and $new, formatted
# in the standard diff(1) output format.
#
   # split the source text into arrays of lines
   $t1 = explode("\n",$old);
   $x=array_pop($t1); 
   if ($x>'') $t1[]="$x\n\\ No newline at end of file";
   $t2 = explode("\n",$new);
   $x=array_pop($t2); 
   if ($x>'') $t2[]="$x\n\\ No newline at end of file";

   # build a reverse-index array using the line as key and line number as value
   # don't store blank lines, so they won't be targets of the shortest distance
   # search
   foreach($t1 as $i=>$x) if ($x>'') $r1[$x][]=$i;
   foreach($t2 as $i=>$x) if ($x>'') $r2[$x][]=$i;

   $a1=0; $a2=0;   # start at beginning of each list
   $actions=array();

   # walk this loop until we reach the end of one of the lists
   while ($a1<count($t1) && $a2<count($t2)) {
     # if we have a common element, save it and go to the next
     if ($t1[$a1]==$t2[$a2]) { $actions[]=4; $a1++; $a2++; continue; } 

     # otherwise, find the shortest move (Manhattan-distance) from the
     # current location
     $best1=count($t1); $best2=count($t2);
     $s1=$a1; $s2=$a2;
     while(($s1+$s2-$a1-$a2) < ($best1+$best2-$a1-$a2)) {
       $d=-1;
       foreach((array)@$r1[$t2[$s2]] as $n) 
         if ($n>=$s1) { $d=$n; break; }
       if ($d>=$s1 && ($d+$s2-$a1-$a2)<($best1+$best2-$a1-$a2))
         { $best1=$d; $best2=$s2; }
       $d=-1;
       foreach((array)@$r2[$t1[$s1]] as $n) 
         if ($n>=$s2) { $d=$n; break; }
       if ($d>=$s2 && ($s1+$d-$a1-$a2)<($best1+$best2-$a1-$a2))
         { $best1=$s1; $best2=$d; }
       $s1++; $s2++;
     }
     while ($a1<$best1) { $actions[]=1; $a1++; }  # deleted elements
     while ($a2<$best2) { $actions[]=2; $a2++; }  # added elements
  }

  # we've reached the end of one list, now walk to the end of the other
  while($a1<count($t1)) { $actions[]=1; $a1++; }  # deleted elements
  while($a2<count($t2)) { $actions[]=2; $a2++; }  # added elements

  # and this marks our ending point
  $actions[]=8;

  # now, let's follow the path we just took and report the added/deleted
  # elements into $out.
  $op = 0;
  $x0=$x1=0; $y0=$y1=0;
  $out = array();
  foreach($actions as $act) {
    if ($act==1) { $op|=$act; $x1++; continue; }
    if ($act==2) { $op|=$act; $y1++; continue; }
    if ($op>0) {
      $xstr = ($x1==($x0+1)) ? $x1 : ($x0+1).",$x1";
      $ystr = ($y1==($y0+1)) ? $y1 : ($y0+1).",$y1";
      if ($op==1) $out[] = "{$xstr}d{$y1}";
      elseif ($op==3) $out[] = "{$xstr}c{$ystr}";
      while ($x0<$x1) { $out[] = '< '.$t1[$x0]; $x0++; }   # deleted elems
      if ($op==2) $out[] = "{$x1}a{$ystr}";
      elseif ($op==3) $out[] = '---';
      while ($y0<$y1) { $out[] = '> '.$t2[$y0]; $y0++; }   # added elems
    }
    $x1++; $x0=$x1;
    $y1++; $y0=$y1;
    $op=0;
  }
  $out[] = '';
  return join("\n",$out); 
}
?>
