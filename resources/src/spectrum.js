/**
 * Spectrum canvases, ported unchanged from the ~/workspace/podcast prototypes (assets/js/player.js).
 *
 * Every <canvas data-spectrum> on the page is painted, in the player and anywhere in a theme;
 * canvases added later (swup page swaps) are picked up on the next frame.
 *
 *   data-spectrum  bars | mirror | led | wave | pulse | radial | vu | ascii | dots
 *   data-episode   only animates / shows progress while that episode is current
 *   data-bar       bar width / LED cell / line width (px);  data-gap  spacing (px)
 *   data-color     primary (default) | content | accent | secondary … (any daisyUI color name)
 *   data-inner     radial: inner radius ratio (0.7);  data-peak  vu: red zone color; dots: second ink
 *
 * Colors are read from each canvas, so a local data-ars-theme (or a theme's data-theme) applies.
 *
 * Analysis routes the <audio> element through Web Audio. Browsers silence cross-origin audio routed
 * that way unless it is served with CORS headers, so only same-origin audio is analysed; other
 * audio still plays, its canvases just stay at rest.
 */
import { Howler } from 'howler';

const MIN_HZ = 40;
const MAX_HZ = 7000;

let store = null;
let analyser = null;
let freq = null;
let wave = null;
const routed = new WeakSet();
const levels = new WeakMap();

function resumeContext() {
  if (Howler.ctx && Howler.ctx.state === 'suspended') Howler.ctx.resume();
}

function ensureAnalyser() {
  if (!Howler.ctx) return null;
  if (!analyser) {
    analyser = Howler.ctx.createAnalyser();
    analyser.fftSize = 1024;
    analyser.smoothingTimeConstant = 0.8;
    analyser.connect(Howler.ctx.destination);
    freq = new Uint8Array(analyser.frequencyBinCount);
    wave = new Uint8Array(analyser.fftSize);
  }
  return analyser;
}

// 把 Howler 的 <audio> 节点接入分析器；同一个节点只能接一次（Howler 会复用节点池）
function route(h) {
  const node = h._sounds[0] && h._sounds[0]._node;
  if (!node || routed.has(node) || !ensureAnalyser()) return;
  try {
    Howler.ctx.createMediaElementSource(node).connect(analyser);
    routed.add(node);
  } catch (err) {
    console.warn('[player] 无法接入频谱分析：', err);
  }
}

/* ---------------- 频谱绘制 ----------------
 * <canvas data-spectrum="bars|mirror|led|wave|pulse|radial|vu|ascii|dots">
 *   data-episode 只在该集为当前播放时才有动态 / 进度（单集页用）
 *   data-bar   柱宽 / LED 格子边长 / 波形线宽（px）
 *   data-gap   间距（px）
 *   data-color primary（默认）| content（正文色）| accent | secondary …（任意 daisyUI 颜色名）
 *   data-inner radial：内圈半径占比（默认 0.7）
 *   data-peak  vu：红区颜色（默认 primary）；dots：第二种油墨（错版叠印，不写则单色）
 *   ascii      data-bar 为字号（px），用方块字符 ▁▂▃▄▅▆▇█ 绘制
 */

// 颜色从画布自身读取，所以局部 data-theme（如深色播放条）也能生效
let colorCache = new WeakMap();
let painted = new WeakMap();
function colorOf(canvas, attr = 'color') {
  let c = colorCache.get(canvas);
  if (!c) { c = {}; colorCache.set(canvas, c); }
  if (!c[attr]) {
    const v = canvas.dataset[attr] || 'primary';
    const name = v === 'content' ? 'base-content' : v;
    c[attr] = getComputedStyle(canvas).getPropertyValue(`--color-${name}`).trim() || '#888';
  }
  return c[attr];
}
function readColor() {
  colorCache = new WeakMap();
  painted = new WeakMap(); // 主题切换后强制重画静止画布
}
window.addEventListener('themechange', readColor);

// 对数频率分段：低频分得细、高频合并，听感更均匀
function bandValues(count) {
  const out = new Float32Array(count);
  if (!analyser) return out;
  const binHz = Howler.ctx.sampleRate / analyser.fftSize;
  const ratio = MAX_HZ / MIN_HZ;
  for (let i = 0; i < count; i++) {
    const lo = Math.floor((MIN_HZ * Math.pow(ratio, i / count)) / binHz);
    const hi = Math.max(lo + 1, Math.floor((MIN_HZ * Math.pow(ratio, (i + 1) / count)) / binHz));
    let sum = 0;
    for (let b = lo; b < hi && b < freq.length; b++) sum += freq[b];
    // 高频能量天然偏低，略微补偿
    const v = (sum / (hi - lo) / 255) * (0.8 + (i / count) * 0.9);
    out[i] = Math.min(1, Math.pow(v, 1.8));
  }
  return out;
}

