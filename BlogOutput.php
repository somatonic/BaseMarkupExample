<?php

/**
 * Class that holds generated output and is ultimately called upon to render it
 *
 * This is nothing but a simple intermediary class to hold 'content' and 'sidebar'
 * output. It could just as easily be two variables outside of this class, but
 * thought this was a little cleaner way to manage it.
 *
 */
class BlogOutput extends WireData {

	public function __construct() {
		$this->set('content', '');
		$this->set('sidebar', '');
		$this->set('file', dirname(__FILE__) . '/master.php'); 
	}

	/**
	 * Send the final output 
	 *
	 */
	public function ___render() {
		@extract($this->fuel->getArray());
		include($this->file);
	}
}
