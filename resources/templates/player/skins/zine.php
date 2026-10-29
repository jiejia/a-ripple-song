<?php
/**
 * Player skin "拼贴" (zine), converted from the ~/workspace/podcast/zine prototype.
 *
 * Bindings use the plugin's small Alpine-like binder (resources/assets/js/player/binder.js):
 * ars-text, ars-show, ars-bind:attr, ars-on:event, ars-for, ars-data, ars-transition:*, ars-cloak.
 * Expressions run against the player store, e.g. toggle(), fmt(position), current?.title.
 * Themes can override this file with {theme}/a-ripple-song/player/skins/zine.php.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Icon;
use Jiejia\ARippleSong\Libraries\Template;
?>
<div class="ars-player" data-ars-player data-ars-skin="zine" hidden>
    <?php /* ============ 播放器（常驻） ============ */ ?>
    <div class="fixed inset-x-0 bottom-0 z-[9990]" role="region" data-ars-bar aria-label="音频播放器">
      <div data-ars-width="box" data-ars-surface class="relative mx-auto max-w-6xl border-x-2 border-t-2 ink bg-base-100">
      <div class="halftone absolute inset-x-0 -top-2.5 h-2 opacity-70" aria-hidden="true"></div>
      <div class="flex h-[4.75rem] items-center gap-3 px-3 sm:px-6">
        <button type="button" class="flex min-w-0 flex-1 items-center gap-3 text-left md:w-72 md:flex-none" ars-on:click="expanded = true" aria-label="展开正在播放">
          <span class="duo size-14 shrink-0 -rotate-3 rounded border-2 ink"><img ars-bind:src="current?.cover" alt="" class="size-full"></span>
          <span class="min-w-0">
            <span class="block font-hand text-lg leading-none text-primary" ars-text="error || `#${current?.number} · ${loading ? '加载中…' : (playing ? '正在播放!' : '暂停中')}`"></span>
            <span class="block truncate font-bold" ars-text="current?.title"></span>
          </span>
        </button>
        <div class="flex items-center gap-1.5">
          <button type="button" class="btn btn-ghost btn-circle btn-sm hidden sm:inline-flex" ars-on:click="skip(-15)" aria-label="后退 15 秒"><?php Icon::render('rotate-ccw', 'size-4'); ?></button>
          <button type="button" class="grid size-12 place-items-center rounded-full border-2 ink bg-primary text-primary-content shadow-[3px_3px_0_var(--color-base-content)] transition active:translate-x-0.5 active:translate-y-0.5 active:shadow-none" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
            <span ars-show="!playing"><?php Icon::render('play', 'ml-0.5 size-5 fill-current'); ?></span>
            <span ars-show="playing" ars-cloak><?php Icon::render('pause', 'size-5 fill-current'); ?></span>
          </button>
          <button type="button" class="btn btn-ghost btn-circle btn-sm hidden sm:inline-flex" ars-on:click="skip(30)" aria-label="快进 30 秒"><?php Icon::render('rotate-cw', 'size-4'); ?></button>
          <button type="button" class="btn btn-ghost btn-circle btn-sm hidden sm:inline-flex" ars-on:click="next()" aria-label="下一集"><?php Icon::render('skip-forward', 'size-4'); ?></button>
        </div>
        <?php
        (new Template())->renderThemeable('player/parts/progress', [
            'class' => 'hidden min-w-0 flex-1 items-center gap-3 text-sm tabular-nums md:flex',
            'time' => '', 'timeEnd' => 'opacity-60',
            'spectrum' => ['data-spectrum' => 'dots', 'data-peak' => 'secondary', 'data-bar' => '5', 'data-gap' => '2', 'class' => 'hidden h-10 w-28 shrink-0 xl:block'],
            'waveClass' => 'h-10',
        ]);
        ?>
        <?php (new Template())->renderThemeable('player/parts/volume', ['range' => 'range range-secondary range-xs']); ?>
        <button type="button" class="btn btn-ghost btn-sm hidden rounded-full font-bold lg:inline-flex" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}×`">1×</button>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" ars-on:click="expanded = true" aria-label="展开播放器与播放队列"><?php Icon::render('chevron-up', 'size-5'); ?></button>
      </div>
      </div>
    </div>

    <?php /* ============ 正在播放（展开面板） ============ */ ?>
    <div ars-show="expanded" ars-cloak
         ars-transition:enter="transition duration-250 ease-out" ars-transition:enter-start="translate-y-full" ars-transition:enter-end="translate-y-0"
         ars-transition:leave="transition duration-150 ease-in" ars-transition:leave-start="translate-y-0" ars-transition:leave-end="translate-y-full"
         ars-on:keydown.escape.window="expanded = false"
         class="fixed inset-0 z-[9991] overflow-y-auto bg-base-100" role="dialog" aria-modal="true" aria-label="正在播放">
      <div class="halftone h-5 [--ht:var(--color-secondary)]" aria-hidden="true"></div>
      <div data-ars-panel-width="content" class="mx-auto max-w-6xl px-4 py-6 sm:px-6">
        <div class="flex items-center justify-between">
          <p class="font-hand text-3xl text-primary">正在播放 ↓</p>
          <button type="button" class="btn btn-ghost btn-circle" ars-on:click="expanded = false" aria-label="收起"><?php Icon::render('x', 'size-6'); ?></button>
        </div>
        <div class="mt-6 grid grid-cols-1 gap-12 lg:grid-cols-[1.2fr_1fr]">
          <div class="min-w-0">
            <div class="flex flex-col items-center gap-8 sm:flex-row sm:items-end">
              <div class="taped relative shrink-0 -rotate-2">
                <span class="duo block size-56 border-2 ink shadow-[6px_6px_0_var(--color-secondary)]"><img ars-bind:src="current?.cover" alt="" class="size-full"></span>
              </div>
              <div class="min-w-0 text-center sm:text-left">
                <p class="font-display text-7xl leading-none text-primary" ars-text="'#' + current?.number"></p>
                <h2 class="misprint mt-3 font-display text-3xl leading-tight" ars-text="current?.title"></h2>
              </div>
            </div>
            <canvas data-spectrum="dots" data-peak="secondary" data-bar="9" data-gap="3" class="mt-10 block h-32 w-full" aria-hidden="true"></canvas>
            <input type="range" class="range range-primary mt-4 w-full" min="0" ars-bind:max="duration" step="1" ars-bind:value="position" ars-on:input="scrub($event.target.value)" ars-on:change="commit($event.target.value)" aria-label="播放进度">
            <div class="mt-1 flex justify-between text-sm tabular-nums"><span ars-text="fmt(position)"></span><span class="opacity-60" ars-text="fmt(duration)"></span></div>
            <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
              <button type="button" class="btn btn-circle btn-lg border-2 ink bg-base-100" ars-on:click="prev()" aria-label="上一集"><?php Icon::render('skip-back', 'size-5'); ?></button>
              <button type="button" class="btn btn-circle btn-lg border-2 ink bg-base-100" ars-on:click="skip(-15)" aria-label="后退 15 秒"><?php Icon::render('rotate-ccw', 'size-5'); ?></button>
              <button type="button" class="grid size-20 place-items-center rounded-full border-2 ink bg-primary text-primary-content shadow-[4px_4px_0_var(--color-base-content)]" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
                <span ars-show="!playing"><?php Icon::render('play', 'ml-1 size-8 fill-current'); ?></span>
                <span ars-show="playing" ars-cloak><?php Icon::render('pause', 'size-8 fill-current'); ?></span>
              </button>
              <button type="button" class="btn btn-circle btn-lg border-2 ink bg-base-100" ars-on:click="skip(30)" aria-label="快进 30 秒"><?php Icon::render('rotate-cw', 'size-5'); ?></button>
              <button type="button" class="btn btn-circle btn-lg border-2 ink bg-base-100" ars-on:click="next()" aria-label="下一集"><?php Icon::render('skip-forward', 'size-5'); ?></button>
            </div>
            <div class="mt-6 flex items-center justify-center gap-4">
              <button type="button" class="btn rounded-full border-2 ink bg-accent font-bold text-accent-content" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}×`">1×</button>
              <label class="flex items-center gap-2">
                <?php Icon::render('volume-2', 'size-5'); ?><span class="sr-only">音量</span>
                <input type="range" class="range range-secondary range-xs w-32" min="0" max="1" step="0.05" ars-bind:value="muted ? 0 : volume" ars-on:input="setVolume($event.target.value)">
              </label>
            </div>
          </div>
          <section class="cutout min-w-0 rotate-1 p-5" aria-labelledby="queue-title">
            <h3 id="queue-title" class="misprint font-display text-3xl">目录</h3>
            <ol class="mt-3">
              <template ars-for="ep in queueItems">
                <li>
                  <button type="button" class="flex w-full items-baseline gap-2 rounded px-2 py-2 text-left hover:bg-accent hover:text-accent-content" ars-bind:class="isCurrent(ep.id) && 'hl'" ars-on:click="toggle(ep.id)">
                    <span class="w-9 shrink-0 font-hand text-xl text-primary" ars-text="'#' + ep.number"></span>
                    <span class="min-w-0 truncate font-bold" ars-text="ep.title"></span>
                    <span class="min-w-4 flex-1 translate-y-[-0.25em] border-b-2 border-dotted border-current/40" aria-hidden="true"></span>
                    <span class="shrink-0 text-sm tabular-nums" ars-text="isPlaying(ep.id) ? '♫' : fmt(ep.duration)"></span>
                  </button>
                </li>
              </template>
            </ol>
          </section>
        </div>
      </div>
    </div>
</div>