function paint(canvas, active) {
  const w = canvas.clientWidth;
  const h = canvas.clientHeight;
  if (!w || !h) return;
  const dpr = window.devicePixelRatio || 1;
  if (canvas.width !== Math.round(w * dpr) || canvas.height !== Math.round(h * dpr)) {
    canvas.width = Math.round(w * dpr);
    canvas.height = Math.round(h * dpr);
    painted.delete(canvas);
  }
  const mode = canvas.dataset.spectrum;
  const size = Number(canvas.dataset.bar || (mode === 'led' ? 5 : mode === 'wave' ? 1.5 : 3));
  const gap = Number(canvas.dataset.gap || 2);
  const color = colorOf(canvas);
  const epId = canvas.dataset.episode;
  const own = !epId || (!!store && store.isCurrent(epId));
  if (!own) active = false;

  if (mode === 'wave') {
    // 静止时只画一次平线（不写 DOM 属性，免得触发 MutationObserver）
    if (!active && painted.get(canvas)) return;
    painted.set(canvas, !active);
    return drawWave(canvas, dpr, w, h, size, color, active);
  }
  if (mode === 'vu') return drawVu(canvas, dpr, w, h, color, colorOf(canvas, 'peak'), active);

  // radial 只算半圈的频段，左右对称画满一圈
  const radial = mode === 'radial';
  const inner = Math.min(0.95, Number(canvas.dataset.inner || 0.7));
  const step = mode === 'ascii' ? size * 0.6 + gap : size + gap;
  const count = radial
    ? Math.max(8, Math.floor((Math.PI * (Math.min(w, h) / 2) * inner) / step))
    : Math.max(4, Math.floor((w + gap) / step));
  let lv = levels.get(canvas);
  if (!lv || lv.length !== count) { lv = new Float32Array(count); levels.set(canvas, lv); }
  const target = active ? bandValues(count) : null;
  let energy = 0;
  for (let i = 0; i < count; i++) {
    const t = target ? target[i] : 0;
    lv[i] = t > lv[i] ? t : lv[i] * 0.9; // 快起慢落
    energy += lv[i];
  }
  const progress = mode === 'pulse' && own && store && store.duration ? Math.min(1, store.position / store.duration) : 0;
  const settled = !active && energy < 0.01;
  const key = settled ? progress.toFixed(3) : null;
  if (settled && painted.get(canvas) === key) return;
  painted.set(canvas, key);

  const ctx = canvas.getContext('2d');
  ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
  ctx.clearRect(0, 0, w, h);
  ctx.fillStyle = color;
  const offset = (w - (count * (size + gap) - gap)) / 2;

  if (mode === 'pulse') {
    // 进度波形：静止时是固定波形，播放时随频谱跳动；已播放部分实色，未播放部分半透明
    const played = progress * w;
    for (let i = 0; i < count; i++) {
      const x = offset + i * (size + gap);
      const shape = 0.3 + 0.7 * Math.abs(Math.sin(i * 1.7) * Math.cos(i * 0.43));
      const bh = Math.max(size, Math.max(lv[i], shape * (active ? 0.3 : 0.75)) * h);
      ctx.globalAlpha = x + size / 2 <= played ? 1 : 0.3;
      roundRect(ctx, x, (h - bh) / 2, size, bh, size / 2);
    }
    ctx.globalAlpha = 1;
    return;
  }

  if (radial) {
    // 环形：低频在顶部，左右对称向下展开；静止时是一圈圆点
    const cx = w / 2;
    const cy = h / 2;
    const r0 = (Math.min(w, h) / 2) * inner + size / 2;
    const room = Math.min(w, h) / 2 - r0 - size / 2;
    ctx.strokeStyle = color;
    ctx.lineWidth = size;
    ctx.lineCap = 'round';
    for (let i = 0; i < count; i++) {
      const a = ((i + 0.5) / count) * Math.PI;
      const len = lv[i] * room;
      ctx.globalAlpha = 0.35 + lv[i] * 0.65;
      ctx.beginPath();
      for (const side of [-1, 1]) {
        const t = -Math.PI / 2 + side * a;
        ctx.moveTo(cx + Math.cos(t) * r0, cy + Math.sin(t) * r0);
        ctx.lineTo(cx + Math.cos(t) * (r0 + len), cy + Math.sin(t) * (r0 + len));
      }
      ctx.stroke();
    }
    ctx.globalAlpha = 1;
    return;
  }

  if (mode === 'ascii') {
    // 终端字符画：整格 █，顶端用 ▁…▇ 表示不足一格的部分；带荧光
    const BLOCKS = ' ▁▂▃▄▅▆▇█';
    const lh = size * 1.05;
    const rows = Math.max(1, Math.floor(h / lh));
    ctx.font = `${size}px ui-monospace, "JetBrains Mono", monospace`;
    ctx.textBaseline = 'bottom';
    ctx.shadowColor = color;
    ctx.shadowBlur = size * 0.5;
    const x0 = (w - count * step + gap) / 2;
    for (let i = 0; i < count; i++) {
      const x = x0 + i * step;
      const v = Math.max(0.06, lv[i]) * rows;
      const full = Math.floor(v);
      ctx.globalAlpha = 0.45 + lv[i] * 0.55;
      for (let r = 0; r < full; r++) ctx.fillText('█', x, h - r * lh);
      const part = Math.round((v - full) * 8);
      if (part > 0 && full < rows) ctx.fillText(BLOCKS[part], x, h - full * lh);
    }
    ctx.shadowBlur = 0;
    ctx.globalAlpha = 1;
    return;
  }

  if (mode === 'dots') {
    // 半色调网点：电平越高，网点越大；data-peak 给第二种油墨，错开 1/4 格叠印
    const rows = Math.max(2, Math.floor((h + gap) / (size + gap)));
    const top = h - rows * (size + gap) + gap;
    const inks = canvas.dataset.peak ? [[colorOf(canvas, 'peak'), size / 4], [color, 0]] : [[color, 0]];
    for (const [ink, shift] of inks) {
      ctx.fillStyle = ink;
      for (let i = 0; i < count; i++) {
        const lit = lv[i] * rows;
        const cx = offset + i * (size + gap) + size / 2 + shift;
        for (let r = 0; r < rows; r++) {
          const f = Math.min(1, Math.max(0.16, lit - r));
          ctx.beginPath();
          ctx.arc(cx, top + (rows - 1 - r) * (size + gap) + size / 2 + shift, (size / 2) * f, 0, Math.PI * 2);
          ctx.fill();
        }
      }
    }
    return;
  }

  if (mode === 'led') {
    // 点阵：每列按电平点亮若干格，未点亮的格子保留淡底
    const rows = Math.max(2, Math.floor((h + gap) / (size + gap)));
    const top = h - rows * (size + gap) + gap;
    for (let i = 0; i < count; i++) {
      const lit = Math.round(lv[i] * rows);
      const x = offset + i * (size + gap);
      for (let r = 0; r < rows; r++) {
        ctx.globalAlpha = r < lit ? 1 : 0.12;
        ctx.fillRect(x, top + (rows - 1 - r) * (size + gap), size, size);
      }
    }
    ctx.globalAlpha = 1;
    return;
  }

  for (let i = 0; i < count; i++) {
    const x = offset + i * (size + gap);
    const v = lv[i];
    ctx.globalAlpha = 0.3 + v * 0.7;
    if (mode === 'mirror') {
      const bh = Math.max(size, v * h);
      roundRect(ctx, x, (h - bh) / 2, size, bh, size / 2);
    } else {
      const bh = Math.max(2, v * h);
      roundRect(ctx, x, h - bh, size, bh, Math.min(size / 2, bh / 2));
    }
  }
  ctx.globalAlpha = 1;
}

