<?php
/**
 * Episode meta bar in the "集市" (ars-bazaar) style. The play and favorite buttons are the ones on the
 * prototype's episode page (~/workspace/podcast/bazaar/episode.html), icon only.
 * Rendered by Player\EpisodeMeta; episode/meta/ars-default.php documents the variables.
 * Themes can override this file with {theme}/a-ripple-song/episode/meta/ars-bazaar.php.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Icon;
?>
<div class="ars-player" data-ars-island ars-data="{ id: <?php echo (int) $postId; ?> }">
  <div class="<?php echo $position === 'after' ? 'mt-8' : 'mb-8'; ?> flex flex-wrap items-center gap-3">
    <button type="button" class="btn-black size-12 rounded-full" ars-on:click="toggle(id)" ars-bind:aria-label="isPlaying(id) ? '暂停' : '播放本期'" aria-label="播放本期">
      <span ars-show="!isPlaying(id)"><?php Icon::render('play', 'ml-0.5 size-4 fill-current'); ?></span>
      <span ars-show="isPlaying(id)" ars-cloak><?php Icon::render('pause', 'size-4 fill-current'); ?></span>
    </button>
    <button type="button" class="btn-gray size-10 rounded-full !bg-transparent" ars-bind:class="isFav(id) && 'is-on'" ars-on:click="toggleFav(id)" ars-bind:aria-pressed="isFav(id)" ars-bind:aria-label="isFav(id) ? '取消收藏' : '收藏'" aria-pressed="false" aria-label="收藏">
      <span ars-show="!isFav(id)"><?php Icon::render('heart', 'size-5'); ?></span>
      <span ars-show="isFav(id)" ars-cloak><?php Icon::render('heart', 'size-5 fill-current'); ?></span>
    </button>
    <span class="mute inline-flex items-center gap-1.5 text-sm tabular-nums"><?php Icon::render('headphones', 'size-4'); ?><span<?php echo $playsAttributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by Html::attributes(). ?>><?php echo esc_html($playsText); ?></span></span>
  </div>
</div>
