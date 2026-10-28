// Builds the player skin stylesheets (resources/assets/css/player → public/dist/skins): Tailwind CSS 4 + daisyUI 5, confined to the player.
//
// The player is printed into any theme, so its CSS must neither leak out nor be overridden:
// - every selector is prefixed with .ars-player (Preflight then only resets the player);
// - cascade layers are flattened, because unlayered theme CSS would always beat layered rules.
import tailwind from '@tailwindcss/postcss';
import cascadeLayers from '@csstools/postcss-cascade-layers';
import prefixSelector from 'postcss-prefix-selector';

const ROOT = '.ars-player';
const DOCUMENT = /^(?:html|body|:root|:host)/;

// daisyUI switches themes with [data-theme]; the player uses its own data-ars-scheme
// (set by player.js) so a site's daisyUI themes never collide with the player's.
const scheme = (selector) => selector.replace(/\[data-theme(?=[=\]])/g, '[data-ars-scheme');

export default {
  plugins: [
    tailwind({ optimize: { minify: true } }),
    prefixSelector({
      prefix: ROOT,
      // Rules meant for the page itself, e.g. room for the fixed bar.
      exclude: [/has-ars-player/],
      transform(prefix, selector, prefixed) {
        selector = scheme(selector);
        if (selector.includes(ROOT)) return selector;
        // Document-level rules (Tailwind variables, Preflight base) apply to the player root.
        if (DOCUMENT.test(selector)) return selector.replace(DOCUMENT, ROOT);
        if (selector.startsWith('[data-ars-scheme')) return `${ROOT}${selector}`;
        if (selector === '*') return `${ROOT}, ${ROOT} *`;
        return scheme(prefixed);
      },
    }),
    cascadeLayers(),
  ],
};
