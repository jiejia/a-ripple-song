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
  <img alt="A Ripple Song" src="https://img.shields.io/badge/A%20Ripple%20Song-beta-6366f1?style=for-the-badge&logo=wordpress&logoColor=white" height="40">
</p>

<h3 align="center">Tema WordPress moderno para podcasts e blogs</h3>

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

> **Um tema moderno, feito para velocidade.**  
> Player, widgets, i18n, métricas e transições suaves — com cara de produto.

## ✨ O que inclui

| Recurso | Descrição |
|--------|-----------|
| 🎙️ **UI pronta para podcast** | Templates, widgets e UI do player (requer plugin) |
| 🎵 **Experiência imersiva** | Player persistente, waveform, playlists e controles |
| 🎨 **56 temas de cores** | DaisyUI, seletor visual, modo claro/escuro |
| ⚡ **Stack moderno** | Laravel Blade, Tailwind CSS v4, Vite, Alpine.js |
| 🌐 **Internacionalização** | Textos da UI traduzidos (`resources/lang/`) |
| 📊 **Métricas e analytics** | Suporte integrado |
| 🧩 **Sistema de widgets** | Autores, episódios, banners e mais |
| 📱 **Mobile-first** | Responsivo em qualquer dispositivo |
| ✨ **Transições suaves** | Navegação fluida com Swup.js |

---

## 🎙️ Suporte a podcast (plugin companheiro)

Este tema **não** registra CPTs nem taxonomias.

Para sites de podcast, instale o plugin companheiro `a-ripple-song-podcast` (registra o post type `ars_episode`). Com o plugin ativo:

- Widgets e templates de episódios
- Integração do player com o áudio do episódio
- Arquivos de tags incluindo episódios (se disponível)

---

## 🎵 Player de áudio

- **Reprodução persistente**: continua durante a navegação
- **Fila de playlist**: reordenar com arrastar e soltar
- **Waveform**: WaveSurfer.js em tempo real
- **Espectro**: AudioMotion Analyzer
- **Controles**: velocidade, pular, pitch preservado com SoundTouchJS

---

## 📦 Requisitos

- Runtime: PHP 8.2+, WordPress 6.6+
- Desenvolvimento: Node.js 20+, Composer

## 🚀 Começo rápido

### Instalar (usuários)

1. Instale o tema (Aparência → Temas).
2. Ative o tema.
3. Opcional: instale `a-ripple-song-podcast` para habilitar episódios.

### Desenvolver (contribuidores)

```bash
cd wp-content/themes/
git clone https://github.com/jiejia/a-ripple-song.git a-ripple-song
cd a-ripple-song

composer install
npm install

npm run build    # Produção
npm run dev      # Desenvolvimento (HMR)
```

📖 **Para detalhes, veja o [tutorial](https://doc-podcast.aripplesong.me/docs/intro)**

---

## ⚙️ Configuração

No admin do WordPress: **Theme Settings**

| Aba | Configurações |
|-----|---------------|
| **General** | Logo, copyright, seletor DaisyUI |
| **Social Links** | Links sociais do rodapé |

---

## 🧩 Widgets

| Widget | Descrição |
|--------|-----------|
| **Authors** | Membros/equipe com papéis |
| **Banner Carousel** | Carrossel principal |
| **Blog List** | Posts recentes |
| **Podcast List** | Episódios (requer plugin) |
| **Subscribe Links** | Links de assinatura |
| **Footer Links** | Colunas do rodapé |
| **Tags Cloud** | Nuvem de tags |

---

## 🔧 Desenvolvimento

```bash
npm run dev              # Servidor dev (HMR)
npm run build            # Build produção
npm run translate        # Gerar traduções
npm run translate:compile # .po → .mo
```

---

## 📝 Licença

Sob [MIT License](../LICENSE.md).

---

## 🔗 Links

- 📖 [Documentação](https://doc-podcast.aripplesong.me/)
- 🐛 [Issues](https://github.com/jiejia/a-ripple-song/issues)
- ⭐ [GitHub](https://github.com/jiejia/a-ripple-song)
