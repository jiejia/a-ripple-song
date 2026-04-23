=== A Ripple Song ===
Contributors: jiejia
Donate link: https://github.com/jiejia/
Tags: podcast, rss, feed, itunes, podcasting2.0
Requires at least: 6.6
Tested up to: 6.9
Requires PHP: 8.2
Stable tag: 0.5.0
License: GPL-3.0
License URI: https://www.gnu.org/licenses/gpl-3.0.html

A WordPress podcast plugin for publishing and managing podcast episodes, with podcast RSS generation for major podcast platforms.

== Description ==

A Ripple Song adds podcast publishing and management to WordPress. It provides podcast content types, episode metadata management, and a podcast RSS feed that can be submitted to major podcast platforms.

Documentation: https://aripplesong.me/

Key features:

* Podcast post type and categories for podcast publishing and management
* Automatic audio metadata extraction, including duration, file size, and format
* Built-in podcast RSS feed generation for iTunes and Podcasting 2.0 compatible distribution
* Internationalization support

Technical stack:

* Carbon Fields for WordPress custom fields
* getID3 for audio metadata analysis
* PHP-Scoper for PHP namespace isolation in release builds

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`, or install the ZIP through WP Admin.
2. Activate the plugin in WP Admin.
3. Go to `A Ripple Song` -> `Podcast Settings` and fill in the podcast channel metadata.
4. Create podcast episodes from `ARS Episodes` -> `Add New Episode`.
5. Open `/feed/podcast/` and submit the RSS feed to podcast platforms.

== Frequently Asked Questions ==

= What is the podcast RSS URL? =

By default it is `https://your-site.example/feed/podcast/`. If permalinks are disabled, use `https://your-site.example/?feed=podcast`.

= Why does /feed/podcast/ return 404 or redirect? =

Usually rewrite rules have not been flushed. Go to `Settings` -> `Permalinks` and click `Save`. The plugin also attempts a one-time admin-side flush.

= Why are duration or file size not filled automatically? =

The plugin uses getID3 to analyze audio when an episode is saved. For remote audio URLs, make sure the URL is reachable by the server and allow enough time for metadata detection.

== Screenshots ==

1. `A Ripple Song` -> `Podcast Settings`
2. Episode details on the `ARS Episodes` edit screen
3. `/feed/podcast/` RSS output

== Changelog ==

= 0.5.0 =
* Beta release: podcast post type, podcast RSS feed, admin settings, and episode metadata.

== Upgrade Notice ==

= 0.5.0 =
Beta release.
