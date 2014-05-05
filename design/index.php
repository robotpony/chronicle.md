<?php use robotpony\chronicleMD as site;

site\on::startup();

?>

<?= site\theme::header(); ?>

<main>
<?php foreach (site\documents::blog(array('max-posts' => 1)) as $post) { ?>

<section>

	<header>
		<h1><?= $post->title(); ?></h1>
		<date><?= $post->date(); ?></date>
	</header>

	<article><?= $post->body(); ?></article>

</section>

<?php } ?>
</main>


<aside>
<h2>Recent posts</h2>
<?php
	$params = array(
		'max-posts' => 10,
		'exclude-current' => true
	);
	foreach (site\documents::blog($params) as $doc) {
?>
	<a href="<?= $doc->url(); ?>"><?= $doc->title(); ?></a>
<?php } ?>
</aside>

<aside>
<h2>Current projects</h2>
<?php
	foreach (site\documents::projects_current() as $post) {
?>
	<a href="<?= $post->url(); ?>"><?= $post->title(); ?></a>
<?php } ?>
</aside>

<?= site\theme::footer(); ?>
<?php site\on::done(); ?>