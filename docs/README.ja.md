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

<h3 align="center">ポッドキャストとブログ向けのモダンな WordPress テーマ</h3>

<p align="center">
  <a href="https://doc-podcast.aripplesong.me/docs/intro">📖 チュートリアル</a> •
  <a href="https://doc-podcast.aripplesong.me/blog">📝 ブログ</a> •
  <a href="https://github.com/jiejia/a-ripple-song">⭐ GitHub</a>
</p>

<p align="center">
  <img alt="PHP" src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white">
  <img alt="WordPress" src="https://img.shields.io/badge/WordPress-6.6+-21759B?style=flat-square&logo=wordpress&logoColor=white">
  <img alt="License" src="https://img.shields.io/badge/License-GPL--3.0-blue?style=flat-square">
</p>

---

# A Ripple Song

> **スピード重視のモダンテーマ。**  
> プレイヤー、ウィジェット、多言語対応、分析、そして滑らかなページ遷移。

## ✨ 含まれる機能

| 機能 | 説明 |
|------|------|
| 🎙️ **ポッドキャスト向け UI** | エピソード用テンプレート/ウィジェット/プレイヤーUI（プラグインが必要） |
| 🎵 **没入型オーディオ体験** | 常駐プレイヤー、波形表示、プレイリスト、再生コントロール |
| 🎨 **56種類のテーマカラー** | DaisyUI テーマ、ビジュアルピッカー、ライト/ダーク対応 |
| ⚡ **モダンな技術スタック** | Laravel Blade、Tailwind CSS v4、Vite、Alpine.js |
| 🌐 **国際化** | UI 文字列の翻訳（`resources/lang/`） |
| 📊 **メトリクス/分析** | 指標・分析の仕組みを内蔵 |
| 🧩 **柔軟なウィジェット** | 著者、エピソード、バナーなど |
| 📱 **モバイルファースト** | あらゆるデバイスに最適化 |
| ✨ **シームレス遷移** | Swup.js による滑らかなページ遷移 |

---

## 🎙️ ポッドキャスト対応（コンパニオンプラグイン）

このテーマは **CPT や taxonomy を登録しません**。

ポッドキャストサイトでは、コンパニオンプラグイン `a-ripple-song-podcast`（`ars_episode` を登録）をインストールしてください。有効化すると次が利用できます：

- エピソード一覧ウィジェットとエピソードテンプレート
- エピソード音声のプレイヤー連携
- タグアーカイブにエピソードを含める（利用可能な場合）

---

## 🎵 オーディオプレイヤー

- **常駐再生**：ページ遷移しても再生が継続
- **プレイリスト**：ドラッグ&ドロップで並び替え
- **波形表示**：WaveSurfer.js によるリアルタイム波形
- **スペクトラム**：AudioMotion Analyzer による可視化
- **再生制御**：速度変更、スキップ、SoundTouchJS によるピッチ維持

---

## 📦 要件

- 実行環境：PHP 8.2+、WordPress 6.6+
- 開発：Node.js 20+、Composer

## 🚀 クイックスタート

### インストール（利用者）

1. テーマをインストール（外観 → テーマ）。
2. テーマを有効化。
3. 任意：`a-ripple-song-podcast` を導入してエピソード機能を有効化。

### 開発（コントリビューター）

```bash
cd wp-content/themes/
git clone https://github.com/jiejia/a-ripple-song.git a-ripple-song
cd a-ripple-song

composer install
npm install

npm run build    # 本番
npm run dev      # 開発（HMR）
```

📖 **詳細は [チュートリアル](https://doc-podcast.aripplesong.me/docs/intro) を参照してください**

---

## ⚙️ 設定

WordPress 管理画面の **Theme Settings**：

| タブ | 内容 |
|------|------|
| **General** | ロゴ、フッター著作権、DaisyUI テーマピッカー |
| **Social Links** | フッターのソーシャルリンク |

---

## 📁 構成

```
a-ripple-song/
├── app/
│   ├── Metrics/        # メトリクス
│   ├── Providers/      # Service providers
│   ├── ThemeOptions/   # Carbon Fields 設定
│   ├── View/           # Blade view composers
│   └── Widgets/        # ウィジェット
├── resources/
│   ├── css/            # Tailwind
│   ├── js/             # Alpine.js / プレイヤー
│   ├── lang/           # 翻訳
│   └── views/          # Blade テンプレート
├── public/             # ビルド成果物
├── functions.php       # ブートストラップ
└── vite.config.js      # ビルド設定
```

---

## 🧩 ウィジェット

| ウィジェット | 説明 |
|-------------|------|
| **Authors** | 著者/メンバー一覧 |
| **Banner Carousel** | ヒーローのカルーセル |
| **Blog List** | 最新投稿 |
| **Podcast List** | エピソード一覧（プラグインが必要） |
| **Subscribe Links** | 購読リンク |
| **Footer Links** | フッターリンク |
| **Tags Cloud** | タグクラウド |

---

## 🔧 開発

```bash
npm run dev              # 開発サーバー（HMR）
npm run build            # 本番ビルド
npm run translate        # 翻訳生成
npm run translate:compile # .po → .mo
```

---

## 📝 ライセンス

[GPL-3.0](../LICENSE) の下で提供されます。

---

## 🔗 リンク

- 📖 [ドキュメント](https://doc-podcast.aripplesong.me/)
- 🐛 [Issue](https://github.com/jiejia/a-ripple-song/issues)
- ⭐ [GitHub](https://github.com/jiejia/a-ripple-song)

---

<p align="center">
  podcaster のために ❤️ を込めて<br>
  Built on <a href="https://roots.io/sage/">Roots Sage</a>
</p>
