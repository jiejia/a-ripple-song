<?php
/**
 * Player skin "软陶" (soft), converted from the ~/workspace/podcast/soft prototype.
 *
 * Bindings use the plugin's small Alpine-like binder (resources/assets/js/player/binder.js):
 * ars-text, ars-show, ars-bind:attr, ars-on:event, ars-for, ars-data, ars-transition:*, ars-cloak.
 * Expressions run against the player store, e.g. toggle(), fmt(position), current?.title.
 * Themes can override this file with {theme}/a-ripple-song/player/skins/soft.php.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Icon;
use Jiejia\ARippleSong\Libraries\Template;
?>
<div class="ars-player" data-ars-player data-ars-skin="soft" hidden>
    <?php /* ============ 播放器：机身面板（常驻） ============ */ ?>
    <div class="fixed inset-x-0 bottom-3 z-[9990] px-3" role="region" data-ars-bar aria-label="音频播放器">
      <div data-ars-width="box" data-ars-surface class="raised mx-auto flex h-20 max-w-5xl items-center gap-3 rounded-[2rem] px-3 sm:gap-4 sm:px-4">
        <button type="button" class="lcd flex h-14 min-w-0 flex-1 flex-col justify-center rounded-2xl px-4 text-left md:w-64 md:flex-none" ars-on:click="expanded = true" aria-label="展开正在播放">
          <span class="flex items-center justify-between gap-2 text-[11px] uppercase tracking-wider">
            <span ars-text="error || `EP.${current?.number} ${loading ? 'LOAD' : (playing ? '▶ PLAY' : '❚❚ PAUSE')}`"></span>
            <span class="tabular-nums" ars-text="fmt(position)">00:00</span>
          </span>
          <span class="mt-0.5 block truncate text-sm" ars-text="current?.title"></span>
        </button>
        <div class="flex items-center gap-2.5">
          <button type="button" class="raised-sm press hidden size-10 place-items-center rounded-full sm:grid" ars-on:click="skip(-15)" aria-label="后退 15 秒"><?php Icon::render('rotate-ccw', 'size-4'); ?></button>
          <span class="relative grid size-16 shrink-0 place-items-center">
            <svg viewBox="0 0 100 100" class="absolute inset-0 size-full -rotate-90" aria-hidden="true">
              <circle cx="50" cy="50" r="46" fill="none" stroke-width="5" class="ring-track"></circle>
              <circle cx="50" cy="50" r="46" fill="none" stroke-width="5" stroke-linecap="round" pathLength="100" stroke-dasharray="100" ars-bind:stroke-dashoffset="100 - progress" class="ring-fill"></circle>
            </svg>
            <button type="button" class="key-primary grid size-12 place-items-center rounded-full" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
              <span ars-show="!playing"><?php Icon::render('play', 'ml-0.5 size-5 fill-current'); ?></span>
              <span ars-show="playing" ars-cloak><?php Icon::render('pause', 'size-5 fill-current'); ?></span>
            </button>
          </span>
          <button type="button" class="raised-sm press hidden size-10 place-items-center rounded-full sm:grid" ars-on:click="skip(30)" aria-label="快进 30 秒"><?php Icon::render('rotate-cw', 'size-4'); ?></button>
          <button type="button" class="raised-sm press hidden size-10 place-items-center rounded-full sm:grid" ars-on:click="next()" aria-label="下一集"><?php Icon::render('skip-forward', 'size-4'); ?></button>
        </div>
        <?php
        (new Template())->renderThemeable('player/parts/progress', [
            'class' => 'hidden min-w-0 flex-1 items-center gap-3 font-mono text-[11px] tabular-nums md:flex',
            'time' => 'opacity-60',
            'spectrum' => ['data-spectrum' => 'bars', 'class' => 'hidden h-8 w-24 shrink-0 xl:block'],
        ]);
        ?>
        <div class="hidden items-center gap-3 lg:flex">
          <button type="button" class="raised-sm press h-9 rounded-full px-3 font-mono text-xs" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}×`">1×</button>
          <button type="button" role="slider" aria-label="音量（拖动、滚轮或方向键调节）" aria-valuemin="0" aria-valuemax="100" ars-bind:aria-valuenow="Math.round((muted ? 0 : volume) * 100)"
                  class="knob raised-sm size-12"
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
        </div>
        <button type="button" class="raised-sm press grid size-10 shrink-0 place-items-center rounded-full" ars-on:click="expanded = true" aria-label="展开播放器与播放队列"><?php Icon::render('chevron-up', 'size-4'); ?></button>
      </div>
    </div>

    <?php /* ============ 正在播放（展开面板）：整台收音机 ============ */ ?>
    <div ars-show="expanded" ars-cloak
         ars-transition:enter="transition duration-250 ease-out" ars-transition:enter-start="opacity-0 translate-y-6" ars-transition:enter-end="opacity-100 translate-y-0"
         ars-transition:leave="transition duration-150 ease-in" ars-transition:leave-start="opacity-100" ars-transition:leave-end="opacity-0"
         ars-on:keydown.escape.window="expanded = false"
         class="fixed inset-0 z-[9991] overflow-y-auto bg-base-100" role="dialog" aria-modal="true" aria-label="正在播放">
      <div data-ars-panel-width="content" class="mx-auto max-w-6xl px-4 py-6 sm:px-6">
        <div class="flex items-center justify-between">
          <p class="text-xs font-bold uppercase tracking-widest opacity-60">Now Playing</p>
          <button type="button" class="raised-sm press grid size-11 place-items-center rounded-full" ars-on:click="expanded = false" aria-label="收起"><?php Icon::render('chevron-down', 'size-5'); ?></button>
        </div>
        <div class="mt-6 grid grid-cols-1 gap-10 lg:grid-cols-[1.3fr_1fr]">
          <div class="raised min-w-0 rounded-[2.5rem] p-6 sm:p-8">
            <div class="grid grid-cols-1 items-center gap-6 sm:grid-cols-[10rem_1fr]">
              <div class="inset grille mx-auto aspect-square w-40 rounded-full" aria-hidden="true"></div>
              <div class="lcd min-w-0 rounded-2xl p-4">
                <p class="flex justify-between text-xs uppercase tracking-wider"><span ars-text="'EP.' + current?.number + (playing ? ' ▶ PLAY' : ' ❚❚ PAUSE')"></span><span ars-text="`${rate}×`"></span></p>
                <p class="mt-2 truncate text-lg" ars-text="current?.title"></p>
                <canvas data-spectrum="led" data-bar="4" data-gap="2" class="mt-3 block h-16 w-full" aria-hidden="true"></canvas>
                <p class="mt-2 flex justify-between text-xs tabular-nums"><span ars-text="fmt(position)"></span><span ars-text="fmt(duration)"></span></p>
              </div>
            </div>
            <input type="range" class="range range-primary range-sm mt-8 w-full" min="0" ars-bind:max="duration" step="1" ars-bind:value="position" ars-on:input="scrub($event.target.value)" ars-on:change="commit($event.target.value)" aria-label="播放进度">
            <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
              <button type="button" class="raised-sm press grid size-12 place-items-center rounded-full" ars-on:click="prev()" aria-label="上一集"><?php Icon::render('skip-back', 'size-5'); ?></button>
              <button type="button" class="raised-sm press grid size-12 place-items-center rounded-full" ars-on:click="skip(-15)" aria-label="后退 15 秒"><?php Icon::render('rotate-ccw', 'size-5'); ?></button>
              <span class="relative grid size-24 place-items-center">
                <svg viewBox="0 0 100 100" class="absolute inset-0 size-full -rotate-90" aria-hidden="true">
                  <circle cx="50" cy="50" r="46" fill="none" stroke-width="4" class="ring-track"></circle>
                  <circle cx="50" cy="50" r="46" fill="none" stroke-width="4" stroke-linecap="round" pathLength="100" stroke-dasharray="100" ars-bind:stroke-dashoffset="100 - progress" class="ring-fill"></circle>
                </svg>
                <button type="button" class="key-primary grid size-[4.5rem] place-items-center rounded-full" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
                  <span ars-show="!playing"><?php Icon::render('play', 'ml-1 size-7 fill-current'); ?></span>
                  <span ars-show="playing" ars-cloak><?php Icon::render('pause', 'size-7 fill-current'); ?></span>
                </button>
              </span>
              <button type="button" class="raised-sm press grid size-12 place-items-center rounded-full" ars-on:click="skip(30)" aria-label="快进 30 秒"><?php Icon::render('rotate-cw', 'size-5'); ?></button>
              <button type="button" class="raised-sm press grid size-12 place-items-center rounded-full" ars-on:click="next()" aria-label="下一集"><?php Icon::render('skip-forward', 'size-5'); ?></button>
            </div>
            <div class="mt-8 flex items-center justify-center gap-8">
              <div class="text-center">
                <button type="button" class="raised-sm press h-11 rounded-full px-5 font-mono text-sm" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}×`">1×</button>
                <p class="mt-2 text-[11px] font-bold uppercase tracking-widest opacity-60">速度</p>
              </div>
              <div class="text-center">
                <button type="button" role="slider" aria-label="音量（拖动、滚轮或方向键调节）" aria-valuemin="0" aria-valuemax="100" ars-bind:aria-valuenow="Math.round((muted ? 0 : volume) * 100)"
                        class="knob raised size-20"
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
                <p class="mt-2 text-[11px] font-bold uppercase tracking-widest opacity-60">音量 <span class="font-mono" ars-text="Math.round((muted ? 0 : volume) * 100)"></span></p>
              </div>
            </div>
          </div>
          <section class="inset min-w-0 rounded-[2rem] p-4 sm:p-5" aria-labelledby="queue-title">
            <div class="flex items-center justify-between gap-2"><h3 id="queue-title" class="px-2 py-1 text-xs font-bold uppercase tracking-widest opacity-60">播放队列</h3><button type="button" class="px-2 py-1 text-xs font-bold tracking-widest opacity-60 hover:opacity-100" ars-show="queueItems.length" ars-on:click="clearQueue()">清空</button></div>
            <ul class="mt-2 space-y-2 max-h-[min(32rem,60dvh)] overflow-y-auto overscroll-contain [scrollbar-width:thin] -mx-1 px-1 py-1">
              <template ars-for="ep in queueItems">
                <li class="flex items-center gap-1">
                  <button type="button" class="flex min-w-0 flex-1 items-center gap-3 rounded-2xl p-2 text-left" ars-bind:class="isCurrent(ep.id) ? 'raised-sm text-primary' : 'hover:text-primary'" ars-on:click="toggle(ep.id)">
                    <img ars-bind:src="ep.cover" alt="" class="size-11 shrink-0 rounded-xl">
                    <span class="min-w-0 flex-1 truncate font-bold" ars-text="ep.title"></span>
                    <span class="shrink-0 font-mono text-xs opacity-70" ars-text="isPlaying(ep.id) ? '▶' : fmt(ep.duration)"></span>
                  </button>
                  <button type="button" class="shrink-0 rounded-full p-1.5 opacity-50 transition hover:opacity-100" ars-on:click="removeFromQueue(ep.id)" aria-label="从播放列表移除" title="从播放列表移除"><?php Icon::render('x', 'size-4'); ?></button>
                </li>
              </template>
            </ul>
            <p class="py-6 text-center text-sm opacity-60" ars-show="!queueItems.length">播放列表是空的</p>
          </section>
        </div>
      </div>
    </div>
</div>
