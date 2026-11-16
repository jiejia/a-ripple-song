<?php
/**
 * Podcast RSS feed with iTunes and Podcasting 2.0 tags.
 *
 * @var array<string,mixed> $channel Channel data, see PodcastFeed::channel().
 * @var array<int,array<string,mixed>> $items Episode data, see PodcastFeed::item().
 */

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Feeds\PodcastFeed;

// Printed from PHP so short_open_tag cannot mistake it for a PHP tag.
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0"
    xmlns:atom="http://www.w3.org/2005/Atom"
    xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd"
    xmlns:content="http://purl.org/rss/1.0/modules/content/"
    xmlns:podcast="https://podcastindex.org/namespace/1.0">
    <channel>
        <title><?php echo esc_xml($channel['title']); ?></title>
        <link><?php echo esc_xml(esc_url_raw($channel['link'])); ?></link>
        <atom:link href="<?php echo esc_xml(esc_url_raw($channel['feedUrl'])); ?>" rel="self" type="application/rss+xml" />
        <language><?php echo esc_xml($channel['language']); ?></language>
        <?php // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- CDATA sections are escaped by PodcastFeed::cdata(). ?>
        <description><![CDATA[<?php echo PodcastFeed::cdata($channel['description']); ?>]]></description>
        <itunes:summary><![CDATA[<?php echo PodcastFeed::cdata($channel['description']); ?>]]></itunes:summary>
        <?php // phpcs:enable ?>
        <itunes:subtitle><?php echo esc_xml($channel['subtitle']); ?></itunes:subtitle>
        <itunes:author><?php echo esc_xml($channel['author']); ?></itunes:author>
        <itunes:explicit><?php echo esc_xml($channel['explicit']); ?></itunes:explicit>
        <lastBuildDate><?php echo esc_xml($channel['lastBuildDate']); ?></lastBuildDate>
        <itunes:owner>
            <itunes:name><?php echo esc_xml($channel['ownerName']); ?></itunes:name>
            <itunes:email><?php echo esc_xml($channel['ownerEmail']); ?></itunes:email>
        </itunes:owner>
        <?php if ($channel['cover'] !== '') : ?>
        <itunes:image href="<?php echo esc_xml(esc_url_raw($channel['cover'])); ?>" />
        <?php endif; ?>
        <?php foreach ($channel['categories'] as $category) : // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local to Template::include(). ?>
        <itunes:category text="<?php echo esc_xml($category[0]); ?>">
            <?php if (isset($category[1])) : ?>
            <itunes:category text="<?php echo esc_xml($category[1]); ?>" />
            <?php endif; ?>
        </itunes:category>
        <?php endforeach; ?>
        <?php if ($channel['copyright'] !== '') : ?>
        <copyright><?php echo esc_xml($channel['copyright']); ?></copyright>
        <?php endif; ?>
        <?php if ($channel['itunesType'] !== '') : ?>
        <itunes:type><?php echo esc_xml($channel['itunesType']); ?></itunes:type>
        <?php endif; ?>
        <?php if ($channel['itunesTitle'] !== '') : ?>
        <itunes:title><?php echo esc_xml($channel['itunesTitle']); ?></itunes:title>
        <?php endif; ?>
        <?php if ($channel['itunesBlock']) : ?>
        <itunes:block>yes</itunes:block>
        <?php endif; ?>
        <?php if ($channel['itunesComplete']) : ?>
        <itunes:complete>yes</itunes:complete>
        <?php endif; ?>
        <?php if ($channel['newFeedUrl'] !== '') : ?>
        <itunes:new-feed-url><?php echo esc_xml(esc_url_raw($channel['newFeedUrl'])); ?></itunes:new-feed-url>
        <?php endif; ?>
        <?php if ($channel['locked']) : ?>
        <podcast:locked<?php if ($channel['lockedOwner']) : ?> owner="<?php echo esc_xml($channel['lockedOwner']); ?>"<?php endif; ?>>yes</podcast:locked>
        <?php endif; ?>
        <?php if ($channel['guid'] !== '') : ?>
        <podcast:guid><?php echo esc_xml($channel['guid']); ?></podcast:guid>
        <?php endif; ?>
        <?php if ($channel['appleVerify'] !== '') : ?>
        <podcast:txt purpose="applepodcastsverify"><?php echo esc_xml($channel['appleVerify']); ?></podcast:txt>
        <?php endif; ?>
        <?php if ($channel['schedule'] !== null) : ?>
        <podcast:updateFrequency<?php if ($channel['schedule']['rrule']) : ?> rrule="<?php echo esc_xml($channel['schedule']['rrule']); ?>"<?php endif; ?>><?php echo esc_xml($channel['schedule']['text']); ?></podcast:updateFrequency>
        <?php endif; ?>
        <?php if ($channel['generator'] !== '') : ?>
        <generator><?php echo esc_xml($channel['generator']); ?></generator>
        <?php endif; ?>
        <?php foreach ($channel['funding'] as $link) : ?>
        <podcast:funding url="<?php echo esc_xml(esc_url_raw(trim((string) $link['url']))); ?>"><?php echo esc_xml(trim((string) ($link['label'] ?? ''))); ?></podcast:funding>
        <?php endforeach; ?>

        <?php foreach ($items as $item) : // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local to Template::include(). ?>
        <item>
            <title><?php echo esc_xml($item['title']); ?></title>
            <link><?php echo esc_xml(esc_url_raw($item['link'])); ?></link>
            <guid isPermaLink="<?php echo $item['guidIsPermalink'] ? 'true' : 'false'; ?>"><?php echo esc_xml($item['guid']); ?></guid>
            <pubDate><?php echo esc_xml($item['pubDate']); ?></pubDate>
            <?php // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- PodcastFeed::cdata() encodes XML boundaries; article HTML is preserved as feed content. ?>
            <description><![CDATA[<?php echo PodcastFeed::cdata($item['summary']); ?>]]></description>
            <itunes:summary><![CDATA[<?php echo PodcastFeed::cdata($item['summary']); ?>]]></itunes:summary>
            <?php if ($item['content'] !== '') : ?>
            <content:encoded><![CDATA[<?php echo PodcastFeed::cdata($item['content']); ?>]]></content:encoded>
            <?php endif; ?>
            <?php // phpcs:enable ?>
            <?php if ($item['enclosure'] !== null) : ?>
            <enclosure url="<?php echo esc_xml(esc_url_raw($item['enclosure']['url'])); ?>" length="<?php echo esc_xml($item['enclosure']['length']); ?>" type="<?php echo esc_xml($item['enclosure']['type']); ?>" />
            <?php endif; ?>
            <?php if ($item['duration'] !== null) : ?>
            <itunes:duration><?php echo esc_xml((string) $item['duration']); ?></itunes:duration>
            <?php endif; ?>
            <itunes:explicit><?php echo esc_xml($item['explicit']); ?></itunes:explicit>
            <itunes:author><?php echo esc_xml($item['author']); ?></itunes:author>
            <?php if ($item['itunesTitle'] !== '') : ?>
            <itunes:title><?php echo esc_xml($item['itunesTitle']); ?></itunes:title>
            <?php endif; ?>
            <?php if ($item['subtitle'] !== '') : ?>
            <itunes:subtitle><?php echo esc_xml($item['subtitle']); ?></itunes:subtitle>
            <?php endif; ?>
            <?php if ($item['image'] !== '') : ?>
            <itunes:image href="<?php echo esc_xml(esc_url_raw($item['image'])); ?>" />
            <?php endif; ?>
            <?php foreach ($item['people'] as $person) : // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local to Template::include(). ?>
            <podcast:person role="<?php echo esc_xml($person['role']); ?>"<?php if ($person['href']) : ?> href="<?php echo esc_xml(esc_url_raw($person['href'])); ?>"<?php endif; ?><?php if ($person['img']) : ?> img="<?php echo esc_xml(esc_url_raw($person['img'])); ?>"<?php endif; ?>><?php echo esc_xml($person['name']); ?></podcast:person>
            <?php endforeach; ?>
            <?php if ($item['transcript'] !== null) : ?>
            <podcast:transcript url="<?php echo esc_xml(esc_url_raw($item['transcript']['url'])); ?>" type="<?php echo esc_xml($item['transcript']['type']); ?>" />
            <?php endif; ?>
            <?php if ($item['chapters'] !== null) : ?>
            <podcast:chapters url="<?php echo esc_xml(esc_url_raw($item['chapters']['url'])); ?>" type="<?php echo esc_xml($item['chapters']['type']); ?>" />
            <?php endif; ?>
            <?php foreach ($item['soundbites'] as $soundbite) : // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local to Template::include(). ?>
                <?php if ($soundbite['title'] !== '') : ?>
            <podcast:soundbite startTime="<?php echo esc_xml((string) $soundbite['start']); ?>" duration="<?php echo esc_xml((string) $soundbite['duration']); ?>"><?php echo esc_xml($soundbite['title']); ?></podcast:soundbite>
                <?php else : ?>
            <podcast:soundbite startTime="<?php echo esc_xml((string) $soundbite['start']); ?>" duration="<?php echo esc_xml((string) $soundbite['duration']); ?>" />
                <?php endif; ?>
            <?php endforeach; ?>
            <?php if ($item['episode'] > 0) : ?>
            <itunes:episode><?php echo esc_xml((string) $item['episode']); ?></itunes:episode>
            <?php endif; ?>
            <?php if ($item['season'] > 0) : ?>
            <itunes:season><?php echo esc_xml((string) $item['season']); ?></itunes:season>
            <?php endif; ?>
            <itunes:episodeType><?php echo esc_xml($item['episodeType']); ?></itunes:episodeType>
            <?php if ($item['block']) : ?>
            <itunes:block>yes</itunes:block>
            <?php endif; ?>
        </item>
        <?php endforeach; ?>
    </channel>
</rss>
