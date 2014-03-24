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

	public $document;				// The requested resource
	private $entries = null; //		// The list of entries related to the requested document

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
			// renderTemplate the site
			$this->renderTemplate();
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

	public function pageTitle() { return $this->settings->site->name; }

	/* Get the page type (string) */
	public function pageType() { return trim(str_replace('/', ' ', $this->resource->base)); }

	public function page() { return $this->entries->index; }
	public function posts() { return $this->entries->f; }

	/* Get the site last updated date */
	public function lastUpdated() { return date('r', filemtime(0)); /* TODO  - removed broken nav object, replace */ }

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

		$this->resource = (object) array(
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
			'default_template' =>  $this->resource->isFeed ? 'xml.php' : 'index.php'
		);
	}

	/* Load the content specified by the request */
	private function loadContent() {

		if (!$this->resource->exists)
			throw new \Exception("<code>{$this->req->uri}</code> not found in <code>{$this->resource->via}</code>", 404);

		$this->entries = new entries($this->resource);
	}

	/* renderTemplate a page template */
	private function renderTemplate() {
		$t = $this->template->scheme->file;

		if (!stream_resolve_include_path($t)) {
			$t = $this->template->default_template;
			if (!stream_resolve_include_path($t))
				throw new \Exception("No suitable template found (tried $t and {$this->template->scheme->resource} in " . get_include_path() . ')', 500);
		}

		global $chronic;
		$chronic= $this;

		include_once($t);
		presto\trace("Loaded template $t");
	}


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

