=== A Ripple Song ===
Contributors: jiejia
Donate link: https://github.com/jiejia/
Tags: podcast, rss, feed, itunes
Requires at least: 6.6
Tested up to: 7.1
Requires PHP: 8.2
Stable tag: 0.5.4
License: GPL-3.0
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Podcast publishing for WordPress: episodes, an Apple Podcasts & Podcasting 2.0 RSS feed, a site-wide audio player and play statistics.

== Description ==

[A Ripple Song](https://aripplesong.com/) turns a WordPress site into a podcast site. Publish episodes with their audio and podcast metadata, submit one RSS feed to Apple Podcasts, Spotify and other directories, and let visitors listen through a player bar that stays at the bottom of every page.

It works with any theme. [The dedicated themes](https://aripplesong.com/themes/) are designed around it, with episode cards, show pages and a matching player style.

= Features =

* Configure your podcast and its RSS feed with a title, cover art, description, language, and author, plus settings for Apple Podcasts and Podcasting 2.0.
* Publish episodes with categories, tags, transcripts, chapters, and host and guest credits.
* A site-wide player with a playlist, a Now Playing panel, and 15 styles in light and dark. Themes that use swup support continuous playback across pages.
* A statistics dashboard with play counts, listening trends, top episodes, and feed health checks.
* Subscription links for 30+ platforms and a configurable release schedule.
* Supports internationalization.

= Podcast RSS feed =

* Served at `/feed/podcast/` (also `/podcast/feed/` and `?feed=podcast`) with the latest 100 episodes.
* iTunes tags: show and episode artwork, categories, owner, author, explicit, type, block, complete, title and new-feed-url.
* Podcasting 2.0 tags: locked, guid, funding, person, transcript, chapters, soundbite and updateFrequency.
* Cover images are checked against Apple's artwork rules (square JPG/PNG, 1400-3000 px, at most 512 KB, no transparency).
* Feed fixes: non-ASCII file names are percent-encoded, short descriptions are padded for validators, and plain text summaries are built from excerpts.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`, or install the ZIP through `Plugins` -> `Add New Plugin` -> `Upload Plugin`.
2. Activate the plugin.
3. Go to `A Ripple Song` -> `Podcast Settings` and fill in the show details: cover, title, description, language, author, owner and Apple Podcasts category.
4. Optionally set up `Host & Guest Settings`, `Subscription Settings` and `Player Settings` under `A Ripple Song`.
5. Create episodes from `Episodes` -> `Add New Episode` and choose an audio file.
6. Copy the feed URL from `Podcast Settings` (by default `https://your-site.example/feed/podcast/`) and submit it to podcast directories.

== Frequently Asked Questions ==

= What is the podcast RSS URL? =

By default it is `https://your-site.example/feed/podcast/`. If permalinks are disabled, use `https://your-site.example/?feed=podcast`. The exact URL is shown at the top of `A Ripple Song` -> `Podcast Settings`.

= Why does /feed/podcast/ return 404 or redirect? =

Usually rewrite rules have not been flushed. Go to `Settings` -> `Permalinks` and click `Save Changes`. The plugin also flushes them once the first time an administrator opens the admin area.

= Why are duration or file size not filled in automatically? =

They are read with the getID3 library bundled with WordPress, and only for audio files on this site (the media library or another local path). Audio hosted on another site cannot be measured, so its duration and size stay empty. Re-saving the episode after replacing the audio detects them again.

= Why does the live spectrum or waveform not show? =

Browsers only let the player analyse audio it is allowed to read. Audio on another site needs CORS headers (`Access-Control-Allow-Origin`) for the spectrum and the waveform. The waveform is not drawn on phones.

= How are plays counted? =

When a visitor starts an episode, the player sends one request to `/wp-json/a-ripple-song/v1/episodes/{id}/plays`. Plays of drafts and previews are not counted, and the same visitor (by a salted hash of the IP address) is counted at most once every 30 minutes per episode.

= Can my theme style the player? =

Yes. A theme can override any front-end template by copying it from `resources/templates/` to `{theme}/a-ripple-song/`, e.g. `{theme}/a-ripple-song/player/skins/ars-default.php`. A theme that declares `add_theme_support( 'a-ripple-song-player' )` styles the player itself, and the plugin then loads no skin stylesheet. A theme whose folder name matches a player style (such as `ars-petal`) selects that style when it is activated.

= Can my theme show chapters and transcripts? =

Yes. `\Jiejia\ARippleSong\Episodes\Chapters::forPost( $post )` returns the episode's chapters (start, end, title, URL), and `\Jiejia\ARippleSong\Episodes\Transcript::forPost( $post )` returns the transcript URL with its cues (start, speaker, text). They read WebVTT, SRT, Podcasting 2.0 JSON and plain text files, and cache the result.

= Which hooks are available? =

* `a_ripple_song_play_counted` (action) - after a play is counted; receives the episode ID and the new count.
* `a_ripple_song_play_dedupe_seconds` (filter) - seconds within which repeat plays are ignored; `0` counts every play.
* `a_ripple_song_global_player_enabled` (filter) - show or hide the global player.
* `a_ripple_song_episode_meta_enabled` (filter) - show or hide the episode meta bar.
* `a_ripple_song_player_episode_cover` (filter) - the cover the player shows for an episode.
* `a_ripple_song_post_fields_saved` and `a_ripple_song_settings_saved` (actions) - after episode fields or a settings page are saved.

== Privacy ==

* Play counting stores a salted hash of the visitor's IP address in a transient for 30 minutes; the raw IP address is never stored. Daily play totals per episode are kept in the `{prefix}ars_play_stats` table.
* The player saves the playback position, playlist, volume, speed and favorite episodes, plus cached waveforms, in the visitor's browser (localStorage); none of it is sent to the server.

== Screenshots ==

1. `A Ripple Song` -> `Dashboard` with play statistics and health checks
2. `A Ripple Song` -> `Podcast Settings`
3. Episode details on the `Episodes` edit screen
4. The global player bar and its "Now Playing" panel
5. `/feed/podcast/` RSS output

== Changelog ==

= 0.5.5 =
* Simplify the feature overview, clarify podcast and RSS feed settings, and link to the dedicated podcast themes.
* Expand the GitHub installation guide with WordPress admin and WP-CLI instructions.

= 0.5.4 =
* Apply final output escaping to SVG icons, audio player markup and field label tags.
* Fix RSS XML escaping for text, attributes and URLs, including HTML named entities.
* Remove invalid XML characters from CDATA content while preserving article HTML.

= 0.5.3 =
* Remove bundled player fonts to reduce the plugin download size.
* Use fonts available on the visitor's device without downloading additional fonts.

= 0.5.2 =
* Use fonts available on the visitor's device instead of requesting Google Fonts.
* Escape dynamic template output and use distinct names for JavaScript globals.
* Improve the Terminal and Soft player layouts on narrow screens, including the Terminal progress bar.

= 0.5.1 =
* Readme: the External services section now explains the request made to check cover images linked from another site.
* Readme: added a Source code section with the development repository and the build steps.

= 0.5.0 =
* First public release.

== Upgrade Notice ==

= 0.5.4 =
Improves output escaping and fixes RSS feeds that could fail to parse when text contains HTML named entities or invalid XML characters.

= 0.5.3 =
Bundled fonts have been removed to reduce the download size. The player uses fonts available on your device, so its typography may vary between devices.

= 0.5.2 =
The player no longer downloads Google Fonts. This update also improves output escaping and player layouts on narrow screens.

= 0.5.1 =
Documentation update only. No code changes.

= 0.5.0 =
First public release.
