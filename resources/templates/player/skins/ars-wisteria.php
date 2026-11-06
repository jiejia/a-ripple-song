<?php
/**
 * Player skin "紫藤" (ars-wisteria), converted from the ~/workspace/podcast/wisteria prototype.
 *
 * Bindings use the plugin's small Alpine-like binder (resources/assets/js/player/binder.js):
 * ars-text, ars-show, ars-bind:attr, ars-on:event, ars-for, ars-data, ars-transition:*, ars-cloak.
 * Expressions run against the player store, e.g. toggle(), fmt(position), current?.title.
 * Themes can override this file with {theme}/a-ripple-song/player/skins/ars-wisteria.php.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Icon;
use Jiejia\ARippleSong\Libraries\Template;
?>
<div class="ars-player" data-ars-player data-ars-skin="ars-wisteria" hidden>
    <?php /* ============ 底部：播放器 + 手机端标签栏（常驻） ============ */ ?>
    <div class="fixed inset-x-0 bottom-0 z-[9990] px-3 pb-3" role="region" data-ars-bar aria-label="音频播放器">
      <div data-ars-width="box" data-ars-surface class="card-w mx-auto max-w-6xl rounded-[1.75rem]">
        <div class="flex flex-wrap items-center gap-x-3 gap-y-2 p-2.5 @5xl:flex-nowrap @5xl:gap-4 @5xl:pr-4">
        <div class="order-1 flex basis-full items-center gap-3 rounded-2xl bg-base-content/5 p-2 @5xl:contents">
          <a ars-bind:href="current?.url" class="block shrink-0" ars-bind:aria-label="current?.title"><img ars-bind:src="current?.cover" alt="" class="size-14 rounded-2xl"></a>
        <div class="min-w-0 flex-1 @5xl:w-56 @5xl:flex-none">
          <p class="flex items-center gap-2">
            <span ars-show="playing" ars-cloak class="is-on inline-flex items-center gap-1 rounded-full bg-secondary/25 px-2 py-0.5 text-[10px] font-semibold text-[oklch(45%_0.1_210)]"><span class="live-dot"></span>On Live</span>
            <span class="mute truncate text-xs" ars-text="error || (loading ? '加载中…' : `第 ${current?.number} 期`)"></span>
          </p>
          <a ars-bind:href="current?.url" class="block break-words text-sm font-semibold hover:text-primary" ars-text="current?.title"></a>
        </div>
        </div>
        <div class="order-5 flex shrink-0 items-center gap-1 sm:gap-2 @5xl:order-none">
          <button type="button" class="btn btn-ghost btn-circle btn-sm inline-flex" ars-on:click="prev()" aria-label="上一集"><?php Icon::render('skip-back', 'size-5'); ?></button>
          <button type="button" class="grid size-10 place-items-center rounded-full @5xl:size-12 bg-primary text-primary-content shadow-[0_10px_20px_-8px_var(--color-primary)] transition hover:scale-105" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
            <span ars-show="!playing"><?php Icon::render('play', 'ml-0.5 size-5 fill-current'); ?></span>
            <span ars-show="playing" ars-cloak><?php Icon::render('pause', 'size-5 fill-current'); ?></span>
          </button>
          <button type="button" class="btn btn-ghost btn-circle btn-sm inline-flex" ars-on:click="next()" aria-label="下一集"><?php Icon::render('skip-forward', 'size-5'); ?></button>
        </div>
        <?php
        (new Template())->renderThemeable('player/parts/progress', [
            'class' => 'order-3 flex w-full min-w-0 flex-wrap items-center gap-x-3 gap-y-1 @5xl:order-none @5xl:w-auto @5xl:flex-1 @5xl:flex-nowrap',
            'spectrumTop' => ['data-spectrum' => 'mirror', 'data-bar' => '3', 'data-gap' => '2', 'class' => 'block h-9 w-full @5xl:hidden'],
            'time' => 'mute',
            'spectrum' => ['data-spectrum' => 'mirror', 'data-bar' => '3', 'data-gap' => '2', 'class' => 'hidden h-9 w-24 shrink-0 @5xl:block'],
            'waveClass' => 'h-9',
        ]);
        ?>
        <?php (new Template())->renderThemeable('player/parts/volume', ['class' => 'order-4 flex flex-1 shrink-0 items-center justify-start gap-1 @5xl:order-none @5xl:flex-none']); ?>
        <div class="order-6 flex flex-1 items-center justify-end gap-1 @5xl:contents">
        <button type="button" class="btn btn-ghost btn-sm w-12 shrink-0 px-0 text-xs" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}×`">1×</button>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" ars-on:click="expanded = true" aria-label="播放队列"><?php Icon::render('list-music', 'size-5'); ?></button>
        </div>
        </div>
      </div>
    </div>

    <?php (new Template())->renderThemeable('player/parts/queue', [
        'panel' => 'card-w !rounded-none', 'meta' => 'mute', 'sub' => 'mute', 'cover' => 'rounded-2xl bg-base-300',
    ]); ?>
    <?php (new Template())->renderThemeable('player/parts/resume', [
        'panel' => 'card-w rounded-[1.75rem]', 'accept' => 'btn btn-primary rounded-full', 'cancel' => 'btn btn-ghost rounded-full',
    ]); ?>
</div>
