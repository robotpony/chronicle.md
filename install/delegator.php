<?php

define('CHRONICLEMD_ROOT', realpath(dirname(__FILE__) . '/../'));
define('BLOG_ROOT', realpath($_SERVER['DOCUMENT_ROOT']));

set_include_path(get_include_path()
	. PATH_SEPARATOR . CHRONICLEMD_ROOT
	. PATH_SEPARATOR . BLOG_ROOT);

include 'chronicleMD.php';
