<?php
/**
 * Player skin "极光" (ars-aurora), converted from the ~/workspace/podcast/aurora prototype.
 *
 * Bindings use the plugin's small Alpine-like binder (resources/assets/js/player/binder.js):
 * ars-text, ars-show, ars-bind:attr, ars-on:event, ars-for, ars-data, ars-transition:*, ars-cloak.
 * Expressions run against the player store, e.g. toggle(), fmt(position), current?.title.
 * Themes can override this file with {theme}/a-ripple-song/player/skins/ars-aurora.php.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Icon;
use Jiejia\ARippleSong\Libraries\Template;
?>
<div class="ars-player" data-ars-player data-ars-skin="ars-aurora" hidden>
    <?php /* ============ 播放器：悬浮玻璃胶囊（常驻） ============ */ ?>
    <div class="fixed inset-x-0 bottom-3 z-[9990] px-3" role="region" data-ars-bar aria-label="音频播放器">
      <div data-ars-width="box" class="frost frost-strong relative mx-auto max-w-4xl rounded-[2rem]">
        <?php (new Template())->renderThemeable('player/parts/mini'); ?>
        <div class="flex flex-wrap items-center gap-x-3 gap-y-2 p-3 @5xl:min-h-[4.5rem] @5xl:flex-nowrap @5xl:py-0 @5xl:pl-2 @5xl:pr-3">
        <span class="bg-aurora absolute inset-x-6 bottom-0 h-0.5 origin-left rounded-full" ars-bind:style="`transform: scaleX(${progress / 100})`" aria-hidden="true"></span>
        <?php (new Template())->renderThemeable('player/parts/info', [
            'width' => '@5xl:w-64',
            'cover' => 'spin rounded-full ring-2 ring-base-100',
            'coverBind' => 'playing && \'is-on\'',
            'title' => 'hover:text-primary',
        ]); ?>
        <div class="order-5 flex items-center gap-1 @5xl:order-none">
          <button type="button" class="btn btn-ghost btn-circle btn-sm inline-flex" ars-on:click="prev()" aria-label="上一集"><?php Icon::render('skip-back', 'size-4'); ?></button>
          <button type="button" class="bg-aurora grid size-10 place-items-center @5xl:size-12 rounded-full text-white shadow-lg transition hover:scale-105" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
            <span ars-show="!playing"><?php Icon::render('play', 'ml-0.5 size-5 fill-current'); ?></span>
            <span ars-show="playing" ars-cloak><?php Icon::render('pause', 'size-5 fill-current'); ?></span>
          </button>
          <button type="button" class="btn btn-ghost btn-circle btn-sm inline-flex" ars-on:click="next()" aria-label="下一集"><?php Icon::render('skip-forward', 'size-4'); ?></button>
        </div>
        <?php
        (new Template())->renderThemeable('player/parts/progress', [
            'class' => 'order-3 flex w-full min-w-0 flex-wrap items-center gap-x-3 gap-y-1 @5xl:order-none @5xl:w-auto @5xl:flex-1 @5xl:flex-nowrap',
            'spectrumTop' => ['data-spectrum' => 'mirror', 'data-bar' => '2', 'data-gap' => '2', 'class' => 'block h-8 w-full @5xl:hidden'],
            'time' => 'opacity-70', 'timeEnd' => 'opacity-50',
            'spectrum' => ['data-spectrum' => 'mirror', 'data-bar' => '2', 'data-gap' => '2', 'class' => 'hidden h-8 w-20 shrink-0 @5xl:block'],
        ]);
        ?>
        <?php (new Template())->renderThemeable('player/parts/volume', ['rate' => 'btn btn-ghost btn-sm w-14 shrink-0 rounded-full px-0 font-normal tabular-nums', 'class' => 'order-4 flex flex-1 shrink-0 items-center justify-start gap-1 @5xl:order-none @5xl:flex-none', 'range' => 'range range-xs']); ?>
        <div class="order-6 flex flex-1 items-center justify-end gap-1 @5xl:contents">
        <button type="button" class="btn btn-ghost btn-sm w-14 shrink-0 rounded-full px-0 font-normal tabular-nums @max-5xl:!hidden" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}×`">1×</button>
        <?php (new Template())->renderThemeable('player/parts/collapse', ['class' => 'btn btn-ghost btn-circle btn-sm']); ?>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" ars-on:click="expanded = true" aria-label="播放队列"><?php Icon::render('list-music', 'size-5'); ?></button>
        </div>
        </div>
      </div>
    </div>

    <?php (new Template())->renderThemeable('player/parts/queue', [
        'panel' => 'frost frost-strong', 'divider' => 'border-t border-base-content/10', 'title' => 'eyebrow', 'row' => 'rounded-2xl hover:bg-base-100/60', 'rowOn' => 'bg-base-100/70', 'cover' => 'rounded-full',
    ]); ?>
    <?php (new Template())->renderThemeable('player/parts/resume', [
        'panel' => 'frost frost-strong rounded-[2rem]', 'icon' => 'bg-aurora rounded-full text-white', 'accept' => 'btn bg-aurora rounded-full border-0 text-white', 'cancel' => 'btn btn-ghost rounded-full',
    ]); ?>
</div>
