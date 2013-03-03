# Chronicle.md

*Version: proof of concept*

A micro blogging and website tool for publishing Markdown, PHP, and HTML files. 

## Requirements

* PHP 5.3+
* PrestoPHP 1.1+
* Markdown extra

## Installation

1. Install `PrestoPHP`, `Markdown Extra`, and `Chronicle` to the `lib` folder in a web root.
2. Symlink `combobulate.php` to your web root.
3. Copy the example `htaccess` file to your web root.

Your web root should look something like:

	combobulate.php	-> lib/chronicle.md/combobulate.php
	.htaccess
	index.php (Main template)
	site.json (Main settings)
	blog/     (Markdown blog posts)
	pages/    (More markdown things)
	home.md   (More markdown)
	js
	lib
	styles

## Features
	
1. Templates are standard PHP files arranged in folders as if they were your site. One per type of thing you would like to have on your site.
2. Listings are available for any given root (not yet available).
3. Caching is not yet available, but planned for things like listings, Markdown files, and so on.

   