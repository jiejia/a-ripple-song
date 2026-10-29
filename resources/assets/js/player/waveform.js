/**
 * Waveform progress bars (setting "Progress Bar & Spectrum" = waveform), drawn by wavesurfer.js.
 * Built to public/dist/waveform.js and loaded after player.js only for that style.
 *
 * Every [data-ars-waveform] (from player/parts/progress.php) gets a wavesurfer instance that only draws:
 * playback stays with the player store, the range input on top seeks, and the waveform follows the store.
 *
 *   data-bar    bar width (px);  data-gap  spacing (px)
 *   data-color  primary (default) | content | accent | secondary … (any daisyUI color name)
 *
 * The first time a visitor sees an episode, wavesurfer downloads and decodes its whole audio file in the
 * browser (same-origin audio, or audio served with CORS headers). The peaks are then kept in localStorage,
 * so later pages draw at once. Hidden bars (phones, where the progress row is not shown) never download.
 */
import WaveSurfer from 'wavesurfer.js';

const CACHE_KEY = 'ars_waveforms';
// Episodes kept in localStorage; each holds PEAKS numbers.
const CACHE_SIZE = 30;
const PEAKS = 400;

const player = window.ARippleSongPlayer;
const items = [];
// Audio URL → Promise of { peaks, duration }, so several bars share one download.
const decoding = new Map();

function readCache() {
  try {
    const cache = JSON.parse(localStorage.getItem(CACHE_KEY) || '[]');
    return Array.isArray(cache) ? cache : [];
  } catch {
    return [];
  }
}

function cached(url) {
  return readCache().find((entry) => entry.url === url) || null;
}

function remember(url, peaks, duration) {
  const cache = [{ url, peaks, duration }, ...readCache().filter((entry) => entry.url !== url)].slice(0, CACHE_SIZE);
  try {
    localStorage.setItem(CACHE_KEY, JSON.stringify(cache));
  } catch {
    // Storage full or blocked: the waveform is decoded again on the next page.
  }
}

/**
 * The element's daisyUI color for both parts. wavesurfer paints the played part by recoloring the wave
 * (canvas source-in), which keeps the wave's alpha, so the unplayed part is faded with CSS (::part(canvases)) instead.
 */
function colors(el) {
  const name = el.dataset.color === 'content' ? 'base-content' : el.dataset.color || 'primary';
  const color = getComputedStyle(el).getPropertyValue(`--color-${name}`).trim() || '#888';
  return { waveColor: color, progressColor: color };
}

function create(el) {
  const bar = Number(el.dataset.bar || 2);
  const ws = WaveSurfer.create({
    container: el,
    height: 'auto',
    barWidth: bar,
    barGap: Number(el.dataset.gap || 2),
    barRadius: bar / 2,
    barMinHeight: 1,
    normalize: true,
    cursorWidth: 0,
    interact: false,
    hideScrollbar: true,
    autoScroll: false,
    autoCenter: false,
    ...colors(el),
  });
  return { el, ws, url: '', ready: false };
}

function visible(el) {
  return el.clientWidth > 0 && el.clientHeight > 0;
}

function saveData() {
  return !!(navigator.connection && navigator.connection.saveData);
}

/** Download and decode once per audio URL, then keep the peaks. */
function decode(item, url) {
  if (!decoding.has(url)) {
    decoding.set(url, item.ws.load(url).then(() => {
      const result = { peaks: item.ws.exportPeaks({ channels: 1, maxLength: PEAKS, precision: 100 })[0], duration: item.ws.getDuration() };
      remember(url, result.peaks, result.duration);
      return result;
    }).finally(() => decoding.delete(url)));
  }
  return decoding.get(url);
}

/** Show the current episode's waveform in one bar. */
async function show(item) {
  const episode = player.state.current;
  const url = episode && episode.audio ? episode.audio : '';
  if (item.url === url && (item.ready || item.loading)) return;

  item.url = url;
  item.ready = false;
  item.el.removeAttribute('data-ars-wave-ready');
  if (!url) return;

  const entry = cached(url);
  if (!entry && (!visible(item.el) || saveData())) return;

  item.loading = true;
  try {
    const { peaks, duration } = entry || (await decode(item, url));
    if (item.url !== url) return;
    // Peaks only: no audio element source, so the decoded file is released.
    await item.ws.load('', [peaks], duration);
    if (item.url !== url) return;
    item.ready = true;
    item.el.setAttribute('data-ars-wave-ready', '');
    follow(item);
  } catch {
    // Cross-origin audio without CORS, or a decode error: the bar keeps its placeholder line.
  } finally {
    item.loading = false;
  }
}

function follow(item) {
  if (item.ready) item.ws.setTime(player.state.position || 0);
}

function init() {
  if (!player) return;

  document.querySelectorAll('[data-ars-waveform]').forEach((el) => items.push(create(el)));
  if (!items.length) return;

  player.subscribe(() => items.forEach((item) => (item.url === (player.state.current?.audio || '') ? follow(item) : show(item))));
  items.forEach(show);

  // A bar that was hidden (narrow window) gets its waveform once it shows up.
  const observer = new ResizeObserver((entries) => entries.forEach((entry) => {
    const item = items.find((candidate) => candidate.el === entry.target);
    if (item && !item.ready) show(item);
  }));
  items.forEach((item) => observer.observe(item.el));

  // Light / dark switches change the daisyUI colors: player.js sets data-ars-scheme, prototype themes fire themechange.
  const recolor = () => items.forEach((item) => item.ws.setOptions(colors(item.el)));
  const root = document.querySelector('[data-ars-player]');
  if (root) new MutationObserver(recolor).observe(root, { attributes: true, attributeFilter: ['data-ars-scheme'] });
  window.addEventListener('themechange', recolor);
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init);
} else {
  init();
}
