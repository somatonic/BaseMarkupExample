<?php

/**
 * Blog profile initialization
 *
 * This includes and instantiates the classes used for our blog profile
 * It also creates 3 new API variables: $markup, $blog and $output
 *
 */

$dir = dirname(__FILE__);

include("$dir/BlogTools.php");
include("$dir/BaseMarkup.php");
include("$dir/BlogMarkup.php");
//include("$dir/BlogMarkupFoundation.php");
include("$dir/BlogOutput.php");

//$blog = new BlogTools();
$output = new BlogOutput();

// $markup = new BlogMarkupFoundation(); 		// uncomment this line if you want to remove Foundation...
$markup = new BlogMarkup(); 	// ...and comment this line, or point it to your own class.

Wire::setFuel('markup', $markup);
//Wire::setFuel('blog', $blog);
Wire::setFuel('output', $output);


