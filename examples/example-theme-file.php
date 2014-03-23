<?php $chronic->showPart('theme/header.php'); ?>

<style>
body {
	font: normal .9em/1.25em "Open Sans", "Helvetica Neue", Helvetica, sans-serif;
}
main, header {
	width: 80%;
	margin: 2em auto;
	border-top: 4px solid rgba(0,0,0,.1);
}
section {
	margin: 2em auto;
	border-top: 1px solid rgba(0,0,0,.25);
}
</style>
<main>
	<aside>This example theme file shows the template functions and debugging functions.</aside>

	<section>
		<h2>Posts</h2>
<?php while ($post = $chronic->nextPost()) { ?>
	<article>
		<pre>
File : <?php print_r($post->file); ?>

Title: <?php print_r($post->title); ?>

URL  : <?php print_r($post->url); ?>

		</pre>
	</article>
<?php } ?>


<section class="debug">

<h3>Current post</h3>
<pre>
<?= $chronic->debugInfo('nav'); ?>
</pre>

<h3>Post list</h3>
<pre>
<?= json_encode($chronic->postList(), JSON_PRETTY_PRINT); ?>
</pre>

<h3>Current file</h3>
<pre>
<?= $chronic->debugInfo('file'); ?>
</pre>

<h3>Template</h3>
<pre>
<?= $chronic->debugInfo('template'); ?>
</pre>

<h3>HTTP request</h3>
<pre>
<?= $chronic->debugInfo('req'); ?>
</pre>
</section>

</main>



<?php $chronic->showPart('theme/footer.php'); ?>