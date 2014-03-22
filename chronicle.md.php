<?php /* Chronicle.md - Copyright (C) 2013 Bruce Alderson */

namespace napkinware\chronicle;

use napkinware\presto as presto;

/* Chronicle is a little markdown site engine built in PHP. See README.md for docs. */

require 'settings.php';
require 'lister.php';
require 'html.php';

class site {

	public $settings;	// Values from various settings files

	public $req;		// The request itself
	private $resp;		// The pending response

	public $file;		// The requested document (file)
	private $posts	= '';	// The post(s)
	private $html	= '';	// The resultant HTML

	public $nav;		// Site navigation

	public $iterator = 0; // Post iterator

	public $trace = 1;	// trace mode

	/* Sets up the Chronicle site */
	public function __construct() {

		try {

			$this->resp = new presto\Response(); 		// ensure a response is possible
			$this->settings = new siteSettings(); 	// load settings
			$this->parseRequest(); 					// determine what was requested
			$this->loadContent(); 					// load content

		} catch( Exception $e ) {
			$this->showError($e);
		}

	}

	/* Main handler - makes site happen */
	public function go() {

		try {
			// render the site
			$this->render();
		} catch( Exception $e ) {
			$this->showError($e);
		}
	}

	/* ================================= TEMPLATE functions ================================ */

	/* Return the content (marked up if possible) */
	public function __toString() { return $this->defaultOutput(); }
	public function pageContent() { return $this->defaultOutput(); }

	/* Handy template functions */

	/* Get a list of pages based on the current request (and params) */
	public function pageList() {}
	/* Get the root site navigation */
	public function siteNav() {}

	/* Get the navigation related to the page (next/prev, etc.) */
	public function nextNav() { return $this->nav->next; }
	public function prevNav() { return $this->nav->prev; }

	public function pageTitle() {
		if (count($this->posts) === 1)
			return $this->posts[0]->title;
		else
			return $this->settings->site->name;
	}

	/* Get the page type (string) */
	public function pageType() { return trim(str_replace('/', ' ', $this->file->base)); }

	/* Get the site last updated date */
	public function lastUpdated() { return date('r', filemtime($this->nav->files[0])); }

	/* Get the next post object */
	public function nextPost() {
		$count = count($this->nav->files);
		if ($this->iterator + 1 > $count) return false;
		return $this->posts[ $this->iterator++ ];
	}
	/* Reset the internal post count */
	public function resetPosts() { $this->iterator = 0; }

	public function debug() { return prettyPrint(json_encode($this)); }

	/* ======================== Startup and other helper functions ======================== */

	// Process the request (into class objects and structs)
	private function parseRequest() {

		// Determine request to document/template mappings

		$this->req = new presto\Request();
		$s = $this->req->scheme();

		// determine the URL (or default)

		$url = $this->req->uri === '/' ? $this->settings->site->blog : $this->req->uri;

		// extract feed parameters (if any)

		$isFeed = $s->type === 'xml';
		if ($isFeed)
			$url = $this->settings->site->blog;

		// get the local file path

		$r = preg_replace('#(?:feed|feed\.xml|feed\/|page\/.*?|)$#', '', API_BASE . $url);
		$f = realpath($r);

		// parse out page number (if there is one)

		$p = $this->req->get('p', false);
		$p = is_object($p) ? $p->scalar : 0;

		// parse out base path

		$segments = explode('/', $url);
		$segments = array_filter($segments);
		$base = (count($segments) > 0) && strlen($segments[1]) > 0 ? $segments[1] : $this->settings->site->blog;

		// build the requested file/folder object

		$this->file = (object) array(
			'segments'	=> $segments,
			'via'		=> $r,
			'path' 		=> $f,
			'url'		=> $url,
			'base'		=> str_replace('//', '', "/$base/"),
			'type' 		=> $s->type,
			'isFeed'	=> $isFeed,
			'isPaged'	=> $s->type === 'page',
			'page'		=> $p,
			'exists'	=> (boolean) file_exists($f),
			'isFile'	=> (boolean) is_file($f),
			'isFolder'	=> (boolean) is_dir($f)
		);

		$this->template = (object) array( /* template struct */
			'scheme' => $s,
			'default_template' =>  $this->file->isFeed ? 'xml.php' : 'index.php'
		);
	}

	/* Load the content specified by the request */
	private function loadContent() {

		if (!$this->file->exists)
			throw new Exception("Not found: {$this->req->uri}", 404);

		if ($this->file->isFile) {

			$this->posts[] = $this->page($this->file->path, $this->file->base);

			$this->nav = lister::relativeNav(
				$this->file->url,
				$this->file->path,
				$this->file->base);

			$this->nav->files = $this->nav;

		} elseif ($this->file->isFolder) {

			$max = $this->file->isFeed ?
				$this->settings->site->feedPosts :
				$this->settings->site->homePosts;

			$sort = strlen($this->settings->site->sort) > 0 ? $this->settings->site->sort : '';

			$this->nav = lister::folder($this->file->path, $this->file->url,
										$this->file->page, $max, $sort);

			foreach ($this->nav->files as $f)
				$this->posts[] = $this->page($f, $this->file->base);

		} else
			throw new Exception("Not sure what to do with {$this->file->path}, as it does not seem to be a page or listing", 404);

	}

	/* Render a page template */
	private function render() {
		$t = $this->template->scheme->file;

		if (!stream_resolve_include_path($t)) {
			$t = $this->template->default_template;
			if (!stream_resolve_include_path($t))
				throw new Exception("No suitable template found (tried $t and {$this->template->scheme->file} in " . get_include_path() . ')', 500);
		}

		global $chronicle; // this is the name of the Chronicle object for use in the templaces
		$chronicle = $this;

		include_once($t);
		presto_lib::_trace("Loaded template $t");
	}

	/* Private: generate output content */
	private function defaultOutput() {

		if (!is_array($this->posts))
			$this->posts[] = $this->posts;

		foreach ($this->posts as $post)
			$this->html .= "\n<section>\n" . $post->content . "\n</section>\n";

		return $this->html;
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
		if (!file_exists($t)) throw new Exception("Not found: $t", 404);
		$c = file_get_contents($t);
		return $c;
	}
	/* Mark up one chunk of content */
	private function markup($content, $source) {

		$call = 'handle_' . pathinfo($source, PATHINFO_EXTENSION);

		if (!method_exists($this, $call)) {
			// skip processing types we know nothing about (it's ok, plain text returned)
			presto_lib::_trace("Skipping content handler for .{$this->type}, could not find {$call}()");
			return $content;
		}

		return $this->$call($content);
	}

	/* Private: markup (by type) handler functions */

	private function handle_md($t) {
		if (!include_once('lib/markdown/markdown.php')) return $t;
		return Markdown($t);
	}
	private function handle_html($t) { return $t; }
	private function handle_php($t) { return $t; }

	// Show an error condition (on an error page)
	private function showError($e, $p = 'error.php') {
		if ($this->trace) {
			print '<pre>Fatal error';
			print "\n(normally this would redirect to $p)\n\n";
			print_r($e);
			print_r($this);
			print '</pre>';
			die();
		}
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
