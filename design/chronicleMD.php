<?php

namespace robotpony\chronicleMD;

class documents {


	public static function __callStatic($n, $a) {
		return array(new post(), new post());
	}
}

class post {

	public function __call($n, $a) {
		return "$n\n";
	}
}

class theme {

	public static function __callStatic($n, $a) {
		$p = count($a) ?
			' - ' . implode(', ', $a) : '';
		return "$n$p\n";
	}
}




function exception_handler($e) {
	$detail = json_encode($e, JSON_PRETTY_PRINT);
	print "<div class='error'>Exception {$e->getCode()} {$e->getMessage()} <pre>$detail</pre></div>";
}

set_exception_handler('robotpony\chronicleMD\exception_handler');