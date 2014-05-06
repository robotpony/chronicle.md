<?php

namespace robotpony\chronicleMD;


/* Global helpers */


function exception_handler($e) {
	$detail = json_encode($e, JSON_PRETTY_PRINT);
	print "<div class='error'>Exception {$e->getCode()} {$e->getMessage()} <pre>$detail</pre></div>";
}

set_exception_handler('robotpony\chronicleMD\exception_handler');