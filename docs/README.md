# A Ripple Song（WordPress 播客主题）使用文档

本目录是主题 **A Ripple Song** 的使用说明（面向站点管理员 / 内容运营）。文档基于主题源码分析，并在本地 WordPress 后台实际操作后整理（见 `docs/screenshots/`）。

## 快速开始（最短路径）

1. 启用主题：后台「外观 → 主题」启用 `aripplesong`（见 `docs/screenshots/17-themes-active.png`）。
2. 设置固定链接：后台「设置 → 固定链接」建议选择「文章名」，并保持与你的环境一致（本地示例为带 `index.php` 的结构，见 `docs/screenshots/18-permalinks-settings.png`）。
3. 确认播客列表页：创建一个页面（推荐别名 `podcast`），模板选择「播客模板」（见 `docs/screenshots/12-page-template-picker.png`）。
4. 配置播客 RSS：后台「主题设置 → 播客设置」填写节目元信息，复制 RSS URL（见 `docs/screenshots/04-theme-settings-podcast-settings.png`）。
5. 发布播客单集：后台左侧「播客 → 添加新播客」，上传音频并填写必填项（见 `docs/screenshots/06-podcast-edit-metabox.png`）。
6. 配置首页/侧栏模块：后台「外观 → 小工具」按需调整小工具区域（见 `docs/screenshots/09-widgets-home-main-expanded.png`）。

## 文档索引

- 后台配置（主题/菜单/小工具/主题设置）：`docs/admin.md`
- 播客单集发布与字段说明：`docs/podcasts.md`
- RSS 订阅源（Apple Podcasts / Podcasting 2.0）：`docs/rss.md`
- 前台与播放器功能说明：`docs/frontend.md`

## 截图目录

所有截图位于 `docs/screenshots/`，文件名以编号排序，便于在文档中引用。

![前台首页示例](./screenshots/13-frontend-home.png)
