<?php

/**
 * A markup-generating class for basic HTML tags in ProcessWire.
 *
 * Copyright 2012 by Ryan Cramer
 *
 */
class BaseMarkup extends WireData {

	/**
	 * Classes that the attrStr() function should look for and substitute other classes for.
	 * 
	 * Format: 'class' => 'substitute class(s)'
	 *
	 */
	protected $classes = array('some-class' => 'some-other-class'); // as an example

	/**
	 * Substitute one class attribute for another in generated output
	 *
	 * Primarily used by the attrStr() function
	 *
	 * @param string|array $class If array, then it should be an array if class=>replacement
	 * @param string $replacement May include multiple replacements. Ignored if param 1 is an array.
	 *
	 */
	public function setClassAttr($class, $replacement = '') {
		if(is_array($class)) {
			$this->classes = array_merge($this->classes, $class); 
		} else {
			$this->classes[$class] = $replacement; 
		}
	}

	/**
	 * Get the substitute class for $class, or return $class if none.
	 *
	 * Primarily used by the attrStr() function
	 *
	 * @param string $class
	 * @return string
	 *
	 */
	public function getClassAttr($class) {
		if(array_key_exists($class, $this->classes)) return $this->classes[$class]; 
		return $class; 
	}

	/**
	 * Given an string containing a selector or just a class attribute value, conver to an array
	 *
	 * @param string $attrs
	 * @return array
	 *
	 */
	protected function attrArray($str) {

		if(!is_string($str)) return array();

		if(strpos($str, '=')) {
			// if there's an equals, we'll convert a selector to an array
			$selectors = new Selectors($attrs);
			$attrs = array();
			foreach($selectors as $selector) {
				$attrs[$selector->field] = $selector->value; 	
			}
			
		} else {
			// we assume it's a class attribute
			$attrs = array('class' => $str); 
		}

		return $attrs; 
	}

	/**
	 * Given an array or string of attributes, return a string of attributes for use in a markup tag
	 *
	 * It is assumed that the attributes are already entity encoded. However, if a quote or '<' appears
	 * in a value, this function will still attempt to entity encode it. 
	 *
	 * The given attributes may be an array, a selector string. If just a string with no equals sign
	 * is given, then it's assumed to be a 'class' attribute value.
	 *
	 * @param array|string $attrs Array of key=value attributes,
	 * 	or a string with a selector (key=value) string may be specified,
	 * 	or a string with no '=' is assumed to be a class attribute value.
	 * @param bool $removeEmpty When false, empty attributes will be left (default = true, remove them)
	 * @return string Returned string has a leading space (when there is at least one attribute)
	 *
	 */
	public function attrStr($attrs = array(), $removeEmpty = true) {

		if(is_string($attrs)) $attrs = $this->attrArray($attrs);
		if(!count($attrs)) return '';

		$out = '';

		foreach($attrs as $key => $value) {

			if($removeEmpty && empty($value)) continue; 

			if(strpos($value, "'") !== false || strpos($value, '"') !== false || strpos($value, '<') !== false) {
				$value = htmlentities($value, ENT_QUOTES, "UTF-8"); 
			}
	
			if($key == 'class') $value = $this->getClassAttr($value); 

			$out .= " $key='$value'";
		}

		return $out;
	}

	/**
	 * Return a populated <$tag> 
 	 *
	 * @param string $text Content to be wrapped in the tag
	 * @param string $tag Markup tag
	 * @param array|string $attrs 
	 * @return string
 	 *
	 */
	public function wrap($text, $tag, $attrs = array()) {
		$attrStr = $this->attrStr($attrs); 	
		return "<$tag$attrStr>$text</$tag>";
	}

	/**
	 * Return a populated <a> tag
 	 *
	 * @param string|Page $href URL to link to or a Page object
	 * @param string $text Anchor text (omit if Page)
	 * @param array $attrs 
	 * @return string
 	 *
	 */
	public function a($href, $text = '', $attrs = array()) {
		if(is_object($href) && $href instanceof Page) {
			$href = $page->url;
			if(!is_string($text) || (is_string($text) && !strlen($text))) $text = $page->title;
			if(empty($attrs) && is_array($text)) $attrs = $text; 
		}
		$attrStr = $this->attrStr($attrs);
		return "<a href='$href'$attrStr>$text</a>";
	}

	/**
	 * Given a Pageimage or URL, return a populated <img> tag
	 *
	 * @param Pageimage|string 
	 * @param array|string $attrs 
	 * @return string
	 *
	 */
	public function img($img, $attrs = array()) {

		if(!is_array($attrs)) $attrs = $this->attrArray($attrs);

		if(is_string($img)) {
			$attrs['src'] = $img;
			if(empty($attrs['alt'])) $attrs['alt'] = '';

		} else if($img instanceof Pageimage) {
			$attrs['src'] = $img->url; 
			if(empty($attrs['width'])) $attrs['width'] = $img->width();
			if(empty($attrs['height'])) $attrs['height'] = $img->height();
			if(empty($attrs['alt'])) $attrs['alt'] = $img->description;
		}

		$attrStr = $this->attrStr($attrs, false);
		return "<img$attrStr />";
	}

	/**
	 * Other specific tags as indicated
	 *
	 */
	public function p($text, $attrs = array()) { return $this->wrap($text, 'p', $attrs); }
	public function span($text, $attrs = array()) { return $this->wrap($text, 'span', $attrs); }
	public function div($text, $attrs = array()) { return $this->wrap($text, 'div', $attrs); }
	public function ul($text, $attrs = array()) { return $this->wrap($text, 'ul', $attrs); }
	public function li($text, $attrs = array()) { return $this->wrap($text, 'li', $attrs); }
	public function blockquote($text, $attrs = array()) { return $this->wrap($text, 'blockquote', $attrs); }
	public function br() { return '<br />'; }
}
