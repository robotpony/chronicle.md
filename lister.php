<?php /* Chronicle.md - Copyright (C) 2013 Bruce Alderson */

/* Chronicle (Dave) Lister

	Lists files, possibly caching the results.

*/
class lister {

	// list a given folder
	static function folder($in, $url, $page = 0, $pageSize = 0) {
	
		$at = 0;
		$files = '';
		$start = $page * $pageSize;
		$end = $start + $pageSize;

		$list = array_reverse(lister::directory($in));
		
		// TODO - cache $l and load from cache (within $n minutes)
		
		$c = count($list);
		$end = $c >= $end ? $end : $c;

		for ( $at = $start; $at < $end; $at++ ) {
			$files[] = $list[$at];			
		}

		$prevLink = ($p != 0) ? "$url/page/" . (string) ($p - 1) : '';
		$nextLink = ($c >= $end) ? "$url/page/" . (string) ($p + 1) : '';

		return (object) array(
			'files' => $files,
			'category' => '',
			'prev' => preg_replace('/(\/+)/','/', $prevLink),
			'next' => preg_replace('/(\/+)/','/', $nextLink)
		);
	}
	
	/**/
	static function relativeNav($in, $at, $url) {

		$path = preg_replace("#{$in}.*$#", '', $at) . $in;
		$list = array_reverse(lister::directory($path));

		$idx = array_search($at, $list);

		$prevLink = $idx ? "$url/". $list[ --$idx ] : '';
		$nextLink = $idx < count($list) ? "$url/". $list[ ++$idx ] : '';

		return (object) array(
			'files' => $files,			
			'category' => '',
			'prev' => preg_replace('/(\/+)/','/', $prevLink),
			'next' => preg_replace('/(\/+)/','/', $nextLink)
		);
		
	}
	
	/* Get a directory listing  (recursive) */
	static function directory($d, $g = "*") {
		$files = array();
		$scan  = glob(rtrim($d, '/') . '/' . $g);

		if (is_file($d))
			array_push($files, $d);
		elseif (is_dir($d)) foreach ($scan as $path)
			$files = array_merge($files, lister::directory($path, $g));

		return $files;
	}	
}