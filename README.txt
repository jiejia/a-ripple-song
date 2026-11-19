=== A Ripple Song ===
Contributors: jiejia
Donate link: https://github.com/jiejia/
Tags: podcast, rss, feed, itunes
Requires at least: 6.6
Tested up to: 7.1
Requires PHP: 8.2
Stable tag: 0.5.5
License: GPL-3.0
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Publish a podcast on WordPress with a Podcasting 2.0 RSS feed, a site-wide audio player, and play statistics.

== Description ==

[A Ripple Song](https://aripplesong.com/) adds podcast publishing to WordPress. Publish episodes on your site, submit a single RSS feed to Apple Podcasts, Spotify, and other directories, and give listeners a player at the bottom of every page.

The plugin works with any WordPress theme. For episode cards, show pages, and a matching player, explore the [dedicated podcast themes](https://aripplesong.com/themes/).

= Features =

* Set up your show with a title, cover art, description, language, and author.
* Publish an RSS feed with support for Apple Podcasts and Podcasting 2.0. The default path is `/feed/podcast/`.
* Organize episodes with categories and tags. Add transcripts, chapters, and credits for hosts and guests.
* Give listeners a site-wide player with a playlist and a Now Playing panel. Choose from 15 styles, each available in light and dark. Playback continues between pages when the active theme uses swup.
* Track play counts and trends, see your most-played episodes, and check RSS feed health from the dashboard.
* Set a release schedule and add subscription links for more than 30 platforms.
* The plugin is ready for translation into other languages.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`, or go to **Plugins → Add New Plugin → Upload Plugin** to install the ZIP file.
2. Activate A Ripple Song in the WordPress dashboard.
3. Open **A Ripple Song → Podcast Settings** and enter your show's title, cover art, description, language, author, owner details, and Apple Podcasts category.
4. To customize your setup, use **Host & Guest Settings**, **Subscription Settings**, and **Player Settings** under **A Ripple Song**.
5. Go to **Episodes → Add New Episode**, choose an audio file, and publish the episode.
6. Copy the RSS feed URL from **Podcast Settings** and submit it to your chosen podcast directories.

== Frequently Asked Questions ==

= Where can I find my podcast's RSS feed URL? =

Copy the URL at the top of **A Ripple Song → Podcast Settings**. The default URL is `https://your-site.example/feed/podcast/`. If your site uses plain permalinks, use `https://your-site.example/?feed=podcast`.

= Why does the RSS feed return a 404 or redirect? =

WordPress may need to refresh its rewrite rules. Go to **Settings → Permalinks** and click **Save Changes**. The plugin also refreshes these rules once, the first time an administrator opens the WordPress admin area.

= Why are the duration and file size missing? =

The plugin uses the getID3 library bundled with WordPress to read these details from audio files stored on your site, including files in the Media Library. For remotely hosted audio, these fields stay empty. After replacing a local audio file, save the episode again to update its details.

= Why is the live spectrum or waveform missing? =

If you host audio on another domain, its server must send an `Access-Control-Allow-Origin` header that allows your site to read the file. Without it, the browser can't analyze the audio for the live spectrum or waveform. The waveform is hidden on phones.

= How does the plugin count plays? =

When a listener starts an episode, the player sends a request to `/wp-json/a-ripple-song/v1/episodes/{id}/plays`. The plugin skips drafts and previews. By default, it uses a salted hash of the listener's IP address to count repeat plays of the same episode only once within 30 minutes.

= Can my theme customize the player? =

Yes. To override a front-end template, copy it from `resources/templates/` to the corresponding path under `{theme}/a-ripple-song/`. For example, copy `player/skins/ars-default.php` to `{theme}/a-ripple-song/player/skins/ars-default.php`.

Use `add_theme_support( 'a-ripple-song-player' )` to supply your own player styles and skip the plugin's skin stylesheet. If your theme's directory name matches a player style, such as `ars-petal`, the plugin selects that style when you activate the theme.

= Can my theme display chapters and transcripts? =

Yes. `\Jiejia\ARippleSong\Episodes\Chapters::forPost( $post )` reads Podcasting 2.0 chapter files and returns the start time, end time, title, and URL of each chapter. `\Jiejia\ARippleSong\Episodes\Transcript::forPost( $post )` returns the transcript URL and its cues, including the start time, speaker, and text. It supports WebVTT, SRT, Podcasting 2.0 JSON, and plain text. The plugin caches the chapter and transcript files it loads.

= Which hooks can I use? =

* `a_ripple_song_play_counted` (action) runs after the plugin counts a play and receives the episode ID and the updated count.
* `a_ripple_song_play_dedupe_seconds` (filter) sets the number of seconds during which repeat plays are ignored. Return `0` to count every play.
* `a_ripple_song_global_player_enabled` (filter) controls whether the site-wide player is shown.
* `a_ripple_song_episode_meta_enabled` (filter) controls whether the episode's play, favorite, and play count bar is shown.
* `a_ripple_song_player_episode_cover` (filter) changes the cover art the player displays for an episode.
* `a_ripple_song_post_fields_saved` and `a_ripple_song_settings_saved` (actions) run after the plugin saves episode fields or a settings page, respectively.

== Privacy ==

* The plugin uses a salted hash of the listener's IP address to avoid counting repeat plays of the same episode. By default, it keeps the hash in a WordPress transient for 30 minutes. The plugin never stores the raw IP address. It records daily play totals for each episode in the `{prefix}ars_play_stats` table.
* The player stores playback position, playlist, volume, playback speed, favorites, and cached waveforms on the listener's device in the browser's `localStorage`.

== Screenshots ==

1. The A Ripple Song dashboard with play statistics and feed health checks
2. Podcast settings for show details and the RSS feed
3. Episode details in the WordPress editor
4. The site-wide player and its Now Playing panel
5. The podcast RSS feed at `/feed/podcast/`

== Changelog ==

= 0.5.5 =
* Simplify the feature overview, clarify podcast and RSS feed settings, and link to the dedicated podcast themes.
* Expand the GitHub installation guide to cover installation from the WordPress dashboard and with WP-CLI.

= 0.5.4 =
* Escape SVG icons, audio player markup, and field label tags before rendering.
* Correct XML escaping in RSS text, attributes, and URLs, including content with HTML named entities.
* Remove invalid XML characters from CDATA sections while preserving article HTML.

= 0.5.3 =
* Remove bundled player fonts to reduce the plugin download size.
* Use fonts installed on the listener's device.

= 0.5.2 =
* Use fonts installed on the listener's device instead of loading Google Fonts.
* Escape dynamic template output and give JavaScript globals distinct names.
* Improve the Terminal and Soft player layouts on narrow screens, including the Terminal progress bar.

= 0.5.1 =
* Document how the plugin checks cover images hosted on other sites.
* Add a link to the development repository and instructions for building from source.

= 0.5.0 =
* First public release.

== Upgrade Notice ==

= 0.5.4 =
Improves output escaping and prevents RSS parsing errors caused by HTML named entities or invalid XML characters.

= 0.5.3 =
Reduces the download size by using fonts installed on the listener's device. The player's typography may vary by device.

= 0.5.2 =
Stops loading Google Fonts, improves output escaping, and refines the player layout on narrow screens.

= 0.5.1 =
Documentation update only.

= 0.5.0 =
First public release.
