<?php require 'theme/header.php'; ?>

<style>
</style>

<h1><?= CHRONIC ?> error</h1>
<dl>
	<dt>Message:</dt><dd><?= $e->getMessage() ?></dd>
	<dt>Error #:</dt><dd><?= $e->getCode() ?></dd>
	<dt>File:</dt><dd><?= $e->getFile() ?>:<?= $e->getLine(); ?></dd>
	<dt>Chronicle base</dt><dd><?= CHRONIC_BASE ?></dd>
	<dt>Site base</dt><dd><?= SITE_BASE ?></dd>
	<dt>Presto base</dt><dd><?= PRESTO_BASE ?></dd>
	<dt>Stack trace:</dt><dd><pre><?php print_r($e); ?></pre></dd>
</dl>

<?php require 'theme/footer.php'; ?>
