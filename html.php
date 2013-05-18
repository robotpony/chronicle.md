<?php /* Chronicle.md - Copyright (C) 2013 Bruce Alderson */


/* A simple HTML static class - generates HTML from simple code 
	
	Note: only use these if it produces better code than templates, HEREDOCs, or interpolated strings.
*/
class html {
	
	public static function __callStatic($n, $p) {
		return self::_node($n, array_shift($p), array_shift($p));
	}
	
	private static function _node($n, $v, $c) {
		if (empty($n) || is_numeric($n)) throw new Exception('Invalid node type', 500);
		$c = self::_class($c);
		return "<$n{$c}>$v</$n>";
	}
	private static function _class($c) {

		if (!$c) return '';
		elseif (is_string($c)) $c = array($c);
		
		if (is_array($c)) {
			$c = implode(' ', $c);
			$c = " class='$c'";
		} else
			$c = ''; // unknown type (could support non-class items in the 
		
		return $c;

	}
}