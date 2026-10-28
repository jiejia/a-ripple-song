/**
 * Global audio player; bundled into public/dist/player.js by `npm run build:js`.
 *
 * - One PlayerStore owns the Howler sound (html5 streaming, so long episodes start without a full download).
 * - The bar (resources/templates/player/bar.php) and any [data-ars-play] button are views bound
 *   through data-ars-* attributes, so themes can restyle or replace the markup freely.
 * - The bar lives in wp_footer, outside a theme's swup container: with swup, playback continues
 *   across pages. Buttons in swapped-in content are picked up through event delegation and a
 *   MutationObserver, so the player needs no swup hooks. Without swup, the episode and position
 *   are restored from localStorage on the next page.
 *
 * Themes can drive it through window.ARippleSongPlayer (see the API at the bottom) and follow it
 * through the "ars-player:change" event on document.
 */
import { Howl, Howler } from 'howler';
import { Binder } from './binder.js';
import { startSpectrum, refreshSpectrumColors } from './spectrum.js';

const RATES = [1, 1.25, 1.5, 2, 0.8];
const config = window.ARS_PLAYER || { episodes: [], show: {}, i18n: {}, storageKey: 'ars_player', playsUrl: '' };
const t = (key) => config.i18n[key] || key;

function formatTime(seconds) {
  const total = Math.max(0, Math.floor(seconds || 0));
  const h = Math.floor(total / 3600);
  const m = String(Math.floor((total % 3600) / 60)).padStart(2, '0');
  const s = String(total % 60).padStart(2, '0');

  return h ? `${h}:${m}:${s}` : `${m}:${s}`;
}

const storage = {
  read() {
    try {
      return JSON.parse(localStorage.getItem(config.storageKey)) || {};
    } catch {
      return {};
    }
  },
  write(value) {
    try {
      localStorage.setItem(config.storageKey, JSON.stringify(value));
    } catch {
      // Private mode or full storage: the player still works, it just forgets.
    }
  },
};

class PlayerStore {
  constructor(episodes) {
    this.episodes = new Map();
    this.queue = [];
    this.current = null;
    this.playing = false;
    this.loading = false;
    this.error = '';
    this.position = 0;
    this.duration = 0;
    this.rate = 1;
    this.volume = 0.8;
    this.muted = false;
    this.scrubbing = false;
    this.howl = null;
    this.soundId = undefined;
    this.counted = new Set();
    this.expanded = false;
    this.favs = [];
    this.listeners = new Set();

    this.add(episodes);
    this.restore();
    this.tick();
    window.addEventListener('pagehide', () => this.save());
  }

  /** Add episodes ({id, title, url, audio, cover, duration, number}) to the library and the queue. */
  add(episodes) {
    for (const episode of [].concat(episodes || [])) {
      const id = Number(episode.id);

      if (!id || !episode.audio) continue;
      this.episodes.set(id, { ...episode, id });
      if (!this.queue.includes(id)) this.queue.push(id);
    }

    this.emit();
  }

  /** Progress of the current episode in percent. */
  get progress() {
    return this.duration ? (this.position / this.duration) * 100 : 0;
  }

  get queueItems() {
    return this.queue.map((id) => this.episode(id)).filter(Boolean);
  }

  isFav(id) {
    return this.favs.includes(Number(id));
  }

  /** Favorites are kept in the visitor's browser. */
  toggleFav(id) {
    id = Number(id);
    this.favs = this.isFav(id) ? this.favs.filter((fav) => fav !== id) : [...this.favs, id];
    this.save();
    this.emit();
  }

  /** Queue an episode right after the current one. */
  playNext(id) {
    id = Number(id);
    if (!this.current || this.isCurrent(id) || !this.episode(id)) return;
    const queue = this.queue.filter((queued) => queued !== id);
    queue.splice(queue.indexOf(this.current.id) + 1, 0, id);
    this.queue = queue;
    this.emit();
  }

