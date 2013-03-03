<?php
/* Chronicle.md

	See README.md for docs.	
*/



class ChronicleMD {

	public $settings;
	private $file;
	private $req;
	private $contents;
	private $html;
	
	/* Startup */
	public function __construct() {

		$this->req = new Request();
		$s = $this->req->scheme();
		$f = API_BASE.$this->req->uri;
		
		$this->contents = ''; $this->html = '';

		$this->file = (object) array(
			'file' 		=> $f,
			'path' 		=> $f,
			'type' 		=> $s->type,
			'handler'	=> "handle_{$s->type}",
			'exists'	=> (boolean) file_exists($f),
			'isFile'	=> (boolean) is_file($f),
			'isFolder'	=> (boolean) is_dir($f)
		);
		
		$this->template = (object) array(
			'scheme' => $s,
			'default_template' => 'index.php'
		);
		
		$this->settings();

		if (!$this->file->exists)
			throw new Exception("Not found: $f", 404);
		
		if ($this->file->isFile)
			$this->load_page($this->file->path);
		else
			$this->load_listing($this->file->path);
			
	}
	/* Return the content (marked up if possible) */
	public function __toString() { return $this->get_content(); }
	/* Private: generate output content */
	private function get_content() {
		$call = $this->file->handler;
	
		if (!method_exists($this, $call)) {
			presto_lib::_trace("Skipping content handler for .{$this->type}, could not find {$call}()");
			return $this->contents;
		}

		$this->html = $this->$call($this->contents);
		
		return $this->html;
	}
	/* Private: load the page template */
	public function load_template() {
		$t = API_BASE.'/'.$this->template->scheme->file;
		
		if (!file_exists($t)) {
			$t = API_BASE.'/'.$this->template->default_template;
			if (!file_exists($t))
				throw new Exception('No suitable template found.', 500);
		}

		global $site;
		include_once($t);
		
		presto_lib::_trace("Loaded template $t");
	}
	/* Private: Load the current page */
	private function load_page($t) {
		if (!file_exists($t)) throw new Exception("Not found: $t", 404);
		$this->contents = file_get_contents($t);		
		presto_lib::_trace('Loaded content');
	}	
	/* Private: Load the current listing */
	private function load_listing($t) {
		$this->contents = "## LISTING\n\t" . json_encode($this->req->scheme());
		presto_lib::_trace('Loaded listing');
	}
	
	/* Private: type handlers */ 
	
	private function handle_md($t) {
		if (!include('lib/markdown/markdown.php')) return $t;
		
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
		$files = array(API_BASE.'/site.json');
		foreach ($files as $f) {
			if (!file_exists($f)) {
				presto_lib::_trace('SETTINGS', 'missing', $f);
				continue;	
			}

			$config = file_get_contents($f);
			$this->settings = json_decode($config);
		}
		presto_lib::_trace('Loaded settings');
	}
}
