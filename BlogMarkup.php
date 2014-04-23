<?php

/**
 * A markup-generating class specific to blog output. 
 *
 * All the functions here return markup in some form or another. 
 *
 * You may modify, extend, or plugin-to this class to modify the output. 
 *
 * See also ./main.php, which is the container HTML file for all markup. 
 *
 * Copyright 2012 by Ryan Cramer
 *
 */
class BlogMarkup extends BaseMarkup {

	/**
	 * Initialize the BlogMarkup variables and define the default class attributes
	 *
	 * Subclasses should call this constructor before executing their own.
	 *
	 */
	public function __construct() {

		// thumbnail image dimensions, used throughout
		$this->set('thumbWidth', 100);
		$this->set('thumbHeight', 100);

		// a few default class replacements
		$customClassAttrs = array(
			'pagination' => 'MarkupPagerNav',
			'blog-comments' => 'blog-comments CommentList',
			'blog-comment' => 'blog-comment CommentListItem',
			'blog-comment-head' => 'blog-comment-head CommentHeader', 
			'blog-comment-body' => 'blog-comment-body CommentText', 
			'gallery' => 'gallery clearfix',
			'author' => 'author clearfix'
			);

		$this->setClassAttr($customClassAttrs);
		
	}

	/**
	 * Given a PageArray of blog entries generate and return the output.
	 *
	 * @param PageArray$posts The entries to generate output for
	 * @param bool $small Set to true if you want summarized versions (default = false)
	 * @return string The generated output
	 *
	 */
	public function ___posts(PageArray $posts, $small = false) {

		$out = '';
		foreach($posts as $item) $out .= $this->post($item, $small);
		if(!count($posts)) $out .= $this->subhead($this->_('No posts to display')); 

		$class = 'blog-posts';
		if($small) $class .= ' blog-posts-small'; 
		$out = $this->div($out, $class); 

		// if there are more posts than the specified limit, then output pagination
		if($posts->getLimit() < $posts->getTotal()) $out .= $this->pagination($posts);
		
		return $out;	
	}

	/**
	 * Render an individual blog item, as used by renderBlogPosts()
	 *
	 * @param Page $item The item to render
	 * @param bool $small If true, then only a summary is shown rather than full blog entry
	 * @return string The generated output
	 *
	 */
	public function ___post(Page $item, $small = false) {

		$head = $this->div($this->postHead($item, $small), 'blog-post-head'); 
		$body = $this->div($this->postBody($item, $small), 'blog-post-body'); 
		$foot = $this->div($this->postFoot($item, $small), 'blog-post-foot'); 

		return $this->div("$head$body$foot", 'blog-post'); 
	}

	/**
	 * Render a byline of meta data associated with the post: date, author, categories, etc.
	 *
	 * @param Page $item The item being rendered
	 * @param bool $small If true, then a small list is being printed (which may not necessarily affect output)
	 * @return string The generated output
	 *
	 */
	protected function ___postHead(Page $item, $small = false) {

		$title = $item->title;
		// if the current page isn't this item, then make the title a link to it
		if(wire('page') !== $item) $title = $this->a($item->url, $title); // <a href='{$item->url}'>$title</a>

		$title = $small ? $this->subhead($title) : $this->headline($title);
		$rootURL = wire('config')->urls->root; 
		$authorURL = $rootURL . 'authors/' . $item->createdUser->name . '/';
		$authorName = $item->createdUser->get('title|name'); 
		$authorLink = $this->a($authorURL, $authorName); 
		$date = $this->date($item->date);

		// generate categories list
		$categories = '';
		foreach($item->categories as $c) $categories .= $this->a($c->url, $c->title) . ' / '; 
		$categories = rtrim($categories, '/ ');

		if($categories) $out = sprintf($this->_('Posted by %1$s on %2$s in %3$s'), $authorLink, $date, $categories);
			else $out = sprintf($this->_('Posted by %1$s on %2$s'), $authorLink, $date); 

		return "$title " . $this->p($out, 'blog-post-byline'); 
	}

