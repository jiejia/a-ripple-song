# A Ripple Song

A Ripple Song is a WordPress podcast plugin with episode publishing, an Apple Podcasts and Podcasting 2.0 RSS feed, a site-wide audio player, and play statistics. It is built with plain PHP on top of WordPress APIs, with a lightweight esbuild and Tailwind CSS frontend build.

<img src="" alt="A Ripple Song plugin screenshot" />

## Overview

This plugin turns a WordPress site into a podcast site. Publish episodes with their audio and podcast metadata, submit one RSS feed to Apple Podcasts, Spotify, and other directories, and let visitors listen through a player bar that stays at the bottom of every page. It works with any theme, and themes can restyle or fully take over the player when they want to.

## Features

- `Episodes` custom post type (URLs under `/podcasts/`) with tags and hierarchical episode categories that support an image and an icon.
- Duration, file size, and MIME type detected automatically when an episode is saved, for audio hosted on the site.
- Per-episode metadata: cover, episode type, episode and season numbers, explicit flag, iTunes fields, author override, subtitle, and summary.
- Podcasting 2.0 fields: transcript, chapters, soundbites, and a custom GUID, plus hosts and guests chosen from site users.
- Built-in `/feed/podcast/` RSS feed with iTunes tags and Podcasting 2.0 tags (locked, guid, funding, person, transcript, chapters, soundbite, updateFrequency).
- Cover images checked against Apple's artwork rules (square JPG/PNG, 1400–3000 px, at most 512 KB, no transparency).
- Global player bar with a playlist and a full-screen "Now Playing" panel; playback continues across pages with themes that use swup.
- 15 player styles, each with light and dark variants, and progress shown as a plain bar, a live spectrum, or a full-episode waveform.
- Play / favorite / play count bar on single episode pages, and playback position and playlist restored on the next visit.
- REST API play counting with per-visitor dedupe, a statistics dashboard with trends and top episodes, and health checks for missing feed requirements.
- Subscription links for 30+ podcast platforms and a release schedule for themes and the feed.
- Translation-ready structure with English and Simplified Chinese language files.

## Theme Integration

- Any front-end template can be overridden by copying it from `resources/templates/` to `{theme}/a-ripple-song/`.
- A theme that declares `add_theme_support( 'a-ripple-song-player' )` styles the player itself, and the plugin then loads no skin stylesheet.
- A theme whose folder name matches a player style (such as `ars-petal`) selects that style when it is activated.
- Hooks such as `a_ripple_song_play_counted`, `a_ripple_song_global_player_enabled`, and `a_ripple_song_player_episode_cover` let themes and plugins extend the behavior. See `README.txt` for the full list.

## Installation

### Release package

1. Download the `a-ripple-song.zip` release package.
2. In WordPress, go to `Plugins > Add New Plugin > Upload Plugin`.
3. Upload the zip file, install it, and activate the plugin.
4. Open `A Ripple Song > Podcast Settings` to configure your podcast feed metadata.

### Publishing your podcast

1. Fill in the show cover, title, description, language, author, owner, and Apple Podcasts category in `A Ripple Song > Podcast Settings`.
2. Optionally configure `Member Settings`, `Subscription Settings`, and `Player Settings`.
3. Create episodes from `Episodes > Add New Episode` and choose an audio file.
4. Copy the feed URL (by default `https://your-site.example/feed/podcast/`) and submit it to podcast directories.

### Runtime requirements

- WordPress `6.6+`
- PHP `8.2+`

## Development

Use the source version of the plugin when you need to customize, build, or package it locally.

```bash
npm install
```

Watch player styles and scripts during development:

```bash
npm run watch:css
npm run watch:js
npm run watch:waveform
```

Build production assets:

```bash
npm run build
```

Compile translation files:

```bash
wp i18n make-mo resources/lang
```

Build the packaged plugin zip:

```bash
npm run zip
```

Release a new version (sets the version, commits, tags, and pushes; GitHub Actions publishes the zip):

```bash
npm run release 0.5.1
```

## Tech Stack

- [WordPress Plugin API](https://developer.wordpress.org/plugins/)
- [esbuild](https://esbuild.github.io/)
- [PostCSS](https://postcss.org/)
- [Tailwind CSS](https://tailwindcss.com/)
- [daisyUI](https://daisyui.com/)
- [Howler.js](https://howlerjs.com/)
- [wavesurfer.js](https://wavesurfer.xyz/)
- [Lucide](https://lucide.dev/)

## Project Structure

- `app/` contains plugin logic such as custom post types, taxonomies, feeds, settings pages, the player, statistics, REST controllers, and service providers.
- `resources/templates/` contains PHP templates for the player, player skins, episode meta, feeds, dashboard, and settings fields.
- `resources/assets/` contains player scripts, player style sources, and icons.
- `resources/lang/` contains translation files.
- `public/dist/` contains the built player scripts and skin stylesheets.
- `bin/` contains build, packaging, and release scripts.

## Links

- Repository: [github.com/jiejia/a-ripple-song](https://github.com/jiejia/a-ripple-song)
- Website: [aripplesong.com](https://aripplesong.com/)

## Languages

- [English](README.md)
- [简体中文](README.zh.md)

## License

Released under the [GNU General Public License v3.0](LICENSE).
