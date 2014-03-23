<?php /* Chronicle.md - Copyright (C) 2014 Bruce Alderson */

namespace napkinware\chronicle;

use napkinware\presto as presto;

/* Site entries (posts, pages)

Options:

	*

*/
class entries {
	private $settings;
	private $res;
	public function __construct(&$res, &$settings) {
		$this->settings = $settings;
		$this->res = $res;

	}


}