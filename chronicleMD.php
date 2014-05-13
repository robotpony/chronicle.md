<?php

namespace robotpony\chronicleMD;

/* # Chronicle v1.1 */

require_once 'Parsedown.php';

require_once 'config.php';
require_once 'lib/helpers.php';
require_once 'lib/settings.php';
require_once 'lib/engine.php';
require_once 'lib/theme.php';
require_once 'lib/documents.php';


try {
	assert(version_compare(PHP_VERSION, '5.3.0') >= 0 /* Requires 5.3 or better, old style assert in case this is old-school */);

	global $chronicle;

	$chronicle = new engine(array(
		'global_settings' => 'site.json',
		'section_settings' => 'section.json'
	));

	$chronicle->run();
} catch (\Exception $e) {

?>
<pre>
Exception: <?= $e->getMessage(); ?> - <?= $e->getCode(); ?>
</pre>
<?php

}