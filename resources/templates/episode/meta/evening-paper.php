<?php
/**
 * Episode meta bar in the "晚报" (evening-paper) style. The play and favorite buttons are the ones on the
 * prototype's episode page (~/workspace/podcast/evening-paper/episode.html), icon only.
 * Rendered by Player\EpisodeMeta; episode/meta/default.php documents the variables.
 * Themes can override this file with {theme}/a-ripple-song/episode/meta/evening-paper.php.
 */

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Libraries\Icon;
?>
<div class="ars-player" data-ars-island ars-data="{ id: <?php echo (int) $postId; ?> }">
  <div class="<?php echo $position === 'after' ? 'mt-8' : 'mb-8'; ?> flex flex-wrap items-center gap-3">
    <button type="button" class="btn-ring size-14 shrink-0" ars-bind:class="isPlaying(id) && 'is-on'" ars-on:click="toggle(id)" ars-bind:aria-label="isPlaying(id) ? '暂停' : '播放本期'" aria-label="播放本期">
      <span ars-show="!isPlaying(id)"><?php Icon::render('play', 'ml-0.5 size-6 fill-current'); ?></span>
      <span ars-show="isPlaying(id)" ars-cloak><?php Icon::render('pause', 'size-6 fill-current'); ?></span>
    </button>
    <button type="button" class="btn-ring size-14 shrink-0" ars-bind:class="isFav(id) && 'is-on'" ars-on:click="toggleFav(id)" ars-bind:aria-pressed="isFav(id)" ars-bind:aria-label="isFav(id) ? '取消收藏' : '收藏'" aria-pressed="false" aria-label="收藏">
      <span ars-show="!isFav(id)"><?php Icon::render('heart', 'size-5'); ?></span>
      <span ars-show="isFav(id)" ars-cloak><?php Icon::render('heart', 'size-5 fill-current'); ?></span>
    </button>
    <span class="meta inline-flex items-center gap-1.5"><?php Icon::render('headphones', 'size-4'); ?><span<?php echo $playsAttributes; ?>><?php echo esc_html($playsText); ?></span></span>
  </div>
</div>
