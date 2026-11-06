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
$title ??= 'px-5 py-4 text-xl font-bold';
$meta ??= 'text-sm opacity-70';
$clear ??= 'font-semibold hover:opacity-100';
$row ??= 'rounded-2xl hover:bg-base-200';
$rowOn ??= 'bg-base-200';
$cover ??= 'rounded-xl bg-base-300';
$name ??= 'font-semibold';
$nameOn ??= 'text-primary';
$sub ??= 'text-sm opacity-60';
$trash ??= 'btn btn-ghost btn-circle btn-sm';
?>
<div class="drawer ars-queue-drawer">
  <input id="ars-queue-drawer" type="checkbox" class="drawer-toggle" data-ars-drawer aria-label="<?php esc_attr_e('Playlist', 'a-ripple-song'); ?>">
  <div class="drawer-side z-[9992] top-[var(--wp-admin--admin-bar--height,0px)] h-[calc(100dvh-var(--wp-admin--admin-bar--height,0px))]">
    <label for="ars-queue-drawer" aria-label="<?php esc_attr_e('Close', 'a-ripple-song'); ?>" class="drawer-overlay"></label>
    <div class="h-full w-[min(24rem,92vw)] text-base-content"<?php echo $theme !== '' ? ' data-ars-theme="' . esc_attr($theme) . '"' : ''; ?> role="dialog" aria-label="<?php esc_attr_e('Playlist', 'a-ripple-song'); ?>">
      <div class="flex h-full flex-col overflow-hidden <?php echo esc_attr($panel); ?>">
        <h2 class="<?php echo esc_attr($title); ?>"><?php esc_html_e('Playlist', 'a-ripple-song'); ?></h2>
        <section class="flex min-h-0 flex-1 flex-col px-3 pb-3 <?php echo esc_attr($divider); ?>">
          <header class="px-2">
            <p class="flex items-center justify-between gap-2 py-3 <?php echo esc_attr($meta); ?>">
              <span class="tabular-nums"><?php
              // translators: %s: number of episodes in the playlist.
              echo wp_kses(sprintf(__('Total %s episodes', 'a-ripple-song'), '<span ars-text="queueItems.length"></span>'), ['span' => ['ars-text' => true]]);
              ?></span>
              <button type="button" class="flex items-center gap-1.5 <?php echo esc_attr($clear); ?>" ars-show="queueItems.length" ars-on:click="clearQueue()"><?php Icon::render('trash-2', 'size-4'); ?><?php esc_html_e('Clear', 'a-ripple-song'); ?></button>
            </p>
          </header>
          <ul class="min-h-0 flex-1 space-y-1 overflow-y-auto overscroll-contain [scrollbar-width:thin]">
            <template ars-for="ep in queueItems">
              <li class="group relative">
                <button type="button" class="flex w-full min-w-0 items-center gap-3 p-2 pr-12 text-left <?php echo esc_attr($row); ?>" ars-bind:class="isCurrent(ep.id) && '<?php echo esc_attr($rowOn); ?>'" ars-on:click="toggle(ep.id)">
                  <img ars-bind:src="ep.cover" alt="" class="size-14 shrink-0 object-cover <?php echo esc_attr($cover); ?>">
                  <span class="min-w-0 flex-1">
                    <span class="block truncate <?php echo esc_attr($name); ?>" ars-bind:class="isCurrent(ep.id) && '<?php echo esc_attr($nameOn); ?>'" ars-text="ep.title"></span>
                    <span class="block truncate <?php echo esc_attr($sub); ?>" ars-text="join(categoryName(ep.category), fmt(ep.duration))"></span>
                  </span>
                </button>
                <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center group-hover:hidden group-focus-within:hidden [@media(hover:none)]:hidden" ars-show="isPlaying(ep.id)" ars-cloak><span class="loading loading-bars loading-sm text-primary"></span></span>
                <button type="button" class="<?php echo esc_attr($trash); ?> absolute top-1/2 right-2 -translate-y-1/2 opacity-0 transition group-hover:opacity-100 focus-visible:opacity-100 [@media(hover:none)]:opacity-100" ars-on:click="removeFromQueue(ep.id)" aria-label="<?php esc_attr_e('Remove from queue', 'a-ripple-song'); ?>" title="<?php esc_attr_e('Remove from queue', 'a-ripple-song'); ?>"><?php Icon::render('trash-2', 'size-4'); ?></button>
              </li>
            </template>
          </ul>
          <p class="py-8 text-center text-sm opacity-60" ars-show="!queueItems.length" ars-cloak><?php esc_html_e('The queue is empty.', 'a-ripple-song'); ?></p>
        </section>
      </div>
    </div>
  </div>
</div>
