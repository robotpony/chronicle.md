<?php

namespace robotpony\chronicleMD;


class engine {
	public $req;

	public function __construct($o = array()) {

		foreach ($o as $k => $v) $this->$k = $v;

		on::register_event('startup', ['on', '_startup'] );
		on::register_event('done', ['on', '_done'] );

		$this->req = new req();
	}

	public function run() {
		global $chronicle;
		include 'index.php';
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
