<?php

namespace robotpony\chronicleMD;

global $md;
$md = new \Parsedown();


/* The document manager

Provides acccess to folders of documents.

*/
class documents {

	public static $req = null;

	private static $sections = array();

	public static function __callStatic($section, $options = array()) {
		global $chronicle;

		// TODO: check sanity of $section
		// TODO: check sanity of options

		if (!array_key_exists($section, self::$sections)) {

			// load settings for requested section
			settings::load($section, $chronicle->section_settings);

			$options = $options ? array_shift($options) : array();

			// load requested section
			$s = new section($section, $options);

			self::$sections[$section] = $s;

		} else {

			// return cached version
			$s = self::$sections[$section];

			// update options for this use
			// 	- allows multiple uses (which may differ) on a single template page
			$s->set_options($options);
		}

		// Filter document set by request type

		if (self::$req->is_single())
			return $s->as_document(self::$req->path);
		elseif (self::$req->is_section())
			return $s->as_filtered(self::$req->folder);
		else
			throw new \Exception('Request does not make sense for ' . self::$req->url, 404);

		return $s;
	}
}


/* One blog section (folder with documents)

*/
class section
	implements \Iterator {

	private $path;
	private $index = null;
	private $from_cache = false;
	private $files = array();
	private $cursor;
	private $settings;

	private static $default_options = array(
		'max-posts' 	=> false,
		'sort-order' 	=> 'newest',
		'index' 		=> 'index.json',
		'cache-limit'	=> 60
	);

	/* Set up a new site section (folder) */
	public function __construct($section, $options = array()) {

		$this->set_options($options);

		$section = str_replace('_', '/', $section);

		if (!($path = realpath(BLOG_ROOT . '/' . $section)))
			return warn("Section <em>$section</em> does not exist in <code>" . BLOG_ROOT . '</code>', 404);

		$this->path = $path;

		if ($this->is_index_expired())
			$this->scan();
		else
			$this->load_index();

		$this->update_index();
	}

	// Set (and re set) the options for the section
	// 		- options are allowed to change for various in-template page uses
	public function set_options($options) {
		$this->settings = array_merge(
			section::$default_options,
			$options);
		$this->filtered = $this->files;
	}

	// Set the current document (filters the section)
	public function as_document($url) {

		$this->filtered = array_filter($this->files,
			function(&$v)
				use (&$url) {
					return $v->url() === $url;
			}
		);

		$this->rewind();

		return $this;
	}

	// Get the section filtered by a URL
	//		- max-posts is also applied
	public function as_filtered($url) {
		$ii = 0;
		$max = $this->settings['max-posts'];

		$this->filtered = array_filter($this->files,
			function(&$v)
				use (&$url, &$ii, $max) {

				return ($max == -1 || $ii++ < $max)
					&& stripos($v->url(), $url) === 0;
			}
		);

		$this->rewind();

		return $this;
	}

	/* Iterator interface */

	public function current() {
		return $this->filtered[$this->cursor];
	}
	public function key() {
		return $this->cursor;
	}
	public function next() {
		++$this->cursor;
	}
	public function previous() {
		--$this->cursor;
	}
	public function rewind() {
		$this->cursor = 0;
	}

	public function valid() {
		return isset($this->filtered[$this->cursor]);
	}

	/* Scan for files */
	private function scan() {

		$d = new \RecursiveDirectoryIterator($this->path);
		$i = new \RecursiveIteratorIterator($d);
		$filtered = new \RegexIterator($i, '/^.+\.(?:md|txt|text|markdown)$/i',
			\RecursiveRegexIterator::GET_MATCH);

		// add filtered set
		foreach ($filtered as $file)
			$files[] = new document(array_pop($file), count($this->files));

		// sort
		uasort($files, function($a, $b) {
			$a = $a->modified();
			$b = $b->modified();

			if ($a == $b) return 0;
			return ($a > $b) ? -1 : 1;
		});

		// re-index
		$ii = 0;
		foreach ($files as &$value)
			$this->files[] = &$value->reindex($ii++);

		$this->rewind(); // reset iterator
	}

	// does the index cache exist?
	private function index_exists() {
		if (!CACHE_ENABLED) return false;
		$this->index = $this->path . '/' . $this->settings['index'];
		return realpath($this->index) && file_exists($this->index);
	}

	// is the index cache expired?
	private function is_index_expired() {
		if (!CACHE_ENABLED) return false;
		if (!$this->index_exists()) return true;

		return (time() - filemtime($this->index) > $this->settings['cache-limit']);
	}

	// rewrite the index
	private function update_index() {
		if (!CACHE_ENABLED) return false;

		if (!$this->is_index_expired() || $this->from_cache) return true;

		if (!is_writable($this->path))
			return remind("Can't write index to {$this->index} (bad permissions).", error_get_last());

		$t = $this->index . '.tmp';
		$index = array();
		foreach ($this->files as &$f) $index[] = array(
			'file' 		=> $f->file,
			'url'		=> urlize($f->file),
			'modified' 	=> $f->modified,
			'at'		=> $f->at
		);

		file_put_contents($t, json_encode($index), LOCK_EX);
		rename($t, $this->index);
	}

	// load the index
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

	public	$file,
			$modified,
			$at = -1;
	private
			$url,
			$title,
			$date,
			$raw,
			$markdown = '';

	private $meta = array(
		'posted' => 'no date'
	);

	/* Set up a document object */
	public function __construct($o = '') {

		if (is_string($o)) { // from a path

			$this->file = $o;
			$this->modified = \filemtime($o);
			$this->url = ext('html', urlize($this->file));

		} elseif (is_object($o)) { // from a cached document object

			$this->file = $o->file;
			$this->url = ext('html', urlize($this->file));
			$this->modified = $o->modified;
			$this->at = $o->at;

		}
	}

	/* Document template functions */

	public function url() { return $this->url; }
	public function modified() { return $this->modified; }
	public function published() {
		$this->load_document();
		return $this->meta['posted'];
	}
	public function date($f = DEFAULT_DATE_FORMAT) {
		return date($f, $this->meta->modified);
	}
	public function title() {
		$this->load_document();
		return $this->title;
	}
	public function body() {
		$this->load_document();
		return $this->markdown;
	}

	/* Provide safe template use fallback */
	public function __call($n, $a) {
		return "not found - $n";
	}

	// reindex this document
	public function reindex($idx) { $this->at = $idx; return $this; }

	/* Load the document from a file*/
	private function load_document() {
		if (!empty($this->raw))
			return; // already loaded

		$this->raw = \file_get_contents($this->file);
		$this->scan_document();
	}

	/* Parse the markdown and metadata */
	private function scan_document() {
		global $md;

		$parts = preg_split("/\n\n/", $this->raw); // split into blocks

		$found_content = false;
		foreach ($parts as &$p) { // block-by-block

			if (empty($this->title))
				$this->title = $md->parse($p); // title from first line
			elseif (preg_match("/([^:]+)\s+:\s+(.*)$/", $p, $m) && ! $found_content) {
				$this->meta[$m[1]] = trim($m[2]); // metadata from DL items at document head (before content)
			} else { // otherwise it's content, which follows different non-header rules
				$this->markdown .= $p . "\n\n";
				$found_content = true;
			}
		}

		if (array_key_exists('posted', $this->meta) && !empty($this->meta['posted'])) {
			$t = $this->meta['posted'];
			$this->attr['modified'] = strtotime($t);
		}

		$this->markdown = $md->parse($this->markdown);
	}
}

/* Navigation helpers

*/
class navigation {
	public static function __callStatic($section, $p) {

		assert(count($p) === 1 && is_string($p[0]), 'Navigation expects a valid string type');

		$s = documents::$section();
		$tag = $p[0];

		switch ($tag) {

			case 'previous':

				$s->previous();

				if ($s->valid()) {
					$c = $s->current();
					return ext('html', urlize($c->file));
				}
				$s->next();

				return '';
			break;

			case 'next':
				$s->next();
				if ($s->valid()) {
					$c = $s->current();
					return ext('html', urlize($c->file));
				}
				$s->previous();
			break;

			default:
		}
	}
}
