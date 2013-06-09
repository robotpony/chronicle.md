<?php /* Chronicle.md - Copyright (C) 2013 Bruce Alderson */

/* The Chronicle delegator

	Proxy requests between Apache and Chronicle (using simple rewrite rules).
*/
require 'lib/presto/lib/request.php';
require 'lib/presto/lib/response.php';
require "lib/chronicle.md/chronicle.md.php";

set_include_path(get_include_path() 
	. PATH_SEPARATOR . API_BASE
	. PATH_SEPARATOR . API_BASE . '/lib/chronicle.md/');

try {
	// Start up the site and render the current page template

	$site = new ChronicleMD();
	$site->go();

} catch (Exception $e) {
?><h1>Fatal Chronicle error</h1>
<p>Something bad happened, possibly an installation error.</p>
<pre>
<?php
	print_r($e);
}