  episode(id) {
    return this.episodes.get(Number(id)) || null;
  }

  isCurrent(id) {
    return !!this.current && this.current.id === Number(id);
  }

  isPlaying(id) {
    return this.isCurrent(id) && this.playing;
  }

  /** Play or pause an episode; without an id, the current one. */
  toggle(id = this.current && this.current.id) {
    if (!id) return;
    if (this.isCurrent(id) && this.howl) {
      this.playing ? this.pause() : this.play();
      return;
    }
    this.load(id, { at: this.isCurrent(id) ? this.position : 0 });
  }

  /** Play an episode from a given second, e.g. a chapter link. */
  playAt(id, seconds) {
    if (this.isCurrent(id) && this.howl) {
      this.seek(seconds);
      if (!this.playing) this.play();
      return;
    }
    this.load(id, { at: seconds });
  }

  load(id, { at = 0, autoplay = true } = {}) {
    const episode = this.episode(id);

    if (!episode) return;
    if (this.howl) this.howl.unload();
    this.resumeAudio();

    Object.assign(this, {
      current: episode,
      position: at,
      duration: episode.duration || 0,
      playing: false,
      loading: autoplay,
      error: '',
      soundId: undefined,
    });

    const store = this;
    this.howl = new Howl({
      src: [episode.audio],
      html5: true,
      preload: 'metadata',
      rate: this.rate,
      volume: this.volume,
      mute: this.muted,
      onload() {
        store.duration = this.duration() || episode.duration;
        store.emit();
      },
      onplay() {
        Object.assign(store, { playing: true, loading: false });
        store.countPlay(episode.id);
        store.emit();
      },
      onpause() {
        store.playing = false;
        store.save();
        store.emit();
      },
      onstop() {
        store.playing = false;
        store.emit();
      },
      onend() {
        Object.assign(store, { playing: false, position: 0 });
        store.save();
        store.next() || store.emit();
      },
      onloaderror() {
        Object.assign(store, { loading: false, error: t('error') });
        store.emit();
      },
      onplayerror() {
        // Autoplay was blocked: play as soon as the browser allows audio.
        this.once('unlock', () => this.play(store.soundId));
      },
    });

    if (at) this.howl.seek(at);
    this.updateMediaSession();
    this.save();
    autoplay ? this.play() : this.emit();
  }

  /** Resume a suspended AudioContext (spectrum analysis routes audio through it). */
  resumeAudio() {
    if (Howler.ctx && Howler.ctx.state === 'suspended') Howler.ctx.resume();
  }

  play() {
    this.resumeAudio();
    if (!this.current) return;
    if (!this.howl) {
      this.load(this.current.id, { at: this.position });
      return;
    }
    if (!this.howl.playing(this.soundId)) this.soundId = this.howl.play(this.soundId);
  }

  pause() {
    if (this.howl) this.howl.pause(this.soundId);
  }

  seek(seconds) {
    this.position = Math.min(Math.max(0, Number(seconds) || 0), this.duration || Infinity);
    if (this.howl) this.howl.seek(this.position, this.soundId);
    this.emit();
  }

  skip(delta) {
    this.seek(this.position + delta);
  }

  /** While dragging the progress only the display moves; the audio seeks on release. */
  scrub(seconds) {
    this.scrubbing = true;
    this.position = Number(seconds);
    this.emit();
  }

  commit(seconds) {
    this.scrubbing = false;
    this.seek(seconds);
  }

  cycleRate() {
    this.rate = RATES[(RATES.indexOf(this.rate) + 1) % RATES.length];
    if (this.howl) this.howl.rate(this.rate);
    this.save();
    this.emit();
  }

  setVolume(volume) {
    this.volume = Math.min(1, Math.max(0, Number(volume)));
    this.muted = this.volume === 0;
    if (this.howl) {
      this.howl.volume(this.volume);
      this.howl.mute(this.muted);
    }
    this.save();
    this.emit();
  }

