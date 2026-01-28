<p align="center">
  <a href="README.md">English</a> •
  <a href="README.zh-CN.md">简体中文</a> •
  <a href="README.zh-TW.md">繁體中文</a> •
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

<h3 align="center">پوڈکاسٹ اور بلاگ کے لیے جدید WordPress تھیم</h3>

<p align="center">
  <a href="https://doc-podcast.aripplesong.me/docs/intro">📖 ٹیوٹوریل</a> •
  <a href="https://doc-podcast.aripplesong.me/blog">📝 بلاگ</a> •
  <a href="https://github.com/jiejia/a-ripple-song">⭐ GitHub</a>
</p>

<p align="center">
  <img alt="PHP" src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white">
  <img alt="WordPress" src="https://img.shields.io/badge/WordPress-6.6+-21759B?style=flat-square&logo=wordpress&logoColor=white">
  <img alt="License" src="https://img.shields.io/badge/License-MIT-green?style=flat-square">
</p>

---

# A Ripple Song

> **رفتار کے لیے بنایا گیا جدید تھیم۔**  
> پلیئر، ویجٹس، i18n، اینالٹکس اور ہموار نیویگیشن۔

## ✨ خصوصیات

| فیچر | وضاحت |
|------|-------|
| 🎙️ **پوڈکاسٹ‑ریڈی UI** | ایپی سوڈ ٹیمپلیٹس/ویجٹس/پلیئر UI (پلگ اِن درکار) |
| 🎵 **بہترین آڈیو تجربہ** | مستقل پلیئر، ویوفارم، پلے لسٹس اور کنٹرولز |
| 🎨 **56 تھیم کلرز** | DaisyUI تھیمز، بصری پکر، لائٹ/ڈارک سپورٹ |
| ⚡ **جدید اسٹیک** | Laravel Blade, Tailwind CSS v4, Vite, Alpine.js |
| 🌐 **بین الاقوامی کاری** | UI متن کے ترجمے (`resources/lang/`) |
| 📊 **میٹرکس/اینالٹکس** | بلٹ‑اِن سپورٹ |
| 🧩 **لچکدار ویجٹس** | Authors, Episodes, Banner وغیرہ |
| 📱 **Mobile-first** | ہر ڈیوائس پر اچھا لگے |
| ✨ **ہموار ٹرانزیشن** | Swup.js کے ساتھ نیویگیشن |

---

## 🎙️ پوڈکاسٹ سپورٹ (کمپینین پلگ اِن)

یہ تھیم **CPT یا taxonomy رجسٹر نہیں کرتا**۔

پوڈکاسٹ سائٹس کے لیے `a-ripple-song-podcast` پلگ اِن انسٹال کریں (یہ `ars_episode` پوسٹ ٹائپ رجسٹر کرتا ہے)۔ پلگ اِن ایکٹو ہونے پر:

- ایپی سوڈ لسٹ ویجٹس اور ٹیمپلیٹس
- ایپی سوڈ آڈیو کے لیے پلیئر انٹیگریشن
- ٹیگ آرکائیوز میں ایپی سوڈز شامل (اگر دستیاب ہو)

---

## 🎵 آڈیو پلیئر

- **مستقل پلے بیک**: صفحات بدلنے پر بھی جاری
- **پلے لسٹ قطار**: ڈریگ اینڈ ڈراپ ری آرڈر
- **ویوفارم**: WaveSurfer.js ریئل ٹائم
- **اسپیکٹرم**: AudioMotion Analyzer ویژولائزیشن
- **کنٹرولز**: اسپیڈ، اسکیپ، SoundTouchJS کے ساتھ پچ برقرار

---

## 📦 ضروریات

- رن ٹائم: PHP 8.2+، WordPress 6.6+
- ڈیولپمنٹ: Node.js 20+، Composer

## 🚀 فوری آغاز

### انسٹال (یوزرز)

1. تھیم انسٹال کریں (Appearance → Themes)۔
2. تھیم ایکٹیویٹ کریں۔
3. اختیاری: `a-ripple-song-podcast` انسٹال کر کے ایپی سوڈ فیچرز فعال کریں۔

### ڈیولپ (کنٹریبیوٹرز)

```bash
cd wp-content/themes/
git clone https://github.com/jiejia/a-ripple-song.git a-ripple-song
cd a-ripple-song

composer install
npm install

npm run build    # Production
npm run dev      # Development (HMR)
```

📖 **مزید تفصیل کے لیے [ٹیوٹوریل](https://doc-podcast.aripplesong.me/docs/intro) دیکھیں**

---

## ⚙️ کنفیگریشن

WordPress admin میں **Theme Settings**:

| ٹیب | سیٹنگز |
|-----|--------|
| **General** | لوگو، فوٹر کاپی رائٹ، DaisyUI تھیم پکر |
| **Social Links** | فوٹر سوشل لنکس |

---

## 🧩 ویجٹس

| ویجٹ | وضاحت |
|------|-------|
| **Authors** | ٹیم/مصنفین کی فہرست |
| **Banner Carousel** | ہیرو کیروسل |
| **Blog List** | حالیہ پوسٹس |
| **Podcast List** | ایپی سوڈز (پلگ اِن درکار) |
| **Subscribe Links** | سبسکرائب لنکس |
| **Footer Links** | فوٹر کالمز |
| **Tags Cloud** | ٹیگ کلاؤڈ |

---

## 🔧 ڈیولپمنٹ

```bash
npm run dev              # Dev سرور (HMR)
npm run build            # Production build
npm run translate        # ترجمے بنائیں
npm run translate:compile # .po → .mo
```

---

## 📝 لائسنس

[MIT License](../LICENSE.md) کے تحت۔

---

## 🔗 لنکس

- 📖 [ڈاکیومنٹیشن](https://doc-podcast.aripplesong.me/)
- 🐛 [Issues](https://github.com/jiejia/a-ripple-song/issues)
- ⭐ [GitHub](https://github.com/jiejia/a-ripple-song)
