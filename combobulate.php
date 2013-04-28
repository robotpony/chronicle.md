<?php 

/* The ChronicleMD delegator 
	
	Proxies requests from Apache (via `htaccess` to Chronicle. Makes stuff happen.
*/

require 'lib/presto/lib/request.php';
require 'lib/presto/lib/response.php';
require "lib/chronicle.md/chronicle.md.php";
	
try {
	
	global $site; // The site object (available in templates)
	
	// Start up the site and render the current page template
	$site = new ChronicleMD();
	$site->render();
	
	return;
} catch (Exception $e) {
	$error = print_r($e, true);
}
?>
