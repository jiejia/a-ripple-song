<?php
/**
 * Player skin "卡带" (cassette), converted from the ~/workspace/podcast/cassette prototype.
 *
 * Bindings use the plugin's small Alpine-like binder (resources/assets/js/player/binder.js):
 * ars-text, ars-show, ars-bind:attr, ars-on:event, ars-for, ars-data, ars-transition:*, ars-cloak.
 * Expressions run against the player store, e.g. toggle(), fmt(position), current?.title.
 * Themes can override this file with {theme}/a-ripple-song/player/skins/cassette.php.
 */

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Libraries\Icon;
use Jiejia\ARippleSong\Libraries\Template;
?>
<div class="ars-player" data-ars-player data-ars-skin="cassette" hidden>
    <?php /* ============ 播放器：卡座（常驻，固定深色） ============ */ ?>
    <div data-ars-theme="dark" class="fixed inset-x-0 bottom-0 z-[9990] bg-base-100 text-base-content" role="region" data-ars-bar data-ars-surface aria-label="音频播放器">
      <div class="stripes h-2" aria-hidden="true"></div>
      <div data-ars-width="content" class="mx-auto flex h-20 max-w-7xl items-center gap-3 px-3 sm:gap-4 sm:px-6">
        <button type="button" class="flex min-w-0 flex-1 items-center gap-3 rounded-xl py-1 pr-2 text-left hover:bg-base-200 lg:w-72 lg:flex-none" ars-on:click="expanded = true" aria-label="展开正在播放">
          <span class="tape-window h-11 w-24 shrink-0" ars-bind:class="playing && 'is-rolling'" aria-hidden="true"><span class="reel"></span><span class="spool"></span><span class="reel"></span></span>
          <span class="min-w-0">
            <span class="tag block text-secondary" ars-text="error || `EP.${current?.number} · ${loading ? '装带中' : (playing ? 'PLAY' : 'PAUSE')}`"></span>
            <span class="block truncate font-semibold" ars-text="current?.title"></span>
          </span>
        </button>
        <div class="flex items-center gap-1.5">
          <span class="hidden md:contents">
            <button type="button" class="key size-10" ars-on:click="prev()" aria-label="上一集"><?php Icon::render('skip-back', 'size-4'); ?></button>
            <button type="button" class="key size-10" ars-on:click="skip(-15)" aria-label="后退 15 秒"><?php Icon::render('rewind', 'size-4'); ?></button>
          </span>
          <button type="button" class="key h-12 w-14" ars-bind:class="playing && 'is-down'" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
            <span ars-show="!playing"><?php Icon::render('play', 'size-6 fill-current'); ?></span>
            <span ars-show="playing" ars-cloak><?php Icon::render('pause', 'size-6 fill-current'); ?></span>
          </button>
          <span class="hidden md:contents">
            <button type="button" class="key size-10" ars-on:click="skip(30)" aria-label="快进 30 秒"><?php Icon::render('fast-forward', 'size-4'); ?></button>
            <button type="button" class="key size-10" ars-on:click="next()" aria-label="下一集"><?php Icon::render('skip-forward', 'size-4'); ?></button>
          </span>
        </div>
        <div class="hidden min-w-0 flex-1 items-center gap-3 md:flex">
          <span class="counter text-sm" ars-text="fmt(position)">00:00</span>
          <input type="range" class="range range-primary range-xs min-w-0 flex-1" min="0" ars-bind:max="duration" step="1" ars-bind:value="position" ars-on:input="scrub($event.target.value)" ars-on:change="commit($event.target.value)" aria-label="播放进度">
          <span class="font-mono text-sm opacity-60" ars-text="fmt(duration)">00:00</span>
        </div>
        <div class="vu-face hidden h-14 w-28 shrink-0 xl:block" aria-hidden="true"><canvas data-spectrum="vu" data-color="content" class="block size-full"></canvas></div>
        <div class="hidden items-center gap-1 lg:flex">
          <button type="button" class="btn btn-ghost btn-sm font-mono" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}×`">1×</button>
          <?php (new Template())->renderThemeable('player/parts/volume', ['button' => 'btn btn-ghost btn-sm btn-square', 'popover' => 'rounded-box bg-base-200 text-base-content shadow-lg']); ?>
        </div>
        <button type="button" class="btn btn-ghost btn-circle" ars-on:click="expanded = true" aria-label="展开播放器与播放队列"><?php Icon::render('chevron-up', 'size-5'); ?></button>
      </div>
    </div>

    <?php /* ============ 正在播放（展开面板） ============ */ ?>
    <div ars-show="expanded" ars-cloak
         ars-transition:enter="transition duration-250 ease-out" ars-transition:enter-start="translate-y-full" ars-transition:enter-end="translate-y-0"
         ars-transition:leave="transition duration-150 ease-in" ars-transition:leave-start="translate-y-0" ars-transition:leave-end="translate-y-full"
         ars-on:keydown.escape.window="expanded = false"
         data-ars-theme="dark" class="fixed inset-0 z-[9991] overflow-y-auto bg-base-100 text-base-content" role="dialog" aria-modal="true" aria-label="正在播放">
      <div class="stripes h-3" aria-hidden="true"></div>
      <div data-ars-panel-width="content" class="mx-auto max-w-6xl px-4 py-6 sm:px-6">
        <div class="flex items-center justify-between">
          <p class="tag text-secondary">Now Playing · 正在播放</p>
          <button type="button" class="btn btn-ghost btn-circle" ars-on:click="expanded = false" aria-label="收起"><?php Icon::render('chevron-down', 'size-6'); ?></button>
        </div>
        <div class="mt-6 grid grid-cols-1 gap-10 lg:grid-cols-[1.2fr_1fr]">
          <div class="min-w-0">
            <div class="tape mx-auto max-w-lg" ars-bind:class="playing && 'is-rolling'">
              <div class="tape-label">
                <div class="stripes h-5" aria-hidden="true"></div>
                <div class="px-5 pb-4 pt-3">
                  <p class="tag flex justify-between"><span>A 面</span><span ars-text="'EP.' + current?.number"></span></p>
                  <p class="mt-1 truncate font-hand text-3xl" ars-text="current?.title"></p>
                  <div class="tape-window mx-auto mt-3 h-16 w-[70%]"><span class="reel"></span><span class="spool"></span><span class="reel"></span></div>
                </div>
              </div>
            </div>
            <div class="mt-8 flex items-center justify-center gap-5">
              <span class="counter text-3xl" ars-text="fmt(position)">00:00</span>
              <div class="vu-face h-24 w-44 shrink-0" aria-hidden="true"><canvas data-spectrum="vu" data-color="content" class="block size-full"></canvas></div>
            </div>
            <canvas data-spectrum="bars" data-bar="5" data-gap="3" data-color="secondary" class="mt-6 block h-20 w-full" aria-hidden="true"></canvas>
            <input type="range" class="range range-primary mt-3 w-full" min="0" ars-bind:max="duration" step="1" ars-bind:value="position" ars-on:input="scrub($event.target.value)" ars-on:change="commit($event.target.value)" aria-label="播放进度">
            <div class="mt-1 flex justify-between font-mono text-sm opacity-60"><span>00:00</span><span ars-text="fmt(duration)"></span></div>
            <div class="mt-6 flex flex-wrap items-center justify-center gap-2">
              <button type="button" class="key size-14" ars-on:click="prev()" aria-label="上一集"><?php Icon::render('skip-back', 'size-5'); ?></button>
              <button type="button" class="key size-14" ars-on:click="skip(-15)" aria-label="后退 15 秒"><?php Icon::render('rewind', 'size-5'); ?></button>
              <button type="button" class="key h-14 w-20" ars-bind:class="playing && 'is-down'" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
                <span ars-show="!playing"><?php Icon::render('play', 'size-7 fill-current'); ?></span>
                <span ars-show="playing" ars-cloak><?php Icon::render('pause', 'size-7 fill-current'); ?></span>
              </button>
              <button type="button" class="key size-14" ars-on:click="skip(30)" aria-label="快进 30 秒"><?php Icon::render('fast-forward', 'size-5'); ?></button>
              <button type="button" class="key size-14" ars-on:click="next()" aria-label="下一集"><?php Icon::render('skip-forward', 'size-5'); ?></button>
            </div>
            <div class="mt-6 flex items-center justify-center gap-4">
              <button type="button" class="key h-10 px-4 font-mono font-bold" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}×`">1×</button>
              <label class="flex items-center gap-2">
                <?php Icon::render('volume-2', 'size-5'); ?><span class="sr-only">音量</span>
                <input type="range" class="range range-secondary range-xs w-32" min="0" max="1" step="0.05" ars-bind:value="muted ? 0 : volume" ars-on:input="setVolume($event.target.value)">
              </label>
            </div>
          </div>
          <section class="min-w-0 rounded-box bg-base-200 p-5 sm:p-6" aria-labelledby="queue-title">
            <h3 id="queue-title" class="font-display text-2xl">曲目表</h3>
            <ol class="mt-4 space-y-1">
              <template ars-for="(ep, i) in queueItems">
                <li>
                  <button type="button" class="flex w-full items-baseline gap-3 rounded-lg px-2 py-2.5 text-left hover:bg-base-300" ars-bind:class="isCurrent(ep.id) && 'text-secondary'" ars-on:click="toggle(ep.id)">
                    <span class="w-7 shrink-0 font-mono text-sm opacity-60" ars-text="String(i + 1).padStart(2, '0')"></span>
                    <span class="min-w-0 truncate font-semibold" ars-text="ep.title"></span>
                    <span class="dots" aria-hidden="true"></span>
                    <span class="shrink-0 font-mono text-sm" ars-text="isPlaying(ep.id) ? '▶ PLAY' : fmt(ep.duration)"></span>
                  </button>
                </li>
              </template>
            </ol>
          </section>
        </div>
      </div>
    </div>
</div>
