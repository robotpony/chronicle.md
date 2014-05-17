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
	public $type = '';
	public $options = array();

	public function __construct() {

		// parse request
		$this->method = strtolower(req::server('REQUEST_METHOD'));
		$this->url = req::server('REQUEST_URI');
		$this->host = req::server('HTTP_HOST');
		$this->tld = pathinfo($this->host, PATHINFO_EXTENSION);
		$this->referer = req::server('HTTP_REFERER');
		$this->path = parse_url($this->url, PHP_URL_PATH);
		$this->options = (object) $_GET;
		$this->folders = array_filter(explode(DIRECTORY_SEPARATOR, ltrim($this->path, '/')));

		// extract resource (if any)
		$f = end($this->folders);
		if ($f && strpos($f, '.')) {
			$this->resource = array_pop($this->folders);
			$this->type = pathinfo($this->resource, PATHINFO_EXTENSION);
		}
	}

	// get wrapper (with default)
	public static function get($k, $d = '') { return isset($_GET[$k]) ? $_GET[$k] : $d; }
	// post wrapper (with default)
	public static function post($k, $d = '') { return isset($_POST[$k]) ? $_POST[$k] : $d; }
	// server wrapper (with default)
	public static function server($k, $d = '') { return isset($_SERVER[$k]) ? $_SERVER[$k] : $d; }


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