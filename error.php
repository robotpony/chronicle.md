<!DOCTYPE html>
<html lang="">
<head>
  <meta charset="utf-8">
	<title>warpedvisions.org</title>

	<link href="//netdna.bootstrapcdn.com/font-awesome/3.1.1/css/font-awesome.css" rel="stylesheet">
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700' rel='stylesheet' type='text/css'>

<!--[if lt IE 9]>
	<script src="//html5shiv.googlecode.com/svn/trunk/html5.js" onload="window.ieshiv=true;"></script>
<![endif]-->

<style>
* {
	font: 300 1em/150% 'Open Sans', sans-serif;	
}
a {
	font-weight: bold;
	color: #555;
}
h1 {
	font: 600 3em/110% 'Open Sans', sans-serif;
	width: 100%;


}
h1 > var {
	font: 100 1em/110% 'Open Sans', sans-serif;
	display: block;
	font-style: normal;
	float: right;	
	color: #ddd;
}
pre {
	font: normal .75em/120% Menlo, monospace;
	margin-top: 2em; padding-top: 2em;
	border-top: 1px solid #f0f0f0;
	color: #aaa;
}
section {
	width: 30em;
	margin: 10% auto;
	border-left: 20px solid #ddd; padding-left: 2em;
}
</style>

</head>
<?php flush(); ?><body>


<main><div>

	<section>

		<h1>Chronicle error <var><?= $_GET['c'] ?></var></h1>
		
		<p>There was an internal Chronicle error.  You can try visiting the <a href="/">site</a> after correcting the issue.</p>

		<aside class="debug"><div>
		<pre><?= $_GET['e'] ?></pre>
		</div></aside>

	</section>
		
</div></main>

</body>
</html>

