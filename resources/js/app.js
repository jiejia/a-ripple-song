import.meta.glob([
  '../images/**',
  '../fonts/**',
]);
import Swup from 'swup';
import { createIcons, icons } from 'lucide';
import { Howl, Howler } from 'howler';
import AudioMotionAnalyzer from 'audiomotion-analyzer';
import SwupFormsPlugin from '@swup/forms-plugin';
import SwupScriptsPlugin from '@swup/scripts-plugin';
import Alpine from 'alpinejs'
import { DateTime } from 'luxon';
import * as Tone from 'tone';
import WaveSurfer from 'wavesurfer.js';

// WordPress i18n
const { __ } = wp.i18n;

const METRIC_ACTIONS = {
  view: 'aripplesong_increment_view',
  play: 'aripplesong_increment_play',
};

let lastViewMetricKey = null;

const progressHeatmapCache = new Map();

function computeRmsBySecond(audioBuffer, stepSeconds = 1) {
  const step = Math.max(1, Number(stepSeconds) || 1);
  const seconds = Math.max(1, Math.ceil(audioBuffer.duration / step));
  const sampleRate = audioBuffer.sampleRate;
  const channels = Math.max(1, audioBuffer.numberOfChannels || 1);
  const channelData = Array.from({ length: channels }, (_, i) => audioBuffer.getChannelData(i));

  const values = new Array(seconds).fill(0);

  for (let secondIndex = 0; secondIndex < seconds; secondIndex++) {
    const startTime = secondIndex * step;
    const endTime = Math.min((secondIndex + 1) * step, audioBuffer.duration);

    const startSample = Math.max(0, Math.floor(startTime * sampleRate));
    const endSample = Math.min(audioBuffer.length, Math.floor(endTime * sampleRate));
    const windowSamples = Math.max(0, endSample - startSample);

    if (windowSamples <= 0) {
      values[secondIndex] = 0;
      continue;
    }

    // Stride to keep the per-second analysis fast on long files.
    const stride = Math.max(1, Math.floor(windowSamples / 2048));
    let sumSq = 0;
    let count = 0;

    for (let i = startSample; i < endSample; i += stride) {
      let mixed = 0;
      for (let ch = 0; ch < channels; ch++) {
        mixed += channelData[ch][i] || 0;
      }
      mixed /= channels;

      sumSq += mixed * mixed;
      count++;
    }

    values[secondIndex] = count > 0 ? Math.sqrt(sumSq / count) : 0;
  }

  return values;
}

function smoothValues(values, radius = 1) {
  const r = Math.max(0, Math.floor(radius));
  if (r === 0) return values.slice();

  const smoothed = new Array(values.length).fill(0);
  for (let i = 0; i < values.length; i++) {
    let sum = 0;
    let count = 0;
    for (let j = i - r; j <= i + r; j++) {
      if (j < 0 || j >= values.length) continue;
      sum += values[j];
      count++;
    }
    smoothed[i] = count ? sum / count : values[i];
  }
  return smoothed;
}

function buildOrangeHeatGradient(values, options = {}) {
  const levels = Math.max(4, Math.floor(options.levels || 24));
  const gamma = typeof options.gamma === 'number' ? options.gamma : 0.6;
  
  // Use HSL color space to maintain orange hue consistently
  // Orange hue is around 30-40 degrees (35 is a nice orange-yellow)
  const hue = typeof options.hue === 'number' ? options.hue : 35;
  const saturation = typeof options.saturation === 'number' ? options.saturation : 100;
  // Lightness range: high intensity = darker orange, low intensity = lighter orange
  const minLightness = typeof options.minLightness === 'number' ? options.minLightness : 50;
  const maxLightness = typeof options.maxLightness === 'number' ? options.maxLightness : 80;

  if (!Array.isArray(values) || values.length === 0) {
    return '';
  }

  let min = Infinity;
  let max = -Infinity;
  values.forEach(v => {
    const n = Number.isFinite(v) ? v : 0;
    if (n < min) min = n;
    if (n > max) max = n;
  });

  const range = max - min;
  if (!Number.isFinite(range) || range <= 1e-8) {
    return '';
  }

  const normalizedLevels = values.map(v => {
    const n = Number.isFinite(v) ? v : 0;
    const t = Math.min(1, Math.max(0, (n - min) / range));
    const curved = Math.pow(t, gamma);
    return Math.min(levels - 1, Math.max(0, Math.round(curved * (levels - 1))));
  });

  // Generate color using HSL to maintain consistent orange hue
  const levelToColor = (level) => {
    const t = levels <= 1 ? 0 : level / (levels - 1);
    // Higher level (higher intensity) = darker orange, lower level = lighter orange
    const lightness = maxLightness - t * (maxLightness - minLightness);
    return `hsl(${hue}, ${saturation}%, ${lightness}%)`;
  };

  // Run-length encode to keep the gradient string small.
  const n = normalizedLevels.length;
  const stops = [];
  let i = 0;
  while (i < n) {
    const level = normalizedLevels[i];
    let j = i + 1;
    while (j < n && normalizedLevels[j] === level) j++;

    const startPct = ((i / n) * 100).toFixed(3);
    const endPct = ((j / n) * 100).toFixed(3);
    const color = levelToColor(level);

    stops.push(`${color} ${startPct}%`, `${color} ${endPct}%`);
    i = j;
  }

  return `linear-gradient(to right, ${stops.join(', ')})`;
}

function bumpPlayCountDom(postId) {
  if (!postId) return;
  const els = Array.from(document.querySelectorAll(`.js-play-count[data-post-id="${postId}"]`));
  els.forEach(el => {
    const current = parseInt(el.textContent, 10);
    const safe = isNaN(current) ? 0 : current;
    el.textContent = safe + 1;
  });
}

function resolvePrimaryPostId() {
  const ajax = window.aripplesongData?.ajax;
  if (ajax?.postId) {
    return ajax.postId;
  }

  const viewEls = Array.from(document.querySelectorAll('.js-views-count[data-post-id]'));
  const ids = [...new Set(viewEls.map(el => Number(el.dataset.postId)).filter(Boolean))];

  // 仅当页面上唯一一个 postId 时才认为是详情页，避免列表页误计数
  if (ids.length === 1) {
    return ids[0];
  }

  return 0;
}

async function sendAjaxMetric(action, postId) {
  const ajax = window.aripplesongData?.ajax;

  if (!ajax?.url || !ajax?.nonce || !postId) {
    return null;
  }

  try {
    const response = await fetch(ajax.url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        action,
        post_id: postId,
        _ajax_nonce: ajax.nonce,
      }),
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }

    return await response.json();
  } catch (error) {
    console.error(`[aripplesong] Failed to send metric "${action}"`, error);
    return null;
  }
}

