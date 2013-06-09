<?php /* Chronicle.md - Copyright (C) 2013 Bruce Alderson */


/* Chronicle is a little markdown site engine built in PHP. See README.md for docs. */

require 'settings.php';
require 'lister.php';
require 'html.php';

class ChronicleMD {

	public $settings;	// Values from various settings files

	private $req;		// The request itself
	private $resp;		// The pending response

	private $file;		// The requested document (file)
	private $posts;		// The post(s)
	private $html;		// The resultant HTML
	
	private $nav;		// Site navigation
	
	private $iterator = 0;

	/* Sets up the Chronicle site */
	public function __construct() {

		try {
			
			$this->resp = new Response(); 			// ensure a response is possible
			$this->parseRequest(); 					// determine what was requested
			$this->settings = new siteSettings(); 	// load settings
			$this->loadContent(); 					// load content

		} catch( Exception $e ) {
			$this->showError($e->getMessage(),  $e->getCode());
		}

	}
	
	/* Main handler - makes site happen */
	public function go() {
	
		try {

			$this->render();
			
		} catch( Exception $e ) {
			$this->showError($e->getMessage(),  $e->getCode());
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
	
	public function lastUpdated() { return date('r', filemtime($this->nav->files[0])); }
	
	public function nextPost() {
		$posts = count($this->nav->files);

		if ($this->iterator >= $posts) return false;		
		return $this->posts[ ++$this->iterator ];
	}


	/* ======================== Startup and other helper functions ======================== */

	// Process the request (into class objects and structs)
	private function parseRequest() {

		// Determine request to document/template mappings

		$this->req = new Request();
		$s = $this->req->scheme();
		$f = preg_replace('/\/feed(?:.xml|)$/', '', API_BASE . $this->req->uri);

		$this->posts = '';
		$this->html = '';

		$this->file = (object) array( /* document/file struct */
			'file' 		=> $f,
			'path' 		=> $f,
			'type' 		=> $s->type,
			'isFeed'	=> $s->type == 'xml',
			'handler'	=> "handle_{$s->type}",
			'exists'	=> (boolean) file_exists($f),
			'isFile'	=> (boolean) is_file($f),
			'isFolder'	=> (boolean) is_dir($f)
		);

		$this->template = (object) array( /* template struct */
			'scheme' => $s,
			'default_template' =>  $this->file->isFeed ? 'xml.php' : 'index.php'
		);
	}

	// Load the content specified by the request
	private function loadContent() {

		if (!$this->file->exists)
			throw new Exception("Not found: {$this->req->uri}", 404);

		if ($this->file->isFile) {
				
			$this->posts[] = $this->load_one($this->file->path, $url);
			$this->nav = lister::relativeNav($this->settings->site->blog, 
				$this->posts->file, $this->posts->url);

		} elseif ($this->file->isFolder) {
			$this->posts = $this->load_listing($this->file->path);
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

	/* Private: Load the current listing */
	private function load_listing($t, $url = '', $p = 0) {
	
		$url = $url ? $url : $this->settings->site->blog;
		$in = preg_replace('/(\/+)/','/', API_BASE.'/'.$url);
		$max = $this->file->isFeed ? $this->settings->site->feedPosts : $this->settings->site->homePosts;

		$this->nav = lister::folder($in, $url, $p, $max);
		$listing = array();
		
		foreach ($this->nav->files as $f)
			$listing[] = $this->load_one($f, $url);

		return $listing;
	}
	/* Private: load one file into a struct */
	private function load_one($f, $url) {
		$p = $this->load_page($f);
		
		return (object) array(
			'file'		=> $f,
			'url'		=> $url . end(explode($url, $f)),
			'text'		=> $p,
			'content' 	=> $this->markup($p),
			'excerpt' 	=> get_snippet($p, 100),
			'title' 	=> '',
			'published' => date('r', filemtime($f)),
			'author'	=> '',
			'categories' => '',
			'link' 		=> '',
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
	private function markup($content) {
		$call = $this->file->handler;

		if (!method_exists($this, $call)) {
			// skip processing types we know nothing about (it's ok, plain text returned)
			presto_lib::_trace("Skipping content handler for .{$this->type}, could not find {$call}()");
			return $content;
		}
		
		return $this->$call($content);
	}		
	/* Private: type handlers */

	private function handle_md($t) {
		if (!include_once('lib/markdown/markdown.php')) return $t;

		/* TODO
			- pull metadata (if any)
			- pull title from first line (if any)
			- pull first DL (if any)

			- cache (and load from cache based on ?)
		*/
		return Markdown($t);
	}
	private function handle_html($t) { return $t; }
	private function handle_php($t) { return $t; }

	// Show an error condition (on an error page)
	private function showError($m, $c, $p = 'error.php') {
		$this->resp->redirect($p, array('e' => $m, 'c' => $c));
	}



}

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



