# Chronicle design (1.1a)

*This page outlines the design for the next version of ChronicleMD.*

1.1 is intended for production environments, as a candidate replacement for various CMS engines where simpler installation and PHP are preferred. ChronicleMD sites are both dynamic and performant due to a smaller footprint and simpler data storage model, in combination with strategic caching.

ChronicleMD relies on Markdown (via Parsedown), which is a great format for programmers and semi-technical people. It relies on a simple folder layout and configuration format (in JSON) which both play nicely with Git. The intent is that what-you-have-is-what-you-get, where the folder structure is directly reflected as a website and its URLs. Meta data is folded in via simple JSON files and metadata gleaned from the Markdown documents, and cache files are used where they benefit performance (and make sense logically).

## Approach

Chronicle 1.1 is based on a number of global (but namespaced) top level objects. These objects provide a clear API and simple object model, with a minimal code size. The approach requires PHP5.4 or better.

Singletons are used where only a single instance makes sense, i.e., a site has only one theme and only one document manager. This clearly states the promise and intent, and keeps the syntax cleaner.

## Example theme

The best way to see how ChronicleMD works is to see an example theme file. This example uses a single theme page and a few part files for an entire site.

* See [index.php](index.php) for an example of the template outlined below
* See [chronicleMD.php](chronicleMD.php) for an example prototype API implementation.

### Startup

~~~~
<?php // index.php in project root

/* ChronicleMD startup */

use robotpony\chronicleMD as site;

site\on::startup( /* Named parameters that override config.json */ );

?>
~~~~

Startup is simple and explicit in a theme file. It allows a site to pick a namespace alias and set up a page's parameters. The theme signals the startup event (`on::startup`) if it wants to enable event based plugins and use profiling and caching.

### Show a theme part

~~~~

<?= site\theme::header(); ?>

~~~~

Notice that the theme manager can show any named part, as in `site\theme::header()`, `site\theme::nav()`, or any named theme file you have in your theme folder. ChronicleMD will load theme files specific to the *section* being served (much like WP) based on the theme part and section (e.g., `site\theme::footer_blog()`, which serves up `footer_blog.php` from the current theme folder).

### "The loop" (or, getting at posts or folders of documents)
~~~~
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
~~~~

The post loop is similar to WP's, except it uses objects returned from static named functions. The `documents` object returns a set of documents for the given section (in this case, `blog` as named by the static function name). Parameters are named and define the set and properties of the posts returned.

### Additional  loops

~~~~
<aside>
<h2>Recent posts</h2>
<?php 
	$params = array(
		'max-posts' => 10,
		'exclude-current' => true
	);
	foreach (site\documents::blog($params) as $post) { 
?>
	<a href="<?= $post->url(); ?>"><?= $post->title(); ?></a>
<?php } ?>
</aside>
~~~~

A theme page template can use as many document loops as needed.

~~~~
<aside>
<h2>Current projects</h2>
<?php 
	foreach (site\documents::projects_current() as $post) { 
?>
	<a href="<?= $post->url(); ?>"><?= $post->title(); ?></a>
<?php } ?>
</aside>
~~~~

The document manager can reference any folder from the root of a site, in this case `projects/current` (based on the `::projects_current()` request).

### EOF

~~~~
<?= site\theme::footer(); ?>
<?php site\on::eof(); ?>
~~~~

A theme is closed off with any remaining theme template pages and the final event (`eof`).

Events are currently explicit, making ChronicleMD themes a bit more technical than most engines, but also more explicit and transparent. We may revisit this if the eventing functions prove to be troublesome.

