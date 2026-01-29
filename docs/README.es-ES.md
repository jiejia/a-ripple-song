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
  <img alt="A Ripple Song" src="https://img.shields.io/badge/A%20Ripple%20Song-0.5.0--BETA-6366f1?style=for-the-badge&logo=wordpress&logoColor=white" height="40">
</p>

<h3 align="center">Tema moderno de WordPress para podcasts y blogs</h3>

<p align="center">
  <a href="https://doc-podcast.aripplesong.me/docs/intro">📖 Tutorial</a> •
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

> **Un tema moderno, hecho para la velocidad.**  
> Reproductor, widgets, i18n, analíticas y transiciones suaves.

## ✨ Qué incluye

| Función | Descripción |
|--------|-------------|
| 🎙️ **UI lista para podcast** | Plantillas, widgets y UI del reproductor (requiere plugin) |
| 🎵 **Experiencia inmersiva** | Reproductor persistente, forma de onda, playlists y controles |
| 🎨 **56 temas de color** | DaisyUI, selector visual, modo claro/oscuro |
| ⚡ **Stack moderno** | Laravel Blade, Tailwind CSS v4, Vite, Alpine.js |
| 🌐 **Internacionalización** | Textos UI traducidos (`resources/lang/`) |
| 📊 **Métricas y analíticas** | Soporte integrado |
| 🧩 **Sistema de widgets** | Autores, episodios, banners, etc. |
| 📱 **Mobile-first** | Responsive en cualquier dispositivo |
| ✨ **Transiciones suaves** | Swup.js para navegación fluida |

---

## 🎙️ Soporte de podcast (plugin compañero)

Este tema **no** registra CPT ni taxonomías.

Para sitios de podcast, instala el plugin compañero `a-ripple-song-podcast` (registra el post type `ars_episode`). Con el plugin activo:

- Widgets y plantillas de episodios
- Integración del reproductor con el audio del episodio
- Archivos de tags que incluyen episodios (si está disponible)

---

## 🎵 Reproductor de audio

- **Reproducción persistente**: no se corta al navegar
- **Cola de playlist**: reordenar con arrastrar y soltar
- **Forma de onda**: WaveSurfer.js en tiempo real
- **Espectro**: AudioMotion Analyzer
- **Controles**: velocidad, saltos, pitch constante con SoundTouchJS

---

## 📦 Requisitos

- Runtime: PHP 8.2+, WordPress 6.6+
- Desarrollo: Node.js 20+, Composer

## 🚀 Inicio rápido

### Instalar (usuarios)

1. Instala el tema (Apariencia → Temas).
2. Actívalo.
3. Opcional: instala `a-ripple-song-podcast` para habilitar episodios.

### Desarrollar (contribuidores)

```bash
cd wp-content/themes/
git clone https://github.com/jiejia/a-ripple-song.git a-ripple-song
cd a-ripple-song

composer install
npm install

npm run build    # Producción
npm run dev      # Desarrollo (HMR)
```

📖 **Para más detalles, consulta el [tutorial](https://doc-podcast.aripplesong.me/docs/intro)**

---

## ⚙️ Configuración

En el admin de WordPress: **Theme Settings**

| Pestaña | Ajustes |
|--------|---------|
| **General** | Logo, copyright, selector DaisyUI |
| **Social Links** | Enlaces sociales del footer |

---

## 🧩 Widgets

| Widget | Descripción |
|--------|-------------|
| **Authors** | Miembros/equipo con roles |
| **Banner Carousel** | Carrusel principal |
| **Blog List** | Posts recientes |
| **Podcast List** | Episodios (requiere plugin) |
| **Subscribe Links** | Enlaces de suscripción |
| **Footer Links** | Columnas del footer |
| **Tags Cloud** | Nube de etiquetas |

---

## 🔧 Desarrollo

```bash
npm run dev              # Servidor dev (HMR)
npm run build            # Build producción
npm run translate        # Generar traducciones
npm run translate:compile # .po → .mo
```

---

## 📝 Licencia

Bajo [GPL-3.0](../LICENSE).

---

## 🔗 Enlaces

- 📖 [Documentación](https://doc-podcast.aripplesong.me/)
- 🐛 [Issues](https://github.com/jiejia/a-ripple-song/issues)
- ⭐ [GitHub](https://github.com/jiejia/a-ripple-song)
