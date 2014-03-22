<?php /* Chronicle.md - Copyright (C) 2013 Bruce Alderson */

/* The Chronicle delegator

	Proxy requests between Apache and Chronicle (using simple rewrite rules).

	Also serves up command line requests (for us nerdy bloggers)
*/

if (!defined('CHRONIC_BASE')) define('CHRONIC_BASE', realpath(dirname(__FILE__)));
if (!defined('SITE_BASE')) define('SITE_BASE', realpath(CHRONIC_BASE . '/../../'));
if (!defined('LIB_BASE')) define('LIB_BASE', realpath(CHRONIC_BASE . '/../'));
if (!defined('PRESTO_BASE')) define('PRESTO_BASE', realpath(CHRONIC_BASE . '/../presto/'));

// Check ChronicleMD requirements
assert(version_compare(PHP_VERSION, '5.4.0') >= 0, 'Chronicle requires a newer version of PHP.');

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
?><h1>Fatal Chronicle error</h1>
<p>Something bad happened, possibly an installation error.</p>
<pre>
<?php
	print_r($e);
}