<?php

namespace robotpony\chronicleMD;

/* Chronicle blog engine */
class engine {
	public $req;

	public $global_settings = '';
	public $section_settings = '';

	/* Set up the engine */
	public function __construct($o = array()) {

		// fold in defaults
		foreach ($o as $k => $v) $this->$k = $v;

		// set up default events
		on::register_event('startup', ['on', '_startup'] );
		on::register_event('done', ['on', '_done'] );

		// parse the request
		$this->req = new req();

		// load the base site settings
		settings::load('site', $this->global_settings);
	}

	/* Execute the current request */
	public function run() {
		global $chronicle;
		include 'index.php';

		// TODO : route requests to various things (this only shows the index)
	}
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
