<?php
/**
 * Player skin "圈子" (ars-circle), converted from the ~/workspace/podcast/circle prototype.
 *
 * Bindings use the plugin's small Alpine-like binder (resources/assets/js/player/binder.js):
 * ars-text, ars-show, ars-bind:attr, ars-on:event, ars-for, ars-data, ars-transition:*, ars-cloak.
 * Expressions run against the player store, e.g. toggle(), fmt(position), current?.title.
 * Themes can override this file with {theme}/a-ripple-song/player/skins/ars-circle.php.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Icon;
use Jiejia\ARippleSong\Libraries\Template;
?>
<div class="ars-player" data-ars-player data-ars-skin="ars-circle" hidden>
    <?php /* ============ 播放器（常驻，悬浮卡片） ============ */ ?>
    <div class="fixed inset-x-0 bottom-0 z-[9990] px-2 pb-2 sm:px-4 sm:pb-4" role="region" data-ars-bar aria-label="音频播放器">
      <div data-ars-width="box" data-ars-surface class="soft-shadow mx-auto max-w-7xl rounded-2xl border border-base-300 bg-base-100">
        <div class="flex flex-wrap items-center gap-x-3 gap-y-2 p-2.5 @5xl:min-h-[4.5rem] @5xl:flex-nowrap @5xl:px-4 @5xl:py-0">
        <a ars-bind:href="current?.url" class="flex min-w-0 flex-1 items-center gap-3 text-left @5xl:w-72 @5xl:flex-none order-1 basis-full rounded-2xl bg-base-content/5 p-2 @5xl:order-none @5xl:basis-auto @5xl:bg-transparent @5xl:p-0" ars-bind:aria-label="current?.title">
          <img ars-bind:src="current?.cover" alt="" class="size-12 shrink-0 rounded-xl">
          <span class="min-w-0">
            <span class="flex items-center gap-2 text-xs">
              <span class="inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 text-[10px] font-semibold" ars-bind:class="playing ? 'is-on bg-accent text-accent-content' : 'bg-base-300'"><span class="live-dot"></span><span ars-text="playing ? 'LIVE' : (loading ? '加载中' : '暂停')">暂停</span></span>
              <span class="opacity-50" ars-text="error || 'EP.' + current?.number"></span>
            </span>
            <span class="mt-0.5 block break-words text-sm font-semibold" ars-text="current?.title"></span>
          </span>
        </a>
        <div class="order-5 flex items-center gap-1 @5xl:order-none">
          <button type="button" class="btn btn-ghost btn-circle btn-sm inline-flex" ars-on:click="prev()" aria-label="上一集"><?php Icon::render('skip-back', 'size-4'); ?></button>
          <button type="button" class="soft-shadow grid size-10 place-items-center @5xl:size-12 rounded-xl bg-primary text-primary-content transition hover:scale-105" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
            <span ars-show="!playing"><?php Icon::render('play', 'ml-0.5 size-5 fill-current'); ?></span>
            <span ars-show="playing" ars-cloak><?php Icon::render('pause', 'size-5 fill-current'); ?></span>
          </button>
          <button type="button" class="btn btn-ghost btn-circle btn-sm inline-flex" ars-on:click="next()" aria-label="下一集"><?php Icon::render('skip-forward', 'size-4'); ?></button>
        </div>
        <?php
        (new Template())->renderThemeable('player/parts/progress', [
            'class' => 'order-3 flex w-full min-w-0 flex-wrap items-center gap-x-3 gap-y-1 @5xl:order-none @5xl:w-auto @5xl:flex-1 @5xl:flex-nowrap',
            'spectrumTop' => ['data-spectrum' => 'mirror', 'data-bar' => '3', 'data-gap' => '2', 'data-color' => 'secondary', 'class' => 'block h-9 w-full @5xl:hidden'],
            'time' => 'opacity-60', 'timeEnd' => 'opacity-40',
            'spectrum' => ['data-spectrum' => 'mirror', 'data-bar' => '3', 'data-gap' => '2', 'data-color' => 'secondary', 'class' => 'hidden h-9 w-24 shrink-0 @5xl:block'],
            'wave' => ['data-bar' => '3'], 'waveClass' => 'h-9',
        ]);
        ?>
        <?php (new Template())->renderThemeable('player/parts/volume', ['class' => 'order-4 flex flex-1 shrink-0 items-center justify-start gap-1 @5xl:order-8 @5xl:flex-none', 'range' => 'range range-secondary range-xs']); ?>
        <div class="order-6 flex flex-1 items-center justify-end gap-1 @5xl:contents">
          <button type="button" class="btn btn-ghost btn-sm w-14 shrink-0 rounded-lg px-0 font-semibold @5xl:order-7" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}×`">1×</button>
        <button type="button" class="tint grid size-8 shrink-0 place-items-center @5xl:size-10 rounded-xl hover:text-primary @5xl:order-9" ars-on:click="expanded = true" aria-label="播放队列"><?php Icon::render('list-music', 'size-5'); ?></button>
        </div>
        </div>
      </div>
    </div>

    <?php (new Template())->renderThemeable('player/parts/queue', [
        'panel' => 'border-r border-base-300 bg-base-100 soft-shadow', 'row' => 'rounded-xl hover:bg-base-200', 'rowOn' => 'tint', 'cover' => 'rounded-xl bg-base-300', 'trash' => 'tint grid size-8 place-items-center rounded-xl',
    ]); ?>
    <?php (new Template())->renderThemeable('player/parts/resume', [
        'panel' => 'soft-shadow rounded-2xl border border-base-300 bg-base-100', 'icon' => 'tint rounded-xl', 'accept' => 'btn btn-primary rounded-xl', 'cancel' => 'tint btn btn-ghost rounded-xl',
    ]); ?>
</div>
