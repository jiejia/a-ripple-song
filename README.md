# A Ripple Song

A Ripple Song is a classic WordPress podcast theme with built-in episode publishing, podcast RSS output, a persistent audio player, curated widgets, and bundled demo import data. It is built on Sage, Acorn, Carbon Fields, and a modern Vite-based frontend workflow.

<img src="screenshot.png" alt="A Ripple Song theme screenshot" />

## Overview

This theme is designed for podcast-first WordPress sites that also publish regular articles, author content, and curated homepage sections. It includes the podcast content model, feed configuration, playback UI, and admin tooling inside the theme package, so the core podcast experience works without a separate podcast plugin.

## Features

- Built-in podcast episode custom post type with audio upload support and episode metadata.
- Built-in `/feed/podcast/` RSS feed for Apple Podcasts, Spotify, and other podcast directories.
- Podcast Settings screen for feed metadata such as title, author, owner, cover image, language, and Apple category.
- Persistent bottom audio player with playlist drawer, playback speed controls, waveform area, and volume controls.
- Theme-specific widget areas for homepage, sidebar, leftbar, and footer content.
- Bundled widgets for banner carousel, podcast list, blog list, authors, subscribe links, tags cloud, and footer links.
- One Click Demo Import integration with bundled content and widget data.
- Light and dark theme preset selection through the WordPress Customizer.
- Responsive layout with mobile menu, search modal, leftbar drawer, sidebar drawer, and playlist drawer.
- Built-in post view and episode play metrics tracking.
- Translation-ready structure with English and Simplified Chinese language files.

## Optional Plugins

- [One Click Demo Import](https://wordpress.org/plugins/one-click-demo-import/) for importing the bundled demo pages, menus, widgets, and sample content.
- [Advanced Media Offloader](https://wordpress.org/plugins/advanced-media-offloader/) for serving media files from external storage.

These plugins are recommended for specific workflows, but they are not required for the theme's built-in podcast feed, player, or episode publishing features.

## Installation

### Release package

1. Download the packaged theme zip release.
2. In WordPress, go to `Appearance > Themes > Add New > Upload Theme`.
3. Upload the zip file, install it, and activate the theme.
4. Open `A Ripple Song > Podcast Settings` to configure your podcast feed metadata.

### Demo import

1. Install and activate `One Click Demo Import`.
2. Go to `Appearance > Import Demo Data`.
3. Run the bundled `A Ripple Song Demo` import.
4. Review the imported navigation, widgets, and homepage assignment.

### Runtime requirements

- WordPress `6.6+`
- PHP `8.3+`

## Development

Use the source version of the theme when you need to customize, build, or package it locally.

```bash
composer install
npm install
```

Start the development server:

```bash
npm run dev
```

Build production assets:

```bash
npm run build
```

Compile translation files:

```bash
wp i18n make-mo resources/lang
```

Build the packaged release assets:

```bash
composer run release:stage
composer run build:dist
```

## Tech Stack

- [Sage](https://roots.io/sage/)
- [Acorn](https://roots.io/acorn/)
- [Carbon Fields](https://carbonfields.net/)
- [Vite](https://vite.dev/)
- [Tailwind CSS](https://tailwindcss.com/)
- [daisyUI](https://daisyui.com/)
- [Alpine.js](https://alpinejs.dev/)
- [Swup](https://swup.js.org/)
- [Howler.js](https://howlerjs.com/)
- [Tone.js](https://tonejs.github.io/)
- [audioMotion-analyzer](https://github.com/hvianna/audioMotion-analyzer)
- [Lucide](https://lucide.dev/)
- [Simple Icons](https://simpleicons.org/)

## Project Structure

- `app/` contains theme logic such as feeds, settings, custom post types, widgets, providers, and import hooks.
- `resources/views/` contains Blade templates for layouts, templates, partials, and widgets.
- `resources/js/` and `resources/css/` contain frontend scripts, player logic, editor assets, and styles.
- `resources/data/` contains bundled demo import files for content and widgets.

## Links

- Repository: [github.com/jiejia/a-ripple-song](https://github.com/jiejia/a-ripple-song)
- Website: [aripplesong.com](https://aripplesong.com/)

## Languages

- [English](README.md)
- [简体中文](README.zh.md)

## License

Released under the [GNU General Public License v3.0](LICENSE).
