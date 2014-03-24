<?php $chronic->showPart('theme/header.php'); ?>

<style>
body {
	font: normal .9em/1.25em "Open Sans", "Helvetica Neue", Helvetica, sans-serif;
}
body > header h1 a {
	color: rgba(200,125,25,.85);
	text-decoration: none;
}
main > aside {
	font-style: italic;
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
section.debug {
	display: none;
}
pre {
	background-color: rgba(0,0,0,.05);
	padding: .5em 1em;
}

</style>
<main>
	<aside>This example theme file shows the template functions and debugging functions.</aside>


	<article>
<?= $chronic->page()->html(); ?>
	</article>

	<h2>More posts</h2>
<?php foreach ($chronic->posts() as $post) { ?>

	<article>
		<pre>
File : <?php print_r($post->name); ?>

Title: <?php print_r($post->title); ?>

URL  : <?php print_r($post->url); ?>

		</pre>
	</article>
<?php } ?>


<section class="debug">

<h3>Entries</h3>
<pre>
<?= $chronic->debugInfo('entries'); ?>
</pre>

<h3>Post list</h3>
<pre>
<?= json_encode($chronic->postList(), JSON_PRETTY_PRINT); ?>
</pre>

<h3>Requested resource</h3>
<pre>
<?= $chronic->debugInfo('resource'); ?>
</pre>

<h3>HTTP request</h3>
<pre>
<?= $chronic->debugInfo('req'); ?>
</pre>
</section>

<h3>Template</h3>
<pre>
<?= $chronic->debugInfo('template'); ?>
</pre>

</main>



<?php $chronic->showPart('theme/footer.php'); ?>