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

Concepts:
	
1. Templates are standard PHP files arranged in folders as if they were your site. One per type of thing you would like to have on your site.
2. Listings are available for any given root.
3. Caching is not yet available, but planned for things like listings, Markdown files, and so on.

