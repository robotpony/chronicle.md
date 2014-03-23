<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title><?= CHRONIC ?> - <?= $chronic->pageTitle(); ?></title>

	<script src="//cdnjs.cloudflare.com/ajax/libs/less.js/1.7.0/less.min.js" type="text/javascript"></script>
	<link rel="stylesheet/less" type="text/css" href="styles.less" />
<!--[if IE]>
	<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->

</head>

<body id="home">

	<header>
		<h1><a href="<?= $chronic->settings->site->URL ?>"><?= $chronic->settings->site->name ?></a></h1>
		<p><?= $chronic->settings->site->tagline ?></p>
	</header>

