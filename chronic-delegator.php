<?php /* Chronicle.md - Copyright (C) 2013 Bruce Alderson */

/* The Chronicle delegator

	Proxy requests between Apache and Chronicle (using simple rewrite rules).

	Also serves up command line requests (for us nerdy bloggers)
*/

// Chronicle base defines

define('CHRONIC', 'ChronicleMD');
define('CHRONIC_BASE', realpath(dirname(__FILE__)));
defined('SITE_BASE') || define('SITE_BASE', (getenv('SITE_BASE') ?
	realpath(CHRONIC_BASE . getenv('SITE_BASE')) :
	realpath(CHRONIC_BASE . '/../../')) );
defined('LIB_BASE') || define('LIB_BASE', realpath(CHRONIC_BASE . '/lib/'));
defined('PRESTO_BASE') || define('PRESTO_BASE', realpath(LIB_BASE . '/presto/'));

// Absolute requirements
assert(version_compare(PHP_VERSION, '5.4.0') >= 0, 'Chronicle requires a newer version of PHP.');

// Load required libraries
require PRESTO_BASE . '/lib/request.php';
require PRESTO_BASE . '/lib/response.php';
require CHRONIC_BASE . '/chronicle.md.php';


if (isset($argc) && $argc && !array_key_exists('HTTP_HOST', $_SERVER)) {

	// Handle as CLI request

	require CHRONIC_BASE . '/cli.php';
	return;
}

try {

	// Handle as HTTP request

	$site = new napkinware\chronicle\site();
	$site->go();

} catch (Exception $e) {
	include CHRONIC_BASE . '/theme/fatal-error.php';
}