  toggleMute() {
    this.muted = !this.muted;
    if (this.howl) this.howl.mute(this.muted);
    this.emit();
  }

  /** Play the next queued episode; returns false at the end of the queue. */
  next() {
    const index = this.current ? this.queue.indexOf(this.current.id) : -1;

    if (index < 0 || index >= this.queue.length - 1) return false;
    this.load(this.queue[index + 1]);

    return true;
  }

  /** Restart the episode, or go to the previous one when near its start. */
  prev() {
    if (this.position > 5) {
      this.seek(0);
      return;
    }

    const index = this.current ? this.queue.indexOf(this.current.id) : -1;

    if (index > 0) this.load(this.queue[index - 1]);
  }

  restore() {
    const saved = storage.read();

    this.rate = RATES.includes(saved.rate) ? saved.rate : 1;
    this.volume = typeof saved.volume === 'number' ? saved.volume : 0.8;
    this.favs = Array.isArray(saved.favs) ? saved.favs.map(Number) : [];

    const resumed = this.episode(saved.id);
    // Without a saved episode, load the one the page suggests (setting "Before Anything Is Played"),
    // paused, so the player shows and can be started from the bar.
    const episode = resumed || (config.initial ? this.episode(config.initial) || this.queueItems[0] : null);

    if (episode) {
      Object.assign(this, { current: episode, duration: episode.duration || 0, position: resumed ? saved.position || 0 : 0 });
      this.updateMediaSession();
    }
  }

  save() {
    storage.write({
      id: this.current && this.current.id,
      position: this.position,
      rate: this.rate,
      volume: this.volume,
      favs: this.favs,
    });
  }

  /** Count one play per episode and page load; the server also deduplicates per visitor. */
  countPlay(id) {
    if (!config.playsUrl || this.counted.has(id)) return;
    this.counted.add(id);
    fetch(config.playsUrl.replace('%d', id), { method: 'POST', keepalive: true })
      .then((response) => (response.ok ? response.json() : null))
      .then((result) => {
        if (!result || typeof result.play_count !== 'number') return;
        const episode = this.episode(id);
        if (episode) episode.plays = result.play_count;
        showPlays(id, result.play_count);
        this.emit();
      })
      .catch(() => {});
  }

  /**
   * Sync the store with the audio element four times a second.
   *
   * The timer runs for the page's lifetime instead of being started and stopped by play / pause
   * events: Howler can emit those out of order (e.g. when it unlocks audio on the first user
   * gesture), which used to leave the display stuck while audio kept playing.
   */
  tick() {
    let lastSave = Date.now();

    setInterval(() => {
      if (!this.howl || this.loading) return;

      const node = this.howl._sounds[0] && this.howl._sounds[0]._node;
      const playing = node ? !node.paused && !node.ended : this.howl.playing(this.soundId);

      if (playing === this.playing && !playing) return;
      this.playing = playing;

      if (playing && !this.scrubbing) {
        const position = this.howl.seek(this.soundId);
        if (typeof position === 'number') this.position = position;
      }
      if (Date.now() - lastSave > 5000) {
        lastSave = Date.now();
        this.save();
      }
      this.emit();
    }, 250);
  }

  updateMediaSession() {
    if (!('mediaSession' in navigator) || !this.current) return;

    const episode = this.current;
    navigator.mediaSession.metadata = new MediaMetadata({
      title: episode.title,
      artist: episode.number ? t('episode').replace('%s', episode.number) : '',
      artwork: episode.cover ? [{ src: episode.cover }] : [],
    });

    const handlers = {
      play: () => this.play(),
      pause: () => this.pause(),
      seekbackward: () => this.skip(-15),
      seekforward: () => this.skip(30),
      previoustrack: () => this.prev(),
      nexttrack: () => this.next(),
    };
    for (const [action, handler] of Object.entries(handlers)) {
      try {
        navigator.mediaSession.setActionHandler(action, handler);
      } catch {
        // Action not supported by this browser.
      }
    }
  }

