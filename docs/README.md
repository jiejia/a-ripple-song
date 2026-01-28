<p align="center">
  <a href="README.md">English</a> •
  <a href="README.zh-CN.md">简体中文</a> •
  <a href="README.zh-TW.md">繁體中文</a> •
  <a href="README.ja.md">日本語</a> •
  <a href="README.ko-KR.md">한국어</a> •
  <a href="README.fr-FR.md">Français</a> •
  <a href="README.es-ES.md">Español</a> •
  <a href="README.pt-BR.md">Português (Brasil)</a> •
  <a href="README.ru-RU.md">Русский</a> •
  <a href="README.hi-IN.md">हिन्दी</a> •
  <a href="README.bn-BD.md">বাংলা</a> •
  <a href="README.ar.md">العربية</a> •
  <a href="README.ur.md">اردو</a>
</p>

<p align="center">
  <img alt="A Ripple Song" src="https://img.shields.io/badge/A%20Ripple%20Song-beta-6366f1?style=for-the-badge&logo=wordpress&logoColor=white" height="40">
</p>

<h3 align="center">Modern WordPress theme for podcasts and blogs</h3>

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

> **A modern theme, built for speed.**  
> Player, widgets, i18n, analytics, and buttery-smooth navigation — designed to feel like a polished product.

## ✨ What’s Included

| Feature | Description |
|---------|-------------|
| 🎙️ **Podcast-ready UI** | Episode templates, widgets, and player UI (requires companion plugin) |
| 🎵 **Immersive Audio Experience** | Persistent player with waveform visualization, playlists, and playback controls |
| 🎨 **56 Beautiful Theme Colors** | DaisyUI-powered themes with visual picker and light/dark mode support |
| ⚡ **Modern Tech Stack** | Laravel Blade, Tailwind CSS v4, Vite, Alpine.js |
| 🌐 **Internationalization** | UI strings translated (see `resources/lang/`) |
| 📊 **Data Tracking & Analytics** | Built-in metrics and analytics support |
| 🧩 **Flexible Widget System** | Customizable widgets for authors, episodes, banners, and more |
| 📱 **Mobile-First Responsive Design** | Adaptive layouts that look great on any device |
| ✨ **Seamless Page Transitions** | Buttery-smooth navigation powered by Swup.js |

---

## 🎙️ Podcast Support (Companion Plugin)

This theme does **not** register custom post types or taxonomies.

For podcast sites, install the companion plugin `a-ripple-song-podcast` (it registers the `ars_episode` post type). With the plugin active, the theme enables:

- Episode list widgets and episode templates
- Player integration for episode audio
- Tag archives that include episodes (if available)

---

## 🎵 Audio Player

- **Persistent Playback**: Global player persists across page navigation
- **Playlist Queue**: Manage queue with drag-and-drop reordering
- **Wave Visualization**: Real-time waveform display using WaveSurfer.js
- **Audio Spectrum**: Dynamic visualization with AudioMotion Analyzer
- **Playback Controls**: Speed control, skip forward/backward, time-stretch with SoundTouchJS

---

## 📦 Requirements

- Runtime: PHP 8.2+, WordPress 6.6+
- Development: Node.js 20+, Composer

## 🚀 Quick Start

### Install (end users)

1. Download/install the theme (Appearance → Themes).
2. Activate the theme.
3. Optional: install the companion podcast plugin `a-ripple-song-podcast` to enable episode features.

### Develop (contributors)

```bash
cd wp-content/themes/
git clone https://github.com/jiejia/a-ripple-song.git a-ripple-song
cd a-ripple-song

composer install
npm install

npm run build    # Production
npm run dev      # Development with HMR
```

📖 **For detailed setup instructions, visit the [Tutorial](https://doc-podcast.aripplesong.me/docs/intro)**

---

## ⚙️ Configuration

Navigate to **Theme Settings** in WordPress admin:

| Tab | Settings |
|-----|----------|
| **General** | Site logo, footer copyright, DaisyUI theme picker |
| **Social Links** | Footer social media links |

---

## 📁 Project Structure

```
a-ripple-song/
├── app/
│   ├── Metrics/        # Analytics tracking
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
| **Podcast List** | Episode grid with play buttons (requires plugin) |
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

---

## 📝 License

Licensed under the [MIT License](../LICENSE.md).

---

## 🔗 Links

- 📖 [Documentation](https://doc-podcast.aripplesong.me/)
- 🐛 [Issues](https://github.com/jiejia/a-ripple-song/issues)
- ⭐ [GitHub Repository](https://github.com/jiejia/a-ripple-song)

---

<p align="center">
  Made with ❤️ for podcasters<br>
  Built on <a href="https://roots.io/sage/">Roots Sage</a>
</p>