function maybeSendViewMetric() {
  const postId = resolvePrimaryPostId();

  if (!postId) {
    return;
  }

  const key = `${postId}:${window.location.href}`;
  if (lastViewMetricKey === key) {
    return;
  }

  lastViewMetricKey = key;
  sendAjaxMetric(METRIC_ACTIONS.view, postId);
}

async function fetchMetrics(postIds = []) {
  const ajax = window.aripplesongData?.ajax;
  if (!ajax?.url || !ajax?.nonce || !Array.isArray(postIds) || postIds.length === 0) {
    return null;
  }

  try {
    const params = new URLSearchParams({
      action: 'aripplesong_get_metrics',
      _ajax_nonce: ajax.nonce,
    });

    postIds.forEach(id => {
      params.append('post_ids[]', id);
    });

    const response = await fetch(ajax.url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: params,
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }

    const json = await response.json();
    return json?.data?.counts || null;
  } catch (error) {
    console.error('[aripplesong] Failed to fetch metrics', error);
    return null;
  }
}

function markMetricsReady() {
  window.aripplesongMetricsReady = true;
  window.dispatchEvent(new CustomEvent('aripplesong:metrics:ready'));
}

function hydrateMetricsFromDom() {
  window.aripplesongMetricsReady = false;
  const viewEls = Array.from(document.querySelectorAll('.js-views-count'));
  if (!viewEls.length) {
    markMetricsReady();
    return;
  }

  const ids = [...new Set(viewEls.map(el => Number(el.dataset.postId)).filter(Boolean))];

  fetchMetrics(ids).then(counts => {
    if (!counts) {
      return;
    }

    viewEls.forEach(el => {
      const id = Number(el.dataset.postId);
      const entry = counts[id];
      if (entry && typeof entry.views === 'number') {
        el.textContent = entry.views;
      }
    });

    const playEls = Array.from(document.querySelectorAll('.js-play-count'));
    playEls.forEach(el => {
      const id = Number(el.dataset.postId);
      const entry = counts[id];
      if (entry && typeof entry.plays === 'number') {
        el.textContent = entry.plays;
      }
    });
  }).catch(() => null).then(() => {
    markMetricsReady();
  });
}

// Storage helpers: gracefully handle browsers/contexts where storage is blocked
const createMemoryStorage = () => {
  const store = {};
  return {
    getItem(key) {
      return Object.prototype.hasOwnProperty.call(store, key) ? store[key] : null;
    },
    setItem(key, value) {
      store[key] = String(value);
    },
    removeItem(key) {
      delete store[key];
    },
    clear() {
      Object.keys(store).forEach(key => delete store[key]);
    },
    key(index) {
      const keys = Object.keys(store);
      return keys[index] || null;
    },
    get length() {
      return Object.keys(store).length;
    }
  };
};

function createSafeStorage(type = 'localStorage') {
  if (typeof window === 'undefined') {
    return createMemoryStorage();
  }

  try {
    const storage = window[type];
    const testKey = '__aripplesong_storage_test__';
    storage.setItem(testKey, '1');
    storage.removeItem(testKey);
    return storage;
  } catch (error) {
    console.warn(`[aripplesong] ${type} is not accessible; falling back to memory storage.`, error);
    return createMemoryStorage();
  }
}

const safeLocalStorage = createSafeStorage('localStorage');

const FALLBACK_LIGHT_THEME = 'retro';
const FALLBACK_DARK_THEME = 'dim';
const LIGHT_THEMES = window.aripplesongData?.theme?.lightThemes || [
  'retro',
  'pastel-breeze',
  'soft-sand',
  'mint-cream',
  'blush-mist',
  'sky-peach',
  'lemon-fizz',
  'lavender-fog',
  'coral-sunset',
  'sea-glass',
  'apricot-sorbet',
  'cotton-candy',
  'pear-spritz',
  'cloud-latte',
  'dew-frost',
  'peach-foam',
  'lilac-ice',
  'sage-mint',
  'buttercup',
  'powder-blue',
  'melon-ice',
  'hazy-rose',
  'calm-water',
  'honey-milk',
  'arctic-mint',
  'vanilla-berry',
  'morning-sun',
  'matcha-cream'
];

const DARK_THEMES = window.aripplesongData?.theme?.darkThemes || [
  'dim',
  'midnight-aurora',
  'neon-plasma',
  'cyber-grape',
  'velvet-ember',
  'ink-cyan',
  'dusk-rose',
  'obsidian-gold',
  'deep-space',
  'ocean-night',
  'noir-mint',
  'plum-neon',
  'cobalt-flare',
  'dusk-marine',
  'ember-glow',
  'midnight-teal',
  'aurora-mist',
  'shadow-berry',
  'neon-blush',
  'abyss-blue',
  'charcoal-mint',
  'galaxy-candy',
  'violet-storm',
  'magma-ice',
  'stormy-sea',
  'lunar-mauve',
  'acid-jungle',
  'carbon-ember'
];

const THEME_PALETTE = window.aripplesongData?.theme?.palette || {};
const LIGHT_THEME_SET = new Set(LIGHT_THEMES);
const DARK_THEME_SET = new Set(DARK_THEMES);

const resolveTheme = (theme, available, fallback) => {
  if (theme && available.includes(theme)) {
    return theme;
  }
  return fallback;
};

const ensureThemeStylesInjected = (() => {
  let injected = false;
  return () => {
    if (injected) return;
    const entries = Object.entries(THEME_PALETTE || {});
    if (!entries.length) return;

    const rules = entries.map(([slug, colors]) => {
      const scheme = DARK_THEME_SET.has(slug) ? 'dark' : 'light';
      const palette = {
        base100: colors.base100 || '#f3f4f6',
        base200: colors.base200 || '#e5e7eb',
        base300: colors.base300 || '#d1d5db',
        baseContent: colors.baseContent || '#111827',
        primary: colors.primary || '#570df8',
        primaryContent: colors.primaryContent || '#ffffff',
        secondary: colors.secondary || '#f000b8',
        secondaryContent: colors.secondaryContent || '#ffffff',
        accent: colors.accent || '#37cdbe',
        accentContent: colors.accentContent || '#ffffff',
        neutral: colors.neutral || '#3d4451',
        neutralContent: colors.neutralContent || '#f3f4f6',
      };

      return `:root[data-theme="${slug}"]{color-scheme:${scheme};--color-base-100:${palette.base100};--color-base-200:${palette.base200};--color-base-300:${palette.base300};--color-base-content:${palette.baseContent};--color-primary:${palette.primary};--color-primary-content:${palette.primaryContent};--color-secondary:${palette.secondary};--color-secondary-content:${palette.secondaryContent};--color-accent:${palette.accent};--color-accent-content:${palette.accentContent};--color-neutral:${palette.neutral};--color-neutral-content:${palette.neutralContent};}`;
    });

    if (!rules.length) return;

    const style = document.createElement('style');
    style.id = 'aripplesong-theme-styles';
    style.textContent = rules.join('');
    document.head.appendChild(style);
    injected = true;
  };
})();

