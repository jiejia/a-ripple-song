# Web Guidelines Audit

Date: 2026-07-05

Rules source:
https://raw.githubusercontent.com/vercel-labs/web-interface-guidelines/main/command.md

Scope reviewed:
- `resources/views/**/*.blade.php`
- `searchform.php`
- related translation files in `resources/lang/`

## Fixed in this pass

- Added a keyboard-reachable skip link to the main content target in `resources/views/layouts/app.blade.php`.
- Replaced icon-only drawer toggle `label` controls with real `button` elements for opening the left sidebar, right sidebar, search modal, mobile menu, and playlist drawer.
- Replaced icon-only drawer close controls with real `button` elements and added translated accessible names.
- Added accessible names to player controls in `resources/views/sections/player.blade.php`, including seek, playback speed, previous, play/pause, next, volume panel, volume slider, and mute toggle.
- Reworked playlist rows in `resources/views/sections/playlist-drawer.blade.php` so each episode row is keyboard focusable and activatable as a real button.
- Added image dimensions where the UI uses fixed-size thumbnails in the player, playlist drawer, and podcast episode card.
- Added image dimensions for avatar and banner outputs in `resources/views/widgets/authors.blade.php`, `resources/views/partials/entry-authors.blade.php`, and `resources/views/widgets/banner-carousel.blade.php`.
- Added search input labeling and RSS link accessible naming in `searchform.php`.
- Added close-button accessible naming in `resources/views/partials/image-lightbox.blade.php`.
- Replaced demo placeholder links and avatar content in `resources/views/partials/content-search.blade.php` with real tag and author metadata partials.
- Synced new strings into `resources/lang/a-ripple-song-en_US.po` and `resources/lang/a-ripple-song-zh_CN.po`, then rebuilt `.mo` files.

## Remaining follow-up items

- `resources/views/sections/primary-navigation.blade.php`: desktop dropdown navigation is still hover-driven and needs a dedicated keyboard-navigation pass.
- Browser-level checks remain outstanding for focus order, color contrast, reduced-motion behavior, and screen-reader announcements because the in-app browser backend was unavailable during this audit.

## Verification run

- `wp i18n make-pot . resources/lang/a-ripple-song.pot --domain=a-ripple-song --exclude=node_modules,vendor,public,build`
- `wp i18n make-mo resources/lang`
- `php -l searchform.php`
- `curl -s http://localhost:7007/`
