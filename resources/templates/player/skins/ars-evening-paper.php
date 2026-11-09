<?php
/**
 * Player skin "晚报" (ars-evening-paper), converted from the ~/workspace/podcast/evening-paper prototype.
 *
 * Bindings use the plugin's small Alpine-like binder (resources/assets/js/player/binder.js):
 * ars-text, ars-show, ars-bind:attr, ars-on:event, ars-for, ars-data, ars-transition:*, ars-cloak.
 * Expressions run against the player store, e.g. toggle(), fmt(position), current?.title.
 * Themes can override this file with {theme}/a-ripple-song/player/skins/ars-evening-paper.php.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Icon;
use Jiejia\ARippleSong\Libraries\Template;
?>
<div class="ars-player" data-ars-player data-ars-skin="ars-evening-paper" hidden>
    <?php /* ============ 播放器（常驻） ============ */ ?>
    <div class="fixed inset-x-0 bottom-0 z-[9990]" role="region" data-ars-bar aria-label="音频播放器">
      <div data-ars-width="box" data-ars-surface class="mx-auto max-w-6xl bg-base-100">
        <?php (new Template())->renderThemeable('player/parts/mini', ['cover' => 'bg-base-300']); ?>
      <div class="rule-double"></div>
      <div class="flex flex-wrap items-center gap-x-4 gap-y-2 px-4 py-3 @5xl:flex-nowrap sm:px-6">
        <?php (new Template())->renderThemeable('player/parts/info', [
            'width' => '@5xl:w-72',
            'cover' => 'print-img bg-base-300',
        ]); ?>
        <div class="order-5 flex shrink-0 items-center gap-1.5 @5xl:order-none">
          <button type="button" class="btn btn-ghost btn-sm btn-circle inline-flex" ars-on:click="prev()" aria-label="上一期"><?php Icon::render('skip-back', 'size-4'); ?></button>
          <button type="button" class="btn-ring size-10 shrink-0 @5xl:size-12" ars-bind:class="playing && 'is-on'" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
            <span ars-show="!playing"><?php Icon::render('play', 'ml-0.5 size-5 fill-current'); ?></span>
            <span ars-show="playing" ars-cloak><?php Icon::render('pause', 'size-5 fill-current'); ?></span>
          </button>
          <button type="button" class="btn btn-ghost btn-sm btn-circle inline-flex" ars-on:click="next()" aria-label="下一期"><?php Icon::render('skip-forward', 'size-4'); ?></button>
        </div>
        <?php
        (new Template())->renderThemeable('player/parts/progress', [
            'class' => 'order-3 flex w-full min-w-0 flex-wrap items-center gap-x-3 gap-y-1 @5xl:order-none @5xl:w-auto @5xl:flex-1 @5xl:flex-nowrap',
            'spectrumTop' => ['data-spectrum' => 'wave', 'data-bar' => '1.25', 'data-color' => 'content', 'class' => 'block h-7 w-full @5xl:hidden'],
            'time' => 'meta shrink-0 tabular-nums', 'range' => 'range range-xs [--range-thumb:var(--color-primary)]',
            'spectrum' => ['data-spectrum' => 'wave', 'data-bar' => '1.25', 'data-color' => 'content', 'class' => 'hidden h-7 w-28 shrink-0 @5xl:block'],
            'wave' => ['data-color' => 'content'], 'waveClass' => 'h-7',
        ]);
        ?>
        <?php (new Template())->renderThemeable('player/parts/volume', ['rate' => 'btn btn-ghost btn-sm w-14 shrink-0 px-0', 'class' => 'order-4 flex flex-1 shrink-0 items-center justify-start gap-1 @5xl:order-8 @5xl:flex-none', 'range' => 'range range-xs']); ?>
        <div class="order-6 flex flex-1 items-center justify-end gap-1 @5xl:contents">
          <button type="button" class="btn btn-ghost btn-sm w-14 shrink-0 px-0 @5xl:order-7 @max-5xl:!hidden" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}×`">1×</button>
          <?php (new Template())->renderThemeable('player/parts/collapse', ['class' => 'btn btn-ghost btn-sm btn-circle', 'icon' => 'size-4']); ?>
          <button type="button" class="btn btn-ghost btn-sm btn-circle @5xl:order-9" ars-on:click="expanded = true" aria-label="播放队列"><?php Icon::render('list-music', 'size-4'); ?></button>
        </div>
      </div>
      </div>
    </div>

    <?php (new Template())->renderThemeable('player/parts/queue', [
        'panel' => 'border-r border-base-content bg-base-100', 'divider' => 'border-t-2 border-double border-base-content', 'title' => 'font-serif text-lg font-bold', 'meta' => 'meta', 'sub' => 'meta', 'row' => 'border-b border-base-content/15 hover:bg-base-200', 'rowOn' => 'bg-base-200', 'cover' => 'print-img bg-base-300', 'name' => 'font-semibold',
    ]); ?>
    <?php (new Template())->renderThemeable('player/parts/resume', [
        'panel' => 'border border-base-content bg-base-100', 'icon' => 'text-base-content', 'heading' => 'font-serif text-lg font-bold', 'accept' => 'btn btn-neutral rounded-none', 'cancel' => 'btn btn-ghost rounded-none',
    ]); ?>
</div>