	/**
	 * Render the body of the blog post
	 *
	 * @param Page $item The item being rendered
	 * @param bool $small If true, then summarize the body rather than returning the whole thing.
	 * @return string The generated output
	 *
	 */
	protected function ___postBody(Page $item, $small = false) {

		if($small) {
			// display summary rather than full body
			$summary = $item->summary;
			if(empty($summary)) {
				// summary is blank so we auto-generate a summary from the body
				$summary = strip_tags(substr($item->body, 0, 450));
				$summary = substr($summary, 0, strrpos($summary, ' ')) . '&hellip;';
			}
			$more = $this->linkMore($item->url); 
			$body = $this->p("$summary $more", 'blog-post-summary');
		} else {
			// display full body
			$body = $item->body;
		}

		// check for image gallery: a blank paragraph with the word "images", i.e. "<p>images</p>"; 
		$regex = '/<p>\s*images\s*<\/p>/i';
		if(strpos($body, 'images') && preg_match($regex, $body)) {
			if($small) $body = preg_replace($regex, '', $body); 
				else $body = preg_replace($regex, $this->gallery($item->images), $body); 
		}

		return $body;
	}

	/**
	 * Render the footer of the blog post
	 *
	 * @param Page $item The item being rendered
	 * @param bool $small If true, then a small list is being printed (which may not necessarily affect output)
	 * @return string The generated output
	 *
	 */
	protected function ___postFoot(Page $item, $small = false) {
		$numComments = $item->comments->count();
		$comments = sprintf(_n('%d Comment', '%d Comments', $numComments), $numComments); 
		$out = $this->a($item->url . '#comments', $comments, 'num-comments'); 
		return $out;
	}

	/**
	 * Render a comment form 
	 *
	 * @param Page $item The current blog post
	 * @return string The generated output
	 *
	 */
	public function ___commentForm(Page $item) {

		$out = $item->comments->renderForm(array(
			'headline' => $this->subhead($this->_('Post a comment')), // Post comment headline
			'attrs' => array(),
			'successMessage' => $this->div($this->_('Thank you, your submission has been saved.'), 'alert-success'), // Comment post success message
			'errorMessage' => $this->div($this->_('Your submission was not saved due to one or more errors. Try again.'), 'alert-error'), // Comment post error message
			'redirectAfterPost' => true
			));

		return $out; 
	}

	/**
	 * Render a formatted date
	 *
	 * @param int|string $date If given a timestamp, it will be automatically formatted according to the 'date' field in PW
	 *	If given a string, then whatever format it is in will be kept. 
	 * @return string
	 *
	 */
	public function date($date) {

		if(is_int($date)) {
			// get date format from our 'date' field, for consistency
			$dateFormat = wire('fields')->get('date')->dateOutputFormat; 
			$date = FieldtypeDatetime::formatDate($date, $dateFormat);
		}

		return $this->span($date, 'date'); 
	}

	/**
	 * Render next and prev sibling links for a blog post
	 *
	 * @param Page|object $item The current blog post
	 * @return string The generated output
	 *
	 */
	public function linkNextPrev($next, $prev) {

		$prevLink = '';
		$nextLink = '';

		if($prev->id) $prevLink = $this->li($this->a($prev->url, "&laquo; " . $prev->title), 'link-prev'); 
			else $prevLink = $this->li('', 'link-prev'); 
		if($next->id) $nextLink = $this->li($this->a($next->url, $next->title . " &raquo;"), 'link-next');
			else $nextLink = $this->li('', 'link-next'); 

		return $this->ul($prevLink . $nextLink, 'link-next-prev'); 
	}

	/**
	 * Generate a photo gallery
	 *
	 * @param Pageimages $images
	 * @return string
	 *
	 */
	public function gallery(Pageimages $images) {

		// populates $config->styles and $config->scripts with needed files
		$this->modules->get('JqueryFancybox');

		// thumbnail width/height
		$twidth = (int) $this->thumbWidth;
		$theight = (int) $this->thumbHeight;

		$out = '';
		foreach($images as $image) {
			$thumb = $image->size($twidth, $theight); 
			$a = $this->a($image->url, $this->img($thumb), array(
				'rel' => 'gallery', 
				'class' => 'lightbox', 
				'title' => $image->description
				)); 
			$out .= $this->li($a);
		}

		if($out) $out = $this->ul($out, 'gallery'); 
		return $out; 
	}

