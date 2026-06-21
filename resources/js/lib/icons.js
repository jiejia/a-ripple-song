import { createIcons, icons } from 'lucide';

/**
 * Re-render Lucide icons after Alpine or Swup updates the DOM.
 *
 * @param {number} delay Milliseconds before refreshing icons.
 * @return {void}
 */
export function scheduleIconRefresh(delay = 10) {
  window.setTimeout(() => {
    createIcons({ icons });
  }, delay);
}

export { createIcons, icons };
