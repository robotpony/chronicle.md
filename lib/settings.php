<?php

namespace robotpony\chronicleMD;

/* Settings


*/
class settings {


	public static function __callStatic($n, $a) {
		$p = count($a) ?
		' - ' . implode(', ', $a) : '';

		return "($n$p)";
	}

	/**/
}
