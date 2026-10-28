<?php
/**
 * Player skin "夜航" (night-signal), converted from the ~/workspace/podcast/night-signal prototype.
 *
 * Bindings use the plugin's small Alpine-like binder (resources/src/binder.js):
 * ars-text, ars-show, ars-bind:attr, ars-on:event, ars-for, ars-data, ars-transition:*, ars-cloak.
 * Expressions run against the player store, e.g. toggle(), fmt(position), current?.title.
 * Themes can override this file with {theme}/a-ripple-song/player/skins/night-signal.php.
 */

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Libraries\Icon;
?>
<div class="ars-player" data-ars-player data-ars-skin="night-signal" hidden>
    <?php /* ============ 底部播放器（常驻，不随 Swup 切页重载） ============ */ ?>
    <div class="fixed inset-x-0 bottom-0 z-[9990] border-t border-base-300 bg-base-200" role="region" data-ars-bar aria-label="音频播放器">
      <div class="h-1 w-full bg-base-300 md:hidden" aria-hidden="true"><div class="h-full bg-primary transition-[width]" ars-bind:style="`width:${progress}%`"></div></div>
      <div data-ars-width="content" class="mx-auto flex max-w-6xl items-center gap-3 px-3 py-2.5 sm:px-6 md:gap-6">
        <button type="button" class="flex min-w-0 flex-1 items-center gap-3 rounded-field text-left md:w-64 md:flex-none" ars-on:click="expanded = true" aria-label="展开正在播放">
          <span class="relative shrink-0">
            <img ars-bind:src="current?.cover" alt="" class="size-12 rounded-lg bg-base-300">
            <span ars-show="loading" ars-cloak class="absolute inset-0 grid place-items-center rounded-lg bg-base-100/70"><span class="loading loading-spinner loading-sm"></span></span>
          </span>
          <span class="min-w-0">
            <span class="block truncate text-sm font-medium" ars-text="current?.title"></span>
            <span class="flex items-center gap-2 text-xs text-base-content/60">
              <span ars-show="playing" ars-cloak class="eq text-primary" aria-hidden="true"><i></i><i></i><i></i></span>
              <span class="truncate font-mono" ars-text="error || `EP.${current?.number} · ${show.title}`"></span>
            </span>
          </span>
        </button>

        <div class="hidden flex-1 flex-col gap-1 md:flex">
          <div class="flex items-center justify-center gap-1">
            <button type="button" class="btn btn-ghost btn-circle btn-sm" ars-on:click="prev()" aria-label="上一集"><?php Icon::render('skip-back', 'size-4'); ?></button>
            <button type="button" class="btn btn-ghost btn-circle btn-sm" ars-on:click="skip(-15)" aria-label="后退 15 秒"><?php Icon::render('rotate-ccw', 'size-4'); ?></button>
            <button type="button" class="btn btn-primary btn-circle mx-1" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
              <span ars-show="!playing"><?php Icon::render('play', 'size-5 fill-current'); ?></span>
              <span ars-show="playing" ars-cloak><?php Icon::render('pause', 'size-5 fill-current'); ?></span>
            </button>
            <button type="button" class="btn btn-ghost btn-circle btn-sm" ars-on:click="skip(30)" aria-label="快进 30 秒"><?php Icon::render('rotate-cw', 'size-4'); ?></button>
            <button type="button" class="btn btn-ghost btn-circle btn-sm" ars-on:click="next()" aria-label="下一集"><?php Icon::render('skip-forward', 'size-4'); ?></button>
          </div>
          <div class="flex items-center gap-3 font-mono text-xs text-base-content/70">
            <span class="w-12 text-right" ars-text="fmt(position)">00:00</span>
            <input type="range" class="range range-primary range-xs flex-1" min="0" ars-bind:max="duration" step="1" ars-bind:value="position" ars-on:input="scrub($event.target.value)" ars-on:change="commit($event.target.value)" aria-label="播放进度">
            <span class="w-12" ars-text="fmt(duration)">00:00</span>
          </div>
        </div>

        <div class="flex items-center gap-1">
          <canvas data-spectrum="bars" class="mr-2 hidden h-10 w-32 lg:block" aria-hidden="true"></canvas>
          <button type="button" class="btn btn-primary btn-circle md:hidden" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
            <span ars-show="!playing"><?php Icon::render('play', 'size-5 fill-current'); ?></span>
            <span ars-show="playing" ars-cloak><?php Icon::render('pause', 'size-5 fill-current'); ?></span>
          </button>
          <button type="button" class="btn btn-ghost btn-sm hidden w-14 font-mono sm:inline-flex" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}×`">1×</button>
          <div class="hidden items-center gap-1 lg:flex">
            <button type="button" class="btn btn-ghost btn-circle btn-sm" ars-on:click="toggleMute()" ars-bind:aria-label="muted ? '取消静音' : '静音'" aria-label="静音">
              <span ars-show="!muted"><?php Icon::render('volume-2', 'size-4'); ?></span>
              <span ars-show="muted" ars-cloak><?php Icon::render('volume-x', 'size-4'); ?></span>
            </button>
            <input type="range" class="range range-xs w-20" min="0" max="1" step="0.05" ars-bind:value="muted ? 0 : volume" ars-on:input="setVolume($event.target.value)" aria-label="音量">
          </div>
          <button type="button" class="btn btn-ghost btn-circle btn-sm hidden md:inline-flex" ars-on:click="expanded = true" aria-label="展开播放器与播放队列"><?php Icon::render('list-music', 'size-4'); ?></button>
        </div>
      </div>
    </div>

    <?php /* ============ 正在播放（展开面板） ============ */ ?>
    <div ars-show="expanded" ars-cloak
         ars-transition:enter="transition duration-300 ease-out" ars-transition:enter-start="translate-y-full" ars-transition:enter-end="translate-y-0"
         ars-transition:leave="transition duration-200 ease-in" ars-transition:leave-start="translate-y-0" ars-transition:leave-end="translate-y-full"
         ars-on:keydown.escape.window="expanded = false"
         class="fixed inset-0 z-[9991] overflow-y-auto bg-base-100" role="dialog" aria-modal="true" aria-label="正在播放">
      <div class="mx-auto max-w-6xl px-4 pb-10 sm:px-6">
        <div class="flex h-16 items-center justify-between">
          <p class="text-sm font-medium text-base-content/70">正在播放</p>
          <button type="button" class="btn btn-ghost btn-circle" ars-on:click="expanded = false" aria-label="收起"><?php Icon::render('chevron-down', 'size-6'); ?></button>
        </div>
        <div class="grid gap-10 lg:grid-cols-[1fr_380px]">
          <div class="flex flex-col items-center text-center">
            <img ars-bind:src="current?.cover" alt="" class="aspect-square w-full max-w-xs rounded-box">
            <p class="mt-6 font-mono text-sm text-base-content/60" ars-text="join(`EP.${current?.number}`, categoryName(current?.category))"></p>
            <h2 class="mt-2 max-w-xl font-display text-2xl font-bold leading-snug" ars-text="current?.title"></h2>
            <canvas data-spectrum="mirror" data-bar="4" data-gap="3" class="block mt-6 h-28 w-full max-w-xl" aria-hidden="true"></canvas>
            <div class="mt-4 flex w-full max-w-xl items-center gap-3 font-mono text-xs text-base-content/70">
              <span class="w-12 text-right" ars-text="fmt(position)"></span>
              <input type="range" class="range range-primary range-sm flex-1" min="0" ars-bind:max="duration" step="1" ars-bind:value="position" ars-on:input="scrub($event.target.value)" ars-on:change="commit($event.target.value)" aria-label="播放进度">
              <span class="w-12" ars-text="fmt(duration)"></span>
            </div>
            <div class="mt-4 flex items-center gap-2">
              <button type="button" class="btn btn-ghost btn-circle" ars-on:click="prev()" aria-label="上一集"><?php Icon::render('skip-back', 'size-5'); ?></button>
              <button type="button" class="btn btn-ghost btn-circle" ars-on:click="skip(-15)" aria-label="后退 15 秒"><?php Icon::render('rotate-ccw', 'size-5'); ?></button>
              <button type="button" class="btn btn-primary btn-circle btn-lg mx-2" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
                <span ars-show="!playing"><?php Icon::render('play', 'size-6 fill-current'); ?></span>
                <span ars-show="playing" ars-cloak><?php Icon::render('pause', 'size-6 fill-current'); ?></span>
              </button>
              <button type="button" class="btn btn-ghost btn-circle" ars-on:click="skip(30)" aria-label="快进 30 秒"><?php Icon::render('rotate-cw', 'size-5'); ?></button>
              <button type="button" class="btn btn-ghost btn-circle" ars-on:click="next()" aria-label="下一集"><?php Icon::render('skip-forward', 'size-5'); ?></button>
            </div>
            <div class="mt-4 flex items-center gap-4">
              <button type="button" class="btn btn-sm rounded-full font-mono" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}×`">1×</button>
              <label class="flex items-center gap-2">
                <?php Icon::render('volume-2', 'size-4 text-base-content/60'); ?>
                <span class="sr-only">音量</span>
                <input type="range" class="range range-xs w-28" min="0" max="1" step="0.05" ars-bind:value="muted ? 0 : volume" ars-on:input="setVolume($event.target.value)">
              </label>
              <a ars-bind:href="current?.url" class="btn btn-ghost btn-sm" ars-on:click="expanded = false">节目笔记<?php Icon::render('arrow-up-right', 'size-4'); ?></a>
            </div>
          </div>

          <section aria-labelledby="queue-title">
            <h3 id="queue-title" class="mb-3 flex items-center gap-2 font-bold"><?php Icon::render('list-music', 'size-4'); ?>播放队列</h3>
            <ul class="space-y-1">
              <template ars-for="ep in queueItems">
                <li>
                  <button type="button" class="flex w-full items-center gap-3 rounded-field p-2 text-left hover:bg-base-200" ars-bind:class="isCurrent(ep.id) && 'bg-base-200'" ars-on:click="toggle(ep.id)">
                    <img ars-bind:src="ep.cover" alt="" class="size-11 rounded-lg">
                    <span class="min-w-0 flex-1">
                      <span class="block truncate text-sm font-medium" ars-text="ep.title"></span>
                      <span class="block font-mono text-xs text-base-content/60" ars-text="`EP.${ep.number} · ${fmt(ep.duration)}`"></span>
                    </span>
                    <span ars-show="isPlaying(ep.id)" class="eq mr-2 text-primary" aria-label="正在播放"><i></i><i></i><i></i></span>
                  </button>
                </li>
              </template>
            </ul>
          </section>
        </div>
      </div>
    </div>
</div>
