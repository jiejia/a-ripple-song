# A Ripple Song

A Ripple Song 是一个经典的 WordPress 播客主题，内置播客节目发布、播客 RSS 输出、常驻音频播放器、主题专用小工具，以及开箱即用的演示导入数据。项目基于 Sage、Acorn、Carbon Fields 和现代化的 Vite 前端工作流构建。

<img src="screenshot.png" alt="A Ripple Song 主题截图" />

## 概述

这个主题面向以播客内容为核心、同时也会发布常规文章、作者内容和首页编排区块的 WordPress 网站。播客内容模型、Feed 配置、播放界面和后台管理能力都已经集成在主题内部，因此核心播客能力不依赖额外的播客插件。

## 功能特性

- 内置播客节目自定义文章类型，支持上传音频文件和维护节目元数据。
- 内置 `/feed/podcast/` 播客 RSS Feed，可用于 Apple Podcasts、Spotify 等播客平台。
- 提供 `Podcast Settings` 设置页，用于配置播客标题、作者、所有者、封面图、语言和 Apple 分类等 Feed 元数据。
- 内置底部常驻播放器，支持播放列表抽屉、倍速控制、波形区域和音量控制。
- 提供首页、右侧边栏、左侧边栏和页脚等主题专用 Widget 区域。
- 内置 Banner Carousel、Podcast List、Blog List、Authors、Subscribe Links、Tags Cloud 和 Footer Links 等主题小工具。
- 集成 One Click Demo Import，可导入主题自带的演示内容和小工具配置。
- 支持通过 WordPress Customizer 分别选择浅色和深色主题预设。
- 具备响应式布局能力，包含移动菜单、搜索弹窗、左侧抽屉、右侧抽屉和播放列表抽屉。
- 内置文章浏览量和播客播放量统计能力。
- 已准备好多语言支持，当前包含英文和简体中文语言文件。

## 可选插件

- [One Click Demo Import](https://wordpress.org/plugins/one-click-demo-import/)：用于导入主题自带的演示页面、菜单、小工具和示例内容。
- [Advanced Media Offloader](https://wordpress.org/plugins/advanced-media-offloader/)：用于将媒体文件托管到外部存储并对外提供访问。

这些插件只用于特定工作流增强，不是主题核心播客 Feed、播放器或节目发布功能的必需依赖。

## 安装

### 安装发布包

1. 下载主题发布 zip 包。
2. 在 WordPress 后台进入 `外观 > 主题 > 添加主题 > 上传主题`。
3. 上传 zip 文件，安装并启用主题。
4. 进入 `A Ripple Song > Podcast Settings` 配置播客 Feed 元数据。

### 导入演示数据

1. 安装并启用 `One Click Demo Import`。
2. 进入 `外观 > Import Demo Data`。
3. 运行内置的 `A Ripple Song Demo` 导入项。
4. 检查导入后的导航菜单、小工具和首页设置。

### 运行环境要求

- WordPress `6.6+`
- PHP `8.3+`

## 开发

如果你需要在本地进行二次开发、构建或打包，请使用主题源码版本。

```bash
composer install
npm install
```

启动开发服务器：

```bash
npm run dev
```

构建生产环境资源：

```bash
npm run build
```

编译语言文件：

```bash
wp i18n make-mo resources/lang
```

构建可发布的主题包：

```bash
composer run release:stage
composer run build:dist
```

## 技术栈

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

## 项目结构

- `app/`：主题核心逻辑，包括 Feed、设置页、自定义文章类型、小工具、服务提供器和导入钩子。
- `resources/views/`：Blade 模板文件，包含布局、页面模板、局部模板和小工具视图。
- `resources/js/` 与 `resources/css/`：前端脚本、播放器逻辑、编辑器资源和样式文件。
- `resources/data/`：主题内置的演示内容与小工具导入文件。

## 链接

- 仓库地址：[github.com/jiejia/a-ripple-song](https://github.com/jiejia/a-ripple-song)
- 项目网站：[aripplesong.com](https://aripplesong.com/)

## 语言

- [English](README.md)
- [简体中文](README.zh.md)

## 许可协议

本项目基于 [GNU General Public License v3.0](LICENSE) 发布。
