<?php

require 'lib/presto/lib/request.php';
require "lib/chronicle.md/chronicle.md.php";
	
try {
	
	global $site; // The site object (available in templates)
	
	// Start up the site and load the current page template
	$site = new ChronicleMD();
	$site->render();
	
	return;
} catch (Exception $e) {
	$error = print_r($e, true);
}
?>

<h1>Fatal Error</h1>
<pre>
<?php print_r($site); ?>
<?= $error ?>
</pre>