  subscribe(listener) {
    this.listeners.add(listener);
    listener(this);

    return () => this.listeners.delete(listener);
  }

  emit() {
    this.listeners.forEach((listener) => listener(this));
    document.dispatchEvent(new CustomEvent('ars-player:change', { detail: this }));
  }
}

/* ---------------- Views ---------------- */

/** Bind the player bar markup to the store. */
function bindBar(root, store) {
  const all = (selector) => root.querySelectorAll(selector);
  const bar = root.querySelector('[data-ars-bar]') || root;
  const panel = root.querySelector('[data-ars-panel]');
  const list = root.querySelector('[data-ars-queue]');
  const itemTemplate = list && list.querySelector('template');
  let queueKey = '';

  const actions = {
    toggle: () => store.toggle(),
    prev: () => store.prev(),
    next: () => store.next(),
    back: (button) => store.skip(-Number(button.dataset.arsSeconds || 15)),
    forward: (button) => store.skip(Number(button.dataset.arsSeconds || 30)),
    rate: () => store.cycleRate(),
    mute: () => store.toggleMute(),
    panel: () => setPanel(!isPanelOpen()),
  };

  // The panel is a <dialog> in the default template (modal: Esc, focus trap, top layer),
  // but a theme may use any element, which is then toggled with `hidden`.
  const isDialog = panel instanceof HTMLDialogElement;
  const isPanelOpen = () => !!panel && (isDialog ? panel.open : !panel.hidden);

  function setPanel(open) {
    if (!panel || open === isPanelOpen()) return;

    if (isDialog) {
      open ? panel.showModal() : panel.close();
    } else {
      panel.hidden = !open;
    }

    if (open) {
      const current = panel.querySelector('[data-ars-state="playing"], [data-ars-state="paused"]');
      if (current) current.scrollIntoView({ block: 'nearest' });
    }
    syncPanelState();
  }

  function syncPanelState() {
    const open = isPanelOpen();
    all('[data-ars-bar] [data-ars-action="panel"]').forEach((button) => button.setAttribute('aria-expanded', String(open)));
    // The store's expanded flag drives the page scroll lock, as it does for skins.
    if (store.expanded !== open) {
      store.expanded = open;
      store.emit();
    }
  }

  root.addEventListener('click', (event) => {
    const button = event.target.closest('[data-ars-action]');
    if (button && actions[button.dataset.arsAction]) actions[button.dataset.arsAction](button);
    // Following a link (e.g. the episode title, swup navigation) or clicking beside the panel's box leaves the panel.
    if (panel && panel.contains(event.target) && (event.target.closest('a[href]') || isOutsidePanelBox(panel, event.target))) setPanel(false);
  });
  // A <dialog> also closes itself on Esc.
  if (panel) panel.addEventListener('close', syncPanelState);
  root.addEventListener('input', (event) => {
    if (event.target.matches('[data-ars-seek]')) store.scrub(event.target.value);
    if (event.target.matches('[data-ars-volume]')) store.setVolume(event.target.value);
  });
  root.addEventListener('change', (event) => {
    if (event.target.matches('[data-ars-seek]')) store.commit(event.target.value);
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !isDialog) setPanel(false);
  });

  // Reserve room at the page bottom for the fixed bar.
  new ResizeObserver(() => {
    document.documentElement.style.setProperty('--ars-player-height', `${bar.offsetHeight}px`);
  }).observe(bar);

  function renderQueue() {
    const key = store.queue.join(',');

    if (!list || !itemTemplate || key === queueKey) return;
    queueKey = key;
    list.querySelectorAll(':scope > :not(template)').forEach((item) => item.remove());

    for (const id of store.queue) {
      const episode = store.episode(id);
      const item = itemTemplate.content.cloneNode(true);
      const fill = (name, apply) => item.querySelectorAll(`[data-ars-item="${name}"]`).forEach(apply);

      item.querySelectorAll('[data-ars-play]').forEach((button) => (button.dataset.arsPlay = id));
      fill('title', (node) => (node.textContent = episode.title));
      fill('duration', (node) => (node.textContent = episode.duration ? formatTime(episode.duration) : ''));
      fill('cover', (node) => (episode.cover ? (node.src = episode.cover) : (node.hidden = true)));
      list.appendChild(item);
    }
  }

  store.subscribe(() => {
    const episode = store.current;
    const state = store.error ? 'error' : store.loading ? 'loading' : store.playing ? 'playing' : 'paused';
    const flags = {
      playing: state === 'playing',
      paused: state === 'paused' || state === 'error',
      loading: state === 'loading',
      muted: store.muted,
      unmuted: !store.muted,
      cover: !!(episode && episode.cover),
      nocover: !(episode && episode.cover),
    };
    const texts = {
      title: episode ? episode.title : '',
      number: episode && episode.number ? t('episode').replace('%s', episode.number) : '',
      status: store.error || t(state),
      position: formatTime(store.position),
      duration: formatTime(store.duration),
      rate: `${store.rate}×`,
    };

    root.hidden = !episode;
    root.dataset.state = state;
    document.body.classList.toggle('has-ars-player', !!episode);

    all('[data-ars-text]').forEach((node) => {
      const text = texts[node.dataset.arsText];
      if (text !== undefined && node.textContent !== text) node.textContent = text;
    });
    all('[data-ars-show]').forEach((node) => (node.hidden = !flags[node.dataset.arsShow]));
    all('[data-ars-src="cover"]').forEach((node) => {
      if (episode && episode.cover && node.getAttribute('src') !== episode.cover) node.src = episode.cover;
    });
    all('[data-ars-href="url"]').forEach((node) => {
      if (episode) node.href = episode.url;
    });
    all('[data-ars-action="toggle"]').forEach((node) => node.setAttribute('aria-label', t(store.playing ? 'pause' : 'play')));
    all('[data-ars-action="mute"]').forEach((node) => node.setAttribute('aria-label', t(store.muted ? 'unmute' : 'mute')));
    all('[data-ars-seek]').forEach((node) => {
      node.max = String(Math.round(store.duration));
      if (!store.scrubbing) node.value = String(Math.round(store.position));
    });
    all('[data-ars-volume]').forEach((node) => {
      if (document.activeElement !== node) node.value = String(store.muted ? 0 : store.volume);
    });

    renderQueue();
  });
}

