<?php

namespace robotpony\chronicleMD;

/* # Chronicle 1.1 prototype

	This prototype outlines the classes and calling mechanisms (to prove them out). It
	currently generates debugging output for testing themes and evaluating the intended
	calling sequences.


	## Approach

	These classes use magic methods to simplify the calling sequence for theme APIs. Rather
	than force APIs to use parameters for common variable items,


	## Missing
*/

require_once 'helpers.php';
require_once 'settings.php';
require_once 'engine.php';
require_once 'theme.php';
require_once 'documents.php';

$chronicle = new engine();
