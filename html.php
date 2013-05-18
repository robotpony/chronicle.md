<?php /* Chronicle.md - Copyright (C) 2013 Bruce Alderson */


/* A simple HTML static class - generates HTML from simple code (not a replacement for templates, interpolation, etc.) */
class html {
	
	/* Output *any* HTML tag with attributes
		
		USAGE: 
		
		1. A simple node
		
			<?= html::p('Some text'); ?>
		
		2. A simple node with classes

			<?= html::h1('Some text', array('a', 'b', 'c')); ?>		
			
	*/
	public static function __callStatic($n, $p) {
		return self::_node($n, array_shift($p), array_shift($p));
	}
	
	/* ======================== Private helpers ======================== */
	
	// Return an HTML node
	private static function _node($n, $v, $c) {
		if (empty($n) || is_numeric($n)) throw new Exception('Invalid node type', 500);
		$a = self::_attr($c);
		return "<$n{$a}>$v</$n>";
	}
	//
	private static function _attr($c) {

		if (!$c) return '';
		elseif (is_string($c)) $c = array($c);
		
		if (is_array($c)) {
			$c = implode(' ', $c);
			$c = " class='$c'";
		} else
			$c = ''; // unknown type (should support non-class items in the 
		
		return $c;

	}
}