<?php

namespace robotpony\chronicleMD;

/* The site theme

*/
class theme {

	public static function __callStatic($n, $a) {
		$p = count($a) ?
			' (' . implode(', ', $a) . ')' : '';
		return "$n.php$p\n";
	}
}

