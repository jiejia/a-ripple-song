<?php
/**
 * Player skin "脉冲" (pulse), converted from the ~/workspace/podcast/pulse prototype.
 *
 * Bindings use the plugin's small Alpine-like binder (resources/assets/js/player/binder.js):
 * ars-text, ars-show, ars-bind:attr, ars-on:event, ars-for, ars-data, ars-transition:*, ars-cloak.
 * Expressions run against the player store, e.g. toggle(), fmt(position), current?.title.
 * Themes can override this file with {theme}/a-ripple-song/player/skins/pulse.php.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Icon;
use Jiejia\ARippleSong\Libraries\Template;
?>
<div class="ars-player" data-ars-player data-ars-skin="pulse" hidden>
    <?php /* ============ 播放器（常驻，底部悬浮） ============ */ ?>
    <div class="fixed inset-x-0 bottom-3 z-[9990] px-3" role="region" data-ars-bar aria-label="音频播放器">
      <div data-ars-width="box" data-ars-surface="base-200" class="mx-auto flex max-w-6xl items-center gap-3 rounded-[24px] bg-base-200 p-2.5 shadow-2xl ring-1 ring-[color-mix(in_oklch,var(--color-base-content)_10%,transparent)] sm:gap-4 sm:pr-4">
        <button type="button" class="relative h-14 w-16 shrink-0 overflow-hidden rounded-2xl bg-primary" ars-on:click="expanded = true" aria-label="展开播放器">
          <img ars-bind:src="current?.cover" alt="" class="size-full object-cover">
          <span ars-show="loading" ars-cloak class="absolute inset-0 grid place-items-center bg-black/40"><span class="loading loading-spinner loading-sm text-white"></span></span>
        </button>
        <div class="min-w-0 flex-1 md:w-60 md:flex-none">
          <a ars-bind:href="current?.url" class="block truncate font-semibold hover:text-primary" ars-text="current?.title"></a>
          <p class="muted truncate text-xs" ars-text="error || join(`第 ${current?.number} 期`, current?.hosts)"></p>
        </div>
        <div class="flex shrink-0 items-center gap-1 sm:gap-2">
          <button type="button" class="btn btn-ghost btn-circle btn-sm hidden sm:inline-flex" ars-on:click="prev()" aria-label="上一集"><?php Icon::render('skip-back', 'size-4 fill-current'); ?></button>
          <button type="button" class="btn btn-ghost btn-circle btn-sm hidden sm:inline-flex" ars-on:click="skip(-15)" aria-label="后退 15 秒"><?php Icon::render('rotate-ccw', 'size-4'); ?></button>
          <button type="button" class="grid size-12 place-items-center rounded-full bg-base-content text-base-100 transition hover:scale-105" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
            <span ars-show="!playing"><?php Icon::render('play', 'ml-0.5 size-5 fill-current'); ?></span>
            <span ars-show="playing" ars-cloak><?php Icon::render('pause', 'size-5 fill-current'); ?></span>
          </button>
          <button type="button" class="btn btn-ghost btn-circle btn-sm hidden sm:inline-flex" ars-on:click="skip(30)" aria-label="快进 30 秒"><?php Icon::render('rotate-cw', 'size-4'); ?></button>
          <button type="button" class="btn btn-ghost btn-circle btn-sm hidden sm:inline-flex" ars-on:click="next()" aria-label="下一集"><?php Icon::render('skip-forward', 'size-4 fill-current'); ?></button>
        </div>
        <?php
        (new Template())->renderThemeable('player/parts/progress', [
            'time' => 'muted',
            'spectrum' => ['data-spectrum' => 'bars', 'data-bar' => '3', 'data-gap' => '2', 'data-color' => 'content', 'class' => 'hidden h-10 w-24 shrink-0 xl:block'],
            'wave' => ['data-bar' => '3', 'data-gap' => '4', 'data-color' => 'content'], 'waveClass' => 'h-10',
        ]);
        ?>
        <div class="hidden items-center gap-1 lg:flex">
          <button type="button" class="btn btn-ghost btn-sm w-12 px-0 text-sm" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}×`">1×</button>
          <?php (new Template())->renderThemeable('player/parts/volume'); ?>
        </div>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" ars-on:click="expanded = true" aria-label="展开播放器与播放队列"><?php Icon::render('chevron-up', 'size-5'); ?></button>
      </div>
    </div>

    <?php /* ============ 正在播放（展开面板） ============ */ ?>
    <div ars-show="expanded" ars-cloak
         ars-transition:enter="transition duration-300 ease-out" ars-transition:enter-start="opacity-0 translate-y-6" ars-transition:enter-end="opacity-100 translate-y-0"
         ars-transition:leave="transition duration-200 ease-in" ars-transition:leave-start="opacity-100" ars-transition:leave-end="opacity-0"
         ars-on:keydown.escape.window="expanded = false"
         class="fixed inset-0 z-[9991] overflow-y-auto bg-base-100" role="dialog" aria-modal="true" aria-label="正在播放">
      <div data-ars-panel-width="content" class="mx-auto max-w-6xl p-4 sm:p-6">
        <div class="flex items-center justify-between">
          <h2 class="eyebrow">正在播放</h2>
          <button type="button" class="btn btn-ghost btn-circle" ars-on:click="expanded = false" aria-label="收起"><?php Icon::render('x', 'size-6'); ?></button>
        </div>
        <div class="mt-6 grid gap-6 lg:grid-cols-[1.4fr_1fr]">
          <div class="rounded-[28px] bg-base-200 p-6 sm:p-8">
            <div class="aspect-[4/3] overflow-hidden rounded-[22px] bg-primary">
              <img ars-bind:src="current?.cover" alt="" class="size-full object-contain p-6">
            </div>
            <p class="muted mt-6 text-sm" ars-text="join(`第 ${current?.number} 期`, categoryName(current?.category))"></p>
            <h3 class="mt-1 text-2xl font-bold leading-snug sm:text-3xl" ars-text="current?.title"></h3>
            <div class="wave-wrap relative mt-6 h-20">
              <canvas data-spectrum="pulse" data-bar="4" data-gap="5" data-color="content" class="block h-full w-full" aria-hidden="true"></canvas>
              <input type="range" class="wave-seek" min="0" ars-bind:max="duration" step="1" ars-bind:value="position" ars-on:input="scrub($event.target.value)" ars-on:change="commit($event.target.value)" aria-label="播放进度">
            </div>
            <div class="muted mt-2 flex justify-between text-sm tabular-nums">
              <span ars-text="fmt(position)"></span><span ars-text="fmt(duration)"></span>
            </div>
            <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
              <button type="button" class="btn btn-ghost btn-circle" ars-on:click="prev()" aria-label="上一集"><?php Icon::render('skip-back', 'size-5 fill-current'); ?></button>
              <button type="button" class="btn btn-ghost btn-circle" ars-on:click="skip(-15)" aria-label="后退 15 秒"><?php Icon::render('rotate-ccw', 'size-5'); ?></button>
              <button type="button" class="grid size-16 place-items-center rounded-full bg-base-content text-base-100 transition hover:scale-105" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
                <span ars-show="!playing"><?php Icon::render('play', 'ml-1 size-7 fill-current'); ?></span>
                <span ars-show="playing" ars-cloak><?php Icon::render('pause', 'size-7 fill-current'); ?></span>
              </button>
              <button type="button" class="btn btn-ghost btn-circle" ars-on:click="skip(30)" aria-label="快进 30 秒"><?php Icon::render('rotate-cw', 'size-5'); ?></button>
              <button type="button" class="btn btn-ghost btn-circle" ars-on:click="next()" aria-label="下一集"><?php Icon::render('skip-forward', 'size-5 fill-current'); ?></button>
            </div>
            <div class="mt-5 flex flex-wrap items-center justify-center gap-5">
              <button type="button" class="chip" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`语速 ${rate}×`">语速 1×</button>
              <label class="flex items-center gap-2">
                <span ars-show="!muted"><?php Icon::render('volume-2', 'size-5'); ?></span><span ars-show="muted" ars-cloak><?php Icon::render('volume-x', 'size-5'); ?></span>
                <span class="sr-only">音量</span>
                <input type="range" class="range range-primary range-xs w-32" min="0" max="1" step="0.05" ars-bind:value="muted ? 0 : volume" ars-on:input="setVolume($event.target.value)">
              </label>
              <a ars-bind:href="current?.url" class="chip" ars-on:click="expanded = false">节目笔记</a>
            </div>
          </div>
          <section aria-labelledby="queue-title">
            <div class="flex items-center justify-between gap-3"><h3 id="queue-title" class="eyebrow">播放队列</h3><button type="button" class="muted text-sm hover:text-primary" ars-show="queueItems.length" ars-on:click="clearQueue()">清空</button></div>
            <ul class="mt-5 space-y-3 max-h-[min(32rem,60dvh)] overflow-y-auto overscroll-contain [scrollbar-width:thin] -mx-1 px-1 py-1">
              <template ars-for="ep in queueItems">
                <li class="flex items-center gap-1">
                  <button type="button" class="row-card flex min-w-0 flex-1 items-center gap-4 p-2.5 pr-4 text-left" ars-bind:class="isCurrent(ep.id) && 'ring-2 ring-[var(--color-primary)]'" ars-on:click="toggle(ep.id)">
                    <span class="h-14 w-16 shrink-0 overflow-hidden rounded-xl bg-primary"><img ars-bind:src="ep.cover" alt="" class="size-full object-cover"></span>
                    <span class="min-w-0 flex-1"><span class="block truncate font-medium" ars-text="ep.title"></span><span class="muted block text-sm" ars-text="`第 ${ep.number} 期 · ${fmt(ep.duration)}`"></span></span>
                    <span ars-show="isPlaying(ep.id)" class="eq text-primary" aria-label="正在播放"><i></i><i></i><i></i></span>
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
