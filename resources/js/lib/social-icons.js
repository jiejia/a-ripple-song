import {
  siFacebook,
  siX,
  siInstagram,
  siYoutube,
  siTiktok,
  siPinterest,
  siThreads,
  siSinaweibo,
  siWechat,
  siRss,
} from 'simple-icons';

/**
 * Registry mapping platform keys to simple-icons objects.
 * Each object provides `path` (SVG path data) and `hex` (brand colour).
 *
 * @type {Record<string, {path: string, hex: string, title: string}>}
 */
const registry = {
  facebook: siFacebook,
  twitter: siX,
  x: siX,
  instagram: siInstagram,
  linkedin: {
    path: 'M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 0 1-2.063-2.065 2.064 2.064 0 1 1 2.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z',
    hex: '0A66C2',
    title: 'LinkedIn',
  },
  youtube: siYoutube,
  tiktok: siTiktok,
  pinterest: siPinterest,
  threads: siThreads,
  weibo: siSinaweibo,
  wechat: siWechat,
  rss: siRss,
};

/**
 * Replace every `[data-si-icon]` element in the given root with
 * an inline SVG from simple-icons.
 *
 * @param {Document | Element} root DOM root to scan.
 * @return {void}
 */
export function renderSocialIcons(root = document) {
  root.querySelectorAll('[data-si-icon]').forEach((el) => {
    const slug = el.getAttribute('data-si-icon');
    const icon = registry[slug];

    if (!icon) {
      return;
    }

    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('viewBox', '0 0 24 24');
    svg.setAttribute('fill', 'currentColor');
    svg.setAttribute('role', 'img');
    svg.setAttribute('aria-hidden', 'true');

    // Preserve CSS classes from the placeholder element.
    svg.setAttribute('class', el.getAttribute('class') ?? '');

    const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    path.setAttribute('d', icon.path);
    svg.appendChild(path);

    el.parentNode.replaceChild(svg, el);
  });
}
