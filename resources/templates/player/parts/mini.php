<?php
/**
 * Mini player of the bars, for small screens (below 768px), the same in every skin.
 *
 * Rendered as the first child of the skin's [data-ars-width="box"]:
 *   - a tab on top of the bar (data-ars-handle) that collapses the bar into the mini player (chevron down)
 *     and restores it (chevron up);
 *   - the mini player itself (data-ars-mini-bar), one row: cover, title above a thin progress bar, play / pause.
 * The collapsed state (data-ars-mini on the player root) is kept in localStorage. On wider screens neither part
 * shows and the full bar is used. Visibility rules: base styles in GlobalPlayer.php.
 *
 * Included by the skin templates, each passing its own look:
 *   $handle  Classes of the tab (background, border, text color).
 *   $surface data-ars-surface value of the tab, the same as the bar box's ("" = base-100); false when the box has none.
 *   $cover   Classes of the cover (radius, background).
 *   $track   Classes of the progress track.
 *   $fill    Classes of the played part.
 *   $play    Classes of the play / pause button.
 *
 * Themes can override this file with {theme}/a-ripple-song/player/parts/mini.php.
 * Every skin stylesheet scans this file, so its utility classes exist in all of them.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Icon;

$handle ??= 'rounded-t-xl border border-b-0 border-base-content/10 bg-base-100 text-base-content';
$surface ??= '';
$cover ??= 'rounded-lg bg-base-300';
$track ??= 'bg-base-content/15';
$fill ??= 'bg-primary';
$play ??= 'bg-primary text-primary-content';
?>
<button type="button" class="absolute bottom-full left-1/2 z-10 h-5 w-12 -translate-x-1/2 place-items-center leading-[0] <?php echo esc_attr($handle); ?>"<?php echo $surface === false ? '' : ' data-ars-surface="' . esc_attr($surface) . '"'; ?> data-ars-handle data-ars-action="mini" aria-expanded="true" aria-label="<?php esc_attr_e('Collapse player', 'a-ripple-song'); ?>">
  <span data-ars-show="full"><?php Icon::render('chevron-down', 'size-4'); ?></span>
  <span data-ars-show="mini" hidden><?php Icon::render('chevron-up', 'size-4'); ?></span>
</button>
<div class="min-w-0 items-center gap-3 p-2 text-base-content" data-ars-mini-bar>
  <span class="relative grid size-10 shrink-0 place-items-center overflow-hidden <?php echo esc_attr($cover); ?>">
    <img class="size-full object-cover" alt="" data-ars-src="cover" data-ars-show="cover">
    <span class="opacity-50" data-ars-show="nocover"><?php Icon::render('podcast', 'size-4'); ?></span>
  </span>
  <span class="min-w-0 flex-1">
    <span class="block truncate text-sm font-semibold leading-5" data-ars-text="title"></span>
    <span class="mt-1.5 block h-1 overflow-hidden rounded-full <?php echo esc_attr($track); ?>" aria-hidden="true">
      <span class="block h-full w-0 rounded-full <?php echo esc_attr($fill); ?>" data-ars-progress></span>
    </span>
  </span>
  <button type="button" class="grid size-10 shrink-0 place-items-center rounded-full <?php echo esc_attr($play); ?>" data-ars-action="toggle" aria-label="<?php esc_attr_e('Play', 'a-ripple-song'); ?>">
    <span data-ars-show="paused"><?php Icon::render('play', 'ml-0.5 size-4 fill-current'); ?></span>
    <span data-ars-show="playing" hidden><?php Icon::render('pause', 'size-4 fill-current'); ?></span>
    <span class="loading loading-spinner loading-sm" data-ars-show="loading" hidden></span>
  </button>
</div>
<?php
// phpcs:enable
