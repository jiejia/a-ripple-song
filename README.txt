=== A Ripple Song ===
Contributors: jiejia
Donate link: https://github.com/jiejia/
Tags: podcast, rss, feed, itunes, podcasting2.0
Requires at least: 6.6
Tested up to: 7.1
Requires PHP: 8.2
Stable tag: 0.5.1
License: GPL-3.0
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Podcast publishing for WordPress: episodes, an Apple Podcasts & Podcasting 2.0 RSS feed, a site-wide audio player and play statistics.

== Description ==

A Ripple Song turns a WordPress site into a podcast site. Publish episodes with their audio and podcast metadata, submit one RSS feed to Apple Podcasts, Spotify and other directories, and let visitors listen through a player bar that stays at the bottom of every page.

It works with any theme. The A Ripple Song podcast themes are designed around it, with episode cards, show pages and a matching player style.

Documentation: https://aripplesong.com/

= Episodes =

* An `Episodes` post type (URLs under `/podcasts/`) with tags and hierarchical episode categories (`/podcast-category/`); categories can have an image and an icon.
* Duration, file size and MIME type are filled in automatically when an episode is saved, for audio in the media library or elsewhere on this site.
* Per-episode metadata: cover, episode type (full / trailer / bonus), episode and season numbers, explicit flag, iTunes block, iTunes title, author override, subtitle and summary.
* Podcasting 2.0 fields: transcript, chapters, soundbites and a custom GUID.
* Hosts and guests chosen from the site's users.
* A banner image and a "Featured" switch for themes, also switchable from the episode list.
* `.m4a` / `.m4b` uploads are stored as `audio/x-m4a`, the type Apple Podcasts expects, and `.json` uploads are allowed for chapters and transcripts.

= Podcast RSS feed =

* Served at `/feed/podcast/` (also `/podcast/feed/` and `?feed=podcast`) with the latest 100 episodes.
* iTunes tags: show and episode artwork, categories, owner, author, explicit, type, block, complete, title and new-feed-url.
* Podcasting 2.0 tags: locked, guid, funding, person, transcript, chapters, soundbite and updateFrequency.
* Cover images are checked against Apple's artwork rules (square JPG/PNG, 1400-3000 px, at most 512 KB, no transparency).
* Feed fixes: non-ASCII file names are percent-encoded, short descriptions are padded for validators, and plain text summaries are built from excerpts.

= Global player =

* A player bar at the bottom of every front-end page, with a playlist and a full-screen "Now Playing" panel. With a theme that uses swup, playback continues across pages.
* 15 player styles, each with a light and a dark variant; colors can follow the theme, the visitor's system, or stay light or dark.
* Progress shown as a plain bar, a bar with a live spectrum, or a waveform of the whole episode.
* A play / favorite / play count bar on single episode pages, placed before or after the content.
* The playback position and playlist are saved in the visitor's browser and restored on the next visit.

= Statistics =

* Plays are counted through the REST API; repeat plays from the same visitor within 30 minutes are counted once.
* A dashboard with play totals, a daily trend, top episodes for the period and of all time, monthly releases and episode types, for the last 7, 30, 90 or 365 days.
* Health checks list show settings and episodes that miss what podcast directories need, such as a cover, a category, audio or a duration.
* The play count of each episode is shown in its edit screen.

= More =

* Subscription links for 30+ platforms (Apple Podcasts, Spotify, YouTube Music, Pocket Casts, Overcast, Xiaoyuzhou, Ximalaya and more) for themes to display.
* A release schedule (daily, weekly, every other week or monthly) for themes and the feed.
* Translations: English and Simplified Chinese.

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

== External services ==

Chapters and transcript files: when a theme shows an episode's chapters or transcript (see the FAQ), files in the media library are read from disk. A file the editor linked from another site is downloaded by this site's server from that URL and cached for 12 hours. The request goes only to the address the editor entered and carries no visitor data.

Cover images: when a show or episode cover is saved, the plugin checks it against Apple's artwork rules (type, file size and transparency). A cover from the media library is read from disk. A cover the editor linked from another site is checked by this site's server, which requests the first 64 KB of the file from that URL. The request goes only to the address the editor entered, happens only when the setting is saved, and carries no visitor data.

Player styles other than "Default" load their fonts from Google Fonts (`https://fonts.googleapis.com`) on front-end pages that show the player. The visitor's browser then sends a request, including its IP address, to Google. No fonts are loaded when the "Default" style is used or when the theme styles the player itself. Google's terms: https://policies.google.com/terms, privacy policy: https://policies.google.com/privacy.

== Third-party libraries ==

The plugin bundles the following open-source code. Full license texts are in `THIRD-PARTY-LICENSES.txt`.

* Howler.js - (c) 2013-2020 James Simpson and GoldFire Studios, Inc. - MIT License - https://howlerjs.com/
* wavesurfer.js - (c) 2012-2023 katspaugh and contributors - BSD 3-Clause License - https://wavesurfer.xyz/
* Tailwind CSS - (c) Tailwind Labs, Inc. - MIT License - https://tailwindcss.com/
* daisyUI - (c) 2020 Pouya Saadeghi - MIT License - https://daisyui.com/
* Lucide icons - (c) 2026 Lucide Icons and Contributors - ISC License - https://lucide.dev/

== Source code ==

The files in `public/dist/` are built and minified. Their sources are in `resources/assets/`, and the full development repository, with the build configuration, is at https://github.com/jiejia/a-ripple-song.

To build the assets yourself, install Node.js, then run in the plugin folder:

1. `npm install`
2. `npm run build` - compiles the player script, the waveform script and the player style sheets into `public/dist/`.

== Screenshots ==

1. `A Ripple Song` -> `Dashboard` with play statistics and health checks
2. `A Ripple Song` -> `Podcast Settings`
3. Episode details on the `Episodes` edit screen
4. The global player bar and its "Now Playing" panel
5. `/feed/podcast/` RSS output

== Changelog ==

= 0.5.0 =
* First public release.

== Upgrade Notice ==

= 0.5.0 =
First public release.
