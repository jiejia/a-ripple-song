<?php
/**
 * Mini player of the bars, for small screens (below 768px), the same in every skin.
 *
 * Rendered as the first child of the skin's [data-ars-width="box"] (data-ars-mini-bar), one row: round cover and title
 * (both link to the episode), the title above a
 * thin progress bar with a draggable knob (an invisible [data-ars-seek] range on top seeks), play / pause, the queue
 * button and a button that restores the full bar. The full bar collapses through the button right of its queue button
 * (player/parts/collapse.php). The collapsed state (data-ars-mini on the player root) is kept in localStorage.
 * On wider screens it never shows and the full bar is used. Visibility rules: base styles in GlobalPlayer.php.
 * With the "Progress Bar & Spectrum" setting on spectrum, a radial spectrum (like the aurora prototype's) rings the
 * cover: its canvas overflows the cover's 40px slot into the row's padding, so the row keeps its height.
 *
 * Included by the skin templates, each passing its own look:
 *   $handle  Classes of the queue and restore buttons (text color).
 *   $cover   Classes of the cover (background).
 *   $track   Classes of the progress track.
 *   $fill    Classes of the played part and the knob.
 *   $play    Classes of the play / pause button.
 *
 * Themes can override this file with {theme}/a-ripple-song/player/parts/mini.php.
 * Every skin stylesheet scans this file, so its utility classes exist in all of them.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Icon;
use Jiejia\ARippleSong\Settings\PlayerSetting;

$handle ??= 'text-base-content/60 hover:bg-base-content/10 hover:text-base-content';
$cover ??= 'bg-base-300';
$spectrum ??= (new PlayerSetting())->progressStyle() === 'spectrum';
$track ??= 'bg-base-content/15';
$fill ??= 'bg-primary';
$play ??= 'bg-primary text-primary-content';
?>
<div class="min-w-0 items-center gap-3 p-2 text-base-content" data-ars-mini-bar>
  <span class="relative size-10 shrink-0">
    <?php if ($spectrum) : ?>
      <span class="absolute inset-[3px] rounded-full bg-primary opacity-30 blur-md" aria-hidden="true"></span>
      <canvas data-spectrum="radial" data-inner="0.64" data-bar="1.5" data-gap="1.5" class="pointer-events-none absolute -left-2 -top-2 block size-14" aria-hidden="true"></canvas>
    <?php endif; ?>
    <a class="absolute <?php echo $spectrum ? 'inset-[3px]' : 'inset-0'; ?> grid place-items-center overflow-hidden rounded-full <?php echo esc_attr($cover); ?>" data-ars-href="url">
      <img class="size-full object-cover" alt="" data-ars-src="cover" data-ars-show="cover">
      <span class="opacity-50" data-ars-show="nocover"><?php Icon::render('podcast', 'size-4'); ?></span>
    </a>
  </span>
  <span class="min-w-0 flex-1">
    <a class="block truncate text-sm font-semibold leading-5 hover:text-primary" data-ars-href="url" data-ars-text="title"></a>
    <span class="relative mt-2 mr-1.5 block h-1 rounded-full <?php echo esc_attr($track); ?>">
      <span class="relative block h-full w-0 rounded-full <?php echo esc_attr($fill); ?>" data-ars-progress>
        <span class="absolute right-0 top-1/2 size-3 -translate-y-1/2 translate-x-1/2 rounded-full shadow ring-2 ring-base-100 <?php echo esc_attr($fill); ?>" aria-hidden="true"></span>
      </span>
      <input type="range" class="ars-mini-seek" min="0" max="0" step="1" value="0" data-ars-seek aria-label="<?php esc_attr_e('Playback position', 'a-ripple-song'); ?>">
    </span>
  </span>
  <button type="button" class="grid size-10 shrink-0 place-items-center rounded-full <?php echo esc_attr($play); ?>" data-ars-action="toggle" aria-label="<?php esc_attr_e('Play', 'a-ripple-song'); ?>">
    <span data-ars-show="paused"><?php Icon::render('play', 'ml-0.5 size-4 fill-current'); ?></span>
    <span data-ars-show="playing" hidden><?php Icon::render('pause', 'size-4 fill-current'); ?></span>
    <span class="loading loading-spinner loading-sm" data-ars-show="loading" hidden></span>
  </button>
  <span class="-ml-1 flex shrink-0 items-center">
    <button type="button" class="grid size-8 place-items-center rounded-full <?php echo esc_attr($handle); ?>" data-ars-action="panel" aria-expanded="false" aria-label="<?php esc_attr_e('Queue', 'a-ripple-song'); ?>"><?php Icon::render('list-music', 'size-5'); ?></button>
    <button type="button" class="grid size-8 place-items-center rounded-full <?php echo esc_attr($handle); ?>" data-ars-action="mini" aria-expanded="false" aria-label="<?php esc_attr_e('Expand player', 'a-ripple-song'); ?>"><?php Icon::render('chevron-up', 'size-5'); ?></button>
  </span>
</div>
<?php
// phpcs:enable
