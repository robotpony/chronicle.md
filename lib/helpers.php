<?php

namespace robotpony\chronicleMD;


/* A site request

	Based loosely on a Presto request, but tailored to what a content site needs.
*/
class req {
	public $method;
	public $url;
	public $host;
	public $tld;
	public $referer;
	public $path;
	public $folders;
	public $resource = '';
	public $md = '';
	public $type = '';
	public $options = array();

	/* Set up the request from PHP primitives */
	public function __construct() {

		// parse request
		$this->host = req::server('HTTP_HOST');
		$this->url = req::server('REQUEST_URI');
		$this->method = strtolower(req::server('REQUEST_METHOD'));
		$this->referer = req::server('HTTP_REFERER');
		$this->options = (object) $_GET;
		$this->tld = pathinfo($this->host, PATHINFO_EXTENSION);
		$this->path = parse_url($this->url, PHP_URL_PATH);
		$this->folders = array_filter(explode(DIRECTORY_SEPARATOR, ltrim($this->path, DIRECTORY_SEPARATOR)));
		
		// extract resource (if any)
		$f = end($this->folders);
		if ($f && strpos($f, '.')) {
			$this->resource = array_pop($this->folders);
			$this->md = $this->resource ? ext($this->resource, '.md') : '';
			$this->type = pathinfo($this->resource, PATHINFO_EXTENSION);
		}
		
		$this->folder = count($this->folders) === 0 ? DIRECTORY_SEPARATOR :
			DIRECTORY_SEPARATOR  . implode(DIRECTORY_SEPARATOR, $this->folders) . DIRECTORY_SEPARATOR;
	}

	public function is_single() { return !empty($this->resource); }
	public function is_section() {
		return isset($this->path) 
			&& (substr($this->path, -1) === DIRECTORY_SEPARATOR || $this->path === '');
	}

	// get wrapper (with default)
	public static function get($k, $d = '') { return isset($_GET[$k]) ? $_GET[$k] : $d; }
	// post wrapper (with default)
	public static function post($k, $d = '') { return isset($_POST[$k]) ? $_POST[$k] : $d; }
	// server wrapper (with default)
	public static function server($k, $d = '') { return isset($_SERVER[$k]) ? $_SERVER[$k] : $d; }

}

// turn a file path into a URL
function urlize($path) { return str_replace(BLOG_ROOT, '', $path); }
// turn a URL into a valid path
function pathize($parts, $in = BLOG_ROOT) {
	array_unshift($parts, $in);
	return realpath( implode(DIRECTORY_SEPARATOR, $parts) );
}
// replace the extension on a path
function ext($ext, $file) {
	if (!($i = pathinfo($file))) return $name;
	return $i['dirname'] . '/' . $i['filename'] . ".$ext";
}

/* Issue a warning */
function warn() {
	$w = stringify_array(func_get_args());

	print "<div class='warn'><h3>Warning</h3>$w</div>";
	error_log('WARNING: ' . $w);

	return false;
}
/* Trace Chronicle objects */
function trace() {
	global $chronicle;
	dump('Chronicle engine trace',
		'engine = ', $chronicle
		);
}
function notate() {
	$w = stringify_array(func_get_args(), 0, ' ');
	error_log('NOTE: ' . $w);

	return false;
}

/* Debug dump, prints all parameters for output */
function dump() {
	$w = stringify_array(func_get_args());
	print "<pre>$w</pre>";
}
/* Get an array as a neatly formatted string */
function stringify_array($a, $f = JSON_PRETTY_PRINT, $s = "\n") {
	$o = '';
	foreach ($a as $v) {
		if (is_object($v) || is_array($v))
			$o .= json_encode($v, $f);
		else
			$o .= $v;

		$o .= $s;
	}
	return $o;
}

/* Exceptions */

function exception_handler($e) {
	$detail = json_encode($e, JSON_PRETTY_PRINT);
	print "<div class='error'>Exception {$e->getCode()} {$e->getMessage()} <pre>$detail</pre></div>";
}

set_exception_handler('robotpony\chronicleMD\exception_handler');