# Chronicle.md

*Version: proof of concept*

A micro blogging and website tool for publishing Markdown, PHP, and HTML files. 
	
	Usage:
	
		$content = new ChronicleMD();
			
		print $content;
		
		// That's it!
		

Requirements:

* PHP 5.3+
* PrestoPHP 1.1+
* Markdown extra

Concepts:
	
1. Templates are standard PHP files arranged in folders as if they were your site. One per type of thing you would like to have on your site.
2. Listings are available for any given root.
3. Caching is not yet available, but planned for things like listings, Markdown files, and so on.

## Example theme file

    <?php 
    	include "lib/chronicle.md.php";
		
    	try {
    		$site = new ChronicleMD();
    	} catch (Exception $e) {
    		die(print_r($e, true));
    	}
    ?><!DOCTYPE html>
    <html lang="">
    <head>
      <meta charset="utf-8">
    	<title><?= $site->settings->name ?></title>
    	<meta name="description" content="<?= $site->settings->description ?>" />

    	<link rel="stylesheet" href="/styles/main.css" />

    </head>
    <?php flush(); ?><body>

    <header><div>
    <h1><a href="/"><?= $site->settings->name ?> <em><?= $site->settings->tagline ?></em></a></h1>
    <div></header>
    
    <main><div>
    <?= $site; ?>
    </div></main>

    <footer><div>
    </div></footer>

    </body>
    </html>
