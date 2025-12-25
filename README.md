<p align="center">
  <img alt="A Ripple Song" src="https://img.shields.io/badge/A%20Ripple%20Song-beta-6366f1?style=for-the-badge&logo=wordpress&logoColor=white" height="40">
</p>

<h3 align="center">All-in-one podcast theme for WordPress</h3>

<p align="center">
  <a href="https://doc-podcast.aripplesong.me/docs/intro">📖 Tutorial</a> •
  <a href="https://doc-podcast.aripplesong.me/blog">📝 Blog</a> •
  <a href="https://github.com/jiejia/a-ripple-song">⭐ GitHub</a>
</p>

<p align="center">
  <img alt="PHP" src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white">
  <img alt="WordPress" src="https://img.shields.io/badge/WordPress-6.6+-21759B?style=flat-square&logo=wordpress&logoColor=white">
  <img alt="License" src="https://img.shields.io/badge/License-MIT-green?style=flat-square">
</p>

---

# A Ripple Song

> **A modern podcast theme, built for speed.**  
> CMS, player, widgets, i18n, analytics, and buttery-smooth navigation — all designed to feel like a polished product.

## ✨ Everything You Need

| Feature | Description |
|---------|-------------|
| 🎙️ **Professional Podcast Management** | Custom post type with comprehensive metadata, team management, and auto audio analysis |
| 🎵 **Immersive Audio Experience** | Persistent player with waveform visualization, playlists, and playback controls |
| 🎨 **56 Beautiful Theme Colors** | DaisyUI-powered themes with visual picker and light/dark mode support |
| ⚡ **Modern Tech Stack** | Laravel Blade, Tailwind CSS v4, Vite, Alpine.js |
| 🌐 **Comprehensive Internationalization** | Full i18n support with included English & Chinese translations |
| 📊 **Data Tracking & Analytics** | Built-in metrics and analytics support |
| 🧩 **Flexible Widget System** | Customizable widgets for authors, episodes, banners, and more |
| 📱 **Mobile-First Responsive Design** | Adaptive layouts that look great on any device |
| ✨ **Seamless Page Transitions** | Buttery-smooth navigation powered by Swup.js |

---

## 🎙️ Podcast Features

### Episode Management
- **Custom Post Type**: Dedicated `podcast` post type with rich metadata
- **Episode Fields**: Audio file, duration, transcript, season/episode numbers, explicit flags
- **Team Attribution**: Assign hosts (members) and guests to episodes
- **Auto Audio Analysis**: Automatic extraction of duration, file size, and MIME type via getID3

### RSS Feed (Podcasting 2.0)
- **Apple Podcasts Compatible**: Full compliance with Apple Podcasts specifications
- **Multi-Platform Ready**: Works with Spotify, YouTube Music, and all major platforms
- **Podcasting 2.0 Support**: Person tags, transcript links, and chapter metadata
- **Feed URL**: Available at `/feed/podcast/`

---

## 🎵 Audio Player

- **Persistent Playback**: Global player persists across page navigation
- **Playlist Queue**: Manage queue with drag-and-drop reordering
- **Wave Visualization**: Real-time waveform display using WaveSurfer.js
- **Audio Spectrum**: Dynamic visualization with AudioMotion Analyzer
- **Playback Controls**: Speed control, skip forward/backward, time-stretch with SoundTouchJS

---

## 📦 Requirements

- PHP 8.2+
- WordPress 6.6+
- Node.js 20+
- Composer

## 🚀 Quick Start

```bash
# 1. Clone to themes directory
cd wp-content/themes/
git clone https://github.com/jiejia/a-ripple-song.git aripplesong
cd aripplesong

# 2. Install dependencies
composer install
npm install

# 3. Build assets
npm run build    # Production
npm run dev      # Development with HMR

# 4. Activate theme in WordPress admin
```

📖 **For detailed setup instructions, visit the [Tutorial](https://doc-podcast.aripplesong.me/docs/intro)**

---

## ⚙️ Configuration

### Theme Settings

Navigate to **Theme Settings** in WordPress admin:

| Tab | Settings |
|-----|----------|
| **General** | Site logo, footer copyright, DaisyUI theme picker |
| **Podcast** | Feed metadata, cover image, categories, language |
| **Social Links** | Footer social media links |

### Podcast Feed Settings

| Setting | Description |
|---------|-------------|
| Podcast Title | Show name for RSS feed |
| Description | Podcast summary |
| Cover Image | Square image (1400×1400 to 3000×3000 px) |
| Categories | Apple Podcasts categories (up to 3) |
| Language | Feed language code |
| Explicit | Content rating flag |
| Author/Owner | Contact info for directories |

---

## 📁 Project Structure

```
aripplesong/
├── app/
│   ├── Feeds/          # RSS feed generation
│   ├── Metrics/        # Analytics tracking
│   ├── PostTypes/      # Custom post types
│   ├── Providers/      # Service providers
│   ├── ThemeOptions/   # Carbon Fields settings
│   ├── View/           # Blade view composers
│   └── Widgets/        # Custom widgets
├── resources/
│   ├── css/            # Tailwind stylesheets
│   ├── js/             # Alpine.js & player logic
│   ├── lang/           # Translation files
│   └── views/          # Blade templates
├── public/             # Compiled assets
├── functions.php       # Theme bootstrap
└── vite.config.js      # Build configuration
```

---

## 🧩 Widgets

| Widget | Description |
|--------|-------------|
| **Authors** | Team members with avatars and roles |
| **Banner Carousel** | Hero slides with management |
| **Blog List** | Recent posts display |
| **Podcast List** | Episode grid with play buttons |
| **Subscribe Links** | Platform subscription buttons |
| **Footer Links** | Customizable footer columns |
| **Tags Cloud** | Visual tag display |

---

## 🔧 Development

```bash
npm run dev              # Start dev server with HMR
npm run build            # Build for production
npm run translate        # Generate translation files
npm run translate:compile # Compile .po to .mo
```

### Tech Stack

| Technology | Purpose |
|------------|---------|
| [Roots Sage](https://roots.io/sage/) | Theme framework |
| [Laravel Blade](https://laravel.com/docs/blade) | Templating |
| [Acorn](https://roots.io/acorn/) | Laravel-WordPress bridge |
| [Tailwind CSS](https://tailwindcss.com/) | Styling |
| [DaisyUI](https://daisyui.com/) | Components |
| [Alpine.js](https://alpinejs.dev/) | Reactivity |
| [Vite](https://vitejs.dev/) | Build tool |
| [Swup](https://swup.js.org/) | Page transitions |
| [Howler.js](https://howlerjs.com/) | Audio playback |
| [WaveSurfer.js](https://wavesurfer-js.org/) | Waveforms |
| [Carbon Fields](https://carbonfields.net/) | Theme options |
| [CMB2](https://cmb2.io/) | Meta boxes |

---

## 📝 License

Licensed under the [MIT License](LICENSE.md).

---

## � Links

- 📖 [Documentation](https://doc-podcast.aripplesong.me/)
- 🐛 [Issues](https://github.com/jiejia/a-ripple-song/issues)
- ⭐ [GitHub Repository](https://github.com/jiejia/a-ripple-song)

---

<p align="center">
  Made with ❤️ for podcasters<br>
  Built on <a href="https://roots.io/sage/">Roots Sage</a>
</p>
