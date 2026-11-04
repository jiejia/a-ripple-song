<?php
/**
 * Player skin "珊瑚" (coral), converted from the ~/workspace/podcast/coral prototype.
 *
 * Bindings use the plugin's small Alpine-like binder (resources/assets/js/player/binder.js):
 * ars-text, ars-show, ars-bind:attr, ars-on:event, ars-for, ars-data, ars-transition:*, ars-cloak.
 * Expressions run against the player store, e.g. toggle(), fmt(position), current?.title.
 * Themes can override this file with {theme}/a-ripple-song/player/skins/coral.php.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Icon;
use Jiejia\ARippleSong\Libraries\Template;
?>
<div class="ars-player" data-ars-player data-ars-skin="coral" hidden>
    <?php /* ============ 播放器（常驻，底部悬浮） ============ */ ?>
    <div class="fixed inset-x-0 bottom-0 z-[9990] px-2 pb-2 sm:px-5 sm:pb-5" role="region" data-ars-bar aria-label="音频播放器">
      <div data-ars-width="box" data-ars-surface class="panel mx-auto flex max-w-7xl items-center gap-3 rounded-2xl p-2.5 shadow-[0_20px_50px_-20px_oklch(30%_0.05_270/0.45)] sm:gap-4 sm:px-4">
        <button type="button" class="shrink-0" ars-on:click="expanded = true" aria-label="展开播放器"><img ars-bind:src="current?.cover" alt="" class="size-12 rounded-xl"></button>
        <div class="min-w-0 flex-1 md:w-40 md:flex-none xl:w-56">
          <a ars-bind:href="current?.url" class="block truncate text-sm font-semibold hover:text-primary" ars-text="current?.title"></a>
          <p class="mute truncate text-xs" ars-text="error || (loading ? '加载中…' : (current?.hosts ? 'by ' + current.hosts : ''))"></p>
        </div>
        <div class="flex shrink-0 items-center gap-1 rounded-xl bg-base-200 p-1 sm:gap-2 sm:px-2">
          <button type="button" class="btn btn-ghost btn-circle btn-sm hidden sm:inline-flex" ars-on:click="expanded = true" aria-label="播放队列"><?php Icon::render('list-music', 'size-4'); ?></button>
          <button type="button" class="btn btn-ghost btn-circle btn-sm hidden sm:inline-flex" ars-on:click="prev()" aria-label="上一集"><?php Icon::render('skip-back', 'size-4 fill-current'); ?></button>
          <button type="button" class="grid size-10 place-items-center rounded-full bg-secondary text-secondary-content transition hover:scale-105" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
            <span ars-show="!playing"><?php Icon::render('play', 'ml-0.5 size-4 fill-current'); ?></span>
            <span ars-show="playing" ars-cloak><?php Icon::render('pause', 'size-4 fill-current'); ?></span>
          </button>
          <button type="button" class="btn btn-ghost btn-circle btn-sm hidden sm:inline-flex" ars-on:click="next()" aria-label="下一集"><?php Icon::render('skip-forward', 'size-4 fill-current'); ?></button>
          <button type="button" class="btn btn-ghost btn-sm hidden w-11 px-0 text-xs sm:inline-flex" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}×`">1×</button>
        </div>
        <?php
        (new Template())->renderThemeable('player/parts/progress', [
            'time' => 'mute',
            'spectrum' => ['data-spectrum' => 'bars', 'data-bar' => '2', 'data-gap' => '2', 'class' => 'hidden h-9 w-24 shrink-0 xl:block'],
            'waveClass' => 'h-9',
        ]);
        ?>
        <?php (new Template())->renderThemeable('player/parts/volume', ['button' => 'grid size-10 shrink-0 place-items-center rounded-full hover:bg-base-200']); ?>
        <button type="button" class="hidden size-10 shrink-0 place-items-center rounded-full bg-base-200 text-primary lg:grid" ars-on:click="toggleFav(current?.id)" ars-bind:aria-pressed="isFav(current?.id)" aria-label="收藏当前节目">
          <span ars-show="!isFav(current?.id)"><?php Icon::render('heart', 'size-4'); ?></span>
          <span ars-show="isFav(current?.id)" ars-cloak><?php Icon::render('heart', 'size-4 fill-current'); ?></span>
        </button>
        <button type="button" class="grid size-10 shrink-0 place-items-center rounded-full hover:bg-base-200" ars-on:click="expanded = true" aria-label="展开播放器与播放队列"><?php Icon::render('chevron-up', 'size-5'); ?></button>
      </div>
    </div>

    <?php /* ============ 正在播放（展开面板） ============ */ ?>
    <div ars-show="expanded" ars-cloak
         ars-transition:enter="transition duration-250 ease-out" ars-transition:enter-start="opacity-0 translate-y-6" ars-transition:enter-end="opacity-100 translate-y-0"
         ars-transition:leave="transition duration-150 ease-in" ars-transition:leave-start="opacity-100" ars-transition:leave-end="opacity-0"
         ars-on:keydown.escape.window="expanded = false"
         class="fixed inset-0 z-[9991] overflow-y-auto bg-[var(--page)] sm:p-5" role="dialog" aria-modal="true" aria-label="正在播放">
      <div data-ars-panel-width="box" class="dash mx-auto min-h-full max-w-6xl p-4 sm:rounded-[2rem] sm:p-8">
        <div class="flex items-center justify-between">
          <p class="kicker mute">Now Playing · 正在播放</p>
          <button type="button" class="grid size-11 place-items-center rounded-full bg-base-200" ars-on:click="expanded = false" aria-label="收起"><?php Icon::render('x', 'size-5'); ?></button>
        </div>
        <div class="mt-6 grid grid-cols-1 gap-8 lg:grid-cols-[1.2fr_1fr]">
          <section class="panel min-w-0 overflow-hidden rounded-[1.5rem]">
            <div class="p-5 sm:p-6">
              <div class="flex items-start gap-4">
                <img ars-bind:src="current?.cover" alt="" class="size-16 shrink-0 rounded-full">
                <div class="min-w-0 flex-1">
                  <h2 class="text-xl font-semibold leading-snug" ars-text="current?.title"></h2>
                  <p class="mute mt-1 text-sm" ars-text="join(current?.hosts && 'by ' + current.hosts, categoryName(current?.category))"></p>
                </div>
                <button type="button" class="grid size-10 shrink-0 place-items-center rounded-full hover:bg-base-200" ars-on:click="toggleFav(current?.id)" ars-bind:aria-pressed="isFav(current?.id)" aria-label="收藏">
                  <span ars-show="!isFav(current?.id)"><?php Icon::render('bookmark', 'size-5'); ?></span>
                  <span ars-show="isFav(current?.id)" ars-cloak class="text-primary"><?php Icon::render('bookmark', 'size-5 fill-current'); ?></span>
                </button>
              </div>
              <p class="mute mt-5 text-sm leading-relaxed" ars-text="current?.excerpt"></p>
              <div class="wave-wrap relative mt-8 h-16">
                <canvas data-spectrum="pulse" data-bar="2" data-gap="2" class="block h-full w-full" aria-hidden="true"></canvas>
                <input type="range" class="wave-seek" min="0" ars-bind:max="duration" step="1" ars-bind:value="position" ars-on:input="scrub($event.target.value)" ars-on:change="commit($event.target.value)" aria-label="播放进度">
              </div>
              <div class="mute mt-2 flex justify-between text-xs tabular-nums"><span ars-text="fmt(position)"></span><span ars-text="fmt(duration)"></span></div>
            </div>
            <div class="flex items-center justify-between bg-base-200 px-5 py-4 sm:px-6">
              <button type="button" class="btn btn-ghost btn-sm rounded-lg text-xs" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}×`">1×</button>
              <div class="flex items-center gap-3">
                <button type="button" class="btn btn-ghost btn-circle btn-sm" ars-on:click="skip(-15)" aria-label="后退 15 秒"><?php Icon::render('rotate-ccw', 'size-4'); ?></button>
                <button type="button" class="btn btn-ghost btn-circle" ars-on:click="prev()" aria-label="上一集"><?php Icon::render('skip-back', 'size-5 fill-current'); ?></button>
                <button type="button" class="grid size-14 place-items-center rounded-full bg-secondary text-secondary-content transition hover:scale-105" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
                  <span ars-show="!playing"><?php Icon::render('play', 'ml-0.5 size-6 fill-current'); ?></span>
                  <span ars-show="playing" ars-cloak><?php Icon::render('pause', 'size-6 fill-current'); ?></span>
                </button>
                <button type="button" class="btn btn-ghost btn-circle" ars-on:click="next()" aria-label="下一集"><?php Icon::render('skip-forward', 'size-5 fill-current'); ?></button>
                <button type="button" class="btn btn-ghost btn-circle btn-sm" ars-on:click="skip(30)" aria-label="快进 30 秒"><?php Icon::render('rotate-cw', 'size-4'); ?></button>
              </div>
              <label class="flex items-center gap-2">
                <?php Icon::render('volume-2', 'size-4'); ?><span class="sr-only">音量</span>
                <input type="range" class="range range-primary range-xs w-20" min="0" max="1" step="0.05" ars-bind:value="muted ? 0 : volume" ars-on:input="setVolume($event.target.value)">
              </label>
            </div>
          </section>
          <section class="min-w-0" aria-labelledby="queue-title">
            <div class="flex items-center justify-between gap-3"><h3 id="queue-title" class="text-lg font-semibold">播放队列</h3><button type="button" class="mute text-sm hover:underline" ars-show="queueItems.length" ars-on:click="clearQueue()">清空</button></div>
            <ul class="mt-4 space-y-2 max-h-[min(32rem,60dvh)] overflow-y-auto overscroll-contain [scrollbar-width:thin] -mx-1 px-1 py-1">
              <template ars-for="ep in queueItems">
                <li class="flex items-center gap-1">
                  <button type="button" class="panel flex min-w-0 flex-1 items-center gap-3 rounded-xl p-2 pr-3 text-left transition" ars-bind:class="isCurrent(ep.id) && 'is-hot'" ars-on:click="toggle(ep.id)">
                    <img ars-bind:src="ep.cover" alt="" class="size-11 shrink-0 rounded-lg">
                    <span class="min-w-0 flex-1"><span class="block truncate text-sm font-semibold" ars-text="ep.title"></span><span class="mute block text-xs" ars-text="`EP.${ep.number} · ${fmt(ep.duration)}`"></span></span>
                    <span ars-show="isPlaying(ep.id)" class="size-2 shrink-0 rounded-full bg-current" aria-label="正在播放"></span>
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