	/**
	 * Render a list of comments
	 *
	 * If page is editable, then non-approved comments will be included (and identified) in the list.
	 *
	 * @param CommentArray $comments
	 * @param int $limit Optional limit of max comments to show
	 * @return string
	 *
	 */
	public function ___comments(CommentArray $comments, $limit = 0) {

		$out = '';
		$page = wire('page');
		$admin = $page->editable(); 
		$cnt = 0;

		foreach($comments as $comment) { 
			if(!$admin) if($comment->status != Comment::statusApproved) continue;  
			$out .= $this->li($this->comment($comment), array(
				'id' => 'comment' . $comment->id, 
				'class' => 'blog-comment')
				); 
			$cnt++;
			if($limit && $cnt >= $limit) break;
		} 

		if(!$out) return '';

		$headline = '';
		if($page->is('post')) $headline = $this->subhead(sprintf($this->_('%d Comments'), count($comments))); 
		$out = $headline . $this->ul($out, 'blog-comments'); 
		$pageNum = wire('input')->pageNum;

		// check if we should introduce pagination
		if($limit && (count($comments) > $limit || $pageNum > 1)) {

			if($pageNum > 2) $prevURL = $page->url . 'page' . ($pageNum-1);
				else if($pageNum > 1) $prevURL = $page->url;
				else $prevURL = '';

			if(count($comments) > $limit) $nextURL = $page->url . 'page' . ($pageNum+1);
				else $nextURL = '';

			$nav = '';
			if($prevURL) $nav .= $this->a($prevURL, $this->_('Back'), 'comments-pagination-back button') . ' ';
			if($nextURL) $nav .= $this->a($nextURL, $this->_('Next'), 'comments-pagination-next button') . ' ';

			if($nav) $out .= $this->p($nav, 'comments-pagination'); 
		}

		return $out; 
	}

	/**
	 * Render an individual comment
	 *
	 * Seen on: an individual blog post
	 *
	 * @param Comment $comment
	 * @return string
	 *
	 */
	public function ___comment(Comment $comment) {

		$cite = htmlentities(trim($comment->cite), ENT_QUOTES, "UTF-8");
		$date = $this->date($comment->created); 

		if($comment->page && $comment->page !== wire('page')) {
			$header = sprintf($this->_('%1$s replied to %2$s %3$s'), $cite, $comment->page->title, $date); // cite, title, date
			$header = $this->a("{$comment->page->url}#comment{$comment->id}", $header); 
		} else {
			$header = sprintf($this->_('Comment by %1$s on %2$s'), $cite, $date); // cite and date
		}

		if($comment->status == Comment::statusPending) {
			$header .= " " . $this->span($this->_('PENDING'), 'blog-comment-status-pending'); 

		} else if($comment->status == Comment::statusSpam) {
			$header .= " " . $this->span($this->_('SPAM'), 'blog-comment-status-spam'); 
		}

		$text = htmlentities(trim($comment->text), ENT_QUOTES, "UTF-8");
		$text = str_replace("\n\n", "</p><p>", $text);
		$text = str_replace("\n", $this->br(), $text);

		$out =  $this->blockquote(
			$this->p($header, 'blog-comment-head') . 
			$this->div($this->p($text), 'blog-comment-body') 
			);

		return $out; 
	}

	/**
	 * Render a list of categories, optionally showing a few posts from each
	 *
	 * As seen on: /categories/
	 *
	 * @param PageArray $categories
	 * @param int Number of posts to show from each category (default=0)
	 * @return string
	 *
	 */
	public function categories(PageArray $categories, $showNumPosts = 0) {

		if(!count($categories)) return $this->subhead($this->_("No categories to display")); 

		$li = '';

		foreach($categories as $category) {
			$li .= $this->li($this->category($category, $showNumPosts)); 
		}

		if($li) return $this->ul($li, 'categories'); 
		return '';
	}

