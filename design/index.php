<?php use robotpony\chronicleMD as site;

site\theme::startup();

site\theme::header();
?>

<?php foreach (site\documents::blog() as $post) { ?>

<?= $post->title(); ?>
<?= $post->date(); ?>
<?= $post->body(); ?>


<?php } ?>

<?= site\theme::footer(); ?>