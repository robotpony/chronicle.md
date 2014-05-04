<?php

namespace robotpony\chronicleMD;

/* # Chronicle 1.1 prototype

	This prototype outlines the classes and calling mechanisms (to prove them out). It
	currently generates debugging output for testing themes and evaluating the intended
	calling sequences.


	## Approach

	These classes use magic methods to simplify the calling sequence for theme APIs. Rather
	than force APIs to use parameters for common variable items,


	## Missing
*/


/* on <event>

	The event handling infrastructure for ChronicleMD
*/
class on {

	/* Handle user-defined events */
	public static function __callStatic($n, $a) {
		$p = count($a) ?
		' - ' . implode(', ', $a) : '';

		return "($n$p)";
	}
}

/* The document manager

Provides acccess to folders of documents.

*/
class documents {


	public static function __callStatic($n, $a) {
		return array(new document(), new document());
	}
}

/* A single document

Provides access to the document content and metadata. This is what WordPress calls a `post`.

*/
class document {

	public function __call($n, $a) {
		return "$n";
	}
}

/* The site theme

*/
class theme {

	public static function __callStatic($n, $a) {
		$p = count($a) ?
			' (' . implode(', ', $a) . ')' : '';
		return "$n.php$p\n";
	}
}


/* Global helpers */


function exception_handler($e) {
	$detail = json_encode($e, JSON_PRETTY_PRINT);
	print "<div class='error'>Exception {$e->getCode()} {$e->getMessage()} <pre>$detail</pre></div>";
}

set_exception_handler('robotpony\chronicleMD\exception_handler');