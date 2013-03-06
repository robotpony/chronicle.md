<?php
/* Chronicle.md

	See README.md for docs.	
	
	Notes:
		* This is a prototype (it already hints at needing to be refactored)
*/
class ChronicleMD {

	public $settings;
	private $file;
	private $req;
	private $contents;
	private $html;
	
	/* Startup */
	public function __construct() {
		
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
		
		// Load settings
		$this->settings();

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
	
	/* ======================== Startup and theme functions ======================== */
	
	/* Return the content (marked up if possible) */
	public function __toString() { return $this->get_content(); }

	/* Render a page template */
	public function render() {
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
		
		global $site;
		include_once($t);
		
		presto_lib::_trace("Loaded template $t");
	}


	/* ======================== Internals ======================== */

	/* Private: generate output content */
	private function get_content() {
		$call = $this->file->handler;
	
		if (!method_exists($this, $call)) {
			// skip processing types we know nothing about (it's ok, plain text returned)
			presto_lib::_trace("Skipping content handler for .{$this->type}, could not find {$call}()");
			return $this->contents;
		}

		$this->html = $this->$call($this->contents); // process the content based on its type
		
		return $this->html;
	}
	
	/* Private: Load the current page */
	private function load_page($t, $w = '') {
		if (!file_exists($t)) throw new Exception("Not found: $t", 404);
		$c = file_get_contents($t);
		
		// process headers (if any)
		$c = preg_replace("/^title: (.*)\n/m", "<header>\n# $1\n", $c, 1);
		$c = preg_replace("/^date: (.*)\n/m", "\n\nDate\n: $1\n", $c, 1);
		$c = preg_replace("/^categories: (.*)\n/m", "\nTags\n: $1\n\n</header>\n", $c, 1);
		
		presto_lib::_trace('Loaded content');
		return $w ? "<$w>$c</$w>" : $c;
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
		$in = API_BASE.'/'.$this->template->scheme->container.'/blog/';		
		$l = array_reverse(ChronicleMD::list_dir($in));
		$c = '';
		foreach ($l as $f) {
			$n ++;
			$c .= $this->load_page($f, 'section');
			if ($n > 10) break;
		}
		
		presto_lib::_trace('Loaded listing');
		return $c;
	}
	
	/* Private: type handlers */ 
	
	private function handle_md($t) {
		if (!include('lib/markdown/markdown.php')) return $t;
		
		/* TODO
			- pull metadata (if any)
			- pull title from first line (if any)
			- pull first DL (if any)
			
			- cache (and load from cache based on ?)
		*/		
		return Markdown($t);
	}		
	private function handle_html($t) {
		return $t;
	}		
	private function handle_php($t) {
		return $t;
	}
	
	/* Private: load settings */
	
	private function settings() {
		$files = array(
			API_BASE.'/site.json');
		/*
			TODO
				- split loading into another fn (use above)
				- auto-write for certain files?
		*/
		foreach ($files as $f) {
			if (!file_exists($f)) {
				presto_lib::_trace('Skipping missing settings file', $f);
				continue;	
			}

			$config = file_get_contents($f);
			$this->settings = json_decode($config);
		}
		presto_lib::_trace('Loaded settings');
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


