<?php

namespace robotpony\chronicleMD;

/* The document manager

Provides acccess to folders of documents.

*/
class documents {

	private static $sections = array();

	public static function __callStatic($section, $options) {

		// TODO: check sanity of $section
		// TODO: check sanity of options

		if (!array_key_exists($section, self::$sections))
			self::$sections[$section] = new section($section);

		return self::$sections[$section]->files();
	}

	/**/
}

/* One blog section (folder with documents) */
class section {

	private $path;
	private $files = array();
	private $settings;

	public function __construct($section) {

		$section = str_replace('_', '/', $section);

		if (!($path = realpath(BLOG_ROOT . "/$section")))
			throw new \Exception("Invalid section, $section does not exist in " . BLOG_ROOT, 404);

		$this->path = $path;

		$this->scan();
	}

	public function files() { return $this->files; }

	private function scan() {

		$d = new \RecursiveDirectoryIterator($this->path);
		$i = new \RecursiveIteratorIterator($d);
		$filtered = new \RegexIterator($i, '/^.+\.md$/i',
			\RecursiveRegexIterator::GET_MATCH);

		foreach ($filtered as $file)
			$this->files[] = new document(current($file));

		$this->files = $this->files;
	}
}

/* A single document

Provides access to the document content and metadata. This is what WordPress calls a `post`.

*/
class document {

	private $file;

	private $title = 'no title',
			$date = 'no date',
			$markdown = '(empty)',
			$raw;

	private $meta = array();

	/**/
	public function __construct($path = '') {
		$this->file = $path;

		$this->raw = file_get_contents($path);

		$this->title = strtok($this->raw, "\n");
	}

	/**/
	public function title() { return $this->title; }
	/**/
	public function date() { return $this->date; }
	/**/
	public function body() { return $this->markdown; }

	/**/
	public function __call($n, $a) {
		return "not found - $n";
	}
}