// 示波器：时域波形
function drawWave(canvas, dpr, w, h, lineW, color, active) {
  const ctx = canvas.getContext('2d');
  ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
  ctx.clearRect(0, 0, w, h);
  ctx.strokeStyle = color;
  ctx.lineWidth = lineW;
  ctx.lineJoin = 'round';
  ctx.beginPath();
  if (!active || !wave) {
    ctx.globalAlpha = 0.45;
    ctx.moveTo(0, h / 2);
    ctx.lineTo(w, h / 2);
  } else {
    const step = wave.length / w;
    for (let x = 0; x <= w; x++) {
      const v = (wave[Math.min(wave.length - 1, Math.floor(x * step))] - 128) / 128;
      const y = h / 2 + Math.max(-1, Math.min(1, v * 2.4)) * (h / 2 - lineW);
      if (x === 0) ctx.moveTo(x, y); else ctx.lineTo(x, y);
    }
  }
  ctx.stroke();
  ctx.globalAlpha = 1;
}

// 指针式电平表：时域 RMS（-36 dB … 0 dB）→ 指针角度，带机械阻尼
function drawVu(canvas, dpr, w, h, color, peak, active) {
  let lv = levels.get(canvas);
  if (!lv || lv.length !== 1) { lv = new Float32Array(1); levels.set(canvas, lv); }
  let target = 0;
  if (active && wave) {
    let sum = 0;
    for (let i = 0; i < wave.length; i++) { const v = (wave[i] - 128) / 128; sum += v * v; }
    const db = 20 * Math.log10(Math.sqrt(sum / wave.length) || 1e-6);
    target = Math.min(1.08, Math.max(0, (db + 36) / 36));
  }
  lv[0] += (target - lv[0]) * (target > lv[0] ? 0.22 : 0.07);
  const settled = !active && lv[0] < 0.002;
  if (settled && painted.get(canvas) === 'rest') return;
  painted.set(canvas, settled ? 'rest' : null);

  const ctx = canvas.getContext('2d');
  ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
  ctx.clearRect(0, 0, w, h);
  const cx = w / 2;
  const cy = h * 0.92;
  const r = Math.min(w * 0.46, h * 0.8);
  const span = (100 * Math.PI) / 180;
  const ang = (v) => -Math.PI / 2 - span / 2 + v * span;
  const lw = Math.max(1, r / 40);

  ctx.lineCap = 'round';
  ctx.lineWidth = lw * 3;
  ctx.strokeStyle = peak;
  ctx.beginPath();
  ctx.arc(cx, cy, r * 0.86, ang(0.75), ang(1));
  ctx.stroke();

  ctx.strokeStyle = color;
  for (let i = 0; i <= 10; i++) {
    const v = i / 10;
    const a = ang(v);
    const major = i % 2 === 0;
    ctx.lineWidth = major ? lw * 1.4 : lw;
    ctx.globalAlpha = v > 0.75 ? 1 : 0.75;
    if (v > 0.75) ctx.strokeStyle = peak;
    ctx.beginPath();
    ctx.moveTo(cx + Math.cos(a) * r * (major ? 0.9 : 0.93), cy + Math.sin(a) * r * (major ? 0.9 : 0.93));
    ctx.lineTo(cx + Math.cos(a) * r, cy + Math.sin(a) * r);
    ctx.stroke();
  }
  ctx.globalAlpha = 1;
  ctx.fillStyle = color;
  ctx.font = `700 ${Math.max(8, Math.round(r * 0.16))}px ui-monospace, monospace`;
  ctx.textAlign = 'center';
  ctx.fillText('VU', cx, cy - r * 0.42);

  const a = ang(Math.min(1.08, lv[0]));
  ctx.strokeStyle = color;
  ctx.lineWidth = lw * 1.2;
  ctx.beginPath();
  ctx.moveTo(cx, cy);
  ctx.lineTo(cx + Math.cos(a) * r * 0.98, cy + Math.sin(a) * r * 0.98);
  ctx.stroke();
  ctx.beginPath();
  ctx.arc(cx, cy, lw * 3, 0, Math.PI * 2);
  ctx.fill();
}

