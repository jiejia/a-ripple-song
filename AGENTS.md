# Repository Guidelines

## Project Structure & Module Organization

- `functions.php`: theme bootstrap; loads modules under `app/`.
- `app/`: PHP theme logic (Acorn/PSR-4 `App\\` classes, theme hooks, widgets, metrics).
- `resources/`: frontend source (Blade views in `resources/views/`, JS/CSS in `resources/js/` + `resources/css/`, translations in `resources/lang/`).
- `public/`: build output (ignored by git). Vite writes `public/build/...`.
- `data/`: demo/import assets and fixtures.
- `vendor/`, `node_modules/`: dependencies (both ignored by git).

## Build, Test, and Development Commands

Run from the theme root:

- `composer install`: install PHP dependencies (Acorn, Carbon Fields, CMB2).
- `yarn install` (or `npm ci`): install JS dependencies (Node `>=20`).
- `yarn dev` (or `npm run dev`): start Vite dev server for local development.
- `yarn build` (or `npm run build`): production build into `public/build/`.
- `npm run translate`: generate/update `resources/lang/sage.pot` + update `.po` files (requires WP-CLI: `wp i18n ...`).
- `./vendor/bin/pint`: format PHP (Laravel Pint).

## Coding Style & Naming Conventions

- PHP: prefer typed signatures, early returns, and WordPress escaping (`esc_html`, `esc_url`, etc.).
- Indentation: 4 spaces in PHP; 2 spaces in JS/Blade where consistent with existing files.
- Names: `App\\` classes in `app/` use `StudlyCase.php`; functions use `snake_case`; Blade templates use `kebab-case.blade.php`.
- Avoid hardcoding podcast post type slugs; use `aripplesong_get_podcast_post_type()`/`aripplesong_podcast_features_enabled()` when relevant.

## Testing Guidelines

No dedicated automated test suite is present. Validate changes by:

- `php -l <file>` for edited PHP files.
- `yarn build` to ensure the asset pipeline compiles.
- Manual smoke test in WordPress (frontend pages, wp-admin widgets, theme options).

## Commit & Pull Request Guidelines

- Commits: use imperative, sentence-case subjects (e.g., “Fix release package missing composer.json”).
- PRs: include a clear description, linked issue (if any), and screenshots/GIFs for UI changes. Note any WP/PHP/Node version assumptions and migration steps (e.g., permalink flush). 


## 开发说明

- 开发和修改功能时，如果有涉及到多语言的文字，都要本地化，翻译成对应的文字放到/resources/lang/目录下的对应语言.po文件里，然后编译成.mo文件
- 每次开完和修改功能够，把相关的一次或多次修改做成一次commit提交

## 版本号修改清单

把主题版本从 `x.y.z-beta`（或其他）升级到 `x.y.z` 时，需要同步更新下面这些文件（建议最后用 `rg -n "<旧版本>" -S .` 再确认一次没有遗漏）：

- `style.css`：头部注释里的 `Version:`
- `composer.json`：`"version"`
- `package.json`：`"version"`
- `package-lock.json`：
  - 顶部 `"version"`
  - `packages[""].version`
- `readme.txt`：
  - `Stable tag:`
  - `== Changelog ==` 下对应版本标题（例如 `= x.y.z =`）
- `README.md`：顶部 shields.io badge（URL 中包含版本号）
- `docs/README*.md`：同上（多语言 README 的 shields.io badge）
- `resources/lang/a-ripple-song.pot`：`Project-Id-Version: A Ripple Song x.y.z`（通常由 `npm run translate`/WP-CLI 生成 pot 时一起更新）
