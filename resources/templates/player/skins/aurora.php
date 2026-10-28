<?php
/**
 * Player skin "极光" (aurora), converted from the ~/workspace/podcast/aurora prototype.
 *
 * Bindings use the plugin's small Alpine-like binder (resources/src/binder.js):
 * ars-text, ars-show, ars-bind:attr, ars-on:event, ars-for, ars-data, ars-transition:*, ars-cloak.
 * Expressions run against the player store, e.g. toggle(), fmt(position), current?.title.
 * Themes can override this file with {theme}/a-ripple-song/player/skins/aurora.php.
 */

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Libraries\Icon;
?>
<div class="ars-player" data-ars-player data-ars-skin="aurora" hidden>
    <?php /* ============ 播放器：悬浮玻璃胶囊（常驻） ============ */ ?>
    <div class="fixed inset-x-0 bottom-3 z-[9990] px-3" role="region" data-ars-bar aria-label="音频播放器">
      <div data-ars-width="box" class="frost frost-strong relative mx-auto flex h-[4.5rem] max-w-4xl items-center gap-2 overflow-hidden rounded-full pl-2 pr-3 sm:gap-3">
        <span class="bg-aurora absolute inset-x-6 bottom-0 h-0.5 origin-left rounded-full" ars-bind:style="`transform: scaleX(${progress / 100})`" aria-hidden="true"></span>
        <button type="button" class="flex min-w-0 flex-1 items-center gap-3 rounded-full text-left md:w-64 md:flex-none" ars-on:click="expanded = true" aria-label="展开正在播放">
          <img ars-bind:src="current?.cover" alt="" class="spin size-14 shrink-0 rounded-full ring-2 ring-base-100" ars-bind:class="playing && 'is-on'">
          <span class="min-w-0">
            <span class="block truncate text-xs opacity-60" ars-text="error || `EP.${current?.number} · ${loading ? '加载中' : (playing ? '正在播放' : '已暂停')}`"></span>
            <span class="block truncate" ars-text="current?.title"></span>
          </span>
        </button>
        <div class="flex items-center gap-1">
          <button type="button" class="btn btn-ghost btn-circle btn-sm hidden sm:inline-flex" ars-on:click="skip(-15)" aria-label="后退 15 秒"><?php Icon::render('rotate-ccw', 'size-4'); ?></button>
          <button type="button" class="bg-aurora grid size-12 place-items-center rounded-full text-white shadow-lg transition hover:scale-105" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
            <span ars-show="!playing"><?php Icon::render('play', 'ml-0.5 size-5 fill-current'); ?></span>
            <span ars-show="playing" ars-cloak><?php Icon::render('pause', 'size-5 fill-current'); ?></span>
          </button>
          <button type="button" class="btn btn-ghost btn-circle btn-sm hidden sm:inline-flex" ars-on:click="skip(30)" aria-label="快进 30 秒"><?php Icon::render('rotate-cw', 'size-4'); ?></button>
          <button type="button" class="btn btn-ghost btn-circle btn-sm hidden sm:inline-flex" ars-on:click="next()" aria-label="下一集"><?php Icon::render('skip-forward', 'size-4'); ?></button>
        </div>
        <div class="hidden min-w-0 flex-1 items-center gap-3 text-xs tabular-nums md:flex">
          <span class="opacity-70" ars-text="fmt(position)">00:00</span>
          <input type="range" class="range range-primary range-xs min-w-0 flex-1" min="0" ars-bind:max="duration" step="1" ars-bind:value="position" ars-on:input="scrub($event.target.value)" ars-on:change="commit($event.target.value)" aria-label="播放进度">
          <span class="opacity-50" ars-text="fmt(duration)">00:00</span>
        </div>
        <canvas data-spectrum="mirror" data-bar="2" data-gap="2" class="hidden h-8 w-20 shrink-0 lg:block" aria-hidden="true"></canvas>
        <button type="button" class="btn btn-ghost btn-sm hidden rounded-full font-normal tabular-nums lg:inline-flex" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}×`">1×</button>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" ars-on:click="expanded = true" aria-label="展开播放器与播放队列"><?php Icon::render('chevron-up', 'size-5'); ?></button>
      </div>
    </div>

    <?php /* ============ 正在播放（展开面板） ============ */ ?>
    <div ars-show="expanded" ars-cloak
         ars-transition:enter="transition duration-300 ease-out" ars-transition:enter-start="opacity-0 scale-[1.02]" ars-transition:enter-end="opacity-100 scale-100"
         ars-transition:leave="transition duration-200 ease-in" ars-transition:leave-start="opacity-100" ars-transition:leave-end="opacity-0"
         ars-on:keydown.escape.window="expanded = false"
         class="fixed inset-0 z-[9991] overflow-y-auto bg-base-200/60 backdrop-blur-3xl" role="dialog" aria-modal="true" aria-label="正在播放">
      <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6">
        <div class="flex items-center justify-between">
          <p class="eyebrow">Now Playing</p>
          <button type="button" class="btn btn-ghost btn-circle" ars-on:click="expanded = false" aria-label="收起"><?php Icon::render('chevron-down', 'size-6'); ?></button>
        </div>
        <div class="mt-4 grid grid-cols-1 items-start gap-10 lg:grid-cols-[1.3fr_1fr]">
          <div class="min-w-0 text-center">
            <div class="relative mx-auto aspect-square w-full max-w-[26rem]">
              <span class="bg-aurora absolute inset-[22%] rounded-full opacity-50 blur-3xl" aria-hidden="true"></span>
              <canvas data-spectrum="radial" data-inner="0.64" data-bar="3" data-gap="3" class="absolute inset-0 block size-full" aria-hidden="true"></canvas>
              <img ars-bind:src="current?.cover" alt="" class="spin absolute inset-[20%] size-[60%] rounded-full shadow-2xl" ars-bind:class="playing && 'is-on'">
            </div>
            <p class="mt-6 text-sm opacity-60" ars-text="join('第 ' + current?.number + ' 期', categoryName(current?.category))"></p>
            <h2 class="mx-auto mt-2 max-w-xl text-3xl font-light leading-snug" ars-text="current?.title"></h2>
            <input type="range" class="range range-primary range-sm mt-8 w-full" min="0" ars-bind:max="duration" step="1" ars-bind:value="position" ars-on:input="scrub($event.target.value)" ars-on:change="commit($event.target.value)" aria-label="播放进度">
            <div class="mt-2 flex justify-between text-sm tabular-nums opacity-60"><span ars-text="fmt(position)"></span><span ars-text="fmt(duration)"></span></div>
            <div class="mt-6 flex items-center justify-center gap-3">
              <button type="button" class="btn btn-ghost btn-circle btn-lg" ars-on:click="prev()" aria-label="上一集"><?php Icon::render('skip-back', 'size-6'); ?></button>
              <button type="button" class="btn btn-ghost btn-circle btn-lg" ars-on:click="skip(-15)" aria-label="后退 15 秒"><?php Icon::render('rotate-ccw', 'size-6'); ?></button>
              <button type="button" class="bg-aurora grid size-20 place-items-center rounded-full text-white shadow-xl transition hover:scale-105" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
                <span ars-show="!playing"><?php Icon::render('play', 'ml-1 size-8 fill-current'); ?></span>
                <span ars-show="playing" ars-cloak><?php Icon::render('pause', 'size-8 fill-current'); ?></span>
              </button>
              <button type="button" class="btn btn-ghost btn-circle btn-lg" ars-on:click="skip(30)" aria-label="快进 30 秒"><?php Icon::render('rotate-cw', 'size-6'); ?></button>
              <button type="button" class="btn btn-ghost btn-circle btn-lg" ars-on:click="next()" aria-label="下一集"><?php Icon::render('skip-forward', 'size-6'); ?></button>
            </div>
            <div class="mt-6 flex items-center justify-center gap-4">
              <button type="button" class="frost rounded-full px-4 py-2 text-sm tabular-nums" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}×`">1×</button>
              <label class="flex items-center gap-2">
                <?php Icon::render('volume-2', 'size-5 opacity-70'); ?><span class="sr-only">音量</span>
                <input type="range" class="range range-xs w-32" min="0" max="1" step="0.05" ars-bind:value="muted ? 0 : volume" ars-on:input="setVolume($event.target.value)">
              </label>
            </div>
          </div>
          <section class="frost min-w-0 rounded-box p-4 sm:p-5" aria-labelledby="queue-title">
            <h3 id="queue-title" class="eyebrow px-2 py-2">播放队列</h3>
            <ul class="mt-1 space-y-1">
              <template ars-for="ep in queueItems">
                <li>
                  <button type="button" class="flex w-full items-center gap-3 rounded-2xl p-2 text-left hover:bg-base-100/60" ars-bind:class="isCurrent(ep.id) && 'bg-base-100/70'" ars-on:click="toggle(ep.id)">
                    <img ars-bind:src="ep.cover" alt="" class="size-11 shrink-0 rounded-xl">
                    <span class="min-w-0 flex-1 truncate" ars-bind:class="isCurrent(ep.id) && 'text-primary'" ars-text="ep.title"></span>
                    <span class="shrink-0 text-xs tabular-nums opacity-60" ars-text="isPlaying(ep.id) ? '播放中' : fmt(ep.duration)"></span>
                  </button>
                </li>
              </template>
            </ul>
          </section>
        </div>
      </div>
    </div>
</div>
