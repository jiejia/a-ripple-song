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

<h3 align="center">قالب ووردبريس حديث للبودكاست والمدونات</h3>

<p align="center">
  <a href="https://doc-podcast.aripplesong.me/docs/intro">📖 دليل</a> •
  <a href="https://doc-podcast.aripplesong.me/blog">📝 مدونة</a> •
  <a href="https://github.com/jiejia/a-ripple-song">⭐ GitHub</a>
</p>

<p align="center">
  <img alt="PHP" src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white">
  <img alt="WordPress" src="https://img.shields.io/badge/WordPress-6.6+-21759B?style=flat-square&logo=wordpress&logoColor=white">
  <img alt="License" src="https://img.shields.io/badge/License-MIT-green?style=flat-square">
</p>

---

# A Ripple Song

> **قالب حديث مُصمم للسرعة.**  
> مشغّل صوت، ويدجت، تعدد لغات، تحليلات، وتنقّل سلس.

## ✨ ما الذي يتضمنه؟

| الميزة | الوصف |
|--------|-------|
| 🎙️ **واجهة جاهزة للبودكاست** | قوالب/ويدجت للحلقات وواجهة المشغّل (يتطلب إضافة) |
| 🎵 **تجربة صوتية غامرة** | مشغّل ثابت، موجة صوتية، قوائم تشغيل وتحكم كامل |
| 🎨 **56 سِمة لونية** | سِمات DaisyUI مع مُنتقٍ بصري ووضع فاتح/داكن |
| ⚡ **تقنيات حديثة** | Laravel Blade و Tailwind CSS v4 و Vite و Alpine.js |
| 🌐 **تعدد اللغات** | ترجمة نصوص الواجهة (`resources/lang/`) |
| 📊 **قياسات وتحليلات** | دعم مدمج للمؤشرات والتحليلات |
| 🧩 **ويدجت مرنة** | مؤلفون، حلقات، بانرات وغيرها |
| 📱 **Mobile-first** | تصميم متجاوب لكل الأجهزة |
| ✨ **انتقالات سلسة** | تنقّل سلس عبر Swup.js |

---

## 🎙️ دعم البودكاست (إضافة مرافقة)

هذا القالب **لا** يسجّل أنواع محتوى مخصّصة (CPT) أو تصنيفات (Taxonomies).

لمواقع البودكاست، ثبّت الإضافة المرافقة `a-ripple-song-podcast` (تسجّل نوع المحتوى `ars_episode`). عند تفعيلها ستحصل على:

- ويدجت وقوالب للحلقات
- تكامل المشغّل مع صوت الحلقة
- أرشيف الوسوم يشمل الحلقات (إن كان متاحاً)

---

## 🎵 المشغّل الصوتي

- **تشغيل مستمر**: يستمر الصوت أثناء التنقل بين الصفحات
- **قائمة تشغيل**: ترتيب بالسحب والإفلات
- **موجة صوتية**: WaveSurfer.js بشكل فوري
- **طيف صوتي**: AudioMotion Analyzer
- **تحكم**: سرعة/تخطي مع الحفاظ على الطبقة عبر SoundTouchJS

---

## 📦 المتطلبات

- التشغيل: PHP 8.2+ و WordPress 6.6+
- التطوير: Node.js 20+ و Composer

## 🚀 البدء السريع

### التثبيت (للمستخدمين)

1. ثبّت القالب (المظهر → القوالب).
2. فعّل القالب.
3. اختياري: ثبّت `a-ripple-song-podcast` لتفعيل ميزات الحلقات.

### التطوير (للمساهمين)

```bash
cd wp-content/themes/
git clone https://github.com/jiejia/a-ripple-song.git a-ripple-song
cd a-ripple-song

composer install
npm install

npm run build    # Production
npm run dev      # Development (HMR)
```

📖 **للمزيد من التفاصيل، راجع [الدليل](https://doc-podcast.aripplesong.me/docs/intro)**

---

## ⚙️ الإعدادات

داخل لوحة تحكم ووردبريس: **Theme Settings**

| التبويب | الإعدادات |
|--------|-----------|
| **General** | الشعار، حقوق النشر، منتقي سِمات DaisyUI |
| **Social Links** | روابط التواصل في الفوتر |

---

## 🧩 الويدجت

| الويدجت | الوصف |
|--------|-------|
| **Authors** | قائمة أعضاء الفريق |
| **Banner Carousel** | سلايدر/كاروسيل |
| **Blog List** | أحدث المقالات |
| **Podcast List** | حلقات (يتطلب إضافة) |
| **Subscribe Links** | روابط الاشتراك |
| **Footer Links** | روابط الفوتر |
| **Tags Cloud** | سحابة الوسوم |

---

## 🔧 التطوير

```bash
npm run dev              # خادم تطوير (HMR)
npm run build            # بناء للإنتاج
npm run translate        # توليد الترجمات
npm run translate:compile # .po → .mo
```

---

## 📝 الترخيص

بترخيص [MIT License](../LICENSE.md).

---

## 🔗 روابط

- 📖 [التوثيق](https://doc-podcast.aripplesong.me/)
- 🐛 [Issues](https://github.com/jiejia/a-ripple-song/issues)
- ⭐ [GitHub](https://github.com/jiejia/a-ripple-song)

