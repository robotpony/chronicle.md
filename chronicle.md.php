<?php /* Chronicle.md - Copyright (C) 2013 Bruce Alderson */


/* Chronicle is a small markdown blogging engine for PHP.

	See README.md for docs.
*/

require 'settings.php';

class ChronicleMD {

	public $settings;
	private $file;
	private $req;
	private $resp;	
	private $contents;
	private $html;
	
	/* Startup */
	public function __construct() {
		
		try {
			$this->resp = new Response();	
			
			$this->parseRequest();
			$this->settings = new settings();
			$this->loadContent();
			
		} catch( Exception $e ) {
			$this->showError($e->getMessage(),  $e->getCode());
		}
			
	}
	
	public function buildPage() {
		try {
			$this->render();
		} catch( Exception $e ) {
			$this->showError($e->getMessage(),  $e->getCode());
		}
	}
	
	/* Return the content (marked up if possible) */
	public function __toString() { return $this->get_content(); }

	
	// Show debugging information
	
	public function debugInfo() {
		
?>
<pre>Chronicle debug info
<?php print_r($this->settings); ?>
</pre>
<?php
	}
	
	/* ======================== Startup and helper functions ======================== */
	
	// Process the request (into class objects and structs)
	private function parseRequest() {
		
		// Determine request to document/template mappings 
		
		$this->req = new Request();
		$s = $this->req->scheme();
		$f = API_BASE.$this->req->uri;
		
		$this->contents = ''; $this->html = '';

		$this->file = (object) array( /* pseudo document/file object */
			'file' 		=> $f,
			'path' 		=> $f,
			'type' 		=> $s->type,
			'handler'	=> "handle_{$s->type}",
			'exists'	=> (boolean) file_exists($f),
			'isFile'	=> (boolean) is_file($f),
			'isFolder'	=> (boolean) is_dir($f)
		);
		
		$this->template = (object) array( /* pseudo template object */
			'scheme' => $s,
			'default_template' => 'index.php'
		);

	}
	
	// Load the content specified by the request
	public function loadContent() {

		// Load content
		
		if (!$this->file->exists)
			throw new Exception("Not found: $f", 404);
			
		/*
			TODO:
				- load file or folder root file (index.md? or readme.md?)
				- if no file exists, load a blog of files
				- config also loaded (per container?)
		*/
		if ($this->file->isFile)
			$this->contents = $this->load_page($this->file->path);
		else
			$this->contents = $this->load_listing($this->file->path);
		
	}

	/* Render a page template */
	private function render() {
		$t = API_BASE.'/'.$this->template->scheme->file;
		
		if (!file_exists($t)) {
			$t = API_BASE.'/'.$this->template->default_template;
			if (!file_exists($t))
				throw new Exception('No suitable template found.', 500);
		}
		/*
			Test:
				- per container lodaing (should work)
		*/
		
		global $cms;		
		$cms = $this;
		
		include_once($t);		
		presto_lib::_trace("Loaded template $t");
	}


	/* ======================== Deeper helpers ======================== */

	/* Private: generate output content */
	private function get_content() {
	
		$call = $this->file->handler;
	
		if (!method_exists($this, $call)) {
		
			// skip processing types we know nothing about (it's ok, plain text returned)
			presto_lib::_trace("Skipping content handler for .{$this->type}, could not find {$call}()");
			return $this->contents;
		}

		if (is_array($this->contents)) {
			foreach ($this->contents as $post) {
				$this->html .= "\n<section>\n" . $this->$call($post) . "\n</section>\n";
			}
		} else {
			$this->html = "\n<section>\n" . $this->$call($this->contents) . "\n</section>\n";
		}
		
		return $this->html;
	}
	
	/* Private: Load the current page */
	private function load_page($t, $w = '') {
		if (!file_exists($t)) throw new Exception("Not found: $t", 404);
		$c = file_get_contents($t);
		
		presto_lib::_trace("Loaded page $t");
		return $c;
	}
	
	/* Private: Load the current listing 
		
		This should be a separate class
			* pull top level
			* config with root (if not .)
			* write .md files with listings for each
			* and .json?
			
		Important indexes:
			* "menu"
			* blog - all, year, latest
	*/
	private function load_listing($t) {
		$n = 0;
		$c = '';
		$in = API_BASE.'/'.$this->template->scheme->container.'/blog/';	// BUG
		$l = array_reverse(ChronicleMD::list_dir($in));

		foreach ($l as $f) {
			$n ++;
			$c[] = $this->load_page($f);
			if ($n > 11) break;
		}
		
		presto_lib::_trace('Loaded listing');
		return $c;
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
	private function handle_php($t) {
		return $t;
	}


	// Show an error condition (on an error page)
	private function showError($m, $c, $p = 'error.php') { 
		$this->resp->redirect($p, array('e' => $m, 'c' => $c));
	}

	
	/**/
	static function list_dir($d, $g = "*") {
		$files = array();
		$scan  = glob(rtrim($d, '/') . '/' . $g);
	
		if (is_file($d))
			array_push($files, $d);
		elseif (is_dir($d))
			foreach ($scan as $path)
				$files = array_merge($files, ChronicleMD::list_dir($path, $g));
	
		return $files;
	}
}