/** Show a play count in every [data-ars-plays="id"], using its data-ars-plays-one / -other formats. */
function showPlays(id, count) {
  document.querySelectorAll(`[data-ars-plays="${Number(id)}"]`).forEach((node) => {
    const format = (count === 1 ? node.dataset.arsPlaysOne : node.dataset.arsPlaysOther) || '%s';
    node.textContent = format.replace('%s', count.toLocaleString(document.documentElement.lang || undefined));
  });
}

/**
 * Make every [data-ars-play] button on the page control the store and every [data-ars-fav] button
 * toggle a favorite, including ones swapped in later.
 */
function bindPlayButtons(store) {
  document.addEventListener('click', (event) => {
    const fav = event.target.closest('[data-ars-fav]');

    if (fav && fav.dataset.arsFav) {
      event.preventDefault();
      store.toggleFav(fav.dataset.arsFav);
      return;
    }

    const button = event.target.closest('[data-ars-play]');

    if (!button || !button.dataset.arsPlay) return;
    event.preventDefault();
    button.dataset.arsAt ? store.playAt(button.dataset.arsPlay, Number(button.dataset.arsAt)) : store.toggle(button.dataset.arsPlay);
  });

  let lastKey = '';
  const sync = (force = false) => {
    const key = `${store.current && store.current.id}:${store.playing}:${store.favs.join(',')}`;

    if (!force && key === lastKey) return;
    lastKey = key;
    document.querySelectorAll('[data-ars-play]').forEach((button) => {
      const state = store.isPlaying(button.dataset.arsPlay) ? 'playing' : store.isCurrent(button.dataset.arsPlay) ? 'paused' : 'idle';
      button.dataset.arsState = state;
      button.setAttribute('aria-pressed', String(state === 'playing'));
    });
    document.querySelectorAll('[data-ars-fav]').forEach((button) => {
      button.setAttribute('aria-pressed', String(store.isFav(button.dataset.arsFav)));
    });
  };

  store.subscribe(() => sync());

  // New content (swup page swaps, queue renders, lazy sections) gets the current state too.
  // Text-only changes (e.g. the ticking position) are skipped.
  let scheduled = false;
  new MutationObserver((records) => {
    const addsElements = records.some((record) => [...record.addedNodes].some((node) => node.nodeType === Node.ELEMENT_NODE));

    if (scheduled || !addsElements) return;
    scheduled = true;
    requestAnimationFrame(() => {
      scheduled = false;
      sync(true);
    });
  }).observe(document.body, { childList: true, subtree: true });
}

