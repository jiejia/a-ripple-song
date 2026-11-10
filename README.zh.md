# A Ripple Song

A Ripple Song 是一个 WordPress 播客插件，提供播客节目发布、兼容 Apple Podcasts 与 Podcasting 2.0 的 RSS Feed、全站常驻音频播放器以及播放统计。项目基于 WordPress 原生 API 以纯 PHP 编写，前端使用轻量的 esbuild 与 Tailwind CSS 构建流程。

<picture>
  <source media="(prefers-color-scheme: dark)" srcset="screenshots/players-hero-dark.png" />
  <img src="screenshots/players-hero-light.png" alt="A Ripple Song 的六种播放器样式" />
</picture>

## 概述

这个插件可以把 WordPress 网站变成一个播客网站：发布带音频和播客元数据的节目，向 Apple Podcasts、Spotify 等播客平台提交同一个 RSS Feed，并让访客通过固定在每个页面底部的播放条收听。插件适用于任意主题，主题也可以按需重新设计播放器样式或完全接管播放器。

## 功能特性

- 内置 `Episodes` 节目自定义文章类型（URL 位于 `/podcasts/` 下），支持标签和层级式节目分类，分类可设置图片和图标。
- 保存节目时自动识别本站音频的时长、文件大小和 MIME 类型。
- 每期节目的元数据：封面、节目类型、集数与季数、Explicit 标记、iTunes 字段、作者覆盖、副标题和摘要。
- Podcasting 2.0 字段：文字稿、章节、精彩片段和自定义 GUID，并可从站点用户中选择主播与嘉宾。
- 内置 `/feed/podcast/` 播客 RSS Feed，输出 iTunes 标签与 Podcasting 2.0 标签（locked、guid、funding、person、transcript、chapters、soundbite、updateFrequency）。
- 按 Apple 的封面规范校验封面图（正方形 JPG/PNG、1400–3000 像素、不超过 512 KB、无透明通道）。
- 全站底部播放条，支持播放列表和全屏 “Now Playing” 面板；配合使用 swup 的主题时可跨页面连续播放。
- 15 种播放器样式，每种都有浅色和深色版本；进度条可显示为普通进度条、实时频谱或整期波形。
- 单期节目页显示播放 / 收藏 / 播放次数信息条，并在访客下次访问时恢复播放进度和播放列表。
- 通过 REST API 统计播放次数并按访客去重，提供包含趋势和热门节目的统计面板，以及检查 Feed 必填项缺失的健康检查。
- 支持 30 多个播客平台的订阅链接，以及供主题和 Feed 使用的更新周期设置。
- 已准备好多语言支持，当前包含英文和简体中文语言文件。

## 主题集成

- 任意前台模板都可以从 `resources/templates/` 复制到 `{theme}/a-ripple-song/` 进行覆盖。
- 主题声明 `add_theme_support( 'a-ripple-song-player' )` 后可自行设计播放器样式，此时插件不再加载播放器皮肤样式表。
- 主题文件夹名称与某个播放器样式同名时（例如 `ars-petal`），启用该主题会自动选中对应样式。
- 提供 `a_ripple_song_play_counted`、`a_ripple_song_global_player_enabled`、`a_ripple_song_player_episode_cover` 等钩子，便于主题和其他插件扩展功能，完整列表见 `README.txt`。

## 安装

### 安装发布包

1. 下载 `a-ripple-song.zip` 插件发布包。
2. 在 WordPress 后台进入 `插件 > 安装插件 > 上传插件`。
3. 上传 zip 文件，安装并启用插件。
4. 进入 `A Ripple Song > Podcast Settings` 配置播客 Feed 元数据。

### 发布你的播客

1. 在 `A Ripple Song > Podcast Settings` 中填写节目封面、标题、简介、语言、作者、所有者和 Apple Podcasts 分类。
2. 按需配置 `Host & Guest Settings`、`Subscription Settings` 和 `Player Settings`。
3. 在 `Episodes > Add New Episode` 中创建节目并选择音频文件。
4. 复制 Feed 地址（默认为 `https://your-site.example/feed/podcast/`），提交到各播客平台。

### 运行环境要求

- WordPress `6.6+`
- PHP `8.2+`

## 开发

如果你需要在本地进行二次开发、构建或打包，请使用插件源码版本。

```bash
npm install
```

开发时监听播放器样式和脚本的变化：

```bash
npm run watch:css
npm run watch:js
npm run watch:waveform
```

构建生产环境资源：

```bash
npm run build
```

编译语言文件：

```bash
wp i18n make-mo resources/lang
```

构建可发布的插件 zip 包：

```bash
npm run zip
```

发布新版本（更新版本号、提交、打标签并推送，由 GitHub Actions 发布 zip 包）：

```bash
npm run release 0.5.1
```

## 技术栈

- [WordPress Plugin API](https://developer.wordpress.org/plugins/)
- [esbuild](https://esbuild.github.io/)
- [PostCSS](https://postcss.org/)
- [Tailwind CSS](https://tailwindcss.com/)
- [daisyUI](https://daisyui.com/)
- [Howler.js](https://howlerjs.com/)
- [wavesurfer.js](https://wavesurfer.xyz/)
- [Lucide](https://lucide.dev/)

## 项目结构

- `app/`：插件核心逻辑，包括自定义文章类型、分类法、Feed、设置页、播放器、统计、REST 控制器和服务提供器。
- `resources/templates/`：PHP 模板文件，包含播放器、播放器皮肤、节目信息条、Feed、统计面板和设置字段视图。
- `resources/assets/`：播放器脚本、播放器样式源文件和图标。
- `resources/lang/`：语言文件。
- `public/dist/`：构建后的播放器脚本和皮肤样式表。
- `bin/`：构建、打包和发布脚本。

## 链接

- 仓库地址：[github.com/jiejia/a-ripple-song](https://github.com/jiejia/a-ripple-song)
- 项目网站：[aripplesong.com](https://aripplesong.com/)

## 语言

- [English](README.md)
- [简体中文](README.zh.md)

## 许可协议

本项目基于 [GNU General Public License v3.0](LICENSE) 发布。
