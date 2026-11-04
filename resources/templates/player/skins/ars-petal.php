<?php
/**
 * Player skin "花瓣" (ars-petal), converted from the ~/workspace/podcast/petal prototype.
 *
 * Bindings use the plugin's small Alpine-like binder (resources/assets/js/player/binder.js):
 * ars-text, ars-show, ars-bind:attr, ars-on:event, ars-for, ars-data, ars-transition:*, ars-cloak.
 * Expressions run against the player store, e.g. toggle(), fmt(position), current?.title.
 * The .seek-s sliders read their fill from the --p custom property (0–100%), bound with ars-bind:style.
 * Themes can override this file with {theme}/a-ripple-song/player/skins/ars-petal.php.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Icon;
use Jiejia\ARippleSong\Libraries\Template;
?>
<div class="ars-player" data-ars-player data-ars-skin="ars-petal" hidden>
    <?php /* ============ 底部：浮起的玻璃播放条（常驻） ============ */ ?>
    <div class="fixed inset-x-0 bottom-0 z-[9990] px-3 pb-3 md:pb-5" role="region" data-ars-bar aria-label="音频播放器">
      <div data-ars-width="box" class="player-bar mx-auto flex max-w-3xl items-center gap-3 rounded-[1.75rem] p-2 pr-3 sm:gap-4" ars-bind:style="`--p:${progress}%`">
        <button type="button" class="shrink-0" ars-on:click="expanded = true" aria-label="展开正在播放">
          <img ars-bind:src="current?.cover" alt="" class="size-12 rounded-[1.1rem] object-cover sm:size-14">
        </button>
        <div class="min-w-0 flex-1 md:w-48 md:flex-none">
          <p class="flex items-center gap-1.5 text-xs"><span class="badge badge-primary badge-xs font-medium" ars-show="current?.number" ars-text="`EP.${current?.number}`"></span><span class="mute truncate" ars-text="error || (loading ? '加载中…' : categoryName(current?.category))"></span></p>
          <a ars-bind:href="current?.url" class="mt-0.5 block truncate text-sm font-semibold leading-tight hover:underline" ars-text="current?.title"></a>
        </div>
        <div class="flex shrink-0 items-center gap-1 sm:gap-2">
          <button type="button" class="btn-plain hidden size-9 rounded-full sm:inline-flex" ars-on:click="skip(-15)" aria-label="后退 15 秒"><?php Icon::render('rotate-ccw', 'size-[1.05rem]'); ?></button>
          <button type="button" class="btn btn-primary btn-circle size-12 shrink-0 shadow-lg shadow-primary/30" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
            <span ars-show="!playing"><?php Icon::render('play', 'ml-0.5 size-[1.15rem] fill-current'); ?></span>
            <span ars-show="playing" ars-cloak><?php Icon::render('pause', 'size-[1.15rem] fill-current'); ?></span>
          </button>
          <button type="button" class="btn-plain hidden size-9 rounded-full sm:inline-flex" ars-on:click="skip(15)" aria-label="快进 15 秒"><?php Icon::render('rotate-cw', 'size-[1.05rem]'); ?></button>
        </div>
        <?php
        (new Template())->renderThemeable('player/parts/progress', [
            'time' => 'mono mute',
            'range' => 'seek-s',
            'spectrum' => ['data-spectrum' => 'mirror', 'data-color' => 'secondary', 'data-bar' => '2', 'data-gap' => '3', 'class' => 'hidden h-8 w-20 shrink-0 2xl:block'],
            'wave' => ['data-color' => 'primary'],
        ]);
        ?>
        <?php (new Template())->renderThemeable('player/parts/volume', ['button' => 'btn-plain size-9 rounded-full', 'icon' => 'size-[1.05rem]', 'range' => 'range range-primary range-xs']); ?>
        <button type="button" class="chip mono hidden !h-8 !px-3 text-xs lg:inline-flex" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}x`">1x</button>
        <button type="button" class="btn-plain size-9 shrink-0 rounded-full" ars-on:click="expanded = true" aria-label="展开播放器与播放队列"><?php Icon::render('chevron-up', 'size-5'); ?></button>
      </div>
    </div>

    <?php /* ============ 正在播放（展开面板）：渐变环形播放器 + 窗口式播放队列 ============ */ ?>
    <div ars-show="expanded" ars-cloak
         ars-transition:enter="transition duration-300 ease-out" ars-transition:enter-start="translate-y-8 opacity-0" ars-transition:enter-end="translate-y-0 opacity-100"
         ars-transition:leave="transition duration-200 ease-in" ars-transition:leave-start="translate-y-0 opacity-100" ars-transition:leave-end="translate-y-8 opacity-0"
         ars-on:keydown.escape.window="expanded = false"
         class="fixed inset-0 z-[9991] overflow-y-auto bg-base-100" role="dialog" aria-modal="true" aria-label="正在播放">
      <div class="glow left-[-10%] top-[-10%] size-[40rem] bg-[var(--glow-a)]" aria-hidden="true"></div>
      <div class="glow right-[-10%] top-[20%] size-[36rem] bg-[var(--glow-b)]" aria-hidden="true"></div>
      <div data-ars-panel-width="content" class="relative isolate mx-auto max-w-5xl px-4 py-5 sm:px-6">
        <div class="flex items-center justify-between">
          <button type="button" class="btn-white size-11 rounded-full" ars-on:click="expanded = false" aria-label="收起"><?php Icon::render('chevron-down', 'size-5'); ?></button>
          <p class="mono text-sm">正在播放</p>
          <a ars-bind:href="current?.url" ars-on:click="expanded = false" class="btn-white h-11 rounded-full px-5 text-sm">节目笔记<?php Icon::render('arrow-right', 'size-4'); ?></a>
        </div>
        <div class="mt-6 grid grid-cols-1 items-start gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
          <div class="panel mx-auto w-full max-w-md px-6 pb-8 pt-10 text-center shadow-xl shadow-base-content/5">
            <div class="petal-ring mx-auto size-64 sm:size-72" ars-bind:style="`--p:${progress}`">
              <div class="absolute inset-[20px] overflow-hidden rounded-full"><img ars-bind:src="current?.cover" alt="" class="size-full object-cover"></div>
              <span class="ring-knob"></span>
            </div>
            <p class="mt-8" ars-show="current?.category"><span class="badge badge-soft badge-primary" ars-text="categoryName(current?.category)"></span></p>
            <h2 class="display mt-3 text-balance text-2xl" ars-text="current?.title"></h2>
            <p class="mute mt-1 text-sm" ars-text="current?.hosts || show.title"></p>
            <canvas data-spectrum="mirror" data-color="primary" data-bar="3" data-gap="4" class="mt-6 block h-16 w-full" aria-hidden="true"></canvas>
            <input type="range" class="seek-s mt-1 w-full" ars-bind:style="`--p:${progress}%`" min="0" ars-bind:max="duration" step="1" ars-bind:value="position" ars-on:input="scrub($event.target.value)" ars-on:change="commit($event.target.value)" aria-label="播放进度">
            <div class="mono mute flex justify-between text-xs tabular-nums"><span ars-text="fmt(position)"></span><span ars-text="fmt(duration)"></span></div>
            <div class="mt-5 flex items-center justify-center gap-3">
              <button type="button" class="btn-gray size-11 rounded-full" ars-on:click="prev()" aria-label="上一集"><?php Icon::render('skip-back', 'size-5'); ?></button>
              <button type="button" class="btn-gray size-11 rounded-full" ars-on:click="skip(-15)" aria-label="后退 15 秒"><?php Icon::render('rotate-ccw', 'size-5'); ?></button>
              <button type="button" class="btn btn-primary btn-circle size-16 shadow-lg shadow-primary/30" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
                <span ars-show="!playing"><?php Icon::render('play', 'ml-1 size-6 fill-current'); ?></span>
                <span ars-show="playing" ars-cloak><?php Icon::render('pause', 'size-6 fill-current'); ?></span>
              </button>
              <button type="button" class="btn-gray size-11 rounded-full" ars-on:click="skip(15)" aria-label="快进 15 秒"><?php Icon::render('rotate-cw', 'size-5'); ?></button>
              <button type="button" class="btn-gray size-11 rounded-full" ars-on:click="next()" aria-label="下一集"><?php Icon::render('skip-forward', 'size-5'); ?></button>
            </div>
            <div class="mt-5 flex items-center justify-center gap-5">
              <button type="button" class="chip mono" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}x`">1x</button>
              <label class="flex items-center gap-2">
                <?php Icon::render('volume-2', 'mute size-5'); ?><span class="sr-only">音量</span>
                <input type="range" class="seek-s w-28" ars-bind:style="`--p:${(muted ? 0 : volume) * 100}%`" min="0" max="1" step="0.05" ars-bind:value="muted ? 0 : volume" ars-on:input="setVolume($event.target.value)">
              </label>
            </div>
          </div>
          <section class="mockup-window min-w-0 border border-base-300 bg-base-100" aria-labelledby="ars-queue-title">
            <div class="border-t border-base-300 p-3 sm:p-5">
              <div class="flex items-center justify-between px-2 pb-3">
                <h3 id="ars-queue-title" class="display text-xl">播放队列</h3>
                <span class="flex items-center gap-3"><span class="mono mute text-xs" ars-text="queueItems.length + ' 期'"></span><button type="button" class="mute text-sm hover:text-primary" ars-show="queueItems.length" ars-on:click="clearQueue()">清空</button></span>
              </div>
              <ol class="max-h-[min(34rem,60dvh)] overflow-y-auto overscroll-contain [scrollbar-width:thin]">
                <template ars-for="ep in queueItems">
                  <li class="flex items-center gap-1">
                    <button type="button" class="flex min-w-0 flex-1 items-center gap-3 rounded-2xl p-2.5 text-left hover:bg-base-200" ars-bind:class="isCurrent(ep.id) && 'bg-base-200'" ars-on:click="toggle(ep.id)">
                      <img ars-bind:src="ep.cover" alt="" class="size-14 shrink-0 rounded-xl object-cover">
                      <span class="min-w-0 flex-1"><span class="block truncate font-semibold" ars-text="ep.title"></span><span class="mute block text-xs" ars-text="join(categoryName(ep.category), fmt(ep.duration))"></span></span>
                      <span ars-show="isPlaying(ep.id)" ars-cloak class="eq"><b></b><b></b><b></b></span>
                    </button>
                    <button type="button" class="btn-plain size-8 shrink-0 rounded-full opacity-50 hover:opacity-100" ars-on:click="removeFromQueue(ep.id)" aria-label="从播放列表移除" title="从播放列表移除"><?php Icon::render('x', 'size-4'); ?></button>
                  </li>
                </template>
              </ol>
              <p class="mute py-6 text-center text-sm" ars-show="!queueItems.length">播放列表是空的</p>
            </div>
          </section>
        </div>
      </div>
    </div>
</div>
