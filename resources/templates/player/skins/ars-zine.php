<?php
/**
 * Player skin "拼贴" (ars-zine), converted from the ~/workspace/podcast/zine prototype.
 *
 * Bindings use the plugin's small Alpine-like binder (resources/assets/js/player/binder.js):
 * ars-text, ars-show, ars-bind:attr, ars-on:event, ars-for, ars-data, ars-transition:*, ars-cloak.
 * Expressions run against the player store, e.g. toggle(), fmt(position), current?.title.
 * Themes can override this file with {theme}/a-ripple-song/player/skins/ars-zine.php.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Icon;
use Jiejia\ARippleSong\Libraries\Template;
?>
<div class="ars-player" data-ars-player data-ars-skin="ars-zine" hidden>
    <?php /* ============ 播放器（常驻） ============ */ ?>
    <div class="fixed inset-x-0 bottom-0 z-[9990]" role="region" data-ars-bar aria-label="音频播放器">
      <div data-ars-width="box" data-ars-surface class="relative mx-auto max-w-6xl border-x-2 border-t-2 ink bg-base-100">
      <div class="halftone absolute inset-x-0 -top-2.5 h-2 opacity-70" aria-hidden="true"></div>
      <div class="flex flex-wrap items-center gap-x-3 gap-y-2 px-3 py-3 @5xl:min-h-[4.75rem] @5xl:flex-nowrap @5xl:py-0 sm:px-6">
        <?php (new Template())->renderThemeable('player/parts/info', [
            'width' => '@5xl:w-72',
            'cover' => 'duo -rotate-3 rounded border-2 ink',
            'title' => 'font-bold hover:text-primary',
            'meta' => 'font-bold text-primary',
        ]); ?>
        <div class="order-5 flex items-center gap-1.5 @5xl:order-none">
          <button type="button" class="btn btn-ghost btn-circle btn-sm inline-flex" ars-on:click="prev()" aria-label="上一集"><?php Icon::render('skip-back', 'size-4'); ?></button>
          <button type="button" class="grid size-10 place-items-center rounded-full @5xl:size-12 border-2 ink bg-primary text-primary-content shadow-[3px_3px_0_var(--color-base-content)] transition active:translate-x-0.5 active:translate-y-0.5 active:shadow-none" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
            <span ars-show="!playing"><?php Icon::render('play', 'ml-0.5 size-5 fill-current'); ?></span>
            <span ars-show="playing" ars-cloak><?php Icon::render('pause', 'size-5 fill-current'); ?></span>
          </button>
          <button type="button" class="btn btn-ghost btn-circle btn-sm inline-flex" ars-on:click="next()" aria-label="下一集"><?php Icon::render('skip-forward', 'size-4'); ?></button>
        </div>
        <?php
        (new Template())->renderThemeable('player/parts/progress', [
            'class' => 'order-3 flex w-full min-w-0 flex-wrap items-center gap-x-3 gap-y-1 text-sm @5xl:order-none @5xl:w-auto @5xl:flex-1 @5xl:flex-nowrap',
            'spectrumTop' => ['data-spectrum' => 'dots', 'data-peak' => 'secondary', 'data-bar' => '5', 'data-gap' => '2', 'class' => 'block h-10 w-full @5xl:hidden'],
            'time' => '', 'timeEnd' => 'opacity-60',
            'spectrum' => ['data-spectrum' => 'dots', 'data-peak' => 'secondary', 'data-bar' => '5', 'data-gap' => '2', 'class' => 'hidden h-10 w-28 shrink-0 @5xl:block'],
            'waveClass' => 'h-10',
        ]);
        ?>
        <?php (new Template())->renderThemeable('player/parts/volume', ['class' => 'order-4 flex flex-1 shrink-0 items-center justify-start gap-1 @5xl:order-none @5xl:flex-none', 'range' => 'range range-secondary range-xs']); ?>
        <div class="order-6 flex flex-1 items-center justify-end gap-1 @5xl:contents">
        <button type="button" class="btn btn-ghost btn-sm w-14 shrink-0 rounded-full px-0 font-bold" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}×`">1×</button>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" ars-on:click="expanded = true" aria-label="播放队列"><?php Icon::render('list-music', 'size-5'); ?></button>
        </div>
      </div>
      </div>
    </div>

    <?php (new Template())->renderThemeable('player/parts/queue', [
        'panel' => 'border-r-2 ink bg-base-100', 'divider' => 'border-t-2 ink', 'title' => 'font-hand text-2xl text-primary', 'row' => 'border-2 border-transparent hover:ink', 'rowOn' => 'ink bg-base-200 shadow-[3px_3px_0_var(--color-base-content)]', 'cover' => 'rounded border-2 ink', 'name' => 'font-bold',
    ]); ?>
    <?php (new Template())->renderThemeable('player/parts/resume', [
        'panel' => 'border-2 ink bg-base-100 shadow-[4px_4px_0_var(--color-base-content)]', 'icon' => 'rounded-full border-2 ink bg-base-100 text-primary', 'heading' => 'font-bold', 'accept' => 'btn btn-primary rounded-full border-2 ink', 'cancel' => 'btn btn-ghost rounded-full border-2 ink',
    ]); ?>
</div>
