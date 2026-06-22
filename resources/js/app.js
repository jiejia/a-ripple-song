import.meta.glob([
  '../images/**',
  '../fonts/**',
]);

import Swup from 'swup';
import SwupFormsPlugin from '@swup/forms-plugin';
import SwupScriptsPlugin from '@swup/scripts-plugin';
import Alpine from 'alpinejs';
import { hydrateMetricsFromDom, maybeSendViewMetric } from '@scripts/lib/rest.js';
import { registerDateFormatter } from '@scripts/lib/date.js';
import { createIcons, icons, scheduleIconRefresh } from '@scripts/lib/icons.js';
import { registerThemeStore } from '@scripts/theme/store.js';
import { registerPlayerStore } from '@scripts/player/store.js';

registerDateFormatter();

window.Alpine = Alpine;
registerThemeStore(Alpine);
registerPlayerStore(Alpine);
Alpine.start();

/**
 * Initialize image lightbox interactions for content images.
 *
 * @return {void}
 */
function initImageLightbox() {
  const modal = document.getElementById('image-lightbox-modal');
  const lightboxImage = document.getElementById('lightbox-image');

  if (!modal || !lightboxImage) {
    return;
  }

  document.querySelectorAll('#content img').forEach((img) => {
    if (img.closest('a')) {
      return;
    }

    img.replaceWith(img.cloneNode(true));
    const image = img.parentNode ? img : document.querySelector(`#content img[src="${img.src}"]`);
    if (!image) {
      return;
    }

    image.addEventListener('click', (event) => {
      event.preventDefault();
      lightboxImage.src = image.dataset.fullUrl || image.src;
      lightboxImage.alt = image.alt || '';
      modal.showModal();
    });
  });

  modal.addEventListener('close', () => {
    lightboxImage.src = '';
  });
}

const swup = new Swup({
  containers: ['#swup-main', '#swup-header', '#swup-mobile-menu'],
  animateHistoryBrowsing: true,
  plugins: [new SwupFormsPlugin(), new SwupScriptsPlugin()],
});

/**
 * Reinitialize page-level UI enhancements after first load or Swup navigation.
 *
 * @return {void}
 */
function initPageEnhancements() {
  scheduleIconRefresh();
  initImageLightbox();
  void maybeSendViewMetric().finally(() => hydrateMetricsFromDom());
}

document.addEventListener('DOMContentLoaded', initPageEnhancements);
swup.hooks.on('content:replace', initPageEnhancements);

export { createIcons, icons };
