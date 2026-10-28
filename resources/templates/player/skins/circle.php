<?php
/**
 * Player skin "圈子" (circle), converted from the ~/workspace/podcast/circle prototype.
 *
 * Bindings use the plugin's small Alpine-like binder (resources/assets/js/player/binder.js):
 * ars-text, ars-show, ars-bind:attr, ars-on:event, ars-for, ars-data, ars-transition:*, ars-cloak.
 * Expressions run against the player store, e.g. toggle(), fmt(position), current?.title.
 * Themes can override this file with {theme}/a-ripple-song/player/skins/circle.php.
 */

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Libraries\Icon;
use Jiejia\ARippleSong\Libraries\Template;
?>
<div class="ars-player" data-ars-player data-ars-skin="circle" hidden>
    <?php /* ============ 播放器（常驻，悬浮卡片） ============ */ ?>
    <div class="fixed inset-x-0 bottom-0 z-[9990] px-2 pb-2 sm:px-4 sm:pb-4" role="region" data-ars-bar aria-label="音频播放器">
      <div data-ars-width="box" data-ars-surface class="soft-shadow mx-auto flex h-[4.5rem] max-w-7xl items-center gap-3 rounded-2xl border border-base-300 bg-base-100 px-2.5 sm:px-4">
        <button type="button" class="flex min-w-0 flex-1 items-center gap-3 text-left md:w-72 md:flex-none" ars-on:click="expanded = true" aria-label="展开正在播放">
          <img ars-bind:src="current?.cover" alt="" class="size-12 shrink-0 rounded-xl">
          <span class="min-w-0">
            <span class="flex items-center gap-2 text-xs">
              <span class="inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 text-[10px] font-semibold" ars-bind:class="playing ? 'is-on bg-accent text-accent-content' : 'bg-base-300'"><span class="live-dot"></span><span ars-text="playing ? 'LIVE' : (loading ? '加载中' : '暂停')">暂停</span></span>
              <span class="opacity-50" ars-text="error || 'EP.' + current?.number"></span>
            </span>
            <span class="mt-0.5 block truncate text-sm font-semibold" ars-text="current?.title"></span>
          </span>
        </button>
        <div class="flex items-center gap-1">
          <button type="button" class="btn btn-ghost btn-circle btn-sm hidden sm:inline-flex" ars-on:click="prev()" aria-label="上一集"><?php Icon::render('skip-back', 'size-4'); ?></button>
          <button type="button" class="btn btn-ghost btn-circle btn-sm hidden sm:inline-flex" ars-on:click="skip(-15)" aria-label="后退 15 秒"><?php Icon::render('rotate-ccw', 'size-4'); ?></button>
          <button type="button" class="soft-shadow grid size-12 place-items-center rounded-xl bg-primary text-primary-content transition hover:scale-105" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
            <span ars-show="!playing"><?php Icon::render('play', 'ml-0.5 size-5 fill-current'); ?></span>
            <span ars-show="playing" ars-cloak><?php Icon::render('pause', 'size-5 fill-current'); ?></span>
          </button>
          <button type="button" class="btn btn-ghost btn-circle btn-sm hidden sm:inline-flex" ars-on:click="skip(30)" aria-label="快进 30 秒"><?php Icon::render('rotate-cw', 'size-4'); ?></button>
          <button type="button" class="btn btn-ghost btn-circle btn-sm hidden sm:inline-flex" ars-on:click="next()" aria-label="下一集"><?php Icon::render('skip-forward', 'size-4'); ?></button>
        </div>
        <div class="hidden min-w-0 flex-1 items-center gap-3 text-xs tabular-nums md:flex">
          <span class="opacity-60" ars-text="fmt(position)">00:00</span>
          <input type="range" class="range range-primary range-xs min-w-0 flex-1" min="0" ars-bind:max="duration" step="1" ars-bind:value="position" ars-on:input="scrub($event.target.value)" ars-on:change="commit($event.target.value)" aria-label="播放进度">
          <span class="opacity-40" ars-text="fmt(duration)">00:00</span>
        </div>
        <canvas data-spectrum="mirror" data-bar="3" data-gap="2" data-color="secondary" class="hidden h-9 w-28 shrink-0 xl:block" aria-hidden="true"></canvas>
        <div class="hidden items-center gap-1 lg:flex">
          <button type="button" class="btn btn-ghost btn-sm rounded-lg font-semibold" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}×`">1×</button>
          <?php (new Template())->renderThemeable('player/parts/volume', ['popover' => 'rounded-2xl border border-base-300 bg-base-100 shadow-lg', 'range' => 'range range-secondary range-xs']); ?>
        </div>
        <button type="button" class="tint grid size-10 shrink-0 place-items-center rounded-xl hover:text-primary" ars-on:click="expanded = true" aria-label="展开播放器与播放队列"><?php Icon::render('chevron-up', 'size-5'); ?></button>
      </div>
    </div>

    <?php /* ============ 正在播放（展开面板）：LIVE 卡片 + 队列 ============ */ ?>
    <div ars-show="expanded" ars-cloak
         ars-transition:enter="transition duration-250 ease-out" ars-transition:enter-start="opacity-0 translate-y-6" ars-transition:enter-end="opacity-100 translate-y-0"
         ars-transition:leave="transition duration-150 ease-in" ars-transition:leave-start="opacity-100" ars-transition:leave-end="opacity-0"
         ars-on:keydown.escape.window="expanded = false"
         class="fixed inset-0 z-[9991] overflow-y-auto bg-[var(--page)] sm:p-4" role="dialog" aria-modal="true" aria-label="正在播放">
      <div data-ars-panel-width="box" class="app-frame mx-auto min-h-full max-w-6xl p-4 sm:rounded-[2rem] sm:p-6">
        <div class="flex items-center justify-between">
          <p class="caps">Now Playing</p>
          <button type="button" class="tint grid size-10 place-items-center rounded-xl hover:text-primary" ars-on:click="expanded = false" aria-label="收起"><?php Icon::render('x', 'size-5'); ?></button>
        </div>
        <div class="mt-5 grid grid-cols-1 gap-8 lg:grid-cols-[1.3fr_1fr]">
          <div class="min-w-0">
            <div class="relative overflow-hidden rounded-[1.5rem]">
              <img ars-bind:src="current?.cover" alt="" class="aspect-[16/10] w-full object-cover">
              <p class="absolute left-4 top-4 flex items-center gap-2 text-xs font-semibold">
                <span class="inline-flex items-center gap-1 rounded-md px-2 py-1" ars-bind:class="playing ? 'is-on bg-accent text-accent-content' : 'bg-base-100 text-base-content'"><span class="live-dot"></span><span ars-text="playing ? 'LIVE' : '暂停'"></span></span>
                <span class="inline-flex items-center gap-1 rounded-md bg-black/45 px-2 py-1 text-white"><?php Icon::render('headphones', 'size-3.5'); ?><span ars-text="compact(current?.plays || 0)">0</span></span>
              </p>
            </div>
            <div class="relative z-10 mx-4 -mt-10 rounded-2xl bg-primary p-4 text-primary-content soft-shadow">
              <div class="flex items-center justify-between text-xs"><span class="font-semibold">实时频谱 · <span ars-text="'EP.' + current?.number"></span></span><span class="tabular-nums opacity-80"><span ars-text="fmt(position)"></span> / <span ars-text="fmt(duration)"></span></span></div>
              <canvas data-spectrum="mirror" data-bar="4" data-gap="3" data-color="secondary" class="mt-3 block h-16 w-full" aria-hidden="true"></canvas>
            </div>
            <h2 class="mt-6 text-2xl font-semibold leading-snug" ars-text="current?.title"></h2>
            <p class="mt-1 text-sm opacity-60" ars-text="join(categoryName(current?.category), current?.hosts)"></p>
            <input type="range" class="range range-primary range-sm mt-6 w-full" min="0" ars-bind:max="duration" step="1" ars-bind:value="position" ars-on:input="scrub($event.target.value)" ars-on:change="commit($event.target.value)" aria-label="播放进度">
            <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
              <button type="button" class="tint grid size-12 place-items-center rounded-xl hover:text-primary" ars-on:click="prev()" aria-label="上一集"><?php Icon::render('skip-back', 'size-5'); ?></button>
              <button type="button" class="tint grid size-12 place-items-center rounded-xl hover:text-primary" ars-on:click="skip(-15)" aria-label="后退 15 秒"><?php Icon::render('rotate-ccw', 'size-5'); ?></button>
              <button type="button" class="soft-shadow grid size-16 place-items-center rounded-2xl bg-primary text-primary-content" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
                <span ars-show="!playing"><?php Icon::render('play', 'ml-1 size-7 fill-current'); ?></span>
                <span ars-show="playing" ars-cloak><?php Icon::render('pause', 'size-7 fill-current'); ?></span>
              </button>
              <button type="button" class="tint grid size-12 place-items-center rounded-xl hover:text-primary" ars-on:click="skip(30)" aria-label="快进 30 秒"><?php Icon::render('rotate-cw', 'size-5'); ?></button>
              <button type="button" class="tint grid size-12 place-items-center rounded-xl hover:text-primary" ars-on:click="next()" aria-label="下一集"><?php Icon::render('skip-forward', 'size-5'); ?></button>
            </div>
            <div class="mt-5 flex items-center justify-center gap-4">
              <button type="button" class="tint h-10 rounded-xl px-4 text-sm font-semibold" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}×`">1×</button>
              <label class="flex items-center gap-2">
                <?php Icon::render('volume-2', 'size-5 opacity-60'); ?><span class="sr-only">音量</span>
                <input type="range" class="range range-secondary range-xs w-32" min="0" max="1" step="0.05" ars-bind:value="muted ? 0 : volume" ars-on:input="setVolume($event.target.value)">
              </label>
            </div>
          </div>
          <section class="min-w-0 rounded-[1.5rem] bg-base-200 p-4 sm:p-5" aria-labelledby="queue-title">
            <h3 id="queue-title" class="caps px-2">播放队列</h3>
            <ul class="mt-3 space-y-1">
              <template ars-for="ep in queueItems">
                <li>
                  <button type="button" class="flex w-full items-center gap-3 rounded-xl px-2 py-2 text-left hover:bg-base-100" ars-bind:class="isCurrent(ep.id) && 'bg-base-100 soft-shadow'" ars-on:click="toggle(ep.id)">
                    <img ars-bind:src="ep.cover" alt="" class="size-10 shrink-0 rounded-full">
                    <span class="min-w-0 flex-1 truncate text-sm font-medium" ars-bind:class="isCurrent(ep.id) && 'text-primary'" ars-text="ep.title"></span>
                    <span ars-show="isPlaying(ep.id)" class="size-2 shrink-0 rounded-full bg-secondary"></span>
                    <span ars-show="!isPlaying(ep.id)" class="shrink-0 text-xs tabular-nums opacity-50" ars-text="fmt(ep.duration)"></span>
                  </button>
                </li>
              </template>
            </ul>
          </section>
        </div>
      </div>
    </div>
</div>
