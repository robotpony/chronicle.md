<?php $chronic->showPart('theme/header.php'); ?>

<main>
	<aside class="info">
        <p>This example theme file shows the template functions and debugging functions.</p>
    </aside>

	<section class="sitemap">
		<h2>Documentation</h2>
		<article>
			<ul>
<?php foreach ($chronic->posts() as $post) { ?>
				<li class="<?= $post->type; ?>"><a href="<?= $post->url; ?>" title="<?= $post->name; ?>"><?= $post->title; ?></a></li>
<?php } ?>
			</ul>
		</article>
	</section>

	<section class="posts">
		<article>
<?= $chronic->page()->html(); ?>
		</article>
	</section>


<?php $chronic->showPart('theme/footer.php'); ?>