// ========== Date Formatting Utility ==========
/**
 * Format timestamp to localized date string (similar to PHP get_localized_date)
 * @param {number} timestamp - Unix timestamp in seconds
 * @param {string} format - 'relative' (default), 'short', 'long'
 * @returns {string} Formatted date string
 */
window.formatLocalizedDate = function(timestamp, format = 'relative') {
  if (!timestamp || isNaN(timestamp)) return '-';
  
  // Get WordPress locale from HTML lang attribute or document.documentElement.lang
  const wpLocale = document.documentElement.lang || 'en-US';
  // Convert WordPress locale format (e.g., 'zh-CN', 'en-US') to Luxon format
  const luxonLocale = wpLocale.replace('_', '-');
  
  // Create DateTime from Unix timestamp (seconds)
  const date = DateTime.fromSeconds(parseInt(timestamp)).setLocale(luxonLocale);
  const now = DateTime.now();
  
  if (format === 'relative') {
    // Smart relative time: show relative for recent posts, absolute for older ones
    const diffInDays = now.diff(date, 'days').days;
    
    if (diffInDays < 7) {
      // Recent: use relative time (e.g., "30 minutes ago", "2 days ago")
      return date.toRelative() || date.toLocaleString(DateTime.DATE_MED);
    } else {
      // Older: use absolute date format based on locale
      const baseLocale = luxonLocale.split('-')[0];
      
      if (['zh', 'ja'].includes(baseLocale)) {
        // Chinese/Japanese: 2025年11月4日
        return date.toFormat('yyyy年M月d日');
      } else if (baseLocale === 'ko') {
        // Korean: 2025년 11월 4일
        return date.toFormat('yyyy년 M월 d일');
      } else {
        // Western languages: Nov 4, 2025
        return date.toLocaleString(DateTime.DATE_MED);
      }
    }
  } else if (format === 'short') {
    const baseLocale = luxonLocale.split('-')[0];
    
    if (['zh', 'ja'].includes(baseLocale)) {
      return date.toFormat('yyyy年M月d日');
    } else if (baseLocale === 'ko') {
      return date.toFormat('yyyy년 M월 d일');
    } else {
      return date.toLocaleString(DateTime.DATE_MED);
    }
  } else if (format === 'long') {
    const baseLocale = luxonLocale.split('-')[0];
    
    if (['zh', 'ja'].includes(baseLocale)) {
      return date.toFormat('yyyy年M月d日');
    } else if (baseLocale === 'ko') {
      return date.toFormat('yyyy년 M월 d일');
    } else {
      return date.toLocaleString(DateTime.DATE_FULL);
    }
  }
  
  return date.toLocaleString(DateTime.DATE_MED);
};

window.Alpine = Alpine

// 创建主题 Store
Alpine.store('theme', {
  mode: 'auto', // 'light', 'dark', 'auto'
  storageKey: 'aripplesong-theme-mode',
  lightTheme: resolveTheme(window.aripplesongData?.theme?.lightTheme, LIGHT_THEMES, FALLBACK_LIGHT_THEME),
  darkTheme: resolveTheme(window.aripplesongData?.theme?.darkTheme, DARK_THEMES, FALLBACK_DARK_THEME),
  mediaQuery: null,
  
  init() {
    ensureThemeStylesInjected();

    // 从 localStorage 加载主题模式
    const savedMode = safeLocalStorage.getItem(this.storageKey);
    if (savedMode && ['light', 'dark', 'auto'].includes(savedMode)) {
      this.mode = savedMode;
    }
    
    // 监听系统主题变化
    this.mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    this.mediaQuery.addEventListener('change', () => {
      if (this.mode === 'auto') {
        this.applyTheme();
      }
    });
    
    // 应用初始主题
    this.applyTheme();
  },
  
  toggle() {
    // 循环切换：light -> dark -> auto -> light
    if (this.mode === 'light') {
      this.mode = 'dark';
    } else if (this.mode === 'dark') {
      this.mode = 'auto';
    } else {
      this.mode = 'light';
    }
    
    safeLocalStorage.setItem(this.storageKey, this.mode);
    this.applyTheme();
  },
  
  setMode(mode) {
    // 直接设置主题模式
    if (['light', 'dark', 'auto'].includes(mode)) {
      this.mode = mode;
      safeLocalStorage.setItem(this.storageKey, this.mode);
      this.applyTheme();
    }
  },
  
  applyTheme() {
    ensureThemeStylesInjected();
    let theme;
    if (this.mode === 'auto') {
      // 跟随系统
      theme = this.mediaQuery.matches ? this.darkTheme : this.lightTheme;
    } else if (this.mode === 'dark') {
      theme = this.darkTheme;
    } else {
      theme = this.lightTheme;
    }
    
    const root = document.documentElement;
    const isDarkTheme = DARK_THEME_SET.has(theme);
    root.setAttribute('data-theme', theme);
    root.style.setProperty('--aripplesong-color-scheme', isDarkTheme ? 'dark' : 'light');
    root.style.colorScheme = isDarkTheme ? 'dark' : 'light';
  },
  
  get isDark() {
    if (this.mode === 'auto') {
      return this.mediaQuery ? this.mediaQuery.matches : false;
    }
    return this.mode === 'dark';
  },
  
  get isLight() {
    return this.mode === 'light';
  },
  
  get isAuto() {
    return this.mode === 'auto';
  }
});

