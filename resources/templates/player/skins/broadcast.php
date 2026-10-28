<?php
/**
 * Player skin "广播站" (broadcast), converted from the ~/workspace/podcast/broadcast prototype.
 *
 * Bindings use the plugin's small Alpine-like binder (resources/assets/js/player/binder.js):
 * ars-text, ars-show, ars-bind:attr, ars-on:event, ars-for, ars-data, ars-transition:*, ars-cloak.
 * Expressions run against the player store, e.g. toggle(), fmt(position), current?.title.
 * Themes can override this file with {theme}/a-ripple-song/player/skins/broadcast.php.
 */

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Libraries\Icon;
use Jiejia\ARippleSong\Libraries\Template;
?>
<div class="ars-player" data-ars-player data-ars-skin="broadcast" hidden>
    <?php /* ============ 播放器（常驻，固定深色） ============ */ ?>
    <div data-ars-theme="dark" class="fixed inset-x-0 bottom-0 z-[9990] border-t-2 border-base-content bg-base-100 text-base-content" role="region" data-ars-bar data-ars-surface aria-label="音频播放器">
      <div class="flex h-[4.5rem] items-stretch">
        <button type="button" class="flex min-w-0 flex-1 items-center gap-3 border-r-2 border-base-content/25 pr-3 text-left hover:bg-base-200 md:w-80 md:flex-none" ars-on:click="expanded = true" aria-label="展开正在播放">
          <img ars-bind:src="current?.cover" alt="" class="h-full w-[4.5rem] shrink-0 border-r-2 border-base-content/25 object-cover">
          <span class="min-w-0">
            <span class="label block text-accent" ars-text="error || `EP.${current?.number} · ${loading ? 'Loading' : (playing ? 'On Air' : 'Paused')}`"></span>
            <span class="block truncate font-bold" ars-text="current?.title"></span>
          </span>
        </button>
        <div class="hidden items-stretch md:flex">
          <button type="button" class="grid w-12 place-items-center hover:bg-base-200" ars-on:click="prev()" aria-label="上一集"><?php Icon::render('skip-back', 'size-4'); ?></button>
          <button type="button" class="grid w-12 place-items-center hover:bg-base-200" ars-on:click="skip(-15)" aria-label="后退 15 秒"><?php Icon::render('rotate-ccw', 'size-4'); ?></button>
        </div>
        <button type="button" class="grid w-[4.5rem] shrink-0 place-items-center bg-primary text-primary-content hover:bg-accent hover:text-accent-content" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
          <span ars-show="!playing"><?php Icon::render('play', 'size-7 fill-current'); ?></span>
          <span ars-show="playing" ars-cloak><?php Icon::render('pause', 'size-7 fill-current'); ?></span>
        </button>
        <div class="hidden items-stretch md:flex">
          <button type="button" class="grid w-12 place-items-center hover:bg-base-200" ars-on:click="skip(30)" aria-label="快进 30 秒"><?php Icon::render('rotate-cw', 'size-4'); ?></button>
          <button type="button" class="grid w-12 place-items-center border-r-2 border-base-content/25 hover:bg-base-200" ars-on:click="next()" aria-label="下一集"><?php Icon::render('skip-forward', 'size-4'); ?></button>
        </div>
        <div class="hidden min-w-0 flex-1 flex-col justify-center gap-1.5 px-5 md:flex">
          <div class="flex items-baseline justify-between font-mono text-sm">
            <span class="text-lg font-semibold text-accent" ars-text="fmt(position)">00:00</span>
            <span class="text-base-content/60" ars-text="fmt(duration)">00:00</span>
          </div>
          <input type="range" class="range range-accent range-xs w-full" min="0" ars-bind:max="duration" step="1" ars-bind:value="position" ars-on:input="scrub($event.target.value)" ars-on:change="commit($event.target.value)" aria-label="播放进度">
        </div>
        <canvas data-spectrum="led" data-bar="4" data-gap="2" data-color="accent" class="my-3 mr-4 hidden w-40 xl:block" aria-hidden="true"></canvas>
        <div class="hidden items-stretch border-l-2 border-base-content/25 lg:flex">
          <button type="button" class="label w-16 hover:bg-base-200" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}×`">1×</button>
          <?php (new Template())->renderThemeable('player/parts/volume', ['button' => 'grid h-full w-12 place-items-center hover:bg-base-200', 'popover' => 'border-2 border-base-content bg-base-100 text-base-content', 'align' => 'end']); ?>
        </div>
        <button type="button" class="grid w-14 place-items-center border-l-2 border-base-content/25 hover:bg-base-200" ars-on:click="expanded = true" aria-label="展开播放器与播放队列"><?php Icon::render('maximize-2', 'size-4'); ?></button>
      </div>
    </div>

    <?php /* ============ 正在播放（展开面板） ============ */ ?>
    <div ars-show="expanded" ars-cloak
         ars-transition:enter="transition duration-200 ease-out" ars-transition:enter-start="translate-y-full" ars-transition:enter-end="translate-y-0"
         ars-transition:leave="transition duration-150 ease-in" ars-transition:leave-start="translate-y-0" ars-transition:leave-end="translate-y-full"
         ars-on:keydown.escape.window="expanded = false"
         class="fixed inset-0 z-[9991] overflow-y-auto bg-base-100" role="dialog" aria-modal="true" aria-label="正在播放">
      <div class="flex h-16 items-stretch border-b-2 rule">
        <p class="onair label flex items-center gap-2 border-r-2 rule px-6" ars-bind:class="playing && 'is-live'"><span class="dot"></span>Now Playing</p>
        <button type="button" class="ml-auto grid w-16 place-items-center border-l-2 rule hover:bg-accent hover:text-accent-content" ars-on:click="expanded = false" aria-label="收起"><?php Icon::render('x', 'size-6'); ?></button>
      </div>
      <div class="grid lg:grid-cols-12">
        <div class="border-b-2 rule p-6 sm:p-10 lg:col-span-8 lg:border-b-0 lg:border-r-2">
          <div class="flex flex-col gap-8 sm:flex-row sm:items-end">
            <img ars-bind:src="current?.cover" alt="" class="aspect-square w-40 border-2 rule hard-shadow sm:w-56">
            <div class="min-w-0">
              <p class="font-mono text-7xl font-semibold leading-none sm:text-8xl" ars-text="current?.number"></p>
              <h2 class="mt-3 text-3xl font-black leading-tight" ars-text="current?.title"></h2>
            </div>
          </div>
          <canvas data-spectrum="led" data-bar="7" data-gap="3" class="mt-10 h-40 w-full" aria-hidden="true"></canvas>
          <div class="mt-6 flex items-baseline justify-between font-mono">
            <span class="text-4xl font-semibold" ars-text="fmt(position)"></span>
            <span class="text-lg text-base-content/60" ars-text="fmt(duration)"></span>
          </div>
          <input type="range" class="range range-primary mt-3 w-full" min="0" ars-bind:max="duration" step="1" ars-bind:value="position" ars-on:input="scrub($event.target.value)" ars-on:change="commit($event.target.value)" aria-label="播放进度">
          <div class="mt-8 flex flex-wrap items-center gap-3">
            <button type="button" class="btn btn-square btn-lg border-2 rule bg-base-100" ars-on:click="prev()" aria-label="上一集"><?php Icon::render('skip-back', 'size-5'); ?></button>
            <button type="button" class="btn btn-square btn-lg border-2 rule bg-base-100" ars-on:click="skip(-15)" aria-label="后退 15 秒"><?php Icon::render('rotate-ccw', 'size-5'); ?></button>
            <button type="button" class="btn btn-primary btn-lg press hard-shadow border-2 rule px-10" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
              <span ars-show="!playing"><?php Icon::render('play', 'size-6 fill-current'); ?></span>
              <span ars-show="playing" ars-cloak><?php Icon::render('pause', 'size-6 fill-current'); ?></span>
            </button>
            <button type="button" class="btn btn-square btn-lg border-2 rule bg-base-100" ars-on:click="skip(30)" aria-label="快进 30 秒"><?php Icon::render('rotate-cw', 'size-5'); ?></button>
            <button type="button" class="btn btn-square btn-lg border-2 rule bg-base-100" ars-on:click="next()" aria-label="下一集"><?php Icon::render('skip-forward', 'size-5'); ?></button>
            <button type="button" class="btn btn-lg border-2 rule bg-base-100 font-mono" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}×`">1×</button>
            <label class="flex items-center gap-2">
              <?php Icon::render('volume-2', 'size-5'); ?><span class="sr-only">音量</span>
              <input type="range" class="range range-xs w-28" min="0" max="1" step="0.05" ars-bind:value="muted ? 0 : volume" ars-on:input="setVolume($event.target.value)">
            </label>
          </div>
        </div>
        <section class="lg:col-span-4" aria-labelledby="queue-title">
          <h3 id="queue-title" class="label border-b-2 rule px-6 py-4">播放队列</h3>
          <ul>
            <template ars-for="ep in queueItems">
              <li>
                <button type="button" class="flex w-full items-center gap-4 border-b-2 rule px-6 py-3 text-left hover:bg-accent hover:text-accent-content" ars-bind:class="isCurrent(ep.id) && 'bg-base-200'" ars-on:click="toggle(ep.id)">
                  <span class="w-10 font-mono text-xl font-semibold" ars-text="ep.number"></span>
                  <span class="min-w-0 flex-1 truncate font-bold" ars-text="ep.title"></span>
                  <span class="label" ars-text="isPlaying(ep.id) ? 'On Air' : fmt(ep.duration)"></span>
                </button>
              </li>
            </template>
          </ul>
        </section>
      </div>
    </div>
</div>
