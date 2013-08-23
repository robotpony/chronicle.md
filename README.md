# Chronicle.md

*Version: 0.9*

Chronicle is a small Markdown blog engine. It serves up weblog style sites based on folders of Markdown and other plain text files.

**How it works**

Place your pages and post files in some sort of directory structure. 

	index.php <-- this is your "home" theme
	site.json <-- these are your site settings
	blog/
		2012/01/some-post.md
		2011/05/another-post.md
	projects/
		more-content.md 

By default Chronicle displays a list of posts (for `/`). Standard template functions are available for displaying post parts, lists, next/prev, and so on.

Its templates are simple PHP (similar to WP templates, but not split into pieces by default):

    <?= $site->settings->name; ?>

A full example is available as used for my weblog `warpedvisions.org`: https://github.com/robotpony/warpedvisions.org

## Requirements

* PHP 5.3+ (w/json)
* Apache with rewrites enabled
* PrestoPHP 1.1+
* Markdown extra

## Installation

1. Install `PrestoPHP`, `Markdown Extra`, and `Chronicle` to the `lib` folder in a web root (see `gitmodules` below)
2. Symlink `combobulate.php` to the web root.
3. Copy the example `htaccess` file to the web root.

Your web root will now look something like:

	chronicle	-> lib/chronicle.md/chronicle.php
	.htaccess
	index.php (Main template)
	site.json (Settings)
	blog/     (Markdown blog posts)
	pages/    (More markdown things)
	js
	lib
	styles

URLs are simply the expected path to given Markdown files (`/blog/2012/some-file.md`) or to the folder listing (`/blog/`).

**Git modules**

	[submodule "lib/presto"]
		path = lib/presto
		url = git@github.com:robotpony/Presto.git
	[submodule "lib/markdown"]
		path = lib/markdown
		url = https://github.com/gavroche/php-markdown-extra.git
	[submodule "lib/chronicle.md"]
		path = lib/chronicle.md
		url = git@github.com:robotpony/chronicle.md.git


## Features
	
1. Templates are standard PHP files arranged in folders as if they were your site. One file per directory (or just one at the root)
2. Listings are available for any given root.
3. A simple template API is available.

## API

## Site settings 

Loaded from `site.json`, with defaults applied to the standard set.

	<?= $chronicle->settings->site->name ?>
	<?= $chronicle->settings->site->any_variable_you_add ?>

If a configuration value isn't found, a blank is returned.

If you want to sort the files that chronicle loads by time, add this to your `site.json`:

* `"site": "modified"`
	* This sorts files by file modified date 

This can be helpful when you want to display blog posts in order.

## The loop

Showing posts (one or more) is similar to WordPress:

	<?php while ( ( $p = $chronicle->nextPost() ) ) { ?>
		<section>
	
			<h1><a href="<?= $p->url ?>" title="Published on <?= $p->published ?>">
				<?= $p->title ?></a>
			</h1>
	
			<?= $p->content ?>
	
		</section>
	<?php } ?>

Other template APIs can be made available on request.