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

<h3 align="center">适用于播客与博客的现代 WordPress 主题</h3>

<p align="center">
  <a href="https://doc-podcast.aripplesong.me/docs/intro">📖 使用教程</a> •
  <a href="https://doc-podcast.aripplesong.me/blog">📝 博客</a> •
  <a href="https://github.com/jiejia/a-ripple-song">⭐ GitHub</a>
</p>

<p align="center">
  <img alt="PHP" src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white">
  <img alt="WordPress" src="https://img.shields.io/badge/WordPress-6.6+-21759B?style=flat-square&logo=wordpress&logoColor=white">
  <img alt="License" src="https://img.shields.io/badge/License-MIT-green?style=flat-square">
</p>

---

# A Ripple Song

> **一款现代化主题，为速度而生。**  
> 播放器、小工具、国际化、数据分析，以及丝滑流畅的页面导航 — 一切都经过精心打磨。

## ✨ 功能一览

| 功能 | 描述 |
|------|------|
| 🎙️ **播客友好 UI** | 节目模板、小工具与播放器界面（需要搭配插件） |
| 🎵 **沉浸式音频体验** | 持久播放器，波形可视化，播放列表，播放控制 |
| 🎨 **56 款精美主题配色** | 基于 DaisyUI 的主题系统，可视化选择器，支持明暗模式 |
| ⚡ **现代技术栈** | Laravel Blade、Tailwind CSS v4、Vite、Alpine.js |
| 🌐 **完善的国际化支持** | UI 文案已翻译（见 `resources/lang/`） |
| 📊 **数据追踪与分析** | 内置指标统计和数据分析支持 |
| 🧩 **灵活的小工具系统** | 可定制的作者、节目、轮播图等小工具 |
| 📱 **移动优先响应式设计** | 自适应布局，完美适配各种设备 |
| ✨ **丝滑页面切换** | 基于 Swup.js 的流畅页面过渡动画 |

---

## 🎙️ 播客支持（需要搭配插件）

本主题**不会**注册自定义文章类型（CPT）或分类法（taxonomy）。

如果你要搭建播客站点，请安装配套插件 `a-ripple-song-podcast`（会注册 `ars_episode` 文章类型）。启用后，主题将提供：

- 节目列表小工具与节目模板
- 节目音频的播放器整合
- 标签页同时包含节目内容（如可用）

---

## 🎵 音频播放器

- **持久播放**：全局播放器，页面切换时保持播放状态
- **播放列表队列**：支持拖拽排序的播放队列管理
- **波形可视化**：使用 WaveSurfer.js 实现实时波形显示
- **音频频谱**：使用 AudioMotion Analyzer 实现动态可视化
- **播放控制**：倍速播放、快进/快退、使用 SoundTouchJS 实现变速不变调

---

## 📦 环境要求

- 运行环境：PHP 8.2+、WordPress 6.6+
- 开发环境：Node.js 20+、Composer

## 🚀 快速开始

### 安装（普通用户）

1. 下载/安装主题（外观 → 主题）。
2. 激活主题。
3. 可选：安装播客插件 `a-ripple-song-podcast` 以启用节目功能。

### 开发（贡献者）

```bash
cd wp-content/themes/
git clone https://github.com/jiejia/a-ripple-song.git a-ripple-song
cd a-ripple-song

composer install
npm install

npm run build    # 生产环境
npm run dev      # 开发环境（支持热更新）
```

📖 **详细安装说明，请访问 [使用教程](https://doc-podcast.aripplesong.me/docs/intro)**

---

## ⚙️ 配置说明

在 WordPress 后台导航至 **主题设置**：

| 选项卡 | 设置项 |
|--------|--------|
| **常规** | 站点 Logo、页脚版权、DaisyUI 主题选择器 |
| **社交链接** | 页脚社交媒体链接 |

---

## 📁 项目结构

```
a-ripple-song/
├── app/
│   ├── Metrics/        # 数据分析追踪
│   ├── Providers/      # 服务提供者
│   ├── ThemeOptions/   # Carbon Fields 设置
│   ├── View/           # Blade 视图组合器
│   └── Widgets/        # 自定义小工具
├── resources/
│   ├── css/            # Tailwind 样式表
│   ├── js/             # Alpine.js 及播放器逻辑
│   ├── lang/           # 翻译文件
│   └── views/          # Blade 模板
├── public/             # 编译后的资源
├── functions.php       # 主题引导文件
└── vite.config.js      # 构建配置
```

---

## 🧩 小工具

| 小工具 | 描述 |
|--------|------|
| **作者** | 团队成员信息，包含头像和角色 |
| **轮播横幅** | 可管理的主页轮播图 |
| **博客列表** | 最新文章展示 |
| **播客列表** | 节目网格，带播放按钮（需要插件） |
| **订阅链接** | 各平台订阅按钮 |
| **页脚链接** | 可自定义的页脚栏目 |
| **标签云** | 标签可视化展示 |

---

## 🔧 开发指南

```bash
npm run dev              # 启动开发服务器（支持热更新）
npm run build            # 生产环境构建
npm run translate        # 生成翻译文件
npm run translate:compile # 编译 .po 为 .mo 文件
```

### 技术栈

| 技术 | 用途 |
|------|------|
| [Roots Sage](https://roots.io/sage/) | 主题框架 |
| [Laravel Blade](https://laravel.com/docs/blade) | 模板引擎 |
| [Acorn](https://roots.io/acorn/) | Laravel-WordPress 桥接 |
| [Tailwind CSS](https://tailwindcss.com/) | 样式框架 |
| [DaisyUI](https://daisyui.com/) | UI 组件库 |
| [Alpine.js](https://alpinejs.dev/) | 响应式交互 |
| [Vite](https://vitejs.dev/) | 构建工具 |
| [Swup](https://swup.js.org/) | 页面过渡 |
| [Howler.js](https://howlerjs.com/) | 音频播放 |
| [WaveSurfer.js](https://wavesurfer-js.org/) | 波形显示 |
| [Carbon Fields](https://carbonfields.net/) | 主题选项 |
| [CMB2](https://cmb2.io/) | 元数据框 |

---

## 📝 开源许可

基于 [MIT 许可证](../LICENSE.md) 开源。

---

## 🔗 相关链接

- 📖 [使用文档](https://doc-podcast.aripplesong.me/)
- 🐛 [问题反馈](https://github.com/jiejia/a-ripple-song/issues)
- ⭐ [GitHub 仓库](https://github.com/jiejia/a-ripple-song)

---

<p align="center">
  用 ❤️ 为播客创作者打造<br>
  基于 <a href="https://roots.io/sage/">Roots Sage</a> 构建
</p>
