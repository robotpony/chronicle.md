<?php /* Chronicle.md - Copyright (C) 2014 Bruce Alderson */

namespace napkinware\chronicle;

use napkinware\presto as presto;

/* Chronicle is a little markdown site engine built in PHP. See README.md for docs. */

require CHRONIC_BASE.'/settings.php';
require CHRONIC_BASE.'/entries.php';

class site {

	public $settings;				// Site settings

	public $req;						// The HTTP request
	private $resp;					// The HTTP response

	public $document;				// The requested document
	private $entries = null; //		// The list of entries related to the requested document

	private $posts	= array();		// The posts (if any), based on the entries found (TODO - ???)

	public $iterator = 0; 			// The current post iterator

	public $trace = 1;				// Trace mode enables extra logging + debugging

	/* Sets up the Chronicle site */
	public function __construct() {

		try {

			$this->resp = new presto\Response(); 		// ensure a response is possible
			$this->settings = new siteSettings(); 	// load settings
			$this->requestify(); 					// determine what was requested
			$this->loadContent(); 					// load content

		} catch( \Exception $e ) {
			$this->handleError($e);
		}

	}

	/* Main handler - makes site happen */
	public function generate() {

		try {
			// render the site
			$this->render();
		} catch( \Exception $e ) {
			$this->handleError($e);
		}
	}

	/* ================================= TEMPLATE functions ================================ */

	/* Handy template functions */

	public function showPart($p) {
		global $chronic;
		$chronic = $this;

		include SITE_BASE . "/$p";
	}

	/* Get the navigation related to the page (next/prev, etc.) */
	public function nextNav() { }
	public function prevNav() { }

	public function pageTitle() {
		return (count($this->posts) === 1) ? $this->posts[0]->title :
			$this->settings->site->name;
	}

	/* Get the page type (string) */
	public function pageType() { return trim(str_replace('/', ' ', $this->document->base)); }

	/* Get the site last updated date */
	public function lastUpdated() { return date('r', filemtime(0)); /* TODO  - removed broken nav object, replace */ }

	/* Get the next post object */
	public function nextPost() {
		$count = count($this->posts);
		if ($this->iterator + 1 > $count) return false;
		return $this->posts[ $this->iterator++ ];
	}
	/* Reset the internal post count */
	public function resetPosts() { $this->iterator = 0; }

	public function postList() {
		return array_map(function($v) {
			return (object) array(
				'title' => $v->title,
				'url' => $v->url
			);
		}, $this->posts);
	}

	public function debugInfo($type = null) {
		if (is_null($type))
			return json_encode($this, JSON_PRETTY_PRINT);
		elseif (property_exists($this, $type))
			return json_encode($this->$type, JSON_PRETTY_PRINT);
		else
			return "No debug information available for $type";
	}



	/* ======================== Startup and other helper functions ======================== */

	// Process the request (into class objects and structs)
	private function requestify() {

		// Determine request to document/template mappings

		$this->req = new presto\Request();
		$s = $this->req->scheme();

		// determine the URL (or default)

		$url = $this->req->uri === '/' ? $this->settings->site->blog : $this->req->uri;

		// extract feed parameters (if any)

		if (($isFeed = $s->type === 'xml'))
			$url = $this->settings->site->blog;

		// get the local file path

		$r = preg_replace('#(?:feed|feed\.xml|feed\/|page\/.*?|)$#', '', SITE_BASE . $url);
		$f = realpath($r);

		// parse out page number (if there is one)

		$p = $this->req->get('p', false);
		$p = is_object($p) ? $p->scalar : 0;

		// parse out base path

		$segments = explode('/', $url);
		$segments = array_filter($segments);
		$base = (count($segments) > 0) && strlen($segments[1]) > 0 ? $segments[1] : $this->settings->site->blog;

		// build the requested file/folder object

		$this->document = (object) array(
			'segments'	=> $segments,
			'via'		=> $r,
			'path' 		=> $f,
			'url'		=> $url,
			'base'		=> str_replace('//', '', "/$base/"),
			'type' 		=> $s->type,
			'isFeed'		=> $isFeed,
			'isPaged'	=> $s->type === 'page',
			'page'		=> $p,
			'exists'		=> (boolean) file_exists($f),
			'isFile'		=> (boolean) is_file($f),
			'isFolder'	=> (boolean) is_dir($f)
		);

		$this->template = (object) array( /* template struct */
			'scheme' => $s,
			'default_template' =>  $this->document->isFeed ? 'xml.php' : 'index.php'
		);
	}

	/* Load the content specified by the request */
	private function loadContent() {

		if (!$this->document->exists)
			throw new \Exception("<code>{$this->req->uri}</code> not found in <code>{$this->document->via}</code>", 404);

		if ($this->document->isFile) {

			$this->posts[] = $this->page($this->document->path, $this->document->base);

		} elseif ($this->document->isFolder) {

			$this->document->limit = $this->document->isFeed ?
				$this->settings->site->feedPosts :
				$this->settings->site->homePosts;

			$this->entries = new entries($this->document);

		} else
			throw new \Exception("Not sure what to do with {$this->document->path}, as it does not seem to be a page or listing", 404);

	}

