<p align="center">
  <a href="README.md">English</a> •
  <a href="README.zh-CN.md">简体中文</a> •
  <a href="README.zh-Hant.md">繁體中文</a> •
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
  <img alt="A Ripple Song" src="https://img.shields.io/badge/A%20Ripple%20Song-0.5.0-6366f1?style=for-the-badge&logo=wordpress&logoColor=white" height="40">
</p>

<h3 align="center">Thème WordPress moderne pour podcasts et blogs</h3>

<p align="center">
  <a href="https://doc-podcast.aripplesong.me/docs/intro">📖 Tutoriel</a> •
  <a href="https://doc-podcast.aripplesong.me/blog">📝 Blog</a> •
  <a href="https://github.com/jiejia/a-ripple-song">⭐ GitHub</a>
</p>

<p align="center">
  <img alt="PHP" src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white">
  <img alt="WordPress" src="https://img.shields.io/badge/WordPress-6.6+-21759B?style=flat-square&logo=wordpress&logoColor=white">
  <img alt="License" src="https://img.shields.io/badge/License-GPL--3.0-blue?style=flat-square">
</p>

---

# A Ripple Song

> **Un thème moderne, pensé pour la vitesse.**  
> Lecteur, widgets, i18n, analytics et navigation ultra fluide — comme un vrai produit.

## ✨ Ce qui est inclus

| Fonctionnalité | Description |
|---------------|-------------|
| 🎙️ **UI prête pour le podcast** | Templates, widgets et UI du lecteur (nécessite un plugin compagnon) |
| 🎵 **Expérience audio immersive** | Lecteur persistant, visualisation des ondes, playlists et contrôles |
| 🎨 **56 thèmes de couleurs** | Thèmes DaisyUI, sélecteur visuel, mode clair/sombre |
| ⚡ **Stack moderne** | Laravel Blade, Tailwind CSS v4, Vite, Alpine.js |
| 🌐 **Internationalisation** | Traductions des textes UI (`resources/lang/`) |
| 📊 **Mesures & analytics** | Support de métriques intégré |
| 🧩 **Widgets flexibles** | Auteurs, épisodes, bannières, etc. |
| 📱 **Mobile-first** | Responsive sur tous les appareils |
| ✨ **Transitions fluides** | Navigation Swup.js |

---

## 🎙️ Support podcast (plugin compagnon)

Ce thème **n’enregistre pas** de types de contenus (CPT) ni de taxonomies.

Pour un site podcast, installez le plugin compagnon `a-ripple-song-podcast` (il enregistre le post type `ars_episode`). Une fois actif :

- Widgets et templates d’épisodes
- Intégration du lecteur pour l’audio des épisodes
- Archives de tags incluant les épisodes (si disponible)

---

## 🎵 Lecteur audio

- **Lecture persistante** : la lecture continue lors de la navigation
- **File d’attente** : réorganisation par glisser-déposer
- **Ondes** : WaveSurfer.js en temps réel
- **Spectre** : visualisation AudioMotion Analyzer
- **Contrôles** : vitesse, sauts, pitch préservé via SoundTouchJS

---

## 📦 Prérequis

- Exécution : PHP 8.2+, WordPress 6.6+
- Développement : Node.js 20+, Composer

## 🚀 Démarrage rapide

### Installer (utilisateurs)

1. Installer le thème (Apparence → Thèmes).
2. Activer le thème.
3. Optionnel : installer `a-ripple-song-podcast` pour activer les fonctionnalités d’épisodes.

### Développer (contributeurs)

```bash
cd wp-content/themes/
git clone https://github.com/jiejia/a-ripple-song.git a-ripple-song
cd a-ripple-song

composer install
npm install

npm run build    # Production
npm run dev      # Développement (HMR)
```

📖 **Pour les détails, voir le [tutoriel](https://doc-podcast.aripplesong.me/docs/intro)**

---

## ⚙️ Configuration

Dans l’admin WordPress : **Theme Settings**

| Onglet | Réglages |
|--------|----------|
| **General** | Logo, copyright, sélecteur de thème DaisyUI |
| **Social Links** | Liens sociaux du footer |

---

## 🧩 Widgets

| Widget | Description |
|--------|-------------|
| **Authors** | Équipe/avatars et rôles |
| **Banner Carousel** | Carousel héro |
| **Blog List** | Posts récents |
| **Podcast List** | Grille d’épisodes (plugin requis) |
| **Subscribe Links** | Boutons d’abonnement |
| **Footer Links** | Colonnes du footer |
| **Tags Cloud** | Nuage de tags |

---

## 🔧 Développement

```bash
npm run dev              # Serveur dev (HMR)
npm run build            # Build production
npm run translate         # Générer les traductions
npm run translate:compile # .po → .mo
```

---

## 📝 Licence

Sous [GPL-3.0](../LICENSE).

---

## 🔗 Liens

- 📖 [Documentation](https://doc-podcast.aripplesong.me/)
- 🐛 [Issues](https://github.com/jiejia/a-ripple-song/issues)
- ⭐ [GitHub](https://github.com/jiejia/a-ripple-song)
