<?php /* Chronicle.md - Copyright (C) 2013 Bruce Alderson */

/* The Chronicle delegator

	Proxy requests between Apache and Chronicle (using simple rewrite rules).

	Also serves up command line requests (for us nerdy bloggers)
*/

if (!defined('CHRONIC_BASE')) define('CHRONIC_BASE', realpath(dirname(__FILE__)));
if (!defined('PRESTO_BASE')) define('PRESTO_BASE', realpath(CHRONIC_BASE . '/../presto/'));

date_default_timezone_set('America/Los_Angeles');

require PRESTO_BASE . '/lib/request.php';
require PRESTO_BASE . '/lib/response.php';

if (isset($argc) && $argc && !array_key_exists('HTTP_HOST', $_SERVER)) {
	require CHRONIC_BASE . '/cli.php';
	return;
}

require CHRONIC_BASE . '/chronicle.md.php';

try {
	assert(version_compare(PHP_VERSION, '5.4.0') >= 0, 'Chronicle requires a newer version of PHP.');

	// Start up the site and render the current page template

	$site = new napkinware\chronicle\site();
	$site->go();

} catch (Exception $e) {
?><h1>Fatal Chronicle error</h1>
<p>Something bad happened, possibly an installation error.</p>
<pre>
<?php
	print_r($e);
}