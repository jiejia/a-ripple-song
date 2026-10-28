<?php
/**
 * Volume button for the player bars. Hovering it, focusing it by keyboard or clicking it opens a popover
 * with the volume slider and a mute toggle. Hidden on phones, where the system volume rules
 * (iOS ignores page volume) and the full-screen panel keeps its own slider.
 *
 * Included by the skin templates, each passing its own look:
 *   $button   Classes of the bar button.
 *   $icon     Classes of the button icon; empty renders the text "vol" / "mute" instead (terminal style).
 *   $popover  Classes of the popover card (background, border, radius, shadow).
 *   $range    Classes of the slider.
 *   $align    Popover position: "center" over the button (default) or "end" (right edges aligned, for a button at the bar's end).
 *
 * Themes can override this file with {theme}/a-ripple-song/player/parts/volume.php.
 * Every skin stylesheet scans this file, so its utility classes exist in all of them.
 */

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Libraries\Icon;

$button ??= 'btn btn-ghost btn-circle btn-sm';
$icon ??= 'size-4';
$popover ??= 'rounded-box border border-base-300 bg-base-100 text-base-content shadow-lg';
$range ??= 'range range-primary range-xs';
$align ??= 'center';
?>
<div class="group/vol relative hidden shrink-0 sm:block" ars-data="{ volumeOpen: false }"
     ars-on:click.window="if (!$el.contains($event.target)) volumeOpen = false" ars-on:keydown.escape.window="volumeOpen = false">
  <button type="button" class="<?php echo esc_attr($button); ?>" ars-on:click="volumeOpen = !volumeOpen" ars-bind:aria-expanded="volumeOpen" aria-expanded="false" aria-label="音量">
    <?php if ($icon === '') : ?>
      <span ars-text="muted ? 'mute' : 'vol'">vol</span>
    <?php else : ?>
      <span ars-show="!muted"><?php Icon::render('volume-2', $icon); ?></span>
      <span ars-show="muted" ars-cloak><?php Icon::render('volume-x', $icon); ?></span>
    <?php endif; ?>
  </button>
  <?php /* pb-3 bridges the gap above the button, so the popover stays open while the pointer moves onto it. */ ?>
  <div class="invisible absolute bottom-full z-10 pb-3 opacity-0 <?php echo $align === 'end' ? 'right-0' : 'left-1/2 -translate-x-1/2'; ?> transition-opacity duration-150 group-hover/vol:visible group-hover/vol:opacity-100 group-has-[:focus-visible]/vol:visible group-has-[:focus-visible]/vol:opacity-100"
       ars-bind:class="volumeOpen && '!visible !opacity-100'">
    <div class="flex items-center gap-2 px-2 py-1.5 <?php echo esc_attr($popover); ?>">
      <button type="button" class="grid size-7 shrink-0 place-items-center rounded-full opacity-80 hover:opacity-100" ars-on:click="toggleMute()" ars-bind:aria-label="muted ? '取消静音' : '静音'" aria-label="静音">
        <span ars-show="!muted"><?php Icon::render('volume-2', 'size-4'); ?></span>
        <span ars-show="muted" ars-cloak><?php Icon::render('volume-x', 'size-4'); ?></span>
      </button>
      <input type="range" class="<?php echo esc_attr($range); ?> w-28" min="0" max="1" step="0.05" ars-bind:value="muted ? 0 : volume" ars-on:input="setVolume($event.target.value)" aria-label="音量">
      <span class="w-8 shrink-0 text-right text-xs tabular-nums opacity-70" ars-text="Math.round((muted ? 0 : volume) * 100)">100</span>
    </div>
  </div>
</div>
