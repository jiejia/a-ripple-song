<?php
/**
 * Player skin "终端" (terminal), converted from the ~/workspace/podcast/terminal prototype.
 *
 * Bindings use the plugin's small Alpine-like binder (resources/src/binder.js):
 * ars-text, ars-show, ars-bind:attr, ars-on:event, ars-for, ars-data, ars-transition:*, ars-cloak.
 * Expressions run against the player store, e.g. toggle(), fmt(position), current?.title.
 * Themes can override this file with {theme}/a-ripple-song/player/skins/terminal.php.
 */

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Libraries\Icon;
?>
<div class="ars-player" data-ars-player data-ars-skin="terminal" hidden>
    <?php /* ============ 播放器：底部状态行（常驻） ============ */ ?>
    <div class="fixed inset-x-0 bottom-0 z-[9990] border-t tui-line bg-base-200 [--tui-bg:var(--color-base-200)]" role="region" data-ars-bar aria-label="音频播放器">
      <div data-ars-width="content" class="mx-auto flex h-16 max-w-6xl items-center gap-3 px-4">
        <div class="flex shrink-0 items-center">
          <span class="hidden md:contents">
            <button type="button" class="cmd" ars-on:click="prev()" aria-label="上一集">|&lt;</button>
            <button type="button" class="cmd" ars-on:click="skip(-15)" aria-label="后退 15 秒">-15</button>
          </span>
          <button type="button" class="cmd text-lg text-primary" ars-bind:class="playing && 'is-on'" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放" ars-text="playing ? '❚❚' : '▶ '">▶ </button>
          <span class="hidden md:contents">
            <button type="button" class="cmd" ars-on:click="skip(30)" aria-label="快进 30 秒">+30</button>
            <button type="button" class="cmd" ars-on:click="next()" aria-label="下一集">&gt;|</button>
          </span>
        </div>
        <button type="button" class="min-w-0 flex-1 text-left hover:text-primary lg:max-w-xs" ars-on:click="expanded = true" aria-label="展开正在播放">
          <span class="dim block text-xs" ars-text="error || `EP.${String(current?.number).padStart(3, '0')} ${loading ? 'loading…' : (playing ? 'playing' : 'paused')}`"></span>
          <span class="block truncate" ars-text="current?.title"></span>
        </button>
        <div class="hidden shrink-0 items-center gap-3 tabular-nums md:flex">
          <label class="bar-seek tracking-[-0.05em] text-primary">
            <span class="sr-only">播放进度</span>
            <span aria-hidden="true" ars-text="'█'.repeat(Math.round(progress / 100 * 20)) + '░'.repeat(20 - Math.round(progress / 100 * 20))">░░░░░░░░░░░░░░░░░░░░</span>
            <input type="range" min="0" ars-bind:max="duration" step="1" ars-bind:value="position" ars-on:input="scrub($event.target.value)" ars-on:change="commit($event.target.value)">
          </label>
          <span><span ars-text="fmt(position)">00:00</span><span class="dim">/<span ars-text="fmt(duration)">00:00</span></span></span>
        </div>
        <canvas data-spectrum="ascii" data-bar="11" data-gap="1" class="hidden h-10 w-28 shrink-0 xl:block" aria-hidden="true"></canvas>
        <span class="hidden lg:contents">
          <button type="button" class="cmd" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}x`">1x</button>
          <button type="button" class="cmd" ars-on:click="toggleMute()" ars-bind:aria-label="muted ? '取消静音' : '静音'" ars-text="muted ? 'mute' : 'vol'">vol</button>
        </span>
        <button type="button" class="cmd" ars-on:click="expanded = true" aria-label="展开播放器与播放队列">^</button>
      </div>
    </div>

    <?php /* ============ 正在播放（展开面板，类似 htop） ============ */ ?>
    <div ars-show="expanded" ars-cloak
         ars-transition:enter="transition duration-150 ease-out" ars-transition:enter-start="opacity-0" ars-transition:enter-end="opacity-100"
         ars-transition:leave="transition duration-100 ease-in" ars-transition:leave-start="opacity-100" ars-transition:leave-end="opacity-0"
         ars-on:keydown.escape.window="expanded = false"
         class="fixed inset-0 z-[9991] overflow-y-auto bg-base-100" role="dialog" aria-modal="true" aria-label="正在播放">
      <div class="mx-auto max-w-6xl px-4 py-6">
        <div class="flex items-center justify-between">
          <p class="prompt">now-playing --watch</p>
          <button type="button" class="cmd" ars-on:click="expanded = false" aria-label="收起">q 退出</button>
        </div>
        <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-[1.4fr_1fr]">
          <div class="min-w-0">
            <div class="tui p-4 pt-6">
              <span class="tui-title">spectrum</span>
              <canvas data-spectrum="ascii" data-bar="16" data-gap="2" class="block h-48 w-full" aria-hidden="true"></canvas>
            </div>
            <dl class="mt-6 grid grid-cols-[8ch_1fr] gap-y-1">
              <dt class="dim">TITLE</dt><dd class="min-w-0 truncate font-bold" ars-text="current?.title"></dd>
              <dt class="dim">EP</dt><dd class="text-primary" ars-text="String(current?.number).padStart(3, '0')"></dd>
              <dt class="dim">STATE</dt><dd ars-text="playing ? 'playing' : 'paused'"></dd>
              <dt class="dim">TIME</dt><dd class="tabular-nums"><span ars-text="fmt(position)"></span> / <span ars-text="fmt(duration)"></span></dd>
            </dl>
            <label class="bar-seek mt-4 overflow-hidden text-lg tracking-[-0.05em] text-primary">
              <span class="sr-only">播放进度</span>
              <span aria-hidden="true" class="block whitespace-nowrap" ars-text="'█'.repeat(Math.round(progress / 100 * 60)) + '░'.repeat(60 - Math.round(progress / 100 * 60))"></span>
              <input type="range" min="0" ars-bind:max="duration" step="1" ars-bind:value="position" ars-on:input="scrub($event.target.value)" ars-on:change="commit($event.target.value)">
            </label>
            <div class="mt-6 flex flex-wrap items-center gap-2 text-lg">
              <button type="button" class="cmd" ars-on:click="prev()" aria-label="上一集">|&lt; prev</button>
              <button type="button" class="cmd" ars-on:click="skip(-15)" aria-label="后退 15 秒">-15s</button>
              <button type="button" class="cmd text-primary" ars-bind:class="playing && 'is-on'" ars-on:click="toggle()" ars-text="playing ? '❚❚ pause' : '▶ play'">▶ play</button>
              <button type="button" class="cmd" ars-on:click="skip(30)" aria-label="快进 30 秒">+30s</button>
              <button type="button" class="cmd" ars-on:click="next()" aria-label="下一集">next &gt;|</button>
              <button type="button" class="cmd" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`rate ${rate}x`">rate 1x</button>
            </div>
            <label class="mt-4 flex items-center gap-3">
              <span class="dim">VOL</span>
              <span class="bar-seek tracking-[-0.05em] text-secondary">
                <span aria-hidden="true" ars-text="'▮'.repeat(Math.round((muted ? 0 : volume) * 10)) + '▯'.repeat(10 - Math.round((muted ? 0 : volume) * 10))"></span>
                <input type="range" min="0" max="1" step="0.1" ars-bind:value="muted ? 0 : volume" ars-on:input="setVolume($event.target.value)" aria-label="音量">
              </span>
            </label>
          </div>
          <section class="tui min-w-0 p-4 pt-6" aria-labelledby="queue-title">
            <h3 id="queue-title" class="tui-title">ls -l ./queue</h3>
            <ul>
              <template ars-for="ep in queueItems">
                <li>
                  <button type="button" class="flex w-full gap-3 px-1 py-1 text-left hover:bg-base-content hover:text-base-100" ars-bind:class="isCurrent(ep.id) && 'text-primary'" ars-on:click="toggle(ep.id)">
                    <span class="w-2 shrink-0" ars-text="isCurrent(ep.id) ? '>' : ''"></span>
                    <span class="shrink-0" ars-text="String(ep.number).padStart(3, '0')"></span>
                    <span class="min-w-0 flex-1 truncate" ars-text="ep.title"></span>
                    <span class="dim shrink-0 tabular-nums" ars-text="fmt(ep.duration)"></span>
                  </button>
                </li>
              </template>
            </ul>
          </section>
        </div>
      </div>
    </div>
</div>
