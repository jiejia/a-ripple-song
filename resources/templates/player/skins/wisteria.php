<?php
/**
 * Player skin "紫藤" (wisteria), converted from the ~/workspace/podcast/wisteria prototype.
 *
 * Bindings use the plugin's small Alpine-like binder (resources/assets/js/player/binder.js):
 * ars-text, ars-show, ars-bind:attr, ars-on:event, ars-for, ars-data, ars-transition:*, ars-cloak.
 * Expressions run against the player store, e.g. toggle(), fmt(position), current?.title.
 * Themes can override this file with {theme}/a-ripple-song/player/skins/wisteria.php.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Icon;
use Jiejia\ARippleSong\Libraries\Template;
?>
<div class="ars-player" data-ars-player data-ars-skin="wisteria" hidden>
    <?php /* ============ 底部：播放器 + 手机端标签栏（常驻） ============ */ ?>
    <div class="fixed inset-x-0 bottom-0 z-[9990] px-3 pb-3" role="region" data-ars-bar aria-label="音频播放器">
      <div data-ars-width="box" data-ars-surface class="card-w mx-auto flex max-w-6xl items-center gap-3 rounded-[1.75rem] p-2.5 sm:gap-4 sm:pr-4">
        <button type="button" class="shrink-0" ars-on:click="expanded = true" aria-label="展开正在播放"><img ars-bind:src="current?.cover" alt="" class="size-14 rounded-2xl"></button>
        <div class="min-w-0 flex-1 md:w-56 md:flex-none">
          <p class="flex items-center gap-2">
            <span ars-show="playing" ars-cloak class="is-on inline-flex items-center gap-1 rounded-full bg-secondary/25 px-2 py-0.5 text-[10px] font-semibold text-[oklch(45%_0.1_210)]"><span class="live-dot"></span>On Live</span>
            <span class="mute truncate text-xs" ars-text="error || (loading ? '加载中…' : `第 ${current?.number} 期`)"></span>
          </p>
          <a ars-bind:href="current?.url" class="block truncate font-semibold hover:text-primary" ars-text="current?.title"></a>
        </div>
        <div class="flex shrink-0 items-center gap-1 sm:gap-2">
          <button type="button" class="btn btn-ghost btn-circle btn-sm hidden sm:inline-flex" ars-on:click="skip(-15)" aria-label="后退 15 秒"><?php Icon::render('rotate-ccw', 'size-5'); ?></button>
          <button type="button" class="grid size-12 place-items-center rounded-full bg-primary text-primary-content shadow-[0_10px_20px_-8px_var(--color-primary)] transition hover:scale-105" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
            <span ars-show="!playing"><?php Icon::render('play', 'ml-0.5 size-5 fill-current'); ?></span>
            <span ars-show="playing" ars-cloak><?php Icon::render('pause', 'size-5 fill-current'); ?></span>
          </button>
          <button type="button" class="btn btn-ghost btn-circle btn-sm hidden sm:inline-flex" ars-on:click="skip(15)" aria-label="快进 15 秒"><?php Icon::render('rotate-cw', 'size-5'); ?></button>
        </div>
        <?php
        (new Template())->renderThemeable('player/parts/progress', [
            'time' => 'mute',
            'spectrum' => ['data-spectrum' => 'mirror', 'data-bar' => '3', 'data-gap' => '2', 'class' => 'hidden h-9 w-24 shrink-0 xl:block'],
            'waveClass' => 'h-9',
        ]);
        ?>
        <?php (new Template())->renderThemeable('player/parts/volume'); ?>
        <button type="button" class="btn btn-ghost btn-sm hidden w-11 px-0 text-xs lg:inline-flex" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}×`">1×</button>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" ars-on:click="expanded = true" aria-label="展开播放器与播放队列"><?php Icon::render('chevron-up', 'size-5'); ?></button>
      </div>
    </div>

    <?php /* ============ 正在播放（展开面板）：Now Play ============ */ ?>
    <div ars-show="expanded" ars-cloak
         ars-transition:enter="transition duration-250 ease-out" ars-transition:enter-start="opacity-0 translate-y-8" ars-transition:enter-end="opacity-100 translate-y-0"
         ars-transition:leave="transition duration-150 ease-in" ars-transition:leave-start="opacity-100" ars-transition:leave-end="opacity-0 translate-y-8"
         ars-on:keydown.escape.window="expanded = false"
         class="fixed inset-0 z-[9991] overflow-y-auto bg-[var(--page)]" role="dialog" aria-modal="true" aria-label="正在播放">
      <div data-ars-panel-width="content" class="mx-auto max-w-6xl px-4 py-5 sm:px-6">
        <div class="flex items-center justify-between">
          <button type="button" class="grid size-11 place-items-center rounded-full bg-base-100 shadow-[var(--lift)]" ars-on:click="expanded = false" aria-label="收起"><?php Icon::render('chevron-down', 'size-5'); ?></button>
          <p class="font-bold">正在播放</p>
          <a ars-bind:href="current?.url" ars-on:click="expanded = false" class="grid size-11 place-items-center rounded-full bg-base-100 shadow-[var(--lift)]" aria-label="节目笔记"><?php Icon::render('file-text', 'size-5'); ?></a>
        </div>
        <div class="mt-6 grid grid-cols-1 items-start gap-10 lg:grid-cols-[1.1fr_1fr]">
          <div class="mx-auto w-full max-w-md text-center">
            <img ars-bind:src="current?.cover" alt="" class="aspect-square w-full rounded-[2rem] shadow-[0_30px_60px_-30px_oklch(40%_0.15_285/0.6)]">
            <h2 class="mt-7 text-2xl font-bold leading-snug" ars-text="current?.title"></h2>
            <p class="mute mt-1 text-sm" ars-text="join(show.title, categoryName(current?.category))"></p>
            <input type="range" class="range range-primary range-sm mt-7 w-full" min="0" ars-bind:max="duration" step="1" ars-bind:value="position" ars-on:input="scrub($event.target.value)" ars-on:change="commit($event.target.value)" aria-label="播放进度">
            <div class="mute mt-2 flex justify-between text-xs tabular-nums"><span ars-text="fmt(position)"></span><span ars-text="fmt(duration)"></span></div>
            <div class="mt-5 flex items-center justify-center gap-5">
              <button type="button" class="btn btn-ghost btn-circle" ars-on:click="prev()" aria-label="上一集"><?php Icon::render('skip-back', 'size-5'); ?></button>
              <button type="button" class="btn btn-ghost btn-circle" ars-on:click="skip(-15)" aria-label="后退 15 秒"><?php Icon::render('rotate-ccw', 'size-6'); ?></button>
              <button type="button" class="grid size-[4.5rem] place-items-center rounded-full bg-primary text-primary-content shadow-[0_16px_30px_-12px_var(--color-primary)] transition hover:scale-105" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
                <span ars-show="!playing"><?php Icon::render('play', 'ml-1 size-7 fill-current'); ?></span>
                <span ars-show="playing" ars-cloak><?php Icon::render('pause', 'size-7 fill-current'); ?></span>
              </button>
              <button type="button" class="btn btn-ghost btn-circle" ars-on:click="skip(15)" aria-label="快进 15 秒"><?php Icon::render('rotate-cw', 'size-6'); ?></button>
              <button type="button" class="btn btn-ghost btn-circle" ars-on:click="next()" aria-label="下一集"><?php Icon::render('skip-forward', 'size-5'); ?></button>
            </div>
            <?php /* 三行歌词式文字稿 */ ?>
            <div class="card-w mt-7 rounded-[1.5rem] px-5 py-4" aria-live="polite">
              <p class="mute text-sm leading-relaxed" ars-text="current?.excerpt"></p>
            </div>
            <div class="mt-5 flex items-center justify-center gap-4">
              <button type="button" class="rounded-full bg-base-100 px-4 py-2 text-sm font-semibold shadow-[var(--lift)]" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}×`">1×</button>
              <label class="flex items-center gap-2">
                <?php Icon::render('volume-2', 'mute size-5'); ?><span class="sr-only">音量</span>
                <input type="range" class="range range-primary range-xs w-28" min="0" max="1" step="0.05" ars-bind:value="muted ? 0 : volume" ars-on:input="setVolume($event.target.value)">
              </label>
            </div>
          </div>
          <section class="min-w-0" aria-labelledby="queue-title">
            <div class="flex items-center justify-between"><h3 id="queue-title" class="text-lg font-bold">播放队列</h3><span class="mute text-sm" ars-text="queueItems.length + ' 期'"></span></div>
            <canvas data-spectrum="mirror" data-bar="3" data-gap="3" class="mt-4 block h-14 w-full" aria-hidden="true"></canvas>
            <ul class="mt-4 space-y-2">
              <template ars-for="ep in queueItems">
                <li>
                  <button type="button" class="flex w-full items-center gap-4 rounded-[1.25rem] p-2 pr-3 text-left transition hover:bg-base-100" ars-bind:class="isCurrent(ep.id) && 'card-w'" ars-on:click="toggle(ep.id)">
                    <img ars-bind:src="ep.cover" alt="" class="size-14 shrink-0 rounded-2xl">
                    <span class="min-w-0 flex-1"><span class="block truncate font-semibold" ars-text="ep.title"></span><span class="mute block text-xs" ars-text="join(categoryName(ep.category), fmt(ep.duration))"></span></span>
                    <span class="ring-play size-10 shrink-0 text-xs" ars-bind:class="isPlaying(ep.id) && 'is-on'" ars-text="isPlaying(ep.id) ? '❚❚' : '▶'"></span>
                  </button>
                </li>
              </template>
            </ul>
          </section>
        </div>
      </div>
    </div>
</div>
