<style>
.chroniclemd.error {
	border: 4px solid rgba(0,0,0,.25);
	padding: .25em .75em;
	border-radius: 7px;
	margin: 1em auto;
	width: 70%;
	font: normal .8em/1.25em "Open Sans", "Helvetica Neue", Helvetica, sans-serif;
	overflow: hidden;
}
.chroniclemd.error h1 {
	border-bottom: 1px solid rgba(0,0,0,.25);
	font-weight: 200;
	color: rgba(0,0,0,.25);
	line-height: 125%;
	padding-top: 0; margin-top: 0;
}
.chroniclemd.error p {
	font-size: 16pt;
	font-weight: 100;
}
.chroniclemd.error dl {
	border-bottom: 1px solid rgba(0,0,0,.25);
	border-top: 1px solid rgba(0,0,0,.25);
	padding: 1em 0 1em;
}
.chroniclemd.error dl dt {
	font-weight: bold;
}
.chroniclemd.error pre {
	tab-size: 1em;
}
</style>
<div class="chroniclemd error">
<h1><?= CHRONIC ?> error</h1>
<p><strong>Error #<?= $e->getCode() ?></strong>: <?= $e->getMessage() ?></p>
<dl>
	<dt>File:</dt><dd><?= $e->getFile() ?>:<?= $e->getLine(); ?></dd>
	<dt>Chronicle base</dt><dd><?= CHRONIC_BASE ?></dd>
	<dt>Site base</dt><dd><?= SITE_BASE ?></dd>
	<dt>Presto base</dt><dd><?= PRESTO_BASE ?></dd>
</dl>
<h3>Stack trace</h3>
<pre><?php print_r($e); ?></pre>
</div>