	/* Render a page template */
	private function render() {
		$t = $this->template->scheme->file;

		if (!stream_resolve_include_path($t)) {
			$t = $this->template->default_template;
			if (!stream_resolve_include_path($t))
				throw new \Exception("No suitable template found (tried $t and {$this->template->scheme->document} in " . get_include_path() . ')', 500);
		}

		global $chronic;
		$chronic= $this;

		include_once($t);
		presto\trace("Loaded template $t");
	}

	/* Private: load one file into a struct */
	private function page($f, $url) {

		$p = $this->load_page($f);

		/* Strip out page metadata

			The metadata is available to page templates via the page object (and APIs). The
			remaining content is the page body itself.

			The parsing expects:

				# Title

				metadata-field
				: value

				...

				...content...

			The metadata includes the post date, page type, etc.
		*/

		// Strip out title
		$title = strip_chunk("^(?:(.*?)\n[=]+\n\n|#[\s]+(.*?)[\n]+)", $p); // pull title out
		$anchor = strip_chunk("\[(.*?)\]", $title); // pull title anchor out of heading (if there is one)
		if ($anchor) $title = $anchor;

		// strip out specific DL items
		$date = strip_chunk("^posted(?:\s+|)\n: (.*?)\n\n", $p);
		$categories = explode(', ', strip_chunk("^categories(?:\s+|)\n: (.*?)\n\n", $p));
		$type = strip_chunk("^type(?:\s+|)\n: (.*?)\n\n", $p);

		$banner = strip_chunk("^banner(?:\s+|)\n: (.*?)\n\n", $p);

		// simple image plugin syntax

		// [image: some-image.png]
		$p = preg_replace("/\[(image):\s+([^\]]+)\]/", "<img src='/images/$2' title='$2 $1' />", $p);

		$parts = explode($url, $f);

		// build a page object
		return (object) array(
			'file'		=> $f,
			'url'		=> $url . end($parts),
			'anchor'	=> $anchor,
			'text'		=> $p,
			'content' 	=> $this->markup($p, $f),
			'excerpt' 	=> $this->markup(get_snippet($p, 100) . '...', $f),
			'title' 	=> $title,
			'published' => $date,
			'modified'	=> date('r', filemtime($f)),
			'posted'	=> $date,
			'guid'		=> md5($url.$p),
			'author'	=> '',
			'categories' => $categories,
			'link' 		=> '',
			'type'		=> $type . ' ' . str_replace('/', '', $url),
			'banner'	=> trim($banner),
			'comments'	=> 0
		);
	}
	/* Private: Load the current page */
	private function load_page($t, $w = '') {
		if (!file_exists($t)) throw new \Exception("Not found: $t", 404);
		$c = file_get_contents($t);
		return $c;
	}
	/* Mark up one chunk of content */
	private function markup($content, $source) {

		$call = 'handle_' . pathinfo($source, PATHINFO_EXTENSION);

		if (!method_exists($this, $call)) {
			// skip processing types we know nothing about (it's ok, plain text returned)
			presto\trace("Skipping content handler for .{$this->type}, could not find {$call}()");
			return $content;
		}

		return $this->$call($content);
	}

	/* Private: markup (by type) handler functions */

	private function handle_md($t) {
		if (!include_once(LIB_BASE . '/parsedown/Parsedown.php')) return $t;

		$mdizer = new \Parsedown();
		return $mdizer->parse($t);
	}
	private function handle_html($t) { return $t; }
	private function handle_php($t) { return $t; }

	// Show an error condition (on an error page)
	private function handleError($e, $p = 'error.php') {
		if ($this->trace)
			throw $e; // bubble up to trace

		// handle with a custom page
		$this->resp->redirect($p, array('e' => $m, 'c' => $c));
	}



}

/* Simple log trace */
function _trace() { error_log(implode(' ', array('Chronicle.md', json_encode(func_get_args())))); }

/* ======================== Text helper functions ======================== */
/* (these should get moved elsewhere, and fully baked) */

/* Get a chunk from a string */
function get_chunk($pattern, &$string) {
	if (!preg_match("/$pattern/m", $string, $m))
		return '';

	return end($m);
}

/* Strip (and get) a chunk from a string */
function strip_chunk($pattern, &$string) {
	if (!preg_match("/$pattern/m", $string, $m))
		return '';

	$string = str_replace($m[0], '', $string);
	return end($m); // return the last match, allowing one capture
}

/* Get a snippet */
function get_snippet( $s, $wc = 10 ) {
	return implode('', array_slice( preg_split(	'/([\s,\.;\?\!]+)/',
			$s,
			$wc * 2 + 1,
			PREG_SPLIT_DELIM_CAPTURE
		),
		0,
		$wc * 2 - 1
	));
}
