<?php /* Chronicle.md - Copyright (C) 2014 Bruce Alderson */

namespace napkinware\chronicle;

use napkinware\presto as presto;

/* Site entries for a given request

*/
class entries {

	private $res;
	private $via;
	public $d = array();
	public $f = array();
	public $page;

	private $skipped; // for debugging, but not in dumps

	public function __construct(&$res) {

		$this->res = $res;

		$this->readPath();

		// sort folders alphabetically
		usort($this->d, function($a, $b) { return strcasecmp($a->name, $b->name); });

		assert(isset($this->page), 'No page found');
	}

	private function readPath() {
		if (isset($this->files)) return $this->files;

		foreach (new \FilesystemIterator($this->res->folder,
			\FileSystemIterator::SKIP_DOTS) as $f) {

			$e = new entry($f);

			if (strlen($e->name) && $e->name[0] === '.')
				continue;

			/* TODO
				- this needs some TLC, logic is a bit off
			*/
			if ($this->res->isFile && $e->name === $this->res->file)
				$this->page = $e;
			elseif (empty($this->page) && $e->isIndex)
				$this->page = $e;
			elseif ($e->isFolder)
				$this->d[] = $e;
			elseif ($f->getExtension() === $this->res->type)
				$this->f[] = $e;
			else
				$this->skipped[] = $e;
		}
	}

}

/* A single entry (page, post, etc.) */
class entry {
	private $file;
	public $name;
	public $isIndex = false;
	public $isFolder = false;

	public $title = '';
	public $type = 'post';
	public $url = '';
	public $text = '';
	public $html = '';

	public function __construct($f) {
		$this->file = $f;
		$this->name = $f->getFilename(); // easy access
		$this->isIndex = in_array( $this->name, array('README.md', 'index.md') );
		$this->isFolder = $f->getType() === 'dir';
		$this->title = $this->name;

		$base = API_BASE;
		$this->url = preg_replace("#^{$base}#", '', $f);
		if ($this->isFolder) $this->url .= '/';

	}

	public function html() {

		if (empty($this->text)) $this->load();

		$this->html = $this->render();

		// TODO - pull metadata
		// title
		// postdate
		// etc. (via yaml or other awesome format in post)

		return $this->html;
	}

	private function load() {
		$path = $this->file->getPathname();

		if (!file_exists($path)) throw new \Exception("Not found: $path", 404);
		$this->text = file_get_contents($path);
	}

	private function render() {
		$ext = $this->file->getExtension();

		$call = "handle_{$ext}";

		if (empty($ext) || !method_exists($this, $call))
			return $this->text;
		else
			return $this->$call();
	}

	private function handle_md() {
		if (!include_once(LIB_BASE . '/parsedown/Parsedown.php'))
			throw new \Exception('Could not find markdown library, check submodule status.', 500);

		$mdizer = new \Parsedown();
		return $mdizer->parse($this->text);
	}
}


