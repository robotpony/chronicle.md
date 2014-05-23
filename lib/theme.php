<?php

namespace robotpony\chronicleMD;

/* The site theme

*/
class theme {
	
	const default_template = 'index.php';
	
	public static function __callStatic($n, $a) {
		$p = count($a) ?
			' (' . implode(', ', $a) . ')' : '';
		return "$n.php$p\n";
	}
	
	/* Render the given theme file 
		
	*/
	public static function render($for = '', $template = null) {
		global $chronicle;

		if (!$template)
			$template = theme::default_template;

		$t = theme::template_path($for, $template);
		require_once $t;
	}
	
	/* Generate the best template path possible
		
		- attempt the template file close to the request location (e.g., blog/index.php)
		- attempt the root template file
		
	*/
	public static function template_path($base, $template) {

		// attempt loading the template from various locations

		if (!($t = pathize(array($base, $template)))) // most specific path
			$t = pathize(array($template)); // generic path
			
		if (!$t)
			throw new \Exception("Missing theme template for $template in $base", 500);

		return $t;
	}
}

