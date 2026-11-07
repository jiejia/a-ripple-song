<?php
/**
 * Player skin "脉冲" (ars-pulse), converted from the ~/workspace/podcast/pulse prototype.
 *
 * Bindings use the plugin's small Alpine-like binder (resources/assets/js/player/binder.js):
 * ars-text, ars-show, ars-bind:attr, ars-on:event, ars-for, ars-data, ars-transition:*, ars-cloak.
 * Expressions run against the player store, e.g. toggle(), fmt(position), current?.title.
 * Themes can override this file with {theme}/a-ripple-song/player/skins/ars-pulse.php.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Icon;
use Jiejia\ARippleSong\Libraries\Template;
?>
<div class="ars-player" data-ars-player data-ars-skin="ars-pulse" hidden>
    <?php /* ============ 播放器（常驻，底部悬浮） ============ */ ?>
    <div class="fixed inset-x-0 bottom-3 z-[9990] px-3" role="region" data-ars-bar aria-label="音频播放器">
      <div data-ars-width="box" data-ars-surface="base-200" class="mx-auto max-w-6xl rounded-[24px] bg-base-200 shadow-2xl ring-1 ring-[color-mix(in_oklch,var(--color-base-content)_10%,transparent)]">
        <div class="flex flex-wrap items-center gap-x-3 gap-y-2 p-2.5 @5xl:flex-nowrap @5xl:gap-4 @5xl:pr-4">
        <?php (new Template())->renderThemeable('player/parts/info', [
            'width' => '@5xl:w-80',
            'cover' => 'rounded-2xl bg-primary',
            'meta' => 'muted',
        ]); ?>
        <div class="order-5 flex shrink-0 items-center gap-1 sm:gap-2 @5xl:order-none">
          <button type="button" class="btn btn-ghost btn-circle btn-sm inline-flex" ars-on:click="prev()" aria-label="上一集"><?php Icon::render('skip-back', 'size-4 fill-current'); ?></button>
          <button type="button" class="grid size-10 place-items-center rounded-full @5xl:size-12 bg-base-content text-base-100 transition hover:scale-105" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
            <span ars-show="!playing"><?php Icon::render('play', 'ml-0.5 size-5 fill-current'); ?></span>
            <span ars-show="playing" ars-cloak><?php Icon::render('pause', 'size-5 fill-current'); ?></span>
          </button>
          <button type="button" class="btn btn-ghost btn-circle btn-sm inline-flex" ars-on:click="next()" aria-label="下一集"><?php Icon::render('skip-forward', 'size-4 fill-current'); ?></button>
        </div>
        <?php
        (new Template())->renderThemeable('player/parts/progress', [
            'class' => 'order-3 flex w-full min-w-0 flex-wrap items-center gap-x-3 gap-y-1 @5xl:order-none @5xl:w-auto @5xl:flex-1 @5xl:flex-nowrap',
            'spectrumTop' => ['data-spectrum' => 'bars', 'data-bar' => '3', 'data-gap' => '2', 'data-color' => 'content', 'class' => 'block h-10 w-full @5xl:hidden'],
            'time' => 'muted',
            'spectrum' => ['data-spectrum' => 'bars', 'data-bar' => '3', 'data-gap' => '2', 'data-color' => 'content', 'class' => 'hidden h-10 w-24 shrink-0 @5xl:block'],
            'wave' => ['data-bar' => '3', 'data-gap' => '4', 'data-color' => 'content'], 'waveClass' => 'h-10',
        ]);
        ?>
        <?php (new Template())->renderThemeable('player/parts/volume', ['class' => 'order-4 flex flex-1 shrink-0 items-center justify-start gap-1 @5xl:order-8 @5xl:flex-none']); ?>
        <div class="order-6 flex flex-1 items-center justify-end gap-1 @5xl:contents">
          <button type="button" class="btn btn-ghost btn-sm w-14 shrink-0 px-0 text-sm @5xl:order-7" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}×`">1×</button>
        <button type="button" class="btn btn-ghost btn-circle btn-sm @5xl:order-9" ars-on:click="expanded = true" aria-label="播放队列"><?php Icon::render('list-music', 'size-5'); ?></button>
        </div>
        </div>
      </div>
    </div>

    <?php (new Template())->renderThemeable('player/parts/queue', [
        'panel' => 'bg-base-200 shadow-2xl ring-1 ring-base-content/10', 'meta' => 'muted', 'sub' => 'muted', 'row' => 'rounded-2xl hover:bg-base-100', 'rowOn' => 'bg-base-100', 'cover' => 'rounded-2xl bg-base-300',
    ]); ?>
    <?php (new Template())->renderThemeable('player/parts/resume', [
        'panel' => 'rounded-[24px] bg-base-200 shadow-2xl ring-1 ring-base-content/10', 'icon' => 'rounded-full bg-base-content/10 text-base-content', 'accept' => 'btn rounded-full border-0 bg-base-content text-base-100', 'cancel' => 'btn btn-ghost rounded-full',
    ]); ?>
</div>