/* ---------------- Color scheme ---------------- */

const DARK_CLASSES = ['dark', 'dark-mode', 'theme-dark', 'is-dark', 'is-dark-theme'];
const LIGHT_CLASSES = ['light', 'light-mode', 'theme-light', 'is-light'];
const SCHEME_ATTRIBUTES = ['data-theme', 'data-bs-theme', 'data-color-scheme', 'data-color-mode', 'data-mode'];

/** Read an explicit light / dark marker a theme put on an element, e.g. data-theme="dark" or class="dark". */
function markedScheme(element) {
  for (const name of SCHEME_ATTRIBUTES) {
    const value = (element.getAttribute(name) || '').toLowerCase();
    if (/dark|night/.test(value)) return 'dark';
    if (/light|day/.test(value)) return 'light';
  }
  if (DARK_CLASSES.some((name) => element.classList.contains(name))) return 'dark';
  if (LIGHT_CLASSES.some((name) => element.classList.contains(name))) return 'light';

  return '';
}

/** Resolve any CSS color (rgb, oklch, …) to RGBA through a 1×1 canvas. */
let probe;
function rgba(color) {
  probe = probe || Object.assign(document.createElement('canvas'), { width: 1, height: 1 }).getContext('2d', { willReadFrequently: true });
  probe.clearRect(0, 0, 1, 1);
  probe.fillStyle = 'rgba(0, 0, 0, 0)';
  probe.fillStyle = color;
  probe.fillRect(0, 0, 1, 1);

  return probe.getImageData(0, 0, 1, 1).data;
}

