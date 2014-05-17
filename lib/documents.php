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
		global $chronicle;

		// TODO: check sanity of $section
		// TODO: check sanity of options

		if (!array_key_exists($section, self::$sections)) {
			// TODO: consider caching the scan in folder JSON
		}

		$o = count($options) === 1 ? $options[0] : array();
		self::$sections[$section] = new section($section, $o);

		settings::load($section, $chronicle->section_settings);

		return self::$sections[$section]->files();
	}

	/**/
}

/* Navigation helpers

*/
class navigation {
	public static function next() {}
	public static function previous() {}
}

/* One blog section (folder with documents) */
class section {

	private $path;
	private $index = null;
	private $from_cache = false;
	private $files = array();
	private $settings;

	private static $default_options = array(
		'max-posts' 	=> false,
		'sort-order' 	=> 'newest',
		'index' 		=> 'index.json',
		'cache-limit'	=> 60
	);

	/* Set up a new site section (folder) */
	public function __construct($section, $options = array()) {

		$this->settings = array_merge(
			section::$default_options,
			$options);

		$section = str_replace('_', '/', $section);

		if (!($path = realpath(BLOG_ROOT . "/$section")))
			return warn("Section <em>$section</em> does not exist in <code>" . BLOG_ROOT . '</code>', 404);

		$this->path = $path;

		if ($this->is_index_expired())
			$this->scan();
		else
			$this->load_index();

		$this->update_index();
		$this->filter();
	}

	/**/
	public function files() { return $this->files; }

	/**/
	private function scan() {

		$d = new \RecursiveDirectoryIterator($this->path);
		$i = new \RecursiveIteratorIterator($d);
		$filtered = new \RegexIterator($i, '/^.+\.md$/i',
			\RecursiveRegexIterator::GET_MATCH);

		foreach ($filtered as $file)
			$this->files[] = new document(array_pop($file));

		uasort($this->files, function($a, $b) {
			$a = $a->modified();
			$b = $b->modified();

			if ($a == $b) return 0;
			return ($a > $b) ? -1 : 1;
		});

	}

	private function filter() {
		if (!is_array($this->files) || !count($this->files)) return false;

		if ($this->settings['max-posts'])
			$this->files = array_slice($this->files, 0, $this->settings['max-posts']);
	}

	private function index_exists() {
		if (!CACHE_ENABLED) return false;
		$this->index = $this->path . '/' . $this->settings['index'];
		return realpath($this->index);
	}
	private function is_index_expired() {
		if (!CACHE_ENABLED) return false;
		if (!$this->index_exists()) return true;

		return (time() - filemtime($this->index) > $this->settings['cache-limit']);
	}
	private function update_index() {
		if (!CACHE_ENABLED) return false;

		if (!$this->is_index_expired() || $this->from_cache) return true;

		if (is_writable($this->path))
			return file_put_contents($this->index, json_encode($this->files));
		else
			return remind("Can't write index to {$this->index} (bad permissions).", error_get_last());
	}

	private function load_index() {
		if (!CACHE_ENABLED) return false;

		// TODO - checks

		$data = file_get_contents($this->index);
		$files = json_decode($data);

		foreach ($files as $file)
			$this->files[] = new document($file);

		$this->from_cache = true;
	}

}

/* A single document

Provides access to the document content and metadata. This is what WordPress calls a `post`.

*/
class document {

	public $file;
	private
			$title,
			$date,
			$raw,
			$markdown = '';

	private $meta = array(),
			$attr = array();

	/* Set up a document object */
	public function __construct($o = '') {

		if (is_string($o)) {
			$this->file = $o;
			$this->attr = (object) array(
				'modified' => \filemtime($o)
			);
			$this->date = $this->attr->modified;
		} elseif (is_object($o)) {
			$this->file = $o->file;
			$this->attr = (object) array(
				'modified' => \filemtime($o->file)
			);
			$this->date = $this->attr->modified;
		}
	}

	/**/
	public function modified() { return $this->attr->modified; }
	/**/
	public function date($f = 'r') { return date($f, $this->attr->modified); }
	/**/
	public function title() {
		$this->load_document();
		return $this->title;
	}
	/**/
	public function body() {
		$this->load_document();
		return $this->markdown;
	}

	/**/
	public function __call($n, $a) {
		return "not found - $n";
	}

	/**/
	private function load_document() {
		if (!empty($this->raw))
			return; // already loaded

		$this->raw = \file_get_contents($this->file);
		$this->meta = $this->scan_document();
	}
	/**/
	private function scan_document() {
		global $md;

		$parts = preg_split("/\n\n/", $this->raw);

		$found_content = false;
		foreach ($parts as &$p) {
			if (empty($this->title))
				$this->title = $md->parse($p);
			elseif (preg_match("/([^:]+)\s+:\s+(.*)/", $p, $m) && ! $found_content)
				// grab header meta data
				$this->meta[$m[1]] = trim($m[2]);
			else {
				$this->markdown .= $p . "\n\n";
				$found_content = true;
			}
		}

		if (array_key_exists('posted', $this->meta) && !empty($this->meta['posted'])) {
			$t = $this->meta['posted'];
			$this->attr->modified = strtotime($t);
		}

		$this->markdown = $md->parse($this->markdown);
	}
}
