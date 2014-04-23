<?php

/**
 * Modifies some default blog Markup for Zurb Foundation-specific markup and classes
 *
 */
class BlogMarkupFoundation extends BlogMarkup {

	/**
	 * Initialize variables and setup class replacements
	 *
	 */
	public function __construct() {

		// parent constructor must be called first
		parent::__construct();

		// override thumbnail dimensions of you want to
		$this->set('thumbWidth', 100);
		$this->set('thumbHeight', 100);

		// class substitutions for Zurb foundation styles
		$this->setClassAttr(array(
			'num-posts' => 'white label num-posts',
			'page-num' => 'white label page-num',
			'date' => 'white label date',
			'alert-success' => 'alert-box success',
			'alert-error' => 'alert-box error',
			'link-next-prev' => 'link-next-prev block-grid two-up',
			'pagination' => 'pagination',
			));
		
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
		// override to convert pagination to use Zurb Foundation specific classes
		$class = $this->getClassAttr('pagination');
		$options = array(
			'listMarkup' => $this->ul('{out}', $class),
			'separatorItemClass' => 'unavailable',
			'currentItemClass' => 'current'
			);
		return $items->renderPager($options);
	}

	/**
	 * Render a primary headline: override to change the tag from h1 to h2
	 *
	 * @param string $text
	 * @return string
	 *
	 */
	public function headline($text) {
		return "<h2 class='headline'>$text</h2>";
	}

	/**
	 * Render a secondary headline: override to change the tag from h2 to h4
	 *
	 * @param string $text
	 * @return string
	 *
	 */
	public function subhead($text) {
		return "<h4 class='subhead'>$text</h4>";
	}

}
