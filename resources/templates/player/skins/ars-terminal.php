<?php
/**
 * Player skin "终端" (ars-terminal), converted from the ~/workspace/podcast/terminal prototype.
 *
 * Bindings use the plugin's small Alpine-like binder (resources/assets/js/player/binder.js):
 * ars-text, ars-show, ars-bind:attr, ars-on:event, ars-for, ars-data, ars-transition:*, ars-cloak.
 * Expressions run against the player store, e.g. toggle(), fmt(position), current?.title.
 * Themes can override this file with {theme}/a-ripple-song/player/skins/ars-terminal.php.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Icon;
use Jiejia\ARippleSong\Libraries\Template;
?>
<div class="ars-player" data-ars-player data-ars-skin="ars-terminal" hidden>
    <?php /* ============ 播放器：底部状态行（常驻） ============ */ ?>
    <div class="fixed inset-x-0 bottom-0 z-[9990]" role="region" data-ars-bar aria-label="音频播放器">
      <div data-ars-width="box" data-ars-surface="base-200" class="mx-auto max-w-6xl border-x border-t tui-line bg-base-200 text-sm [--tui-bg:var(--color-base-200)]">
        <div class="flex flex-wrap items-center gap-x-3 gap-y-2 px-4 py-3 @5xl:min-h-16 @5xl:flex-nowrap @5xl:py-0">
        <a ars-bind:href="current?.url" class="block min-w-0 flex-1 text-left hover:text-primary order-1 basis-full border border-dashed tui-line p-2 @5xl:order-none @5xl:basis-auto @5xl:border-0 @5xl:p-0 @5xl:max-w-xs" ars-bind:aria-label="current?.title">
          <span class="dim block text-xs" ars-text="error || `EP.${String(current?.number).padStart(3, '0')} ${loading ? 'loading…' : (playing ? 'playing' : 'paused')}`"></span>
          <span class="block break-words text-sm" ars-text="current?.title"></span>
        </a>
        <div class="order-5 flex shrink-0 items-center @5xl:order-none">
          <span class="contents">
            <button type="button" class="cmd" ars-on:click="prev()" aria-label="上一集">|&lt;</button>
          </span>
          <button type="button" class="cmd text-base text-primary @5xl:text-lg" ars-bind:class="playing && 'is-on'" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放" ars-text="playing ? '❚❚' : '▶ '">▶ </button>
          <span class="contents">
            <button type="button" class="cmd" ars-on:click="next()" aria-label="下一集">&gt;|</button>
          </span>
        </div>
        <?php
        (new Template())->renderThemeable('player/parts/progress', [
            // No min-w-0: the text bar has a fixed width, so the title gives way instead.
            'class' => 'order-3 flex w-full flex-wrap items-center gap-x-3 gap-y-1 tabular-nums @5xl:order-none @5xl:w-auto @5xl:flex-1 @5xl:flex-nowrap',
            'spectrumTop' => ['data-spectrum' => 'ascii', 'data-bar' => '11', 'data-gap' => '1', 'class' => 'block h-10 w-full @5xl:hidden'],
            'time' => '', 'timeEnd' => 'dim',
            'bar' => static function (): void { ?>
          <label class="bar-seek shrink-0 tracking-[-0.05em] text-primary">
            <span class="sr-only">播放进度</span>
            <span aria-hidden="true" ars-text="'█'.repeat(Math.round(progress / 100 * 16)) + '░'.repeat(16 - Math.round(progress / 100 * 16))">░░░░░░░░░░░░░░░░</span>
            <input type="range" min="0" ars-bind:max="duration" step="1" ars-bind:value="position" ars-on:input="scrub($event.target.value)" ars-on:change="commit($event.target.value)">
          </label>
            <?php },
            'spectrum' => ['data-spectrum' => 'ascii', 'data-bar' => '11', 'data-gap' => '1', 'class' => 'hidden h-10 w-24 shrink-0 @6xl:block'],
            'wave' => ['data-gap' => '1'],
        ]);
        ?>
        <?php (new Template())->renderThemeable('player/parts/volume', ['class' => 'order-4 flex flex-1 shrink-0 items-center justify-start gap-1 @5xl:order-8 @5xl:flex-none', 'button' => 'cmd', 'icon' => '', 'range' => 'accent-primary']); ?>
        <div class="order-6 flex flex-1 items-center justify-end gap-1 @5xl:contents">
          <button type="button" class="cmd w-[9ch] shrink-0 justify-between @5xl:order-7" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}x`">1x</button>
        <button type="button" class="cmd @5xl:order-9" ars-on:click="expanded = true" aria-label="播放队列">ls</button>
        </div>
        </div>
      </div>
    </div>

    <?php (new Template())->renderThemeable('player/parts/queue', [
        'panel' => 'border-r tui-line bg-base-200 font-mono text-sm [--tui-bg:var(--color-base-200)]', 'divider' => 'border-t tui-line', 'title' => 'px-4 py-3 text-sm text-primary', 'meta' => 'dim', 'clear' => 'cmd', 'row' => 'hover:text-primary', 'rowOn' => 'text-primary', 'cover' => 'mono-img bg-base-300', 'name' => '', 'sub' => 'dim', 'trash' => 'cmd',
    ]); ?>
    <?php (new Template())->renderThemeable('player/parts/resume', [
        'panel' => 'border tui-line bg-base-200 font-mono text-sm', 'icon' => 'text-primary', 'heading' => 'text-primary', 'accept' => 'cmd justify-center text-primary', 'cancel' => 'cmd justify-center',
    ]); ?>
</div>