// 创建 Alpine Store
Alpine.store('player', {
  // player
  currentSound: null,
  soundId: null,
  audioMotion: null,
  audioContext: null,
  audioSourceNode: null,
  pitchShiftNode: null,
  toneContextReady: false,
  isPlaying: false,
  isLoading: false, // 音频加载状态
  currentTime: 0,
  duration: 0,
  volume: 0.5,
  isMuted: false,
  lastVolume: 1,
  volumePanelOpen: false,
  timer: null,
  volumeGainNode: null, // 用于独立控制输出音量的 GainNode

  // progress heatmap (per-second intensity -> orange shades)
  progressHeatmapGradient: '',
  progressHeatmapReady: false, // for fade-in effect
  progressHeatmapStepSeconds: 10,
  progressHeatmapSmoothingRadius: 1,
  _progressHeatmapNonce: 0,
  
  // playback rate
  playbackRate: 1.0,
  availableRates: [0.5, 0.75, 1, 1.25, 1.5, 2],
  playbackRatePanelOpen: false,

  // autoplay confirm (用于处理浏览器自动播放策略)
  showAutoplayConfirm: false,
  pendingAutoplay: false, // 标记是否有待确认的自动播放
  autoplayConfirmTimer: null, // 自动关闭定时器
  autoplayCountdown: 10, // 倒计时秒数
  autoplayCountdownTimer: null, // 倒计时更新定时器

  // playlist
  playlist: [],
  currentIndex: 0,
  currentEpisode: null,
  storageKey: 'aripplesong-playlist',
  currentIndexKey: 'aripplesong-current-index',
  latestSignatureKey: 'aripplesong-latest-playlist-signature',
  volumeKey: 'aripplesong-volume',
  currentTimeKey: 'aripplesong-current-time',
  isPlayingKey: 'aripplesong-is-playing',
  playbackRateKey: 'aripplesong-playback-rate',

  // ========== 计算属性 ==========
  get currentTimeText() {
    return this.formatTime(this.currentTime);
  },

  get durationText() {
    return this.formatTime(this.duration);
  },

  get playbackRateText() {
    return this.playbackRate === 1 ? '1x' : `${this.playbackRate}x`;
  },

  get progressRangeStyle() {
    if (!this.progressHeatmapGradient) return {};
    return { '--aripplesong-progress-gradient': this.progressHeatmapGradient };
  },

  get currentEpisodePublishDate() {
    if (!this.currentEpisode?.publishDate) return '-';
    return window.formatLocalizedDate(this.currentEpisode.publishDate);
  },

  /**
   * Fetch latest 10 podcast episodes from WordPress REST API and append to playlist.
   * @param {boolean} autoPlay Whether to autoplay the first new episode (default: false).
   */
  async fetchLatestPodcast(autoPlay = false) {
    try {
      // 使用 PHP 传递的 REST API URL
      const restUrl = window.aripplesongData?.restUrl || '/wp-json/';
      
      // 构建 API URL，处理不同的 REST URL 格式
      // 如果 restUrl 已经包含 ?（如 /index.php?rest_route=/），则用 & 连接参数
      // 否则使用标准的 ? 连接参数
      const queryParams = 'per_page=10&orderby=date&order=desc&_embed';
      const separator = restUrl.includes('?') ? '&' : '?';
      const apiUrl = `${restUrl}wp/v2/podcast${separator}${queryParams}`;
      
      // Fetch latest podcast episodes via WordPress REST API.
      const response = await fetch(apiUrl);

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const podcasts = await response.json();

      if (podcasts.length === 0) {
        console.log(__('No podcasts found', 'sage'));
        return [];
      }

      console.log(__('Fetched %d podcasts', 'sage').replace('%d', podcasts.length));

      const addedEpisodes = [];
      let firstNewEpisode = null;

      // 遍历所有播客
      for (const post of podcasts) {
        // 获取音频文件 URL（从自定义字段）
        let audioUrl = post.audio_file || '';

        // 如果没有通过 meta 获取到，尝试从 ACF 或其他方式获取
        if (!audioUrl && post.acf?.audio_file) {
          audioUrl = post.acf.audio_file;
        }

        if (!audioUrl) {
          console.warn(__('Podcast has no audio file, skipping:', 'sage'), post.title.rendered);
          continue;
        }

        // 获取特色图片（为空时交由前端显示占位图标）
        let featuredImage = null;
        if (post._embedded?.['wp:featuredmedia']?.[0]?.source_url) {
          featuredImage = post._embedded['wp:featuredmedia'][0].source_url;
        }

        // 构建 episode 对象
        const episode = {
          id: post.id,
          audioUrl: audioUrl,
          title: post.title.rendered,
          description: post.excerpt.rendered.replace(/<[^>]*>/g, ''), // 移除 HTML 标签
          publishDate: Math.floor(new Date(post.date).getTime() / 1000), // Store Unix timestamp (seconds)
          featuredImage: featuredImage,
          link: post.link
        };

        // 检查是否已存在
        const existingIndex = this.playlist.findIndex(item => item.id === episode.id);

        if (existingIndex === -1) {
          // 不存在，添加到播放列表（不自动播放）
          this.addEpisodeToPlaylist(episode);
          addedEpisodes.push(episode);

          // 记录第一个新添加的节目
          if (!firstNewEpisode) {
            firstNewEpisode = episode;
          }

          console.log(__('Added:', 'sage'), episode.title);
        } else {
          console.log(__('Already exists, skipping:', 'sage'), episode.title);
        }
      }

      if (addedEpisodes.length > 0) {
        console.log(__('Successfully added %d new podcasts to playlist', 'sage').replace('%d', addedEpisodes.length));

        // 如果需要自动播放且有新节目
        if (autoPlay && firstNewEpisode) {
          // 切换到第一个新添加的节目
          const index = this.playlist.findIndex(item => item.id === firstNewEpisode.id);
          if (index !== -1) {
            // ⭐ 只加载音轨，不自动播放，显示确认对话框
            this.currentIndex = index;
            this.currentEpisode = firstNewEpisode;
            this.loadTrack(firstNewEpisode.audioUrl);
            this.savePlaylist();
            // 显示确认对话框让用户决定是否播放
            this.showAutoplayConfirmDialog();
          }
        }
      } else {
        console.log(__('No new podcasts to add', 'sage'));
      }

      return addedEpisodes;

    } catch (error) {
      console.error(__('Failed to fetch latest podcasts:', 'sage'), error);
      return [];
    }
  },

  getLatestPlaylistSignatureFromServer() {
    const signature = window.aripplesongData?.latestPlaylistSignature;
    return typeof signature === 'string' ? signature : '';
  },

  getLatestPlaylistEpisodesFromServer() {
    const episodes = window.aripplesongData?.latestPlaylistEpisodes;
    return Array.isArray(episodes) ? episodes : [];
  },

  persistLatestPlaylistSignature(signature) {
    const s = typeof signature === 'string' ? signature : '';
    if (!s) return;
    safeLocalStorage.setItem(this.latestSignatureKey, s);
  },

  async maybeRebuildPlaylistOnNewPodcasts() {
    const latestSignature = this.getLatestPlaylistSignatureFromServer();
    const latestEpisodes = this.getLatestPlaylistEpisodesFromServer();

    if (!latestSignature || latestEpisodes.length === 0) {
      return false;
    }

    const storedSignature = safeLocalStorage.getItem(this.latestSignatureKey) || '';

    // If this is the first time we see a signature, only seed it (avoid rebuilding on upgrade).
    if (!storedSignature) {
      if (this.playlist.length === 0) {
        this.rebuildPlaylistFromEpisodes(latestEpisodes, latestSignature);
        return true;
      }

      this.persistLatestPlaylistSignature(latestSignature);
      return false;
    }

    if (storedSignature !== latestSignature) {
      this.rebuildPlaylistFromEpisodes(latestEpisodes, latestSignature);
      return true;
    }

    return false;
  },

  rebuildPlaylistFromEpisodes(episodes, signature) {
    const nextEpisodes = Array.isArray(episodes) ? episodes.slice(0, 10) : [];

    this.stopAndClear();
    this.playlist = nextEpisodes;
    this.currentIndex = 0;
    this.currentEpisode = this.playlist[0] || null;
    this.savePlaylist();
    this.persistLatestPlaylistSignature(signature);

    if (this.currentEpisode?.audioUrl) {
      this.loadTrack(this.currentEpisode.audioUrl);
    }
  },

  // ========== 初始化 ==========
  async init() {
    // 从本地存储加载播放列表
    this.loadPlaylist();

    // Only rebuild the playlist when the "latest podcasts" signature changes (new podcast published).
    await this.maybeRebuildPlaylistOnNewPodcasts();

    // 从本地存储加载音量设置
    this.loadVolume();

    // 从本地存储加载播放速度
    this.loadPlaybackRate();

    // 从本地存储加载播放状态
    const playbackState = this.loadPlaybackState();

    // 如果播放列表为空，则获取最新播客
    if (this.playlist.length == 0) {
      await this.fetchLatestPodcast(true);
      // Baseline the latest signature so we only rebuild on future changes.
      const latestSignature = this.getLatestPlaylistSignatureFromServer();
      if (latestSignature) {
        this.persistLatestPlaylistSignature(latestSignature);
      }
      return; // 如果是新加载的播客，fetchLatestPodcast 会自动播放
    }

    // 加载当前播放的节目
    const episode = this.playlist[this.currentIndex];

    console.log('current_episode', episode);

    // 检查 episode 是否存在
    if (!episode) {
      console.log(__('No episode available to play', 'sage'));
      return;
    }

    if (!this.currentSound || this.currentEpisode?.audioUrl !== episode.audioUrl) {
      this.currentEpisode = episode;
      this.loadTrack(episode.audioUrl);
    }

    // 恢复播放进度
    if (playbackState.currentTime > 0) {
      // ⭐ 立即更新 UI 中的进度显示（即使音频还在加载）
      this.currentTime = playbackState.currentTime;
      
      // 等待音频加载完成后再跳转到保存的位置
      this.currentSound.once('load', () => {
        this.seek(playbackState.currentTime);
        console.log(__('Playback progress restored:', 'sage'), playbackState.currentTime);
        
        // 根据保存的状态决定是否自动播放
        // ⭐ 由于浏览器自动播放策略，需要用户交互后才能播放
        // 显示确认提示栏让用户决定是否继续播放
        if (playbackState.isPlaying) {
          this.showAutoplayConfirmDialog();
        }
      });
    } else if (playbackState.isPlaying) {
      // 如果没有保存的进度但保存了播放状态
      // ⭐ 同样需要用户确认
      this.showAutoplayConfirmDialog();
    }
  },

  /**
   * 显示自动播放确认对话框
   */
  showAutoplayConfirmDialog() {
    this.pendingAutoplay = true;
    this.showAutoplayConfirm = true;
    this.autoplayCountdown = 10; // 重置倒计时
    
    // 重新初始化图标
    setTimeout(() => {
      createIcons({ icons });
    }, 10);
    
    // 每秒更新倒计时
    this.autoplayCountdownTimer = setInterval(() => {
      this.autoplayCountdown--;
      if (this.autoplayCountdown <= 0) {
        this.cancelAutoplay();
        console.log(__('Autoplay confirm dialog auto-closed', 'sage'));
      }
    }, 1000);
    
    console.log(__('Showing autoplay confirm dialog', 'sage'));
  },

  /**
   * 清除所有自动播放相关的定时器
   */
  clearAutoplayTimers() {
    if (this.autoplayConfirmTimer) {
      clearTimeout(this.autoplayConfirmTimer);
      this.autoplayConfirmTimer = null;
    }
    if (this.autoplayCountdownTimer) {
      clearInterval(this.autoplayCountdownTimer);
      this.autoplayCountdownTimer = null;
    }
  },

  /**
   * 用户确认自动播放
   */
  confirmAutoplay() {
    this.clearAutoplayTimers();
    this.showAutoplayConfirm = false;
    this.pendingAutoplay = false;
    this.play();
    console.log(__('User confirmed autoplay', 'sage'));
  },

  /**
   * 用户取消自动播放
   */
  cancelAutoplay() {
    this.clearAutoplayTimers();
    this.showAutoplayConfirm = false;
    this.pendingAutoplay = false;
    // 更新保存的状态为暂停
    this.isPlaying = false;
    this.savePlaybackState();
    console.log(__('User cancelled autoplay', 'sage'));
  },

  // ========== 播放器核心方法 ==========
  loadTrack(audioUrl) {
    // Invalidate any previous analysis and clear the heatmap while loading.
    this._progressHeatmapNonce++;
    const heatmapNonce = this._progressHeatmapNonce;
    this.progressHeatmapGradient = '';
    this.progressHeatmapReady = false;

    // 停止当前播放
    if (this.currentSound) {
      this.currentSound.stop();
      this.currentSound.unload();
    }

    // ⭐ 重置 soundId（新增这一行）
    this.soundId = null;

    // 清理 AudioMotion
    if (this.audioMotion) {
      this.audioMotion.destroy();
      this.audioMotion = null;
    }

    // 清理 volumeGainNode
    if (this.volumeGainNode) {
      this.volumeGainNode.disconnect();
      this.volumeGainNode = null;
    }

    // 清理 Tone pitch shift
    if (this.pitchShiftNode) {
      this.pitchShiftNode.dispose();
      this.pitchShiftNode = null;
    }

    this.audioContext = null;
    this.audioSourceNode = null;
    this.toneContextReady = false;

    // ⭐ 设置加载状态为 true
    this.isLoading = true;

    // 创建新的 Howl 实例，volume 保持为 1，让波形图获取完整信号
    this.currentSound = new Howl({
      src: [audioUrl],
      volume: 1, // 保持最大音量，音量控制将在 GainNode 中进行
      onplay: () => {
        this.isPlaying = true;
        this.initAudioMotion();
      },
      onpause: () => {
        this.isPlaying = false;
      },
      onload: () => {
        this.duration = this.currentSound.duration();
        // ⭐ 音频加载完成，设置加载状态为 false
        this.isLoading = false;
        this.generateProgressHeatmap(audioUrl, heatmapNonce);
        // console.log('duration', this.durationText);
      },
      onloaderror: (id, error) => {
        // ⭐ 加载失败也要重置加载状态
        this.isLoading = false;
        console.error(__('Audio load error:', 'sage'), error);
      },
      onend: () => {
        this.playNext();
      }
    });
  },

  async generateProgressHeatmap(audioUrl, nonceFromLoad) {
    const url = typeof audioUrl === 'string' ? audioUrl : '';
    if (!url) return;

    const expectedNonce = Number.isFinite(nonceFromLoad) ? nonceFromLoad : this._progressHeatmapNonce;
    if (expectedNonce !== this._progressHeatmapNonce) return;

    const cacheKey = `${url}::step=${this.progressHeatmapStepSeconds}::v=rgb-orange-1`;
    const cached = progressHeatmapCache.get(cacheKey);
    if (cached) {
      this.progressHeatmapGradient = cached;
      // Trigger fade-in effect after a brief delay.
      setTimeout(() => {
        if (expectedNonce === this._progressHeatmapNonce) {
          this.progressHeatmapReady = true;
        }
      }, 50);
      return;
    }

    const nonce = expectedNonce;

    if (!document.body) {
      await new Promise(resolve => {
        document.addEventListener('DOMContentLoaded', resolve, { once: true });
      });
    }

    let ws = null;
    let container = null;

    try {
      // Create a hidden, offscreen WaveSurfer instance to decode audio.
      container = document.createElement('div');
      container.style.position = 'absolute';
      container.style.left = '-9999px';
      container.style.top = '-9999px';
      container.style.width = '1000px';
      container.style.height = '1px';
      container.style.overflow = 'hidden';
      document.body.appendChild(container);

      ws = WaveSurfer.create({
        container,
        height: 1,
        interact: false,
        cursorWidth: 0,
        waveColor: 'transparent',
        progressColor: 'transparent',
        crossOrigin: 'anonymous',
      });

      const ready = new Promise((resolve, reject) => {
        ws.once('ready', resolve);
        ws.once('error', reject);
      });

      ws.load(url);
      await ready;

      const decoded = ws.getDecodedData?.();
      ws.destroy();
      ws = null;
      container.remove();
      container = null;

      if (!decoded) return;
      if (nonce !== this._progressHeatmapNonce) return;

      const rms = computeRmsBySecond(decoded, this.progressHeatmapStepSeconds);
      const smoothed = smoothValues(rms, this.progressHeatmapSmoothingRadius);
      const gradient = buildOrangeHeatGradient(smoothed);

      if (!gradient) return;
      if (nonce !== this._progressHeatmapNonce) return;

      progressHeatmapCache.set(cacheKey, gradient);
      this.progressHeatmapGradient = gradient;
      // Trigger fade-in effect after a brief delay.
      setTimeout(() => {
        if (nonce === this._progressHeatmapNonce) {
          this.progressHeatmapReady = true;
        }
      }, 50);
    } catch (error) {
      console.warn('[aripplesong] Failed to generate progress heatmap', error);
    } finally {
      try {
        ws?.destroy?.();
      } catch (_) {
        // ignore
      }
      try {
        container?.remove?.();
      } catch (_) {
        // ignore
      }
    }
  },

  play() {
    if (!this.currentSound) {
      return;
    }

    if (Howler?.ctx) {
      try {
        if (!this.toneContextReady) {
          Tone.setContext(Howler.ctx);
          this.toneContextReady = true;
        }

        Tone.start().catch(() => null);
      } catch (error) {
        console.warn('[aripplesong] Tone.js initialization failed', error);
      }
    }
    
    // 如果弹窗正在显示，直接关闭弹窗（用户通过其他方式触发了播放）
    if (this.showAutoplayConfirm) {
      this.clearAutoplayTimers();
      this.showAutoplayConfirm = false;
      this.pendingAutoplay = false;
    }
    
    if (this.soundId === null) {
      if (this.currentEpisode?.id) {
        bumpPlayCountDom(this.currentEpisode.id);
        sendAjaxMetric(METRIC_ACTIONS.play, this.currentEpisode.id);
      }

      this.soundId = this.currentSound.play();
      // 应用播放速度
      this.currentSound.rate(this.playbackRate, this.soundId);
    } else {
      this.currentSound.play(this.soundId);
    }
    this.isPlaying = true;

    this.startProgressTimer();
    
    // 保存播放状态
    this.savePlaybackState();
  },

  pause() {
    if (!this.currentSound) return;
    this.currentSound.pause(this.soundId);
    this.isPlaying = false;
    this.stopProgressTimer();
    
    // 保存播放状态
    this.savePlaybackState();
  },

  recreateIcons() {
    setTimeout(() => {
      createIcons({ icons });
    }, 10);
  },

  togglePlay() {
    if (this.isPlaying) {
      this.pause();
    } else {
      this.play();
    }
  },

  seek(position) {
    if (!this.currentSound) return;
    this.currentSound.seek(parseFloat(position));
    this.currentTime = parseFloat(position);
    
    // 保存播放进度
    this.savePlaybackState();
  },

  setVolume(volume) {
    this.volume = volume;

    // 使用独立的 GainNode 控制音量，不影响波形图
    if (this.volumeGainNode) {
      this.volumeGainNode.gain.value = volume;
    }

    this.isMuted = volume == 0;
    this.recreateIcons();

    if (volume > 0) {
      this.lastVolume = volume;
    }

    // 保存音量到 localStorage
    this.saveVolume();
  },

  toggleVolumePanel() {
    this.volumePanelOpen = !this.volumePanelOpen;
  },

  toggleMute() {

    if (this.isMuted) {
      this.setVolume(this.lastVolume);
    } else {
      this.lastVolume = this.volume;
      this.setVolume(0);
    }

    console.log('isMuted', this.isMuted);

    this.recreateIcons();

  },

  /**
   * 循环切换播放速度
   */
  cyclePlaybackRate() {
    const currentIndex = this.availableRates.indexOf(this.playbackRate);
    const nextIndex = (currentIndex + 1) % this.availableRates.length;
    this.setPlaybackRate(this.availableRates[nextIndex]);
  },

  /**
   * 切换播放速度面板显示状态
   */
  togglePlaybackRatePanel() {
    this.playbackRatePanelOpen = !this.playbackRatePanelOpen;
  },

  /**
   * 设置播放速度
   */
  setPlaybackRate(rate) {
    this.playbackRate = rate;
    if (this.currentSound && this.soundId !== null) {
      this.currentSound.rate(rate, this.soundId);
    }

    this.applyPitchCompensation();

    // 设置后关闭面板
    this.playbackRatePanelOpen = false;
    
    // 保存播放速度到 localStorage
    this.savePlaybackRate();
  },
  getPitchCompensationSemitones(rate) {
    if (!rate || rate === 1) return 0;
    return -12 * Math.log2(rate);
  },
  applyPitchCompensation() {
    if (!this.pitchShiftNode) {
      return;
    }

    const shouldPitchCompensate = this.playbackRate !== 1;
    this.pitchShiftNode.wet.value = shouldPitchCompensate ? 1 : 0;
    this.pitchShiftNode.pitch = shouldPitchCompensate ? this.getPitchCompensationSemitones(this.playbackRate) : 0;
  },
  setupAudioGraph() {
    if (!this.audioContext || !this.audioSourceNode || !this.volumeGainNode) {
      return;
    }

    const canReconnect = typeof this.audioSourceNode.disconnect === 'function' && typeof this.audioSourceNode.connect === 'function';
    if (!canReconnect) {
      return;
    }

    const audioContext = this.audioContext;
    const sourceNode = this.audioSourceNode;

    try {
      if (!this.toneContextReady) {
        Tone.setContext(audioContext);
        this.toneContextReady = true;
      }

      if (!this.pitchShiftNode) {
        this.pitchShiftNode = new Tone.PitchShift({
          pitch: 0,
          wet: 0,
        });
      }

      try {
        this.pitchShiftNode.disconnect();
      } catch (_) {
        // ignore
      }

      try {
        this.volumeGainNode.disconnect();
      } catch (_) {
        // ignore
      }

      sourceNode.disconnect();
      Tone.connect(sourceNode, this.pitchShiftNode);

      this.pitchShiftNode.connect(this.volumeGainNode);
      this.volumeGainNode.gain.value = this.volume;
      this.volumeGainNode.connect(audioContext.destination);

      this.applyPitchCompensation();
    } catch (error) {
      console.warn('[aripplesong] Failed to setup Tone.js audio graph; falling back to direct connection', error);
      try {
        sourceNode.disconnect();
      } catch (_) {
        // ignore
      }

      try {
        this.volumeGainNode.disconnect();
      } catch (_) {
        // ignore
      }

      sourceNode.connect(this.volumeGainNode);
      this.volumeGainNode.gain.value = this.volume;
      this.volumeGainNode.connect(audioContext.destination);
    }
  },
  initAudioMotion() {
    if (!this.audioMotion && this.currentSound) {
      const container = document.getElementById('wave');
      if (container) {
        const audioContext = Howler.ctx;
        const sourceNode = this.currentSound._sounds[0]._node;

        // 创建独立的 GainNode 用于音量控制
        this.volumeGainNode = audioContext.createGain();
        this.volumeGainNode.gain.value = this.volume;

        this.audioContext = audioContext;
        this.audioSourceNode = sourceNode;

        this.setupAudioGraph();

        // AudioMotion 分析原始的 sourceNode（音量控制之前）
        this.audioMotion = new AudioMotionAnalyzer(container, {
          source: sourceNode,
          connectSpeakers: false, // 改为 false，因为我们手动管理连接
          mode: 4,
          alphaBars: false,
          ansiBands: false,
          barSpace: .25,
          channelLayout: 'single',
          colorMode: 'bar-level',
          frequencyScale: 'log',
          gradient: 'prism',
          ledBars: false,
          linearAmplitude: true,
          linearBoost: 1.6,
          lumiBars: false,
          maxFreq: 16000,
          minFreq: 30,
          mirror: 0,
          radial: false,
          reflexRatio: .5,
          reflexAlpha: 1,
          roundBars: true,
          showPeaks: false,
          showScaleX: false,
          smoothing: .7,
          weightingFilter: 'D',
          overlay: true,
          showBgColor: false,
          maxDecibels: -30
        });
      }
    }
  },
  startProgressTimer() {
    let saveCounter = 0;
    this.timer = setInterval(() => {
      if (this.currentSound && this.isPlaying) {
        this.currentTime = this.currentSound.seek(this.soundId) || 0;
        
        // 每10次（约1秒）保存一次播放状态，避免频繁写入
        saveCounter++;
        if (saveCounter >= 10) {
          this.savePlaybackState();
          saveCounter = 0;
        }
      }
    }, 100);
  },

  stopProgressTimer() {
    clearInterval(this.timer);
    this.timer = null;
  },

  // ========== 播放列表管理 ==========
  loadPlaylist() {
    const data = safeLocalStorage.getItem(this.storageKey);
    this.playlist = data ? JSON.parse(data) : [];
    const index = safeLocalStorage.getItem(this.currentIndexKey);
    this.currentIndex = index ? parseInt(index) : 0;
  },

  savePlaylist() {
    safeLocalStorage.setItem(this.storageKey, JSON.stringify(this.playlist));
    safeLocalStorage.setItem(this.currentIndexKey, this.currentIndex.toString());
    // 触发播放列表更新事件，通知播放列表抽屉更新
    window.dispatchEvent(new CustomEvent('playlist-updated'));

    // 重新初始化 Lucide 图标（延迟执行以确保 DOM 更新完成）
    setTimeout(() => {
      createIcons({ icons });
    }, 10);
  },

  // ========== 音量管理 ==========
  loadVolume() {
    const savedVolume = safeLocalStorage.getItem(this.volumeKey);
    if (savedVolume !== null) {
      const volume = parseFloat(savedVolume);
      this.volume = volume;
      this.lastVolume = volume > 0 ? volume : this.lastVolume;
      this.isMuted = volume === 0;
      console.log(__('Volume settings loaded:', 'sage'), volume);
    }
  },

  saveVolume() {
    safeLocalStorage.setItem(this.volumeKey, this.volume.toString());
  },

  // ========== 播放速度管理 ==========
  /**
   * 保存播放速度到 localStorage
   */
  savePlaybackRate() {
    safeLocalStorage.setItem(this.playbackRateKey, this.playbackRate.toString());
  },

  /**
   * 从 localStorage 加载播放速度
   */
  loadPlaybackRate() {
    const savedRate = safeLocalStorage.getItem(this.playbackRateKey);
    if (savedRate !== null) {
      const rate = parseFloat(savedRate);
      // 确保速率在可用范围内
      if (this.availableRates.includes(rate)) {
        this.playbackRate = rate;
        console.log(__('Playback rate loaded:', 'sage'), rate);
      }
    }
  },

  // ========== 播放状态管理 ==========
  /**
   * 保存播放状态到 localStorage
   */
  savePlaybackState() {
    safeLocalStorage.setItem(this.currentTimeKey, this.currentTime.toString());
    safeLocalStorage.setItem(this.isPlayingKey, this.isPlaying.toString());
  },

  /**
   * 从 localStorage 加载播放状态
   */
  loadPlaybackState() {
    const savedTime = safeLocalStorage.getItem(this.currentTimeKey);
    const savedIsPlaying = safeLocalStorage.getItem(this.isPlayingKey);
    
    return {
      currentTime: savedTime ? parseFloat(savedTime) : 0,
      isPlaying: savedIsPlaying === 'true'
    };
  },

  /**
   * 清除播放状态
   */
  clearPlaybackState() {
    safeLocalStorage.removeItem(this.currentTimeKey);
    safeLocalStorage.removeItem(this.isPlayingKey);
  },

  /**
   * 添加节目到播放列表并立即播放
   * @param {Object} episode - 节目对象
   */
  addEpisode(episode) {
    const existingIndex = this.playlist.findIndex(item => item.id === episode.id);

    if (existingIndex !== -1) {
      // 已存在，切换到该节目并播放
      this.currentIndex = existingIndex;
      this.currentEpisode = episode;
      this.loadTrack(episode.audioUrl);
      this.play();
      this.savePlaylist();
      // 切换曲目后重置进度为0
      this.currentTime = 0;
      this.savePlaybackState();
      console.log(__('Switched to existing episode:', 'sage'), episode.title);
      return;
    }

    // 添加到播放列表
    this.playlist.push(episode);
    this.currentIndex = this.playlist.length - 1;
    this.currentEpisode = episode;
    this.savePlaylist();

    // 加载并播放
    this.loadTrack(episode.audioUrl);
    this.play();
    
    // 新节目从头播放
    this.currentTime = 0;
    this.savePlaybackState();

    console.log(__('Added to playlist:', 'sage'), episode.title);
  },

  /**
   * 添加节目到播放列表但不播放（用于批量添加）
   * @param {Object} episode - 节目对象
   */
  addEpisodeToPlaylist(episode) {
    const existingIndex = this.playlist.findIndex(item => item.id === episode.id);

    if (existingIndex !== -1) {
      console.log(__('Episode already exists:', 'sage'), episode.title);
      return false;
    }

    // 添加到播放列表
    this.playlist.push(episode);
    this.savePlaylist();

    return true;
  },
  removeEpisode(episodeId) {
    this.playlist = this.playlist.filter(item => item.id !== episodeId);
    if (this.currentIndex >= this.playlist.length) {
      this.currentIndex = Math.max(0, this.playlist.length - 1);
    }
    
    // 如果播放列表为空，停止播放并清空状态
    if (this.playlist.length === 0) {
      this.stopAndClear();
    }
    
    this.savePlaylist();
  },

  clearPlaylist() {
    this.stopAndClear();
    this.playlist = [];
    this.currentIndex = 0;
    this.currentEpisode = null;
    this.savePlaylist();
  },

  /**
   * 停止播放并清空所有状态
   */
  stopAndClear() {
    // 停止当前播放
    if (this.currentSound) {
      this.currentSound.stop();
      this.currentSound.unload();
      this.currentSound = null;
    }

    // 清理定时器
    this.stopProgressTimer();

    // 清理 AudioMotion
    if (this.audioMotion) {
      this.audioMotion.destroy();
      this.audioMotion = null;
    }

    // 清理 volumeGainNode
    if (this.volumeGainNode) {
      this.volumeGainNode.disconnect();
      this.volumeGainNode = null;
    }

    // 清理 Tone pitch shift
    if (this.pitchShiftNode) {
      this.pitchShiftNode.dispose();
      this.pitchShiftNode = null;
    }

    // 重置所有播放状态
    this.soundId = null;
    this.isPlaying = false;
    this.currentTime = 0;
    this.duration = 0;
    this.currentEpisode = null;
    this.audioContext = null;
    this.audioSourceNode = null;
    this.toneContextReady = false;
    
    // 清除保存的播放状态
    this.clearPlaybackState();
  },

  playNext() {
    if (this.playlist.length === 0) return;
    this.currentIndex = (this.currentIndex + 1) % this.playlist.length;
    const episode = this.playlist[this.currentIndex];
    this.currentEpisode = episode;
    this.loadTrack(episode.audioUrl);
    this.play();
    this.savePlaylist();
    // 切换曲目后重置进度为0
    this.currentTime = 0;
    this.savePlaybackState();
  },
  playPrevious() {
    if (this.playlist.length === 0) return;
    this.currentIndex = (this.currentIndex - 1 + this.playlist.length) % this.playlist.length;
    const episode = this.playlist[this.currentIndex];
    this.currentEpisode = episode;
    this.loadTrack(episode.audioUrl);
    this.play();
    this.savePlaylist();
    // 切换曲目后重置进度为0
    this.currentTime = 0;
    this.savePlaybackState();
  },

  playByIndex(index) {
    if (index >= 0 && index < this.playlist.length) {
      this.currentIndex = index;
      const episode = this.playlist[index];
      this.currentEpisode = episode;
      console.log('audio_url', episode.audioUrl);
      this.loadTrack(episode.audioUrl);
      this.play();
      this.savePlaylist();
      // 切换曲目后重置进度为0
      this.currentTime = 0;
      this.savePlaybackState();
    }
  },

  // ========== 工具方法 ==========
  formatTime(seconds) {
    // 处理 undefined, null, NaN 等情况
    if (!seconds || isNaN(seconds)) return '00:00';
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
  }
});