	/**
	 * Render an individual category, optionally showing a few posts
	 *
	 * Seen on: /categories/
	 *
	 * @param Page $category
	 * @param int Number of posts to show from the category (default=0)
	 * @return string
	 *
	 */
	public function category(Page $category, $showNumPosts = 0) {

		if($showNumPosts) {
			$posts = wire('blog')->findPosts("categories=$category, limit=$showNumPosts"); 
			$numPosts = $posts->getTotal();
			$numPostsLabel = $this->numPosts($numPosts);
		} else {
			$posts = null;
			$numPosts = 0;
			$numPostsLabel = '';
		}

		$ul = '';

		if($numPosts) {

			foreach($posts as $item) {
				$ul .= $this->li($this->a($item->url, $item->title));
			}

			if($numPosts > $showNumPosts) $ul .= $this->li($this->linkMore($category->url));
			if($ul) $ul = $this->ul($ul, 'posts'); 
		} 

		$headline = $this->subhead($this->a($category->url, $category->title) . " $numPostsLabel");

		$out = $this->div("$headline$ul", 'category'); 

		return $out;
	}

	/**
	 * Render an author biography with photo where available
	 *
	 * @param Page $author
	 * @return string
	 *
	 */
	public function authorBio(Page $author) {

		$photo = $author->images->first();

		if($photo) {
			$photo = $this->a($photo->url, $this->img($photo->width($this->thumbWidth), 'author-photo'), 'lightbox');
		} else $photo = '';

		$onAuthor = wire('input')->urlSegment1 == $author->name; 
		$authorName = $author->get('title|name');
		$authorsPage = wire('pages')->get('template=authors'); 
		$authorURL = $authorsPage->url . $author->name . '/';
		$authorLink = $this->a($authorURL, $authorName); 

		$out = '';
		if(!$onAuthor) $out .= $this->subhead($authorLink); 
		$out .= $photo . $author->body;
		$out = $this->div($out, 'author'); 

		return $out; 
	}

	/**
	 * Render an archives list by year and month
	 *
	 * @param int $year
	 * @return string
	 *
	 */
	public function archives($year = 0) {

		if($year) {
			$firstYear = $year;
			$lastYear = $year;
		} else {
			$oldest = wire('blog')->getPost("date>0, sort=date");
			$newest = wire('blog')->getPost("date>0, sort=-date");
			if(!$newest->id) return $this->subhead($this->_('No archives to display')); 
			$firstYear = date('Y', $oldest->getUnformatted('date'));
			$lastYear = date('Y', $newest->getUnformatted('date'));
		}

		$out = "<ul class='archives'>";

		$archivesPage = wire('pages')->get('template=archives');
		$url = $archivesPage->url;

		for($year = $lastYear; $year >= $firstYear; $year--) {

			$out .= "<li>" . $this->subhead("<a href='$url$year/'>$year</a>");
			$out .= "<ul class='months'>";

			for($month = 1; $month <= 12; $month++) {

				$firstDay = strtotime("$year-$month-01");
				$lastDay = strtotime("+1 month", $firstDay)-1;

				$cnt = wire('blog')->findPosts("date>=$firstDay, date<=$lastDay, limit=2")->getTotal();
				if(!$cnt) continue;

				$monthName = date('F', $firstDay);
				$numPosts = $this->numPosts($cnt);
				$monthURL = $url . "$year/$month/";

				$out .= "<li>" . $this->a($monthURL, "$monthName $numPosts") . "</li>";
			}
			$out .= "</ul></li>";
		}

		$out .= "</ul>";

		return $out;
	}

	/**
	 * Render a breadcrumb trail
	 *
	 * @param array|PageArray $breadcrumbs array of $url => $title or Page
	 * @return string
	 *
	 */
	public function breadcrumbs($breadcrumbs) {
		$out = '';
		foreach($breadcrumbs as $url => $title) {
			if($title instanceof Page) {
				$page = $title;	
				$url = $page->url;
				$title = $page->title;
			}
			$out .= $this->li($this->a($url, $title));
		}

		if($out) $out = $this->ul($out, 'breadcrumbs'); 
		return $out; 
	}

	/**
	 * Render a 'View More' link
	 *
 	 * @param string $url URL to link to
	 * @param string $text Text to show (default = auto)
	 * @return string
	 *
	 */
	public function linkMore($url, $text = '') {
		if(empty($text)) $text = $this->_('View More');
		return $this->a($url, $text, 'more') . ' '; 
	}

