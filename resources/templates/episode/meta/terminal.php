<?php
/**
 * Episode meta bar in the "终端" (terminal) style. The play and favorite buttons are the [ … ] commands on the
 * prototype's episode page (~/workspace/podcast/terminal/episode.html), symbols only.
 * Rendered by Player\EpisodeMeta; episode/meta/default.php documents the variables.
 * Themes can override this file with {theme}/a-ripple-song/episode/meta/terminal.php.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.
?>
<div class="ars-player" data-ars-island ars-data="{ id: <?php echo (int) $postId; ?> }">
  <div class="<?php echo $position === 'after' ? 'mt-8' : 'mb-8'; ?> flex flex-wrap items-center gap-2">
    <button type="button" class="cmd text-primary" ars-bind:class="isPlaying(id) && 'is-on'" ars-on:click="toggle(id)" ars-bind:aria-label="isPlaying(id) ? '暂停' : '播放本期'" aria-label="播放本期" ars-text="isPlaying(id) ? '❚❚' : '▶'">▶</button>
    <button type="button" class="cmd" ars-bind:class="isFav(id) && 'text-accent'" ars-on:click="toggleFav(id)" ars-bind:aria-pressed="isFav(id)" ars-bind:aria-label="isFav(id) ? '取消收藏' : '收藏'" aria-pressed="false" aria-label="收藏" ars-text="isFav(id) ? '♥' : '♡'">♡</button>
    <span class="dim"># <span<?php echo $playsAttributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by Html::attributes(). ?>><?php echo esc_html($playsText); ?></span></span>
  </div>
</div>
