<?php

namespace robotpony\chronicleMD;


class engine {
	public $req;

	public function __construct() {

		on::register_event('startup', ['on', '_startup'] );
		on::register_event('done', ['on', '_done'] );

		$this->req = new req();
	}
}


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
dump($this, $_SERVER);
	}

	// get wrapper (with default)
	public static function get($k, $d = '') { return isset($_GET[$k]) ? $_GET[$k] : $d; }
	// post wrapper (with default)
	public static function post($k, $d = '') { return isset($_POST[$k]) ? $_POST[$k] : $d; }
	// server wrapper (with default)
	public static function server($k, $d = '') { return isset($_SERVER[$k]) ? $_SERVER[$k] : $d; }


}

/* On <event> handler delegation

	The event handling infrastructure for ChronicleMD
*/
class on {

	/* Register an event handler for a given event

	*/
	public static function register_event($event, $handlerFn) {
		assert( $event && is_string($event) && strlen($event), 'Invalid event value.' );
		assert( $handlerFn && is_callable($handlerFn, true), 'Invalid event handler.' );

		on::$handlers[$event][] = $handlerFn;
	}

	/* User-defined events */
	public static function __callStatic($n, $a) {
		$p = count($a) ?
		' - ' . implode(', ', $a) : '';

		return "($n$p)";
	}

	/* Built in events (not called directly) */
	public static function _startup() {}
	public static function _done() {}

	private static $handlers = array();

}