Alpine.start();




// ========== Image Lightbox ==========
/**
 * Initialize image lightbox for content images
 */
function initImageLightbox() {
  const contentImages = document.querySelectorAll('#content img');
  const modal = document.getElementById('image-lightbox-modal');
  const lightboxImage = document.getElementById('lightbox-image');
  
  if (!modal || !lightboxImage) return;
  
  contentImages.forEach(img => {
    // Skip if image is already inside a link
    if (img.closest('a')) return;
    
    // Remove any existing click listeners
    img.replaceWith(img.cloneNode(true));
    const newImg = img.parentNode ? img : document.querySelector(`#content img[src="${img.src}"]`);
    
    if (!newImg) return;
    
    newImg.addEventListener('click', function(e) {
      e.preventDefault();
      
      // Get the full-size image URL (WordPress responsive images)
      const fullSizeUrl = this.dataset.fullUrl || this.src;
      const imgAlt = this.alt || '';
      
      // Set lightbox image
      lightboxImage.src = fullSizeUrl;
      lightboxImage.alt = imgAlt;
      
      // Show modal
      modal.showModal();
    });
  });
  
  // Close modal on ESC key
  modal.addEventListener('close', () => {
    lightboxImage.src = '';
  });
}

// 初始化 Swup (v4.x 版本)
const swup = new Swup({
  containers: ['#swup-main', '#swup-header', '#swup-mobile-menu'], // 指定要替换的容器
  animateHistoryBrowsing: true,
  // 让 Swup 在无刷新切换后重新执行页面内联脚本
  plugins: [new SwupFormsPlugin(), new SwupScriptsPlugin()]
});

function init() {
  // 重新初始化 Lucide 图标
  createIcons({ icons });
  
  // 初始化图片灯箱
  initImageLightbox();

  // Send view metric on each page render
  maybeSendViewMetric();

  // Hydrate metrics for all posts on page
  hydrateMetricsFromDom();
}

// 页面首次加载
document.addEventListener('DOMContentLoaded', init);

// Swup v4.x 使用 hooks API
swup.hooks.on('content:replace', init);
