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

<h3 align="center">팟캐스트와 블로그를 위한 현대적인 WordPress 테마</h3>

<p align="center">
  <a href="https://doc-podcast.aripplesong.me/docs/intro">📖 튜토리얼</a> •
  <a href="https://doc-podcast.aripplesong.me/blog">📝 블로그</a> •
  <a href="https://github.com/jiejia/a-ripple-song">⭐ GitHub</a>
</p>

<p align="center">
  <img alt="PHP" src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white">
  <img alt="WordPress" src="https://img.shields.io/badge/WordPress-6.6+-21759B?style=flat-square&logo=wordpress&logoColor=white">
  <img alt="License" src="https://img.shields.io/badge/License-MIT-green?style=flat-square">
</p>

---

# A Ripple Song

> **속도를 위해 설계된 현대적인 테마.**  
> 플레이어, 위젯, i18n, 분석, 그리고 매끄러운 페이지 전환까지.

## ✨ 포함된 기능

| 기능 | 설명 |
|------|------|
| 🎙️ **팟캐스트 친화 UI** | 에피소드 템플릿/위젯/플레이어 UI (동반 플러그인 필요) |
| 🎵 **몰입형 오디오 경험** | 전역 고정 플레이어, 파형, 재생목록, 재생 제어 |
| 🎨 **56가지 테마 컬러** | DaisyUI 테마, 시각적 선택기, 라이트/다크 지원 |
| ⚡ **모던 스택** | Laravel Blade, Tailwind CSS v4, Vite, Alpine.js |
| 🌐 **국제화** | UI 문자열 번역 포함 (`resources/lang/`) |
| 📊 **메트릭/분석** | 기본 메트릭과 분석 지원 |
| 🧩 **유연한 위젯** | Authors, Episodes, Banner 등 다양한 위젯 |
| 📱 **모바일 퍼스트** | 모든 기기에서 보기 좋은 반응형 |
| ✨ **부드러운 페이지 전환** | Swup.js 기반 전환 |

---

## 🎙️ 팟캐스트 지원 (동반 플러그인)

이 테마는 **CPT/택소노미를 등록하지 않습니다.**

팟캐스트 사이트를 위해 동반 플러그인 `a-ripple-song-podcast`를 설치하세요(`ars_episode` 포스트 타입 등록). 플러그인이 활성화되면:

- 에피소드 목록 위젯 및 템플릿
- 에피소드 오디오 플레이어 연동
- 태그 아카이브에 에피소드 포함(가능한 경우)

---

## 🎵 오디오 플레이어

- **지속 재생**: 페이지 이동 후에도 재생 유지
- **재생목록 큐**: 드래그 앤 드롭 정렬
- **파형 시각화**: WaveSurfer.js 실시간 파형
- **스펙트럼**: AudioMotion Analyzer 시각화
- **재생 제어**: 속도 조절, 건너뛰기, SoundTouchJS 피치 유지

---

## 📦 요구 사항

- 런타임: PHP 8.2+, WordPress 6.6+
- 개발: Node.js 20+, Composer

## 🚀 빠른 시작

### 설치(사용자)

1. 테마 설치(외모 → 테마).
2. 테마 활성화.
3. (선택) `a-ripple-song-podcast` 설치로 에피소드 기능 활성화.

### 개발(기여자)

```bash
cd wp-content/themes/
git clone https://github.com/jiejia/a-ripple-song.git a-ripple-song
cd a-ripple-song

composer install
npm install

npm run build    # 프로덕션
npm run dev      # 개발(HMR)
```

📖 **자세한 내용은 [튜토리얼](https://doc-podcast.aripplesong.me/docs/intro) 참고**

---

## ⚙️ 설정

워드프레스 관리자에서 **Theme Settings**:

| 탭 | 설정 |
|----|------|
| **General** | 로고, 푸터 저작권, DaisyUI 테마 선택기 |
| **Social Links** | 푸터 소셜 링크 |

---

## 📁 프로젝트 구조

```
a-ripple-song/
├── app/
│   ├── Metrics/        # 메트릭
│   ├── Providers/      # Service providers
│   ├── ThemeOptions/   # Carbon Fields 설정
│   ├── View/           # Blade view composers
│   └── Widgets/        # 위젯
├── resources/
│   ├── css/            # Tailwind
│   ├── js/             # Alpine.js & 플레이어 로직
│   ├── lang/           # 번역
│   └── views/          # Blade 템플릿
├── public/             # 빌드 산출물
├── functions.php       # 부트스트랩
└── vite.config.js      # 빌드 설정
```

---

## 🧩 위젯

| 위젯 | 설명 |
|------|------|
| **Authors** | 팀 멤버/작성자 목록 |
| **Banner Carousel** | 배너 캐러셀 |
| **Blog List** | 최근 글 |
| **Podcast List** | 에피소드 목록(플러그인 필요) |
| **Subscribe Links** | 구독 링크 |
| **Footer Links** | 푸터 링크 |
| **Tags Cloud** | 태그 클라우드 |

---

## 🔧 개발

```bash
npm run dev              # 개발 서버(HMR)
npm run build            # 프로덕션 빌드
npm run translate        # 번역 생성
npm run translate:compile # .po → .mo
```

---

## 📝 라이선스

[MIT License](../LICENSE.md)로 배포됩니다.

---

## 🔗 링크

- 📖 [문서](https://doc-podcast.aripplesong.me/)
- 🐛 [이슈](https://github.com/jiejia/a-ripple-song/issues)
- ⭐ [GitHub](https://github.com/jiejia/a-ripple-song)

---

<p align="center">
  팟캐스터를 위해 ❤️ 로 만들었습니다<br>
  Built on <a href="https://roots.io/sage/">Roots Sage</a>
</p>