/** Guess whether the site currently looks light or dark. */
function detectScheme() {
  const html = document.documentElement;
  const marked = markedScheme(html) || markedScheme(document.body);

  if (marked) return marked;

  const declared = getComputedStyle(html).colorScheme.split(' ');
  if (declared.length === 1 && (declared[0] === 'dark' || declared[0] === 'light')) return declared[0];

  // Fall back to the brightness of the first opaque page background.
  for (const element of [document.body, html, document.querySelector('.wp-site-blocks, #page, .site, main')]) {
    if (!element) continue;
    const [r, g, b, a] = rgba(getComputedStyle(element).backgroundColor);
    if (a > 128) return (0.2126 * r + 0.7152 * g + 0.0722 * b) / 255 < 0.5 ? 'dark' : 'light';
  }

  return declared.includes('dark') && matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

/**
 * Set data-ars-scheme="light|dark" on the player.
 *
 * Modes: auto (follow the site theme), system (follow the OS), light, dark,
 * inherit (the theme styles the player itself: nothing is set).
 */
function bindScheme(root, mode) {
  if (mode === 'inherit') return;

  const media = matchMedia('(prefers-color-scheme: dark)');
  const apply = () => {
    const scheme = mode === 'light' || mode === 'dark' ? mode : mode === 'system' ? (media.matches ? 'dark' : 'light') : detectScheme();
    if (root.dataset.arsScheme === scheme) return;
    root.dataset.arsScheme = scheme;
    refreshSpectrumColors();
  };

  apply();
  if (mode === 'light' || mode === 'dark') return;
  media.addEventListener('change', apply);
  if (mode !== 'auto') return;

  // Theme toggles flip a class or attribute on <html> / <body>; backgrounds may fade, so look again afterwards.
  let timer;
  const observer = new MutationObserver(() => {
    apply();
    clearTimeout(timer);
    timer = setTimeout(apply, 400);
  });
  observer.observe(document.documentElement, { attributes: true });
  observer.observe(document.body, { attributes: true });
}

/* ---------------- Width ---------------- */

/**
 * Measure the theme's layout width with a hidden probe that uses WordPress's layout classes,
 * like a header / footer group (alignwide) or post content inside a constrained layout.
 *
 * @param {'wide'|'content'} kind Which width to measure.
 * @return {number} Width in px, or 0 when the theme has no WordPress layout styles (nothing constrains the probe).
 */
function measureThemeWidth(kind) {
  const host = document.querySelector('.wp-site-blocks') || document.body;
  const probe = document.createElement('div');
  const box = document.createElement('div');

  probe.className = 'has-global-padding is-layout-constrained';
  probe.setAttribute('aria-hidden', 'true');
  probe.style.cssText = 'height:0;margin:0;overflow:hidden;visibility:hidden';
  if (kind === 'wide') box.className = 'alignwide';
  probe.append(box);

  // Inserted, measured and removed in one task: the page never paints it.
  host.append(probe);
  const width = box.getBoundingClientRect().width;
  probe.remove();

  return width > 0 && width < document.documentElement.clientWidth - 1 ? Math.round(width) : 0;
}

/**
 * Size the bar's [data-ars-width] and the panel's [data-ars-panel-width] containers (setting "Player Width").
 *
 * The panel follows the theme's wide width even when the bar uses the content width:
 * a two-column full-screen panel does not fit the narrow post column.
 *
 * @param {Element} root Player root.
 * @param {string} mode wide | content (match the theme), full, or skin (keep the style's own widths).
 */
function bindWidth(root, mode) {
  if (mode === 'skin') return;

  const px = (width) => (width === 'none' ? width : `${width}px`);
  const apply = () => {
    const wide = mode === 'full' ? 'none' : measureThemeWidth('wide');
    const bar = mode === 'content' ? measureThemeWidth('content') : wide;

    if (bar && wide) {
      root.dataset.arsFit = mode;
      root.style.setProperty('--ars-fit-width', px(bar));
      root.style.setProperty('--ars-fit-panel-width', px(wide));
    } else {
      // The theme has no WordPress layout to match: keep the style's own widths.
      delete root.dataset.arsFit;
    }
  };

  let frame = 0;
  apply();
  window.addEventListener('resize', () => {
    cancelAnimationFrame(frame);
    frame = requestAnimationFrame(apply);
  });
}

/* ---------------- Skins ---------------- */

/**
 * Whether a click inside a full-screen panel landed beside its [data-ars-panel-width] box (on the backdrop
 * or the margins), which closes the panel. Panels without a marked box (e.g. full-bleed layouts) never do.
 */
function isOutsidePanelBox(panel, target) {
  const box = panel.querySelector('[data-ars-panel-width]');

  return !!box && target.isConnected && !box.contains(target);
}

/**
 * The scope skin expressions run in: the store's state and methods plus fmt(), join(), compact(), categoryName() and show.
 */
function skinScope(store) {
  const helpers = {
    fmt: formatTime,
    // Join the non-empty parts with " · ", e.g. join('EP.3', category) when an episode may have no category.
    join: (...parts) => parts.filter((part) => part || part === 0).join(' · '),
    compact: (number) => new Intl.NumberFormat(document.documentElement.lang || undefined, { notation: 'compact' }).format(number),
    show: config.show || {},
    categoryName: (category) => category || '',
  };
  return new Proxy(store, {
    has: (target, key) => typeof key === 'string' && !key.startsWith('$') && (key in helpers || key in target),
    get: (target, key) => {
      if (key in helpers) return helpers[key];
      const value = target[key];
      return typeof value === 'function' ? value.bind(target) : value;
    },
    set: (target, key, value) => {
      target[key] = value;
      return true;
    },
  });
}

/** Bind a skin's ars-* expressions (see binder.js) to the store. */
function bindSkin(root, store) {
  const binder = new Binder(root, skinScope(store), () => store.emit());

  root.addEventListener('click', (event) => {
    const panel = event.target.closest('[aria-modal="true"]');

    if (store.expanded && panel && isOutsidePanelBox(panel, event.target)) {
      store.expanded = false;
      store.emit();
    }
  });

  store.subscribe(() => {
    binder.render();
    // No page scrolling behind a full-screen "Now Playing" panel.
    document.documentElement.classList.toggle('ars-player-panel-open', !!store.expanded);
  });
}

/**
 * Bind [data-ars-island] elements outside the player, e.g. the episode meta bar, like the skin:
 * same ars-* expressions and the player's light / dark scheme. Islands swapped in later (swup) are bound too.
 */
function bindIslands(root, store) {
  const scope = skinScope(store);
  let islands = [];

  const syncScheme = (island) => {
    if (root.dataset.arsScheme) island.dataset.arsScheme = root.dataset.arsScheme;
  };
  const bindNew = () => {
    islands = islands.filter(({ element }) => element.isConnected);
    document.querySelectorAll('[data-ars-island]').forEach((element) => {
      if (islands.some((island) => island.element === element)) return;
      syncScheme(element);
      islands.push({ element, binder: new Binder(element, scope, () => store.emit()) });
    });
  };

  bindNew();
  store.subscribe(() => islands.forEach(({ binder }) => binder.render()));
  new MutationObserver(() => islands.forEach(({ element }) => syncScheme(element))).observe(root, { attributes: true, attributeFilter: ['data-ars-scheme'] });

  let scheduled = false;
  new MutationObserver((records) => {
    const addsElements = records.some((record) => [...record.addedNodes].some((node) => node.nodeType === Node.ELEMENT_NODE && !root.contains(node)));

    if (scheduled || !addsElements) return;
    scheduled = true;
    requestAnimationFrame(() => {
      scheduled = false;
      bindNew();
    });
  }).observe(document.body, { childList: true, subtree: true });
}

/* ---------------- Boot ---------------- */

const store = new PlayerStore(config.episodes);
const root = document.querySelector('[data-ars-player]');

if (root) {
  bindScheme(root, config.scheme || 'auto');
  bindWidth(root, config.width || 'wide');
  root.toggleAttribute('data-ars-translucent', config.translucent !== false);
  bindBar(root, store);
  bindSkin(root, store);
  bindIslands(root, store);
}
startSpectrum(store);
bindPlayButtons(store);

/**
 * Public API for themes, e.g. ARippleSongPlayer.toggle(42), ARippleSongPlayer.playAt(42, 225),
 * ARippleSongPlayer.add([{ id, title, url, audio, cover, duration, number }]),
 * ARippleSongPlayer.subscribe((player) => …).
 */
window.ARippleSongPlayer = {
  get state() {
    return store;
  },
  toggle: (id) => store.toggle(id),
  playAt: (id, seconds) => store.playAt(id, seconds),
  pause: () => store.pause(),
  add: (episodes) => store.add(episodes),
  subscribe: (listener) => store.subscribe(listener),
};
