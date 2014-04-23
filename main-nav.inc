<?php

/**
 * Functions for generating navigation in the main.php output template
 *
 * These functions generate markup that uses Zurb Foundation classes.
 *
 */

/**
 * Render primary navigation bar
 *
 * This one is a Foundation nav-bar and we use a MarkupCache to only create it once a day
 *
 * @return string
 *
 */
function renderTopnav() {

	$page = wire('page');
	$ul = "<ul id='topnav' class='nav-bar' data-root='{$page->rootParent->id}'>";
	$homepage = wire('pages')->get('/');
	$cache = wire('modules')->get('MarkupCache'); 

	if(!$li = $cache->get('topnav', 86400)) {

		// the data-root attr is for JS use to highlight current nav item (see scripts/main.js)

		$maxChildren = 20; // max number of children we allow in a drop-down
		$li = '';

		foreach($homepage->children()->prepend($homepage) as $child) {

			$hasFlyout = false;
			$class = "page-$child";

			if($child->id > 1 && $child->numChildren && $child->numChildren < $maxChildren) {
				$hasFlyout = true; 
				$class .= ' has-flyout';
			}
			
			$li .= 	"<li class='$class'>" . 
				"<a class='main' href='{$child->url}'>{$child->title}</a>";

			if($hasFlyout) {

				$li .= "<a href='#' class='flyout-toggle'><span></span></a>";
				$li .= "<div class='flyout small'><ul>";

				foreach($child->children() as $c) {
					$li .= "<li><a href='{$c->url}'>{$c->title}</a></li>";
				}

				$li .= "</ul></div>";
			}

			$li .= "</li>";	

		}

		$cache->save($li);
	}

	$li = "<li class='search'>" . renderSearchBox() . "</li>" . $li;

	return "$ul$li</ul>";
}

/**
 * Render a search box
 *
 */
function renderSearchBox() {

	$q = htmlentities(wire('input')->whitelist('q'), ENT_QUOTES, "UTF-8");
	$rootURL = wire('config')->urls->root; 

	$p = wire('pages')->get('template=search');
	if(!$p->id) return '';
	$placeholder = __('Search', __FILE__); // Search placeholder text

	$out = 	"<form class='site-search' action='{$p->url}' method='get'>" . 
		"<input type='search' name='q' value='$q' placeholder='$placeholder'>" . 
		"</form>";

	return $out;
}

