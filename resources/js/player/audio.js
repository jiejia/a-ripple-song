import { Howler } from 'howler';

let toneModulePromise = null;

/**
 * Lazily load Tone.js after user interaction to avoid AudioContext autoplay warnings.
 *
 * @return {Promise<typeof import('tone')>}
 */
export function loadToneModule() {
  if (!toneModulePromise) {
    toneModulePromise = import('tone');
  }

  return toneModulePromise;
}

/**
 * Disconnect an audio node without throwing when it is already detached.
 *
 * @param {AudioNode|null|undefined} node Audio node to disconnect.
 * @return {void}
 */
export function safeDisconnect(node) {
  if (!node || typeof node.disconnect !== 'function') {
    return;
  }

  try {
    node.disconnect();
  } catch {
    // Ignore disconnect failures while rebuilding the audio graph.
  }
}

/**
 * Calculate pitch compensation semitones for a playback rate.
 *
 * @param {number} rate Playback rate multiplier.
 * @return {number}
 */
export function getPitchCompensationSemitones(rate) {
  if (!rate || rate === 1) {
    return 0;
  }

  return -12 * Math.log2(rate);
}

/**
 * Ensure the Tone.js audio context is started through Howler.
 *
 * @param {boolean} toneContextReady Whether Tone already uses the active context.
 * @return {Promise<boolean>} Updated tone context ready flag.
 */
export async function ensureToneContext(toneContextReady) {
  if (!Howler?.ctx) {
    return toneContextReady;
  }

  try {
    const Tone = await loadToneModule();

    if (!toneContextReady) {
      Tone.setContext(Howler.ctx);
      toneContextReady = true;
    }

    await Tone.start();
  } catch (error) {
    console.warn('[aripplesong] Tone.js initialization failed', error);
  }

  return toneContextReady;
}
