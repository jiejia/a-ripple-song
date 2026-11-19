# A Ripple Song

[A Ripple Song](https://aripplesong.com/) turns a WordPress site into a podcast site. Publish episodes with their audio and podcast metadata, submit one RSS feed to Apple Podcasts, Spotify and other directories, and let visitors listen through a player bar that stays at the bottom of every page.

It works with any theme. [The dedicated themes](https://aripplesong.com/themes/) are designed around it, with episode cards, show pages and a matching player style.

<picture>
  <source media="(prefers-color-scheme: dark)" srcset="screenshots/players-hero-dark.png" />
  <img src="screenshots/players-hero-light.png" alt="Six of the A Ripple Song player styles" />
</picture>

## Features

- Configure your podcast and its RSS feed with a title, cover art, description, language, and author, plus settings for Apple Podcasts and Podcasting 2.0.
- Publish episodes with categories, tags, transcripts, chapters, and host and guest credits.
- A site-wide player with a playlist, a Now Playing panel, and 15 styles in light and dark. Themes that use swup support continuous playback across pages.
- A statistics dashboard with play counts, listening trends, top episodes, and feed health checks.
- Subscription links for 30+ platforms and a configurable release schedule.
- Supports internationalization.
- `/feed/podcast/` RSS feed with iTunes and Podcasting 2.0 tags.

## Installation

| | Minimum |
| --- | --- |
| WordPress | 6.6 |
| PHP | 8.2 |

Choose any of the following three ways to install A Ripple Song.

### Method 1: Download from WordPress.org

1. Download [`a-ripple-song.zip`](https://downloads.wordpress.org/plugin/a-ripple-song.zip) from WordPress.org.
2. In WordPress, go to **Plugins → Add New Plugin → Upload Plugin**.
3. Upload the zip file, click **Install Now**, then activate the plugin.

### Method 2: Install from the WordPress plugin directory

1. In WordPress, go to **Plugins → Add New Plugin**.
2. Enter `a ripple song` in the search field.
3. Find **A Ripple Song**, click **Install Now**, then activate the plugin.

### Method 3: Install with WP-CLI

If WP-CLI is already installed on your server, run the following command from your WordPress site's root directory to install and activate the plugin:

```bash
wp plugin install a-ripple-song --activate
```

After activation, a new **A Ripple Song** menu appears in the admin sidebar, along with an **Episodes** menu for the episode post type.

Continue with the [quick start](https://aripplesong.com/docs/getting-started/quick-start/) to set up your show and publish the first episode.

## Development

```bash
npm install
```

Watch during development:

```bash
npm run watch:css
npm run watch:js
npm run watch:waveform
```

Build production assets:

```bash
npm run build
```

Compile translations:

```bash
wp i18n make-mo resources/lang
```

Build the plugin zip:

```bash
npm run zip
```

Release a new version (GitHub Actions publishes the zip):

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

- `app/`: plugin logic.
- `resources/templates/`: PHP templates.
- `resources/assets/`: player scripts, styles, and icons.
- `resources/lang/`: translation files.
- `public/dist/`: built assets.
- `bin/`: build, packaging, and release scripts.

## Links

- Repository: [github.com/jiejia/a-ripple-song](https://github.com/jiejia/a-ripple-song)
- Website: [aripplesong.com](https://aripplesong.com/)

## Languages

- [English](README.md)
- [简体中文](README.zh.md)

## License

Released under the [GNU General Public License v3.0](LICENSE).