function roundRect(ctx, x, y, w, h, r) {
  ctx.beginPath();
  if (ctx.roundRect) ctx.roundRect(x, y, w, h, r); else ctx.rect(x, y, w, h);
  ctx.fill();
}

function sameOrigin(url) {
  try {
    return new URL(url, location.href).origin === location.origin;
  } catch {
    return false;
  }
}

/**
 * Start painting all spectrum canvases for a player store.
 *
 * @param {object} player Store exposing howl, current, playing, position, duration and isCurrent(id).
 */
export function startSpectrum(player) {
  store = player;
  readColor();
  // html5 audio routed through the AudioContext would turn silent once Howler auto-suspends it.
  Howler.autoSuspend = false;
  window.addEventListener('themechange', readColor);

  const tick = () => {
    const canvases = document.querySelectorAll('canvas[data-spectrum]');
    const howl = store.howl;

    if (canvases.length && howl && store.current && sameOrigin(store.current.audio)) {
      route(howl);
      resumeContext();
    }

    const active = !!(howl && store.playing && analyser);
    if (active) {
      analyser.getByteFrequencyData(freq);
      analyser.getByteTimeDomainData(wave);
    }
    canvases.forEach((canvas) => paint(canvas, active));
    requestAnimationFrame(tick);
  };

  requestAnimationFrame(tick);
}

/** Re-read canvas colors, e.g. after a light / dark switch. */
export { readColor as refreshSpectrumColors };
