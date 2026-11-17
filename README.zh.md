# A Ripple Song

[A Ripple Song](https://aripplesong.com/) 可将 WordPress 站点变成播客网站。发布包含音频和播客元数据的单集，将同一个 RSS Feed 提交到 Apple Podcasts、Spotify 等平台，访客即可通过每个页面底部的播放条收听。

适用于任意主题。[专用主题](https://aripplesong.com/themes/) 围绕插件设计，提供单集卡片、播客页面和配套的播放器样式。

<picture>
  <source media="(prefers-color-scheme: dark)" srcset="screenshots/players-hero-dark.png" />
  <img src="screenshots/players-hero-light.png" alt="A Ripple Song 的六种播放器样式" />
</picture>

## 功能特性

- `Episodes` 文章类型（位于 `/podcasts/`），支持标签和节目分类。
- 自动识别本站音频的时长、文件大小和 MIME 类型。
- 节目元数据：封面、类型、集数/季数、Explicit 标记、iTunes 字段、副标题和摘要。
- Podcasting 2.0 字段：文字稿、章节、精彩片段、GUID、主播和嘉宾。
- `/feed/podcast/` RSS Feed，输出 iTunes 与 Podcasting 2.0 标签。
- 按 Apple 封面规范校验封面图。
- 全站播放条，支持播放列表和 “Now Playing” 面板；配合 swup 主题可跨页连续播放。
- 15 种浅色/深色播放器样式，进度可显示为进度条、频谱或波形。
- 下次访问时恢复播放进度和播放列表。
- 去重的播放统计、统计面板和 Feed 健康检查。
- 30 多个平台的订阅链接和更新周期设置。
- 英文和简体中文语言文件。

## 安装

| | 最低版本 |
| --- | --- |
| WordPress | 6.6 |
| PHP | 8.2 |

可以选择以下三种方式之一安装 A Ripple Song。

### 方式一：从 WordPress.org 下载

1. 从 WordPress.org 下载 [`a-ripple-song.zip`](https://downloads.wordpress.org/plugin/a-ripple-song.zip)。
2. 在 WordPress 后台进入 **插件 → 安装插件 → 上传插件**。
3. 上传 zip 文件，点击 **立即安装**，然后启用插件。

### 方式二：从 WordPress 插件目录安装

1. 在 WordPress 后台进入 **插件 → 安装插件**。
2. 在搜索框中输入 `a ripple song`。
3. 找到 **A Ripple Song**，点击 **立即安装**，然后启用插件。

### 方式三：使用 WP-CLI 安装

如果服务器已安装 WP-CLI，在 WordPress 站点根目录运行以下命令即可安装并启用插件：

```bash
wp plugin install a-ripple-song --activate
```

启用后，后台侧边栏会出现 **A Ripple Song** 菜单，以及用于管理播客单集的 **Episodes** 菜单。

接下来请参阅[快速开始](https://aripplesong.com/docs/getting-started/quick-start/)，配置播客并发布第一集。

## 开发

```bash
npm install
```

开发时监听变化：

```bash
npm run watch:css
npm run watch:js
npm run watch:waveform
```

构建生产资源：

```bash
npm run build
```

编译语言文件：

```bash
wp i18n make-mo resources/lang
```

构建插件 zip 包：

```bash
npm run zip
```

发布新版本（由 GitHub Actions 发布 zip 包）：

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

- `app/`：插件核心逻辑。
- `resources/templates/`：PHP 模板。
- `resources/assets/`：播放器脚本、样式和图标。
- `resources/lang/`：语言文件。
- `public/dist/`：构建产物。
- `bin/`：构建、打包和发布脚本。

## 链接

- 仓库地址：[github.com/jiejia/a-ripple-song](https://github.com/jiejia/a-ripple-song)
- 项目网站：[aripplesong.com](https://aripplesong.com/)

## 语言

- [English](README.md)
- [简体中文](README.zh.md)

## 许可协议

本项目基于 [GNU General Public License v3.0](LICENSE) 发布。
