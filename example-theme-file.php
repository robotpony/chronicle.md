<!DOCTYPE html>
<html lang="">
<head>
  <meta charset="utf-8">
	<title><?= $site->settings->name ?></title>
	<meta name="description" content="<?= $site->settings->description ?>" />

	<link rel="stylesheet" href="/styles/main.css" />

</head>
<?php flush(); ?><body>

<header><div>
<h1><a href="/"><?= $site->settings->name ?> <em><?= $site->settings->tagline ?></em></a></h1>
<div></header>

<main><div>
<?= $site; ?>
</div></main>

<footer><div>
</div></footer>

</body>
</html>
