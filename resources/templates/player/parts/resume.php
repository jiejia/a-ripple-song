<?php
/**
 * "Continue playing?" offer, shared by every skin: shown at the very bottom of the window, over the bar, after a page load when the episode was
 * playing as the last page went away. Browsers block audio that starts without a click, so the visitor decides.
 * The store runs it (resumeOffer, resumeLeft, acceptResume(), dismissResume()); when time runs out it just goes away.
 *
 * Each skin passes its own look (all optional):
 *   $theme      "dark" pins the card to the dark palette (skins whose bar is always dark).
 *   $panel      Classes of the card (background, border, radius, shadow).
 *   $icon       Classes of the round icon on the left.
 *   $heading    Classes of the "Continue playing?" line.
 *   $accept     Classes of the "Play" button.
 *   $cancel     Classes of the "Cancel" button.
 *
 * Themes can override this file with {theme}/a-ripple-song/player/parts/resume.php.
 * Every skin stylesheet scans this file, so its utility classes exist in all of them.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Icon;

$theme ??= '';
$panel ??= 'rounded-box border border-base-300 bg-base-100 shadow-xl';
$icon ??= 'rounded-full bg-primary/10 text-primary';
$heading ??= 'font-semibold';
$accept ??= 'btn btn-primary';
$cancel ??= 'btn btn-soft';
?>
<div class="fixed bottom-3 left-1/2 z-[9991] w-[min(26rem,calc(100vw-1.5rem))] -translate-x-1/2 p-4 text-base-content <?php echo esc_attr($panel); ?>"<?php echo $theme !== '' ? ' data-ars-theme="' . esc_attr($theme) . '"' : ''; ?> role="dialog" aria-labelledby="ars-resume-title"
     ars-show="resumeOffer" ars-cloak
     ars-transition:enter="transition duration-200 ease-out" ars-transition:enter-start="translate-y-2 opacity-0" ars-transition:enter-end="translate-y-0 opacity-100"
     ars-transition:leave="transition duration-150 ease-in" ars-transition:leave-start="opacity-100" ars-transition:leave-end="opacity-0">
  <div class="flex items-center gap-3">
    <span class="grid size-12 shrink-0 place-items-center <?php echo esc_attr($icon); ?>"><?php Icon::render('circle-play', 'size-6'); ?></span>
    <div class="min-w-0 flex-1">
      <div class="flex items-baseline justify-between gap-2">
        <p id="ars-resume-title" class="<?php echo esc_attr($heading); ?>"><?php esc_html_e('Continue playing?', 'a-ripple-song'); ?></p>
        <span class="shrink-0 text-sm tabular-nums opacity-60" ars-text="`${resumeLeft}s`"></span>
      </div>
      <p class="truncate text-sm opacity-60" ars-text="current?.title"></p>
    </div>
  </div>
  <div class="mt-3 grid grid-cols-2 gap-2">
    <button type="button" class="<?php echo esc_attr($accept); ?>" ars-on:click="acceptResume()"><?php Icon::render('play', 'size-4'); ?><?php esc_html_e('Play', 'a-ripple-song'); ?></button>
    <button type="button" class="<?php echo esc_attr($cancel); ?>" ars-on:click="dismissResume()"><?php Icon::render('x', 'size-4'); ?><?php esc_html_e('Cancel', 'a-ripple-song'); ?></button>
  </div>
</div>
