<?php

namespace robotpony\chronicleMD;

/* # Chronicle v1.1 */

require_once 'helpers.php';
require_once 'settings.php';
require_once 'engine.php';
require_once 'theme.php';
require_once 'documents.php';

$chronicle = new engine(array(
	'settings' => 'site.json',
	'root' => '/'
));
