<?php

require 'lib/presto/lib/request.php';
require "lib/chronicle.md/chronicle.md.php";
	
try {
	global $site;
	$site = new ChronicleMD();
	
	$site->load_template();
	
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