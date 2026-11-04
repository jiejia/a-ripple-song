<?php
/**
 * Episode meta bar in the "Default" style: icon play and favorite buttons plus the play count.
 * Added to single episode pages by Player\EpisodeMeta, in the style of the selected player skin
 * (episode/meta/{skin}.php), while the global player is on (otherwise episode/audio.php is used).
 *
 * Themes can override this file with {theme}/a-ripple-song/episode/meta/{skin}.php. The root carries the
 * player's `.ars-player` class and data-ars-island: player.js binds its ars-* attributes to the player store
 * like a skin (see resources/assets/js/player/binder.js) and gives it the player's light / dark scheme.
 *
 * @var int    $postId          Episode ID (the ars-data `id`).
 * @var string $position        "before" or "after" the content.
 * @var string $playsText       Play count text, e.g. "1,234 plays".
 * @var string $playsAttributes Escaped attributes of the play count element; player.js updates its text after a
 *                              counted play (data-ars-plays, data-ars-plays-one / -other).
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Icon;
?>
<div class="ars-player" data-ars-island ars-data="{ id: <?php echo (int) $postId; ?> }">
  <div class="<?php echo $position === 'after' ? 'mt-8' : 'mb-8'; ?> flex flex-wrap items-center gap-2 text-base-content">
    <button type="button" class="btn btn-primary btn-circle" ars-on:click="toggle(id)" ars-bind:aria-label="isPlaying(id) ? '<?php echo esc_js(__('Pause', 'a-ripple-song')); ?>' : '<?php echo esc_js(__('Play episode', 'a-ripple-song')); ?>'" aria-label="<?php esc_attr_e('Play episode', 'a-ripple-song'); ?>">
      <span ars-show="!isPlaying(id)"><?php Icon::render('play', 'ml-0.5 size-5 fill-current'); ?></span>
      <span ars-show="isPlaying(id)" ars-cloak><?php Icon::render('pause', 'size-5 fill-current'); ?></span>
    </button>
    <button type="button" class="btn btn-ghost btn-circle" ars-on:click="toggleFav(id)" ars-bind:aria-pressed="isFav(id)" ars-bind:aria-label="isFav(id) ? '<?php echo esc_js(__('Remove from favorites', 'a-ripple-song')); ?>' : '<?php echo esc_js(__('Add to favorites', 'a-ripple-song')); ?>'" aria-pressed="false" aria-label="<?php esc_attr_e('Add to favorites', 'a-ripple-song'); ?>">
      <span ars-show="!isFav(id)"><?php Icon::render('heart', 'size-5'); ?></span>
      <span ars-show="isFav(id)" ars-cloak class="text-primary"><?php Icon::render('heart', 'size-5 fill-current'); ?></span>
    </button>
    <span class="ml-1 inline-flex items-center gap-1.5 text-sm opacity-70"><?php Icon::render('headphones', 'size-4'); ?><span<?php echo $playsAttributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by Html::attributes(). ?>><?php echo esc_html($playsText); ?></span></span>
  </div>
</div>
