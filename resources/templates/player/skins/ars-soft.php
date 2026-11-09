<?php
/**
 * Player skin "软陶" (ars-soft), converted from the ~/workspace/podcast/soft prototype.
 *
 * Bindings use the plugin's small Alpine-like binder (resources/assets/js/player/binder.js):
 * ars-text, ars-show, ars-bind:attr, ars-on:event, ars-for, ars-data, ars-transition:*, ars-cloak.
 * Expressions run against the player store, e.g. toggle(), fmt(position), current?.title.
 * Themes can override this file with {theme}/a-ripple-song/player/skins/ars-soft.php.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Icon;
use Jiejia\ARippleSong\Libraries\Template;
?>
<div class="ars-player" data-ars-player data-ars-skin="ars-soft" hidden>
    <?php /* ============ 播放器：机身面板（常驻） ============ */ ?>
    <div class="fixed inset-x-0 bottom-3 z-[9990] px-3" role="region" data-ars-bar aria-label="音频播放器">
      <div data-ars-width="box" data-ars-surface class="raised mx-auto max-w-5xl rounded-[2rem]">
        <?php (new Template())->renderThemeable('player/parts/mini'); ?>
        <div class="flex flex-wrap items-center gap-x-3 gap-y-2 p-3 @5xl:min-h-20 @5xl:flex-nowrap @5xl:gap-4 @5xl:px-4 @5xl:py-0">
        <?php (new Template())->renderThemeable('player/parts/info', [
            'class' => 'lcd order-1 flex-1 basis-full rounded-2xl p-2 @5xl:order-none @5xl:w-72 @5xl:flex-none @5xl:basis-auto',
            'cover' => 'rounded-xl bg-base-300',
            'title' => '',
        ]); ?>
        <div class="order-5 flex items-center gap-2.5 @5xl:order-none">
          <button type="button" class="raised-sm press grid size-8 place-items-center rounded-full @5xl:size-10" ars-on:click="prev()" aria-label="上一集"><?php Icon::render('skip-back', 'size-4'); ?></button>
          <span class="relative grid size-12 shrink-0 place-items-center @5xl:size-16">
            <svg viewBox="0 0 100 100" class="pointer-events-none absolute inset-0 size-full -rotate-90" aria-hidden="true">
              <circle cx="50" cy="50" r="46" fill="none" stroke-width="5" class="ring-track"></circle>
              <circle cx="50" cy="50" r="46" fill="none" stroke-width="5" stroke-linecap="round" pathLength="100" stroke-dasharray="100" ars-bind:stroke-dashoffset="100 - progress" class="ring-fill"></circle>
            </svg>
            <button type="button" class="key-primary grid size-9 place-items-center @5xl:size-12 rounded-full" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
              <span ars-show="!playing && !loading"><?php Icon::render('play', 'ml-0.5 size-5 fill-current'); ?></span>
              <span ars-show="playing && !loading" ars-cloak><?php Icon::render('pause', 'size-5 fill-current'); ?></span>
              <span class="loading loading-spinner size-5" ars-show="loading" ars-cloak></span>
            </button>
          </span>
          <button type="button" class="raised-sm press grid size-8 place-items-center rounded-full @5xl:size-10" ars-on:click="next()" aria-label="下一集"><?php Icon::render('skip-forward', 'size-4'); ?></button>
        </div>
        <?php
        (new Template())->renderThemeable('player/parts/progress', [
            'class' => 'order-3 flex w-full min-w-0 flex-wrap items-center gap-x-3 gap-y-1 font-mono text-[11px] tabular-nums @5xl:order-none @5xl:w-auto @5xl:flex-1 @5xl:flex-nowrap',
            'spectrumTop' => ['data-spectrum' => 'bars', 'class' => 'block h-8 w-full @5xl:hidden'],
            'time' => 'opacity-60',
            'spectrum' => ['data-spectrum' => 'bars', 'class' => 'hidden h-8 w-24 shrink-0 @5xl:block'],
        ]);
        ?>
        <div class="order-4 flex flex-1 items-center justify-start gap-2 @5xl:order-8 @5xl:flex-none">
          <button type="button" role="slider" aria-label="音量（拖动、滚轮或方向键调节）" aria-valuemin="0" aria-valuemax="100" ars-bind:aria-valuenow="Math.round((muted ? 0 : volume) * 100)"
                  class="knob raised-sm size-9 @5xl:size-12"
                  ars-data="{ y: null, v: 0 }"
                  ars-on:pointerdown="y = $event.clientY; v = volume; $el.setPointerCapture($event.pointerId)"
                  ars-on:pointermove="if (y !== null) setVolume(Math.min(1, Math.max(0, v + (y - $event.clientY) / 150)))"
                  ars-on:pointerup="y = null" ars-on:pointercancel="y = null"
                  ars-on:wheel.prevent="setVolume(Math.min(1, Math.max(0, volume - Math.sign($event.deltaY) * 0.05)))"
                  ars-on:keydown.up.prevent="setVolume(Math.min(1, volume + 0.05))"
                  ars-on:keydown.right.prevent="setVolume(Math.min(1, volume + 0.05))"
                  ars-on:keydown.down.prevent="setVolume(Math.max(0, volume - 0.05))"
                  ars-on:keydown.left.prevent="setVolume(Math.max(0, volume - 0.05))">
            <span class="knob-dial" ars-bind:style="`--angle: ${-135 + (muted ? 0 : volume) * 270}deg`"></span>
          </button>
          <?php /* Narrow bar: the speed sits right of the volume; from @5xl on the one in the group below shows. */ ?>
          <button type="button" class="raised-sm press h-8 w-14 shrink-0 rounded-full px-0 font-mono text-xs @5xl:!hidden" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}×`">1×</button>
        </div>
        <div class="order-6 flex flex-1 items-center justify-end gap-1 @5xl:contents">
          <button type="button" class="@max-5xl:!hidden @5xl:order-7 raised-sm press h-8 w-14 shrink-0 rounded-full px-0 font-mono text-xs @5xl:h-9" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}×`">1×</button>
        <button type="button" class="raised-sm press grid size-8 shrink-0 place-items-center rounded-full @5xl:order-9 @5xl:size-10" ars-on:click="expanded = true" aria-label="播放队列"><?php Icon::render('list-music', 'size-4'); ?></button>
        <?php (new Template())->renderThemeable('player/parts/collapse', ['class' => 'raised-sm press grid size-8 shrink-0 place-items-center rounded-full', 'icon' => 'size-4']); ?>
        </div>
        </div>
      </div>
    </div>

    <?php (new Template())->renderThemeable('player/parts/queue', [
        'panel' => 'raised', 'row' => 'rounded-2xl hover:bg-base-200', 'rowOn' => 'lcd', 'nameOn' => '', 'cover' => 'rounded-xl bg-base-300', 'trash' => 'raised-sm press grid size-9 place-items-center rounded-full',
    ]); ?>
    <?php (new Template())->renderThemeable('player/parts/resume', [
        'panel' => 'raised rounded-[2rem]', 'icon' => 'raised-sm rounded-full text-primary', 'accept' => 'key-primary flex h-11 items-center justify-center gap-2 rounded-full', 'cancel' => 'raised-sm press flex h-11 items-center justify-center gap-2 rounded-full',
    ]); ?>
</div>
