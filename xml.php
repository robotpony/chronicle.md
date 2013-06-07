<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	>

<channel>
	<title><?= $chronicle->settings->site->name ?></title>
	<atom:link href="<?= $chronicle->settings->site->feedURL ?>" rel="self" type="application/rss+xml" />
	<link><?= $chronicle->settings->site->URL ?></link>
	<description><?= $chronicle->settings->site->tagline ?></description>
	<lastBuildDate><?= $chronicle->lastUpdated() ?>Sun, 27 Jan 2013 19:52:07 +0000</lastBuildDate>
	<language>en-US</language>
	<sy:updatePeriod>hourly</sy:updatePeriod>
	<sy:updateFrequency>1</sy:updateFrequency>

<?php while ( ( $p = $chronicle->nextPost() ) ) ?>	
		<item>
			<title><?= $p->title ?></title>
			<link><?= $p->link ?></link>
			<pubDate><?= $p->pubDate ?>Tue, 15 Jan 2013 05:30:11 +0000</pubDate>
			<dc:creator><?= $p->author ?></dc:creator>
					<category><![CDATA[<?= $p->categories ?>]]></category>
	
					<guid isPermaLink="false"><?= $p->link ?>http://warpedvisions.org/?p=4519</guid>
			
					<description><![CDATA[<?= $p->excerpt ?>]]></description>
					<content:encoded><![CDATA[<?= $p->content ?>]]></content:encoded>
	
			<slash:comments>0</slash:comments>
		</item>
<?php } ?>

	</channel>
</rss>
