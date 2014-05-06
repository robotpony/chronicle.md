<?php

namespace robotpony\chronicleMD;

function dump() {
	$p = func_get_args();
	print "<pre>";
	foreach ($p as $v) {
		if (is_object($v) || is_array($v))
			print(json_encode($v, JSON_PRETTY_PRINT));
		else
			print("$v\n");
	}
	print "</pre>";
}




/* Global helpers */


function exception_handler($e) {
	$detail = json_encode($e, JSON_PRETTY_PRINT);
	print "<div class='error'>Exception {$e->getCode()} {$e->getMessage()} <pre>$detail</pre></div>";
}

set_exception_handler('robotpony\chronicleMD\exception_handler');