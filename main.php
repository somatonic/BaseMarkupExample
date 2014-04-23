<?php

/**
 * The main HTML/markup wrapper file for the blog templates
 *
 * This is rendered by the BlogOutput class. 
 *
 */

// We pull some data from the homepage in a few places in this main template, so we keep it ready here
$homepage = $pages->get('/');

// determine what the ever important browser <title> tag should be
if($page->browser_title) $browserTitle = $page->browser_title; 
	else if($page === $homepage) $browserTitle = $page->headline; 
	else $browserTitle = $page->title . ' &bull; ' . $homepage->headline; 

// navigation output functions used by this main template
include(dirname(__FILE__) . '/main-nav.inc');


?><!DOCTYPE html>

<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<meta charset="utf-8" />

	<!-- Set the viewport width to device width for mobile -->
	<meta name="viewport" content="width=device-width" />
		
	<title><?php echo $browserTitle; ?></title>

	<?php if($page->summary) echo "<meta name='description' content='{$page->summary}' />"; ?>
  
	<!-- Included CSS Files -->
	<link rel="stylesheet" href="<?php echo $config->urls->templates; ?>foundation/stylesheets/foundation.css">
	<link rel="stylesheet" href="<?php echo $config->urls->templates; ?>foundation/stylesheets/app.css">

	<?php foreach($config->styles as $key => $file) echo "<link rel='stylesheet' type='text/css' href='$file'>"; ?>

	<link rel="stylesheet" href="<?php echo $config->urls->templates; ?>styles/main.css">

	<!--[if lt IE 9]>
		<link rel="stylesheet" href="<?php echo $config->urls->templates; ?>foundation/stylesheets/ie.css">
	<![endif]-->
	
	<script src="<?php echo $config->urls->templates; ?>foundation/javascripts/modernizr.foundation.js"></script>

	<!-- IE Fix for HTML5 Tags -->
	<!--[if lt IE 9]>
		<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->

</head>
<body class='template-<?php echo $page->template; ?>'>

	<?php if($page->editable()) echo "<a id='editpage' href='{$config->urls->admin}page/edit/?id={$page->id}'>Edit</a>"; ?>

	<div class="container">

		<div id="masthead" class="row">

			<div class="twelve columns">

				<?php echo "<a href='{$config->urls->root}'><h1>{$homepage->headline}</h1></a>"; ?>

				<p><?php echo $homepage->summary; ?></p>

				<?php echo renderTopnav(); ?>

			</div>

		</div>

		<div id="content" class="row">

			<div id="bodycopy" class="eight columns">

				<?php echo $output->content; ?>

			</div><!--/#bodycopy-->

			<div id="sidebar" class="four columns">			

				<?php

				echo $markup->adminWidget();
				echo $output->sidebar; 
				echo $markup->widgets($page);

				?>

			</div><!--/#sidebar-->

		</div><!--/#content-->

		<div id="footer" class="row">
			<div class="twelve columns">
				<small>
				&copy; <?php echo date('Y'); ?> / 
				Powered by <a target='_blank' href='http://processwire.com'>ProcessWire Open Source CMS</a>
				</small>
			</div>
		</div>

	</div><!--/.container-->

	<script src="<?php echo $config->urls->templates; ?>foundation/javascripts/jquery.min.js"></script>
	<script src="<?php echo $config->urls->templates; ?>foundation/javascripts/foundation.js"></script>
	<script src="<?php echo $config->urls->templates; ?>foundation/javascripts/app.js"></script>

	<?php foreach($config->scripts as $file) echo "<script src='$file'></script>"; ?>

	<script src="<?php echo $config->urls->templates; ?>scripts/main.js"></script>

</body>
</html>
