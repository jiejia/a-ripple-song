import { safeLocalStorage } from '@scripts/lib/storage.js';

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
  'matcha-cream',
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
  'carbon-ember',
];
const THEME_PALETTE = window.aripplesongData?.theme?.palette || {};
const LIGHT_THEME_SET = new Set(LIGHT_THEMES);
const DARK_THEME_SET = new Set(DARK_THEMES);
const THEME_MODES = ['light', 'dark', 'auto'];

/**
 * Resolve a theme slug against an allowed list.
 *
 * @param {string|undefined} theme Requested theme slug.
 * @param {string[]} available Allowed theme slugs.
 * @param {string} fallback Fallback theme slug.
 * @return {string}
 */
function resolveTheme(theme, available, fallback) {
  return theme && available.includes(theme) ? theme : fallback;
}

/**
 * Inject palette CSS variables for custom DaisyUI themes once.
 *
 * @return {void}
 */
const ensureThemeStylesInjected = (() => {
  let injected = false;

  return () => {
    if (injected) {
      return;
    }

    const entries = Object.entries(THEME_PALETTE || {});
    if (!entries.length) {
      return;
    }

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

    const style = document.createElement('style');
    style.id = 'aripplesong-theme-styles';
    style.textContent = rules.join('');
    document.head.appendChild(style);
    injected = true;
  };
})();

/**
 * Register the Alpine theme store.
 *
 * @param {import('alpinejs').Alpine} Alpine Alpine instance.
 * @return {void}
 */
export function registerThemeStore(Alpine) {
  Alpine.store('theme', {
    mode: 'auto',
    storageKey: 'aripplesong-theme-mode',
    lightTheme: resolveTheme(window.aripplesongData?.theme?.lightTheme, LIGHT_THEMES, FALLBACK_LIGHT_THEME),
    darkTheme: resolveTheme(window.aripplesongData?.theme?.darkTheme, DARK_THEMES, FALLBACK_DARK_THEME),
    mediaQuery: null,

    init() {
      ensureThemeStylesInjected();

      const savedMode = safeLocalStorage.getItem(this.storageKey);
      if (savedMode && THEME_MODES.includes(savedMode)) {
        this.mode = savedMode;
      }

      this.mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
      this.mediaQuery.addEventListener('change', () => {
        if (this.mode === 'auto') {
          this.applyTheme();
        }
      });

      this.applyTheme();
    },

    toggle() {
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
      if (!THEME_MODES.includes(mode)) {
        return;
      }

      this.mode = mode;
      safeLocalStorage.setItem(this.storageKey, this.mode);
      this.applyTheme();
    },

    applyTheme() {
      ensureThemeStylesInjected();

      let theme = this.lightTheme;
      if (this.mode === 'auto') {
        theme = this.mediaQuery?.matches ? this.darkTheme : this.lightTheme;
      } else if (this.mode === 'dark') {
        theme = this.darkTheme;
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
    },
  });
}
