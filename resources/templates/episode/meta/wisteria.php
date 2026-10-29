<?php
/**
 * Episode meta bar in the "紫藤" (wisteria) style. The play and favorite buttons are the ones on the
 * prototype's episode page (~/workspace/podcast/wisteria/episode.html), icon only.
 * Rendered by Player\EpisodeMeta; episode/meta/default.php documents the variables.
 * Themes can override this file with {theme}/a-ripple-song/episode/meta/wisteria.php.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Icon;
?>
<div class="ars-player" data-ars-island ars-data="{ id: <?php echo (int) $postId; ?> }">
  <div class="<?php echo $position === 'after' ? 'mt-8' : 'mb-8'; ?> flex flex-wrap items-center gap-3">
    <button type="button" class="grid size-12 place-items-center rounded-2xl bg-primary text-primary-content shadow-[0_10px_20px_-10px_var(--color-primary)]" ars-on:click="toggle(id)" ars-bind:aria-label="isPlaying(id) ? '暂停' : '播放本期'" aria-label="播放本期">
      <span ars-show="!isPlaying(id)"><?php Icon::render('play', 'ml-0.5 size-5 fill-current'); ?></span>
      <span ars-show="isPlaying(id)" ars-cloak><?php Icon::render('pause', 'size-5 fill-current'); ?></span>
    </button>
    <button type="button" class="grid size-11 place-items-center rounded-full bg-base-100 shadow-[var(--lift)] hover:text-primary" ars-bind:class="isFav(id) && 'text-primary'" ars-on:click="toggleFav(id)" ars-bind:aria-pressed="isFav(id)" ars-bind:aria-label="isFav(id) ? '取消收藏' : '收藏'" aria-pressed="false" aria-label="收藏">
      <span ars-show="!isFav(id)"><?php Icon::render('bookmark', 'size-5'); ?></span>
      <span ars-show="isFav(id)" ars-cloak><?php Icon::render('bookmark', 'size-5 fill-current'); ?></span>
    </button>
    <span class="mute text-sm inline-flex items-center gap-1.5"><?php Icon::render('headphones', 'size-4'); ?><span<?php echo $playsAttributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by Html::attributes(). ?>><?php echo esc_html($playsText); ?></span></span>
  </div>
</div>
