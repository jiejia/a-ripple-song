<?php
/**
 * Play queue drawer: a daisyUI drawer that slides in from the page's left edge, shared by every skin.
 * Printed inside [data-ars-player], next to the bar; the bar's list button opens it (`expanded = true`).
 *
 * player.js keeps the checkbox [data-ars-drawer] in step with the store's `expanded` flag
 * (also closes it on Esc and when a link inside it is followed).
 * Rows use the binder's ars-* expressions, so the skin's own colors carry over through the daisyUI tokens.
 *
 * Each skin passes its own look so the drawer matches its player bar (all optional):
 *   $theme    "dark" pins the drawer to the dark palette (skins whose bar is always dark).
 *   $panel    Classes of the drawer panel (background, border, shadow).
 *   $divider  Classes of the line under the title.
 *   $title    Classes of the "Playlist" title.
 *   $meta     Classes of the "Total N episodes" / "Clear" line.
 *   $clear    Classes of the "Clear" button.
 *   $row      Classes of an episode row; $rowOn those added to the current one.
 *   $cover    Classes of the cover image.
 *   $name     Classes of the episode title; $nameOn those added to the current one.
 *   $sub      Classes of the "category · duration" line.
 *   $trash    Classes of the hover-only remove button.
 *
 * Themes can override this file with {theme}/a-ripple-song/player/parts/queue.php.
 * Every skin stylesheet scans this file, so its utility classes exist in all of them.
 */

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Libraries\Icon;

$theme ??= '';
$panel ??= 'border-r border-base-300 bg-base-100 shadow-2xl';
$divider ??= 'border-t border-base-300';
$title ??= 'px-4 py-3 text-base font-bold';
$meta ??= 'opacity-70';
$clear ??= 'font-semibold hover:opacity-100';
$row ??= 'rounded-2xl hover:bg-base-200';
$rowOn ??= 'bg-base-200';
$cover ??= 'rounded-xl bg-base-300';
$name ??= 'font-semibold';
$nameOn ??= 'text-primary';
$sub ??= 'opacity-60';
$trash ??= 'btn btn-ghost btn-circle btn-sm';
?>
<div class="drawer ars-queue-drawer">
  <input id="ars-queue-drawer" type="checkbox" class="drawer-toggle" data-ars-drawer aria-label="<?php esc_attr_e('Playlist', 'a-ripple-song'); ?>">
  <?php /* Topmost layer, full viewport height: above the theme's header and the WordPress admin bar (z-index 99999),
     which scrolls away on narrow screens and would otherwise leave a gap above the drawer. */ ?>
  <div class="drawer-side z-[100001]">
    <label for="ars-queue-drawer" aria-label="<?php esc_attr_e('Close', 'a-ripple-song'); ?>" class="drawer-overlay"></label>
    <div class="h-full w-72 max-w-[85vw] text-base-content"<?php echo $theme !== '' ? ' data-ars-theme="' . esc_attr($theme) . '"' : ''; ?> role="dialog" aria-label="<?php esc_attr_e('Playlist', 'a-ripple-song'); ?>">
      <div class="flex h-full flex-col overflow-hidden <?php echo esc_attr($panel); ?>">
        <h2 class="<?php echo esc_attr($title); ?>"><?php esc_html_e('Playlist', 'a-ripple-song'); ?></h2>
        <section class="flex min-h-0 flex-1 flex-col px-2 pb-2 <?php echo esc_attr($divider); ?>">
          <header class="px-2">
            <p class="flex items-center justify-between gap-2 py-2.5 text-xs <?php echo esc_attr($meta); ?>">
              <span class="tabular-nums"><?php
              // translators: %s: number of episodes in the playlist.
              echo wp_kses(sprintf(__('Total %s episodes', 'a-ripple-song'), '<span ars-text="queueItems.length"></span>'), ['span' => ['ars-text' => true]]);
              ?></span>
              <button type="button" class="flex items-center gap-1.5 <?php echo esc_attr($clear); ?>" ars-show="queueItems.length" ars-on:click="confirmClear = true"><?php Icon::render('trash-2', 'size-3.5'); ?><?php esc_html_e('Clear', 'a-ripple-song'); ?></button>
            </p>
          </header>
          <?php /* "Clear" asks first in a modal: player.js opens it while `confirmClear` is set (showModal puts it in the top layer)
             and resets the flag when it closes (Esc, backdrop, Cancel). Closing the drawer takes the question back too. */ ?>
          <dialog class="modal" data-ars-clear-dialog aria-labelledby="ars-clear-title">
            <div class="modal-box max-w-xs text-base-content">
              <h3 id="ars-clear-title" class="text-base font-bold"><?php esc_html_e('Clear the playlist?', 'a-ripple-song'); ?></h3>
              <div class="modal-action mt-5">
                <button type="button" class="btn btn-ghost btn-sm" ars-on:click="confirmClear = false"><?php esc_html_e('Cancel', 'a-ripple-song'); ?></button>
                <button type="button" class="btn btn-error btn-sm" ars-on:click="confirmClear = false; clearQueue()"><?php esc_html_e('Clear', 'a-ripple-song'); ?></button>
              </div>
            </div>
            <form method="dialog" class="modal-backdrop"><button><?php esc_html_e('Close', 'a-ripple-song'); ?></button></form>
          </dialog>
          <ul class="min-h-0 flex-1 space-y-1 overflow-y-auto overscroll-contain [scrollbar-width:thin]">
            <template ars-for="ep in queueItems">
              <li class="group relative">
                <button type="button" class="flex w-full min-w-0 items-center gap-2.5 p-1.5 pr-10 text-left <?php echo esc_attr($row); ?>" ars-bind:class="isCurrent(ep.id) && '<?php echo esc_attr($rowOn); ?>'" ars-on:click="toggle(ep.id)">
                  <img ars-bind:src="ep.cover" alt="" class="size-11 shrink-0 object-cover <?php echo esc_attr($cover); ?>">
                  <span class="min-w-0 flex-1">
                    <span class="block truncate text-sm <?php echo esc_attr($name); ?>" ars-bind:class="isCurrent(ep.id) && '<?php echo esc_attr($nameOn); ?>'" ars-text="ep.title"></span>
                    <span class="block truncate text-xs <?php echo esc_attr($sub); ?>" ars-text="join(categoryName(ep.category), fmt(ep.duration))"></span>
                  </span>
                </button>
                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center group-hover:hidden group-focus-within:hidden [@media(hover:none)]:hidden" ars-show="isPlaying(ep.id)" ars-cloak><span class="loading loading-bars loading-xs text-primary"></span></span>
                <button type="button" class="<?php echo esc_attr($trash); ?> absolute top-1/2 right-1 -translate-y-1/2 opacity-0 transition group-hover:opacity-100 focus-visible:opacity-100 [@media(hover:none)]:opacity-100" ars-on:click="removeFromQueue(ep.id)" aria-label="<?php esc_attr_e('Remove from queue', 'a-ripple-song'); ?>" title="<?php esc_attr_e('Remove from queue', 'a-ripple-song'); ?>"><?php Icon::render('trash-2', 'size-3.5'); ?></button>
              </li>
            </template>
          </ul>
          <p class="py-8 text-center text-xs opacity-60" ars-show="!queueItems.length" ars-cloak><?php esc_html_e('The queue is empty.', 'a-ripple-song'); ?></p>
        </section>
      </div>
    </div>
  </div>
</div>