	/**
	 * Render an RSS link
	 *
 	 * @param string $url URL to link to
	 * @param string $text Text to show (default = 'RSS')
	 * @return string
	 *
	 */
	public function linkRSS($url, $text = '') {
		if(empty($text)) $text = $this->_('RSS');
		return $this->a($url, $text, 'rss'); 
	}

	/**
	 * Render an label indicating quantity of posts
	 *
	 * @param int $n Number of posts
	 * @return string
	 *
	 */
	public function numPosts($n) {
		return $this->span(sprintf($this->_n('%d post', '%d posts', $n), $n), 'num-posts'); 
	}

	/**
	 * Render an label indicating the current page number
	 *
	 * @param int $n Page number
	 * @return string
	 *
	 */
	public function pageNum($n) {
		return $this->span(sprintf($this->_('Page %d'), $n), 'page-num'); 
	}

	/**
	 * Render secondary navigation bar
	 *
	 * @param array|PageArray $items May be an array of Page objects or array of ($url => $label)
	 * @param Page|string Current item that should be highlighted, may be Page or $url
	 * @return string
	 *
	 */
	public function subnav($items, $current = '') {

		$out = '';
		if($current instanceof Page) $current = $current->url;

		foreach($items as $url => $title) {

			if($title instanceof Page) {
				$page = $title;
				$title = $page->title;
				$url = $page->url;
			} 

			$class = $current == $url ? $this->getClassAttr('on') : '';
			$out .= $this->li($this->a($url, $title, $class), $class); 
		}

		if($out) $out = $this->ul($out, 'subnav links'); 
		return $out; 
	}

	/**
	 * Render pagination links for the given PageArray
	 *
	 * In this case, we're using the default PW MarkupPagerNav class, but customizing the classes
 	 * to use a Foundation pagination style.
	 *
	 * @param PageArray $items
	 * @return string
	 *
 	 */
	public function pagination(PageArray $items) {
		$class = $this->getClassAttr('pagination');
		$options = array(
			'listMarkup' => $this->ul('{out}', $class)
			);
		return $items->renderPager($options);
	}

	/**
	 * Render a primary headline
	 *
	 * @param string $text
	 * @return string
	 *
	 */
	public function headline($text) {
		return "<h1>$text</h2>";
	}

	/**
	 * Render a secondary headline
	 *
	 * @param string $text
	 * @return string
	 *
	 */
	public function subhead($text) {
		return "<h2>$text</h4>";
	}

	/**
	 * Render widgets for the current page
	 *
	 * @param Page $page
	 * @return string
	 *
	 */
	public function ___widgets(Page $page) {
		// first see if there are widgets assigned to the current $page
		$widgets = $page->widgets; 

		// if no widgets on current page, grab them from the homepage
		if(!$widgets || !count($widgets)) $widgets = wire('pages')->get('/')->widgets; 

		$out = '';

		// render each widget in order
		foreach($widgets as $widget) {
			$out .= $this->div($widget->render(), 'widget'); 
		}

		return $out; 
	}

	/**
	 * Render an admin widget
	 *
	 * This is displayed as the first widebar widget when an author is logged in
	 *
	 * @return string
	 *
	 */
	public function ___adminWidget() {

		$user = wire('user');
		if(!$user->isSuperuser() && !$user->hasRole('author')) return '';

		$adminURL = wire('config')->urls->admin;
		$page = wire('page');
		$posts = wire('pages')->get('template=posts');

		if($page->editable()) $editpage = $this->li($this->a($adminURL, $this->_('Edit this page'), 'edit-page')); 
			else $editPage = '';

		$ul = 	$this->ul(
			$editpage . 
			$this->li($this->a("{$adminURL}page/add/?parent_id={$posts->id}", $this->_('Create new blog post'), 'create-post')) . 
			$this->li($this->a("{$adminURL}profile/", $this->_('Edit your profile'), 'edit-profile')) . 
			$this->li($this->a("{$adminURL}login/logout/", $this->_('Logout'), 'logout')),
			'links'
			);

		$out = 	$this->div(
			$this->subhead($user->get('title|name')) . 
			$ul,
			'admin widget'
			);
			
		return $out; 
	}


}
