<?php

namespace robotpony\chronicleMD;

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
