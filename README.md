# ChronicleMD.md

*Version: 1.1*

ChronicleMD is tool for publishing dynamic Markdown websites. It serves up blog style sites based on folders of Markdown and other plain text files.

**How it works**

Place your pages and post files in some sort of directory structure.  Install the `.htaccess` and `delegator.php` and copy the default theme from the `install/` folder.

By default ChronicleMD displays a list of posts (for `/`). Standard template functions are available for displaying post parts, lists, next, prev, and so on.

ChronicleMD templates are clean and simple PHP 5.4+. Example:

    <?= site\settings::page_title(); ?>

A full example is available as used for my weblog `warpedvisions.org`: https://github.com/robotpony/warpedvisions.org

## Requirements

* PHP 5.4+ (w/json)
* Apache with rewrites enabled

## Features
	
1. Templates are standard PHP files arranged in folders as if they were your site. One file per directory (or just one at the root)
2. Listings are available for any given root.
3. A simple template API is available.

## API

## Site settings 

Loaded from `site.json`, with defaults applied to the standard set.

	<?= $ChronicleMD->settings->site->name ?>
	<?= $ChronicleMD->settings->site->any_variable_you_add ?>

If a configuration value isn't found, a blank is returned.

If you want to sort the files that ChronicleMD loads by time, add this to your `site.json`:

* `"site": "modified"`
	* This sorts files by file modified date 

This can be helpful when you want to display blog posts in order.

## The loop

Showing posts (one or more) is similar to WordPress:

	<?php while ( ( $p = $ChronicleMD->nextPost() ) ) { ?>
		<section>
	
			<h1><a href="<?= $p->url ?>" title="Published on <?= $p->published ?>">
				<?= $p->title ?></a>
			</h1>
	
			<?= $p->content ?>
	
		</section>
	<?php } ?>

Other template APIs can be made available on request.