<?php

namespace robotpony\chronicleMD;


class engine {

	public function __construct() {

		on::register_event('startup', ['on', '_startup'] );
		on::register_event('done', ['on', '_done'] );
	}
}

/* on <event>

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
