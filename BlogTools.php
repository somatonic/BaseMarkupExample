<?php

/**
 * A class for retrieving blog data (posts and comments)
 *
 * This is basically a wrapper for other API functions already built into PW,
 * but this class enables your calls to be simpler since you need not specify
 * things like templates, field names, sort order, etc. 
 *
 * Copyright 2012 by Ryan Cramer
 *
 */

class BlogTools extends WireData {

	/**
	 * Initialize BlogTools
	 *
	 */
	public function __construct() {
		$this->set('blogPostTemplate', 'post');
		$this->set('commentsField', 'comments'); 
		$this->set('dateField', 'date');
	}

	/**
	 * Find blog posts matching the given selector string
	 *
	 * This is the same as calling $pages->find(); except that it'll only find blog posts
	 *
	 * @param string $selector
	 * @return PageArray
	 *
	 */
	public function findPosts($selector) {
		if(strpos($selector, "sort=") === false) $selector .= ", sort=-{$this->dateField}";
		return $this->pages->find("template={$this->blogPostTemplate}, $selector");
	}

	/**
	 * Get 1 blog posts matching the given selector string
	 *
	 * This is the same as calling $pages->get(); except that it'll only find blog posts
	 *
	 * @param string $selector
	 * @return Page|NullPage
	 *
	 */
	public function getPost($selector) {
		$selector .= ", limit=1";
		$item = $this->findPosts($selector)->first();
		if(!$item) return new NullPage();
		return $item;
	}

	/**
	 * Given a post, return the next one in date order
	 *
	 * Returns a NullPage (id=0) when supplied $item is already the last post. 
	 *
	 * This is more efficient than $page->next(); since it doesn't have to load all siblings. 
	 *
	 * @param Page $item
	 * @return Page|NullPage 
	 *
	 */
	public function nextPost(Page $item) {
		$date = $item->getUnformatted($this->dateField);
		$nextPost = $item->parent->child("template={$this->blogPostTemplate}, date>$date, sort={$this->dateField}");
		return $nextPost;
	}

	/**
	 * Given a post, return the previous one in date order
	 *
	 * Returns a NullPage (id=0) when the supplied $item is already the first post.
	 *
	 * This is more efficient than $page->prev(); since it doesn't have to load all siblings. 
	 *
	 * @param Page $item
	 * @return Page|NullPage 
	 *
	 */
	public function prevPost(Page $item) {
		$date = $item->getUnformatted($this->dateField);
		$prevPost = $item->parent->child("template={$this->blogPostTemplate}, date<$date, sort=-{$this->dateField}");
		return $prevPost;
	}

	/**
	 * Find comments from the given selector string
	 *
	 * @param string $selector
	 * @return CommentArray
	 *
	 */
	public function findComments($selector) {
		$comments = FieldtypeComments::findComments($this->commentsField, $selector); 
		foreach($comments as $comment) {
			if(!$comment->page->viewable()) $comments->remove($comment);
		}
		return $comments; 
	}

	/**
	 * Find $limit recent comments
	 *
	 * @param int $limit Number of recent comments to find
	 * @param int $start Where to start, like 0 (default: null = automatic, based on page number)
	 * @param bool $admin Include non-approved and spam comments? (default: null = determine automatically)
	 * @return CommentArray
	 *
	 */
	public function findRecentComments($limit = 3, $start = null, $admin = null) {

		$limit = (int) $limit; 
		$_limit = is_null($start) ? $limit : $limit+1;
		$out = '';
		$pageNum = $this->input->pageNum; 

		// auto-determine $start if not specified
		if(is_null($start)) {
			if($pageNum > 1) $start = $pageNum * $limit; 
				else $start = 0;
		}

		// we show pending and spam comments when page is editable
		if(is_null($admin)) $admin = $this->page->editable();

		// build selector to locate comments
		$selector = "limit=$_limit, start=$start, sort=-created, ";

		if($admin) $selector .= "status>=" . Comment::statusSpam . ", ";
			else $selector .= "status>=" . Comment::statusApproved . ", ";

		// find the comments we want to output
		$comments = $this->findComments($selector);

		return $comments; 
	}
}

