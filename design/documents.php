<?php

namespace robotpony\chronicleMD;

global $md;
$md = new \Parsedown();


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

	private static $default_options = array(
		'max-posts' => false,
		'sort-order' => 'newest'
	);


	public function __construct($section) {

		$section = str_replace('_', '/', $section);

		if (!($path = realpath(BLOG_ROOT . "/$section")))
			return warn("Section <em>$section</em> does not exist in <code>" . BLOG_ROOT . '</code>', 404);

		$this->path = $path;

		$this->scan();
	}

	public function files() { return $this->files; }

	private function scan() {

		$d = new \RecursiveDirectoryIterator($this->path);
		$i = new \RecursiveIteratorIterator($d);
		$filtered = new \RegexIterator($i, '/^.+\.md$/i',
			\RecursiveRegexIterator::GET_MATCH);
		foreach ($filtered as $file) {
			$this->files[] = new document(array_pop($file));
		}

		$this->files = $this->files;
	}
}

/* A single document

Provides access to the document content and metadata. This is what WordPress calls a `post`.

*/
class document {

	private $file;

	private $title,
			$date,
			$raw,
			$markdown = '';

	private $meta = array(),
			$attr = array();

	/**/
	public function __construct($path = '') {
		global $md;

		$this->file = $path;

		$this->attr = (object) array(
			'modified' => \filemtime($path)
		);
		$this->date = $this->attr->modified;
		$this->raw = \file_get_contents($path);
		$this->meta = $this->scan_document();
	}

	/**/
	public function title() { return $this->title; }
	/**/
	public function date($f = 'r') { return date($f, $this->attr->modified); }
	/**/
	public function body() { return $this->markdown; }

	/**/
	public function __call($n, $a) {
		return "not found - $n";
	}

	private function scan_document() {
		global $md;

		$parts = preg_split("/\n\n/", $this->raw);


		foreach ($parts as &$p) {
			if (empty($this->title))
				$this->title = $md->parse($p);
			elseif (preg_match("/([^:]+)\s+:\s+(.*)/", $p, $m)) {
				$this->meta[$m[1]] = $m[2];
			} else
				$this->markdown .= $p;

		}

		$this->markdown = $md->parse($this->markdown);
	}
}
