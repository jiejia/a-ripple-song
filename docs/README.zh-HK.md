<p align="center">
  <a href="README.md">English</a> •
  <a href="README.zh-CN.md">简体中文</a> •
  <a href="README.zh-TW.md">繁體中文</a> •
  <a href="README.zh-HK.md">繁體中文（香港）</a> •
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

<h3 align="center">適用於播客及網誌的現代 WordPress 主題</h3>

<p align="center">
  <a href="https://doc-podcast.aripplesong.me/docs/intro">📖 教學</a> •
  <a href="https://doc-podcast.aripplesong.me/blog">📝 網誌</a> •
  <a href="https://github.com/jiejia/a-ripple-song">⭐ GitHub</a>
</p>

<p align="center">
  <img alt="PHP" src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white">
  <img alt="WordPress" src="https://img.shields.io/badge/WordPress-6.6+-21759B?style=flat-square&logo=wordpress&logoColor=white">
  <img alt="License" src="https://img.shields.io/badge/License-MIT-green?style=flat-square">
</p>

---

# A Ripple Song

> **為速度而生的現代主題。**  
> 播放器、小工具、i18n、分析與絲滑導覽——打磨到像產品一樣好用。

## ✨ 功能一覽

| 功能 | 說明 |
|------|------|
| 🎙️ **播客友善 UI** | 集數樣板、小工具及播放器介面（需要外掛） |
| 🎵 **沉浸式音訊體驗** | 全站常駐播放器、波形視覺化、播放清單及完整控制 |
| 🎨 **56 種主題配色** | DaisyUI 主題系統，視覺化揀選器，支援明暗模式 |
| ⚡ **現代技術棧** | Laravel Blade、Tailwind CSS v4、Vite、Alpine.js |
| 🌐 **多語系** | UI 字串已翻譯（見 `resources/lang/`） |
| 📊 **資料追蹤與分析** | 內建指標/統計支援 |
| 🧩 **彈性小工具系統** | 作者、集數、橫幅等多種可自訂小工具 |
| 📱 **流動裝置優先** | 自適應版面，各種裝置都好睇 |
| ✨ **無縫頁面轉場** | Swup.js 帶來流暢頁面過渡 |

---

## 🎙️ 播客支援（需要外掛）

此主題**不會**註冊自訂文章類型（CPT）或 taxonomy。

要做播客網站，請安裝外掛 `a-ripple-song-podcast`（會註冊 `ars_episode` 文章類型）。啟用後主題可用：

- 集數列表小工具與集數樣板
- 集數音訊的播放器整合
- 標籤彙整頁可同時包含集數（如可用）

---

## 🎵 音訊播放器

- **持續播放**：換頁時播放不中斷
- **播放清單佇列**：拖曳排序
- **波形視覺化**：WaveSurfer.js 即時波形
- **音訊頻譜**：AudioMotion Analyzer 動態視覺化
- **播放控制**：倍速、快進/快退、SoundTouchJS 變速不變調

---

## 📦 環境需求

- 執行環境：PHP 8.2+、WordPress 6.6+
- 開發環境：Node.js 20+、Composer

## 🚀 快速開始

### 安裝（一般使用者）

1. 下載/安裝主題（外觀 → 主題）。
2. 啟用主題。
3. 可選：安裝播客外掛 `a-ripple-song-podcast` 以啟用集數功能。

### 開發（貢獻者）

```bash
cd wp-content/themes/
git clone https://github.com/jiejia/a-ripple-song.git a-ripple-song
cd a-ripple-song

composer install
npm install

npm run build    # 生產環境
npm run dev      # 開發環境（HMR）
```

📖 **詳細設定請參考 [教學](https://doc-podcast.aripplesong.me/docs/intro)**

---

## ⚙️ 設定

在 WordPress 後台前往 **Theme Settings**：

| 分頁 | 內容 |
|------|------|
| **General** | 網站 Logo、頁腳版權、DaisyUI 主題揀選器 |
| **Social Links** | 頁腳社交連結 |

---

## 📁 專案結構

```
a-ripple-song/
├── app/
│   ├── Metrics/        # 指標/統計
│   ├── Providers/      # Service providers
│   ├── ThemeOptions/   # Carbon Fields 設定
│   ├── View/           # Blade view composers
│   └── Widgets/        # 自訂小工具
├── resources/
│   ├── css/            # Tailwind 樣式
│   ├── js/             # Alpine.js 與播放器邏輯
│   ├── lang/           # 翻譯檔
│   └── views/          # Blade 模板
├── public/             # 編譯後資源
├── functions.php       # 主題啟動
└── vite.config.js      # 建置設定
```

---

## 🧩 小工具

| 小工具 | 說明 |
|--------|------|
| **Authors** | 團隊成員（頭像與角色） |
| **Banner Carousel** | 首頁橫幅輪播 |
| **Blog List** | 最新文章列表 |
| **Podcast List** | 集數網格（需要外掛） |
| **Subscribe Links** | 訂閱平台連結 |
| **Footer Links** | 頁腳欄位連結 |
| **Tags Cloud** | 標籤雲 |

---

## 🔧 開發

```bash
npm run dev              # 啟動開發伺服器（HMR）
npm run build            # 生產環境建置
npm run translate        # 產生翻譯檔
npm run translate:compile # 編譯 .po 為 .mo
```

---

## 📝 授權

以 [MIT License](../LICENSE.md) 授權。

---

## 🔗 連結

- 📖 [文件](https://doc-podcast.aripplesong.me/)
- 🐛 [Issues](https://github.com/jiejia/a-ripple-song/issues)
- ⭐ [GitHub](https://github.com/jiejia/a-ripple-song)

---

<p align="center">
  為播客創作者用 ❤️ 打造<br>
  基於 <a href="https://roots.io/sage/">Roots Sage</a>
</p>

