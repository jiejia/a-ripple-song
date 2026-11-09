<?php
/**
 * Player skin "珊瑚" (ars-coral), converted from the ~/workspace/podcast/coral prototype.
 *
 * Bindings use the plugin's small Alpine-like binder (resources/assets/js/player/binder.js):
 * ars-text, ars-show, ars-bind:attr, ars-on:event, ars-for, ars-data, ars-transition:*, ars-cloak.
 * Expressions run against the player store, e.g. toggle(), fmt(position), current?.title.
 * Themes can override this file with {theme}/a-ripple-song/player/skins/ars-coral.php.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Icon;
use Jiejia\ARippleSong\Libraries\Template;
?>
<div class="ars-player" data-ars-player data-ars-skin="ars-coral" hidden>
    <?php /* ============ 播放器（常驻，底部悬浮） ============ */ ?>
    <div class="fixed inset-x-0 bottom-0 z-[9990] px-2 pb-2 sm:px-5 sm:pb-5" role="region" data-ars-bar aria-label="音频播放器">
      <div data-ars-width="box" data-ars-surface class="panel mx-auto max-w-7xl rounded-2xl shadow-[0_20px_50px_-20px_oklch(30%_0.05_270/0.45)]">
        <?php (new Template())->renderThemeable('player/parts/mini'); ?>
        <div class="flex flex-wrap items-center gap-x-3 gap-y-2 p-2.5 @5xl:flex-nowrap @5xl:gap-4 @5xl:px-4">
        <?php (new Template())->renderThemeable('player/parts/info', [
            'width' => '@5xl:w-72',
            'cover' => 'rounded-xl bg-base-300',
            'meta' => 'mute',
        ]); ?>
        <div class="order-5 flex shrink-0 items-center gap-1 rounded-xl bg-base-200 p-1 sm:gap-2 sm:px-2 @5xl:order-none">
          <button type="button" class="btn btn-ghost btn-circle btn-sm inline-flex" ars-on:click="prev()" aria-label="上一集"><?php Icon::render('skip-back', 'size-4 fill-current'); ?></button>
          <button type="button" class="grid size-9 place-items-center rounded-full @5xl:size-10 bg-secondary text-secondary-content transition hover:scale-105" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
            <span ars-show="!playing && !loading"><?php Icon::render('play', 'ml-0.5 size-4 fill-current'); ?></span>
            <span ars-show="playing && !loading" ars-cloak><?php Icon::render('pause', 'size-4 fill-current'); ?></span>
            <span class="loading loading-spinner size-4" ars-show="loading" ars-cloak></span>
          </button>
          <button type="button" class="btn btn-ghost btn-circle btn-sm inline-flex" ars-on:click="next()" aria-label="下一集"><?php Icon::render('skip-forward', 'size-4 fill-current'); ?></button>
          <button type="button" class="btn btn-ghost btn-sm w-12 shrink-0 px-0 text-xs @max-5xl:!hidden" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}×`">1×</button>
        </div>
        <?php
        (new Template())->renderThemeable('player/parts/progress', [
            'class' => 'order-3 flex w-full min-w-0 flex-wrap items-center gap-x-3 gap-y-1 @5xl:order-none @5xl:w-auto @5xl:flex-1 @5xl:flex-nowrap',
            'spectrumTop' => ['data-spectrum' => 'bars', 'data-bar' => '2', 'data-gap' => '2', 'class' => 'block h-9 w-full @5xl:hidden'],
            'time' => 'mute',
            'spectrum' => ['data-spectrum' => 'bars', 'data-bar' => '2', 'data-gap' => '2', 'class' => 'hidden h-9 w-24 shrink-0 @5xl:block'],
            'waveClass' => 'h-9',
        ]);
        ?>
        <?php (new Template())->renderThemeable('player/parts/volume', ['rate' => 'btn btn-ghost btn-sm w-12 shrink-0 px-0 text-xs', 'class' => 'order-4 flex flex-1 shrink-0 items-center justify-start gap-1 @5xl:order-none @5xl:flex-none', 'button' => 'grid size-8 shrink-0 place-items-center rounded-full hover:bg-base-200 @5xl:size-10']); ?>
        <button type="button" class="hidden size-10 shrink-0 place-items-center rounded-full bg-base-200 text-primary @5xl:grid" ars-on:click="toggleFav(current?.id)" ars-bind:aria-pressed="isFav(current?.id)" aria-label="收藏当前节目">
          <span ars-show="!isFav(current?.id)"><?php Icon::render('heart', 'size-4'); ?></span>
          <span ars-show="isFav(current?.id)" ars-cloak><?php Icon::render('heart', 'size-4 fill-current'); ?></span>
        </button>
        <div class="order-6 flex flex-1 items-center justify-end gap-1 @5xl:contents">
        <button type="button" class="grid size-8 shrink-0 place-items-center rounded-full hover:bg-base-200 @5xl:size-10" ars-on:click="expanded = true" aria-label="播放队列"><?php Icon::render('list-music', 'size-5'); ?></button>
        <?php (new Template())->renderThemeable('player/parts/collapse', ['class' => 'grid size-8 shrink-0 place-items-center rounded-full hover:bg-base-200 @5xl:size-10']); ?>
        </div>
        </div>
      </div>
    </div>

    <?php (new Template())->renderThemeable('player/parts/queue', [
        'panel' => 'panel !rounded-none', 'meta' => 'mute', 'sub' => 'mute', 'row' => 'rounded-xl hover:bg-base-200', 'rowOn' => 'is-hot', 'cover' => 'rounded-xl bg-base-300',
    ]); ?>
    <?php (new Template())->renderThemeable('player/parts/resume', [
        'panel' => 'panel rounded-2xl shadow-xl', 'icon' => 'rounded-full bg-secondary/15 text-secondary', 'accept' => 'btn btn-secondary rounded-full', 'cancel' => 'btn btn-ghost rounded-full',
    ]); ?>
</div>
