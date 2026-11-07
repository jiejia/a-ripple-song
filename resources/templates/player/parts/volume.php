<?php
/**
 * Volume control for the player bars: a mute toggle and the volume slider side by side, always visible
 * like the default skin's. Hidden below the lg breakpoint, where the system volume rules
 * (iOS ignores page volume) and the full-screen panel keeps its own slider; the slider joins at xl,
 * so the progress bar keeps its room on small laptops.
 *
 * Included by the skin templates, each passing its own look:
 *   $class    Classes of the wrapper (layout, visibility).
 *   $slider   Extra classes of the slider (size, visibility); hidden below @5xl by default.
 *   $button   Classes of the mute button.
 *   $icon     Classes of the button icon; empty renders the text "vol" / "mute" instead (terminal style).
 *   $range    Classes of the slider.
 *
 * Themes can override this file with {theme}/a-ripple-song/player/parts/volume.php.
 * Every skin stylesheet scans this file, so its utility classes exist in all of them.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Icon;

$class ??= 'hidden shrink-0 items-center gap-1 @3xl:flex';
$slider ??= 'hidden w-20 @5xl:block';
$button ??= 'btn btn-ghost btn-circle btn-sm';
$icon ??= 'size-4';
$range ??= 'range range-primary range-xs';
?>
<div class="<?php echo esc_attr($class); ?>">
  <button type="button" class="<?php echo esc_attr($button); ?>" ars-on:click="toggleMute()" ars-bind:aria-label="muted ? '取消静音' : '静音'" aria-label="静音">
    <?php if ($icon === '') : ?>
      <span ars-text="muted ? 'mute' : 'vol'">vol</span>
    <?php else : ?>
      <span ars-show="!muted"><?php Icon::render('volume-2', $icon); ?></span>
      <span ars-show="muted" ars-cloak><?php Icon::render('volume-x', $icon); ?></span>
    <?php endif; ?>
  </button>
  <input type="range" class="<?php echo esc_attr($range); ?> <?php echo esc_attr($slider); ?>" min="0" max="1" step="0.05" ars-bind:value="muted ? 0 : volume" ars-bind:style="`--p:${(muted ? 0 : volume) * 100}%`" ars-on:input="setVolume($event.target.value)" aria-label="音量">
</div>
