<?php
/**
 * Player skin "晚报" (evening-paper), converted from the ~/workspace/podcast/evening-paper prototype.
 *
 * Bindings use the plugin's small Alpine-like binder (resources/assets/js/player/binder.js):
 * ars-text, ars-show, ars-bind:attr, ars-on:event, ars-for, ars-data, ars-transition:*, ars-cloak.
 * Expressions run against the player store, e.g. toggle(), fmt(position), current?.title.
 * Themes can override this file with {theme}/a-ripple-song/player/skins/evening-paper.php.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Icon;
use Jiejia\ARippleSong\Libraries\Template;
?>
<div class="ars-player" data-ars-player data-ars-skin="evening-paper" hidden>
    <?php /* ============ 播放器（常驻） ============ */ ?>
    <div class="fixed inset-x-0 bottom-0 z-[9990]" role="region" data-ars-bar aria-label="音频播放器">
      <div data-ars-width="box" data-ars-surface class="mx-auto max-w-6xl bg-base-100">
      <div class="rule-double"></div>
      <div class="flex items-center gap-4 px-4 py-3 sm:px-6">
        <button type="button" class="min-w-0 flex-1 text-left md:w-60 md:flex-none" ars-on:click="expanded = true" aria-label="展开正在播放">
          <span class="kicker block" ars-text="error || join(`第 ${current?.number} 期`, categoryName(current?.category))"></span>
          <span class="block truncate text-[15px] font-semibold" ars-text="current?.title"></span>
        </button>
        <div class="flex shrink-0 items-center gap-1.5">
          <button type="button" class="btn btn-ghost btn-sm btn-circle hidden sm:inline-flex" ars-on:click="prev()" aria-label="上一期"><?php Icon::render('skip-back', 'size-4'); ?></button>
          <button type="button" class="btn btn-ghost btn-sm btn-circle hidden sm:inline-flex" ars-on:click="skip(-15)" aria-label="后退 15 秒"><?php Icon::render('rotate-ccw', 'size-4'); ?></button>
          <button type="button" class="btn-ring size-12 shrink-0" ars-bind:class="playing && 'is-on'" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
            <span ars-show="!playing"><?php Icon::render('play', 'ml-0.5 size-5 fill-current'); ?></span>
            <span ars-show="playing" ars-cloak><?php Icon::render('pause', 'size-5 fill-current'); ?></span>
          </button>
          <button type="button" class="btn btn-ghost btn-sm btn-circle hidden sm:inline-flex" ars-on:click="skip(30)" aria-label="快进 30 秒"><?php Icon::render('rotate-cw', 'size-4'); ?></button>
          <button type="button" class="btn btn-ghost btn-sm btn-circle hidden sm:inline-flex" ars-on:click="next()" aria-label="下一期"><?php Icon::render('skip-forward', 'size-4'); ?></button>
        </div>
        <?php
        (new Template())->renderThemeable('player/parts/progress', [
            'class' => 'hidden min-w-0 flex-1 items-center gap-4 md:flex',
            'time' => 'meta shrink-0 tabular-nums', 'range' => 'range range-xs [--range-thumb:var(--color-primary)]',
            'spectrum' => ['data-spectrum' => 'wave', 'data-bar' => '1.25', 'data-color' => 'content', 'class' => 'hidden h-7 w-28 shrink-0 xl:block'],
            'wave' => ['data-color' => 'content'], 'waveClass' => 'h-7',
        ]);
        ?>
        <div class="flex shrink-0 items-center gap-1 font-sans">
          <button type="button" class="btn btn-ghost btn-sm hidden w-14 lg:inline-flex" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}×`">1×</button>
          <?php (new Template())->renderThemeable('player/parts/volume', ['range' => 'range range-xs']); ?>
          <button type="button" class="btn btn-ghost btn-sm btn-circle" ars-on:click="expanded = true" aria-label="展开播放器与节目目录"><?php Icon::render('book-open', 'size-4'); ?></button>
        </div>
      </div>
      </div>
    </div>

    <?php /* ============ 正在播放（展开面板） ============ */ ?>
    <div ars-show="expanded" ars-cloak
         ars-transition:enter="transition duration-300 ease-out" ars-transition:enter-start="opacity-0" ars-transition:enter-end="opacity-100"
         ars-transition:leave="transition duration-200 ease-in" ars-transition:leave-start="opacity-100" ars-transition:leave-end="opacity-0"
         ars-on:keydown.escape.window="expanded = false"
         class="fixed inset-0 z-[9991] overflow-y-auto bg-base-100" role="dialog" aria-modal="true" aria-label="正在播放">
      <div data-ars-panel-width="content" class="mx-auto max-w-5xl px-4 pb-12 sm:px-6">
        <div class="meta flex h-16 items-center justify-between border-b hair">
          <span>正在收听</span>
          <button type="button" class="btn btn-ghost btn-circle" ars-on:click="expanded = false" aria-label="收起"><?php Icon::render('x', 'size-5'); ?></button>
        </div>
        <div class="grid gap-12 pt-10 lg:grid-cols-[1fr_320px]">
          <div class="text-center">
            <p class="kicker" ars-text="join(`第 ${current?.number} 期`, categoryName(current?.category))"></p>
            <h2 class="mx-auto mt-4 max-w-2xl text-3xl font-black leading-snug sm:text-4xl" ars-text="current?.title"></h2>
            <img ars-bind:src="current?.cover" alt="" class="mx-auto mt-8 aspect-square w-48 border ink">
            <div class="mt-10 border-y ink py-4">
              <canvas data-spectrum="wave" data-bar="1.5" data-color="content" class="block h-32 w-full" aria-hidden="true"></canvas>
            </div>
            <div class="mt-4 flex items-center gap-4">
              <span class="meta tabular-nums" ars-text="fmt(position)"></span>
              <input type="range" class="range range-xs flex-1 [--range-thumb:var(--color-primary)]" min="0" ars-bind:max="duration" step="1" ars-bind:value="position" ars-on:input="scrub($event.target.value)" ars-on:change="commit($event.target.value)" aria-label="播放进度">
              <span class="meta tabular-nums" ars-text="fmt(duration)"></span>
            </div>
            <div class="mt-8 flex items-center justify-center gap-3">
              <button type="button" class="btn-ring size-11" ars-on:click="prev()" aria-label="上一期"><?php Icon::render('skip-back', 'size-4'); ?></button>
              <button type="button" class="btn-ring size-11" ars-on:click="skip(-15)" aria-label="后退 15 秒"><?php Icon::render('rotate-ccw', 'size-4'); ?></button>
              <button type="button" class="btn-ring size-16" ars-bind:class="playing && 'is-on'" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
                <span ars-show="!playing"><?php Icon::render('play', 'ml-1 size-6 fill-current'); ?></span>
                <span ars-show="playing" ars-cloak><?php Icon::render('pause', 'size-6 fill-current'); ?></span>
              </button>
              <button type="button" class="btn-ring size-11" ars-on:click="skip(30)" aria-label="快进 30 秒"><?php Icon::render('rotate-cw', 'size-4'); ?></button>
              <button type="button" class="btn-ring size-11" ars-on:click="next()" aria-label="下一期"><?php Icon::render('skip-forward', 'size-4'); ?></button>
            </div>
            <div class="mt-6 flex items-center justify-center gap-5 font-sans">
              <button type="button" class="btn btn-ghost btn-sm" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`语速 ${rate}×`">语速 1×</button>
              <label class="flex items-center gap-2">
                <?php Icon::render('volume-2', 'size-4'); ?><span class="sr-only">音量</span>
                <input type="range" class="range range-xs w-28" min="0" max="1" step="0.05" ars-bind:value="muted ? 0 : volume" ars-on:input="setVolume($event.target.value)">
              </label>
              <a ars-bind:href="current?.url" class="btn btn-ghost btn-sm" ars-on:click="expanded = false">阅读节目笔记</a>
            </div>
          </div>
          <section aria-labelledby="queue-title">
            <div class="flex items-center gap-4"><h3 id="queue-title" class="section-title flex-1 text-lg font-black"><span>节目目录</span></h3><button type="button" class="meta shrink-0 hover:text-primary" ars-show="queueItems.length" ars-on:click="clearQueue()">清空</button></div>
            <ol class="mt-4 max-h-[min(32rem,60dvh)] overflow-y-auto overscroll-contain [scrollbar-width:thin]">
              <template ars-for="ep in queueItems">
                <li class="flex items-center gap-1 border-b hair">
                  <button type="button" class="leaders min-w-0 flex-1 py-3 text-left hover:text-primary" ars-bind:class="isCurrent(ep.id) && 'text-primary'" ars-on:click="toggle(ep.id)">
                    <span class="font-display text-xl italic" ars-text="ep.number"></span>
                    <span class="truncate text-[15px] font-semibold" ars-text="ep.title"></span><span class="fill"></span>
                    <span class="meta shrink-0" ars-text="isPlaying(ep.id) ? '收听中' : fmt(ep.duration)"></span>
                  </button>
                  <button type="button" class="shrink-0 rounded-full p-1.5 opacity-50 transition hover:opacity-100 hover:text-primary" ars-on:click="removeFromQueue(ep.id)" aria-label="从播放列表移除" title="从播放列表移除"><?php Icon::render('x', 'size-4'); ?></button>
                </li>
              </template>
            </ol>
            <p class="py-6 text-center text-sm opacity-60" ars-show="!queueItems.length">播放列表是空的</p>
          </section>
        </div>
      </div>
    </div>
</div>
