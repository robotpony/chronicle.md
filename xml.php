<?='<?xml version="1.0" encoding="UTF-8"?>'?>
<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/">

<channel>
	<title><?= $chronicle->settings->site->name ?></title>
	<atom:link href="<?= $chronicle->settings->site->feedURL ?>" rel="self" type="application/rss+xml" />
	<link><?= $chronicle->settings->site->URL ?></link>
	<description><?= $chronicle->settings->site->tagline ?></description>
	<lastBuildDate><?= $chronicle->lastUpdated() ?></lastBuildDate>
	<language>en-US</language>
	<sy:updatePeriod>hourly</sy:updatePeriod>
	<sy:updateFrequency>1</sy:updateFrequency>

<?php while ( ( $p = $chronicle->nextPost() ) ) { ?>

		<item>
			<title><?= $p->title ?></title>
			<link><?= $chronicle->settings->site->URL ?><?= $p->url ?></link>
			<pubDate><?= date('r', strtotime($p->published)); ?></pubDate>
			<dc:creator><?= $p->author ?></dc:creator>
					<category><![CDATA[<?= $p->categories ?>]]></category>
	
					<guid isPermaLink="false"><?= $chronicle->settings->site->URL ?>/guids/<?= $p->guid ?></guid>
			
					<description><![CDATA[<?= $p->excerpt ?>]]></description>
					<content:encoded><![CDATA[<?= $p->content ?>]]></content:encoded>
	
			<slash:comments><?= $p->comments ?></slash:comments>
		</item>
		
<?php } ?>

	</channel>
</rss>
