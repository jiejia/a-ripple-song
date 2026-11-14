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
        <?php (new Template())->renderThemeable('player/parts/mini', ['cover' => 'bg-base-300']); ?>
        <div class="flex flex-wrap items-center gap-x-3 gap-y-2 px-4 py-3 [--cmd-gap:0.6ch] [--cmd-pad:0.4ch] @max-xl:[--cmd-gap:0.3ch] @max-xl:[--cmd-pad:0.3ch] @max-[22rem]:gap-x-2 @max-[22rem]:px-3 @max-sm:[--cmd-gap:0.25ch] @max-sm:[--cmd-pad:0.25ch] @5xl:min-h-16 @5xl:flex-nowrap @5xl:py-0">
        <?php (new Template())->renderThemeable('player/parts/info', [
            'class' => 'order-1 flex-1 basis-full border border-dashed tui-line p-2 @5xl:order-none @5xl:max-w-xs @5xl:basis-auto @5xl:border-0 @5xl:p-0',
            'cover' => 'mono-img bg-base-300',
            'title' => 'hover:text-primary',
            'meta' => 'dim',
        ]); ?>
        <div class="order-5 flex shrink-0 items-center @max-xl:text-xs @max-[22rem]:text-[11px] @5xl:order-none">
          <span class="contents">
            <button type="button" class="cmd" ars-on:click="prev()" aria-label="上一集">|&lt;</button>
          </span>
          <button type="button" class="cmd text-base text-primary @max-xl:text-sm @5xl:text-lg" ars-bind:class="playing && 'is-on'" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放" ars-text="loading ? '… ' : playing ? '❚❚' : '▶ '">▶ </button>
          <span class="contents">
            <button type="button" class="cmd" ars-on:click="next()" aria-label="下一集">&gt;|</button>
          </span>
        </div>
        <?php
        (new Template())->renderThemeable('player/parts/progress', [
            // min-w-0: the bar is a long character run clipped to the row width, so the row must be allowed to shrink.
            'class' => 'order-3 flex min-w-0 w-full flex-wrap items-center gap-x-3 gap-y-1 tabular-nums @5xl:order-none @5xl:w-auto @5xl:flex-1 @5xl:flex-nowrap',
            'spectrumTop' => ['data-spectrum' => 'ascii', 'data-bar' => '11', 'data-gap' => '1', 'class' => 'block h-10 w-full @5xl:hidden'],
            'time' => '', 'timeEnd' => 'dim',
            // A character bar that stretches with the row: two identical runs of █ / ░ are stacked, and the filled
            // run is clipped to progress% of the width, so the bar fills whatever room the row has instead of
            // staying 12 characters wide. The run is longer than the widest row can get, so it never runs out.
            'bar' => static function (): void {
                $run = static fn (string $char): string => esc_html(str_repeat($char, 320)); ?>
          <label class="bar-seek relative min-w-0 flex-1 tracking-[-0.05em] text-primary">
            <span class="sr-only">播放进度</span>
            <span aria-hidden="true" class="block overflow-hidden whitespace-nowrap"><?php echo $run('░'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above. ?></span>
            <span aria-hidden="true" class="absolute inset-y-0 left-0 overflow-hidden whitespace-nowrap" style="width:0" ars-bind:style="'width:' + progress + '%'"><?php echo $run('█'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above. ?></span>
            <input type="range" min="0" ars-bind:max="duration" step="1" ars-bind:value="position" ars-on:input="scrub($event.target.value)" ars-on:change="commit($event.target.value)">
          </label>
            <?php },
            'spectrum' => ['data-spectrum' => 'ascii', 'data-bar' => '11', 'data-gap' => '1', 'class' => 'hidden h-10 w-24 shrink-0 @[70rem]:block'],
            'wave' => ['data-gap' => '1'],
        ]);
        ?>
        <?php (new Template())->renderThemeable('player/parts/volume', ['rate' => 'cmd w-[9ch] shrink-0 justify-between @max-xl:w-[6ch]', 'rateUnit' => 'x', 'class' => 'order-4 flex flex-1 shrink-0 items-center justify-start gap-1 @max-xl:text-xs @max-[22rem]:text-[11px] @5xl:order-8 @5xl:flex-none', 'button' => 'cmd', 'icon' => '', 'range' => 'accent-primary']); ?>
        <div class="order-6 flex flex-1 items-center justify-end gap-1 @max-xl:text-xs @max-[22rem]:text-[11px] @5xl:contents">
          <button type="button" class="cmd w-[9ch] shrink-0 justify-between @5xl:order-7 @max-5xl:!hidden" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}x`">1x</button>
        <button type="button" class="cmd @5xl:order-9" ars-on:click="expanded = true" aria-label="播放队列">ls</button>
        <?php (new Template())->renderThemeable('player/parts/collapse', ['class' => 'cmd']); ?>
        </div>
        </div>
      </div>
    </div>

    <?php (new Template())->renderThemeable('player/parts/queue', [
        'panel' => 'border-r tui-line bg-base-200 font-mono text-sm [--tui-bg:var(--color-base-200)]', 'divider' => 'border-t tui-line', 'title' => 'text-sm text-primary', 'meta' => 'dim', 'clear' => 'cmd', 'row' => 'hover:text-primary', 'rowOn' => 'text-primary', 'cover' => 'mono-img bg-base-300', 'name' => '', 'sub' => 'dim', 'trash' => 'cmd',
    ]); ?>
    <?php (new Template())->renderThemeable('player/parts/resume', [
        'panel' => 'border tui-line bg-base-200 font-mono text-sm', 'icon' => 'text-primary', 'heading' => 'text-primary', 'accept' => 'cmd justify-center text-primary', 'cancel' => 'cmd justify-center',
    ]); ?>
</div>
