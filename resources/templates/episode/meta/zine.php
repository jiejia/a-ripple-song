<?php
/**
 * Episode meta bar in the "拼贴" (zine) style. The play and favorite buttons are the ones on the
 * prototype's episode page (~/workspace/podcast/zine/episode.html), icon only.
 * Rendered by Player\EpisodeMeta; episode/meta/default.php documents the variables.
 * Themes can override this file with {theme}/a-ripple-song/episode/meta/zine.php.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Icon;
?>
<div class="ars-player" data-ars-island ars-data="{ id: <?php echo (int) $postId; ?> }">
  <div class="<?php echo $position === 'after' ? 'mt-8' : 'mb-8'; ?> flex flex-wrap items-center gap-3">
    <button type="button" class="btn btn-lg btn-circle border-2 ink bg-primary text-primary-content shadow-[4px_4px_0_var(--color-base-content)] hover:bg-primary active:translate-x-1 active:translate-y-1 active:shadow-none" ars-on:click="toggle(id)" ars-bind:aria-label="isPlaying(id) ? '暂停' : '播放本期'" aria-label="播放本期">
      <span ars-show="!isPlaying(id)"><?php Icon::render('play', 'ml-0.5 size-5 fill-current'); ?></span>
      <span ars-show="isPlaying(id)" ars-cloak><?php Icon::render('pause', 'size-5 fill-current'); ?></span>
    </button>
    <button type="button" class="btn btn-circle btn-lg border-2 ink bg-base-100" ars-on:click="toggleFav(id)" ars-bind:aria-pressed="isFav(id)" ars-bind:aria-label="isFav(id) ? '取消收藏' : '收藏'" aria-pressed="false" aria-label="收藏">
      <span ars-show="!isFav(id)"><?php Icon::render('heart', 'size-5'); ?></span>
      <span ars-show="isFav(id)" ars-cloak class="text-primary"><?php Icon::render('heart', 'size-5 fill-current'); ?></span>
    </button>
    <span class="font-hand text-xl text-primary inline-flex items-center gap-1.5"><?php Icon::render('headphones', 'size-4'); ?><span<?php echo $playsAttributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by Html::attributes(). ?>><?php echo esc_html($playsText); ?></span></span>
  </div>
</div>
