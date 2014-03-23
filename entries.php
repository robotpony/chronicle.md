<?php /* Chronicle.md - Copyright (C) 2014 Bruce Alderson */

namespace napkinware\chronicle;

use napkinware\presto as presto;

/* Site entries (posts, pages)

Options:

	*

*/
class entries {

	private $res;
	public $d = array();
	public $f = array();
	public $index;

	public function __construct(&$res) {
		$this->res = $res;
		$this->readPath();

		// sort folders alphabetically
		usort($this->d, function($a, $b) { return strcasecmp($a->name, $b->name); });
	}

	private function readPath() {
		if (isset($this->files)) return $this->files;

		foreach (new \FilesystemIterator($this->res->path, \FileSystemIterator::SKIP_DOTS) as $f) {

			$e = new entry($f);

			if (strlen($e->name) && $e->name[0] === '.') continue;

			if ($e->isKeyDocument)
				$this->index = $e;
			elseif ($e->isFolder)
				$this->d[] = $e;
			elseif ($f->getExtension() === $this->res->type)
				$this->f[] = $e;
			else
				$this->skipped[] = $e;
		}
	}

}

class entry {
	private $file;
	public $name;
	public $isKeyDocument = false;
	public $isFolder = false;

	public $text = '';
	public $html = '';

	public function __construct($f) {
		$this->file = $f;
		$this->name = $f->getFilename(); // easy access
		$this->isKeyDocument = in_array( $this->name, array('README.md', 'index.md') );
		$this->isFolder = $f->getType() === 'dir';
	}
}