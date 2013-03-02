<?php
/* Chronicle.md

	A micro blogging and website tool for publishing Markdown, PHP, and HTML files.
	
	Usage:
	
		$content = new ChronicleMD();
			
		print $content;
		
		# That's it!
		

	Basics:
	
	1. Templates: index.php is the root template. This is used if 

	TODO:
	
	* Sidebars
	* Part loading (footers, chunks)
	* Listings
	* Templates
	* Hooks for addons
	
*/

class ChronicleMD {

	public function __construct() {
		include('lib/presto/lib/request.php');

		$req = new Request();
		$f = API_BASE.$req->uri;
		$s = $req->scheme();
			
		$this->path = $f;
		$this->type = $s->type;
		$this->handler = "handle_{$this->type}";

		$this->exists = (boolean) file_exists($f);
		$this->isFile = (boolean) is_file($f);
		$this->isFolder = (boolean) is_dir($f);

		$this->contents = '';
		$this->html = '';
		
		if (!$this->exists)
			throw new Exception('Not found', 404);
		
		if ($this->isFile)
			$this->contents = file_get_contents($this->path);
		elseif ($this->isFolder)
			$this->contents = 'LISTING';
			
	}
	
	public function __toString() {
		try {
			$call = $this->handler;
		
			if (!method_exists($this, $call))
				throw new Exception("No content handler for .{$this->type}, could not find Yammer::{$call}()", 500);
					
			$this->html = $this->$call($this->contents);
			
			return !empty($this->html) ? $this->html : $this->contents;
			
		} catch (Exception $e) {
			print_r($this);

			print_r($e);
		}
	}

	private function handle_md($t) {
		if (!include('lib/markdown/markdown.php')) return;
		
		return Markdown($t);
	}		
	private function handle_html($t) {
		
	}		
	private function handle_php($t) {
		
	}		
}
