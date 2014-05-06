<?php

namespace robotpony\chronicleMD;

/* The document manager

Provides acccess to folders of documents.

*/
class documents
	extends \DirectoryIterator {


	public static function __callStatic($n, $a) {
		return array(new document(), new document());
	}

	/**/
}

/* A single document

Provides access to the document content and metadata. This is what WordPress calls a `post`.

*/
class document {

	private $file;
	private $markdown;

	public function __construct($path = '') {

	}

	public function __call($n, $a) {
		return "$n";
	}
}
