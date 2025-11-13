import domReady from '@wordpress/dom-ready';

import { createIcons, icons } from 'lucide';

domReady(() => {
  createIcons({ icons });
});
