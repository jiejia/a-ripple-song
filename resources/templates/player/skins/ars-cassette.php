<?php
/**
 * Player skin "卡带" (ars-cassette), converted from the ~/workspace/podcast/cassette prototype.
 *
 * Bindings use the plugin's small Alpine-like binder (resources/assets/js/player/binder.js):
 * ars-text, ars-show, ars-bind:attr, ars-on:event, ars-for, ars-data, ars-transition:*, ars-cloak.
 * Expressions run against the player store, e.g. toggle(), fmt(position), current?.title.
 * Themes can override this file with {theme}/a-ripple-song/player/skins/ars-cassette.php.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Icon;
use Jiejia\ARippleSong\Libraries\Template;
?>
<div class="ars-player" data-ars-player data-ars-skin="ars-cassette" hidden>
    <?php /* ============ 播放器：卡座（常驻，固定深色） ============ */ ?>
    <div class="fixed inset-x-0 bottom-0 z-[9990]" role="region" data-ars-bar aria-label="音频播放器">
      <div data-ars-width="box" data-ars-surface class="mx-auto max-w-7xl bg-base-100 text-base-content">
        <?php (new Template())->renderThemeable('player/parts/mini'); ?>
      <div class="stripes h-2" aria-hidden="true"></div>
      <div class="flex flex-wrap items-center gap-x-3 gap-y-2 px-3 py-3 @5xl:min-h-20 @5xl:flex-nowrap @5xl:gap-4 @5xl:py-0 sm:px-6">
        <?php (new Template())->renderThemeable('player/parts/info', [
            'width' => '@5xl:w-72',
            'cover' => 'rounded-lg bg-base-300',
            'meta' => 'font-mono text-secondary',
        ]); ?>
        <div class="order-5 flex items-center gap-1.5 @5xl:order-none">
          <span class="contents">
            <button type="button" class="key size-8 @5xl:size-10" ars-on:click="prev()" aria-label="上一集"><?php Icon::render('skip-back', 'size-4'); ?></button>
          </span>
          <button type="button" class="key h-10 w-12 @5xl:h-12 @5xl:w-14" ars-bind:class="playing && 'is-down'" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
            <span ars-show="!playing"><?php Icon::render('play', 'size-5 fill-current @5xl:size-6'); ?></span>
            <span ars-show="playing" ars-cloak><?php Icon::render('pause', 'size-5 fill-current @5xl:size-6'); ?></span>
          </button>
          <span class="contents">
            <button type="button" class="key size-8 @5xl:size-10" ars-on:click="next()" aria-label="下一集"><?php Icon::render('skip-forward', 'size-4'); ?></button>
          </span>
        </div>
        <?php
        (new Template())->renderThemeable('player/parts/progress', [
            'class' => 'order-3 flex w-full min-w-0 flex-wrap items-center gap-x-3 gap-y-1 @5xl:order-none @5xl:w-auto @5xl:flex-1 @5xl:flex-nowrap',
            'spectrumTop' => ['data-spectrum' => 'bars', 'data-color' => 'content', 'class' => 'block h-8 w-full @5xl:hidden'],
            'time' => 'counter text-sm', 'timeEnd' => 'font-mono text-sm opacity-60',
            'spectrum' => ['data-spectrum' => 'vu', 'data-color' => 'content'], 'spectrumWrap' => 'vu-face hidden h-12 w-24 shrink-0 @5xl:block',
            'waveClass' => 'h-10',
        ]);
        ?>
        <?php (new Template())->renderThemeable('player/parts/volume', ['class' => 'order-4 flex flex-1 shrink-0 items-center justify-start gap-1 @5xl:order-8 @5xl:flex-none', 'button' => 'btn btn-ghost btn-sm btn-square']); ?>
        <div class="order-6 flex flex-1 items-center justify-end gap-1 @5xl:contents">
          <button type="button" class="btn btn-ghost btn-sm w-14 shrink-0 px-0 font-mono @5xl:order-7" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}×`">1×</button>
        <button type="button" class="btn btn-ghost btn-circle btn-sm @5xl:btn-md @5xl:order-9" ars-on:click="expanded = true" aria-label="播放队列"><?php Icon::render('list-music', 'size-5'); ?></button>
        </div>
      </div>
      </div>
    </div>

    <?php (new Template())->renderThemeable('player/parts/queue', [
        'panel' => 'border-r border-base-content/20 bg-base-100 text-base-content', 'divider' => 'border-t border-base-content/20', 'title' => 'tag text-sm text-secondary', 'meta' => 'font-mono opacity-60', 'row' => 'rounded-xl hover:bg-base-200', 'rowOn' => 'bg-base-200', 'cover' => 'rounded-lg bg-base-300', 'nameOn' => 'text-secondary', 'sub' => 'font-mono opacity-60',
    ]); ?>
    <?php (new Template())->renderThemeable('player/parts/resume', [
        'panel' => 'rounded-2xl border border-base-content/20 bg-base-100', 'icon' => 'rounded-full bg-base-200 text-secondary', 'accept' => 'key h-11 auto-cols-max grid-flow-col justify-center gap-2', 'cancel' => 'key h-11 auto-cols-max grid-flow-col justify-center gap-2',
    ]); ?>
</div>
