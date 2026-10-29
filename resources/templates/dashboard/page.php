<?php
/**
 * Statistics dashboard page.
 *
 * Charts are drawn by resources/assets/js/dashboard.js from the data-ars-chart
 * points; every chart also ships the same numbers as a table.
 *
 * @var string $title Page title.
 * @var string $pageUrl Dashboard URL without the period.
 * @var \Jiejia\ARippleSong\Statistics\DashboardStats $stats Dashboard numbers.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Template;
use Jiejia\ARippleSong\Settings\PodcastSetting;
use Jiejia\ARippleSong\Statistics\DashboardStats;

$template = new Template();
$dateFormat = (string) get_option('date_format');

$overview = $stats->overview();
$listening = $stats->listening();
$trend = $stats->trend();
$periodTop = $stats->periodTop();
$allTimeTop = $stats->allTimeTop();
$monthly = $stats->monthly();
$types = $stats->types();
$showChecks = $stats->showChecks();
$episodeChecks = $stats->episodeChecks();

$ranges = [
    7 => __('7 days', 'a-ripple-song'),
    30 => __('30 days', 'a-ripple-song'),
    90 => __('90 days', 'a-ripple-song'),
    365 => __('12 months', 'a-ripple-song'),
];
/* translators: %s: period such as "30 days". */
$periodLabel = sprintf(__('Last %s', 'a-ripple-song'), $ranges[$stats->days()]);
$episodeCount = static fn (int $count): string => sprintf(
    /* translators: %s: number of episodes. */
    _n('%s episode', '%s episodes', $count, 'a-ripple-song'),
    number_format_i18n($count)
);

// Change against the previous period of the same length.
$delta = null;
$deltaMeta = __('No earlier plays to compare with', 'a-ripple-song');

if ($listening['previous'] !== null) {
    $change = $listening['plays'] - $listening['previous'];
    $direction = $change > 0 ? 'up' : ($change < 0 ? 'down' : 'flat');
    $text = $listening['previous'] > 0
        ? number_format_i18n(abs($change) / $listening['previous'] * 100, 1) . '%'
        : number_format_i18n(abs($change));
    $delta = ['text' => $text, 'direction' => $direction];
    /* translators: %s: number of plays in the previous period. */
    $deltaMeta = sprintf(__('vs %s the period before', 'a-ripple-song'), number_format_i18n($listening['previous']));
}

$episodesMeta = [];

if ($overview['drafts'] > 0) {
    /* translators: %s: number of drafts. */
    $episodesMeta[] = sprintf(_n('%s draft', '%s drafts', $overview['drafts'], 'a-ripple-song'), number_format_i18n($overview['drafts']));
}

if ($overview['scheduled'] > 0) {
    /* translators: %s: number of scheduled episodes. */
    $episodesMeta[] = sprintf(__('%s scheduled', 'a-ripple-song'), number_format_i18n($overview['scheduled']));
}

if ($overview['latest'] instanceof WP_Post) {
    /* translators: %s: time since the latest episode, e.g. "3 days". */
    $episodesMeta[] = sprintf(__('latest %s ago', 'a-ripple-song'), human_time_diff((int) get_post_time('U', true, $overview['latest'])));
}

$periodTopMax = $periodTop !== [] ? $periodTop[0]['plays'] : 0;
$typesTotal = array_sum(array_column($types, 'count'));
$severityIcons = ['critical' => 'dismiss', 'warning' => 'warning', 'notice' => 'info-outline'];
$chartTable = static function (array $points, string $heading, string $valueHeading): void {
    ?>
    <details class="ars-dash-table">
        <summary><?php esc_html_e('Show as table', 'a-ripple-song'); ?></summary>
        <table class="widefat striped">
            <thead><tr><th scope="col"><?php echo esc_html($heading); ?></th><th scope="col" class="num"><?php echo esc_html($valueHeading); ?></th></tr></thead>
            <tbody>
                <?php foreach ($points as $point) : ?>
                    <tr><td><?php echo esc_html($point['title']); ?></td><td class="num"><?php echo esc_html(number_format_i18n($point['value'])); ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </details>
    <?php
};
?>
<div class="wrap ars-dashboard">
    <h1><?php echo esc_html($title); ?></h1>
    <hr class="wp-header-end">

    <section class="ars-dash-section" aria-labelledby="ars-dash-overview">
        <h2 id="ars-dash-overview" class="ars-dash-section__title"><?php esc_html_e('Overview', 'a-ripple-song'); ?></h2>
        <div class="ars-dash-tiles">
            <?php
            $template->render('dashboard/tile', [
                'icon' => 'microphone',
                'label' => __('Published episodes', 'a-ripple-song'),
                'value' => number_format_i18n($overview['published']),
                'meta' => implode(' · ', $episodesMeta),
            ]);
            $template->render('dashboard/tile', [
                'icon' => 'controls-play',
                'label' => __('Total plays', 'a-ripple-song'),
                'value' => number_format_i18n($overview['plays']),
                /* translators: %s: average number of plays. */
                'meta' => sprintf(__('%s per episode on average', 'a-ripple-song'), number_format_i18n($overview['avgPlays'], 1)),
            ]);
            $template->render('dashboard/tile', [
                'icon' => 'clock',
                'label' => __('Total audio length', 'a-ripple-song'),
                'value' => DashboardStats::formatDuration($overview['duration']),
                /* translators: %s: average episode length, e.g. "45 min". */
                'meta' => $overview['avgDuration'] > 0 ? sprintf(__('%s per episode on average', 'a-ripple-song'), DashboardStats::formatDuration($overview['avgDuration'])) : '',
            ]);
            $template->render('dashboard/tile', [
                'icon' => 'media-audio',
                'label' => __('Audio file size', 'a-ripple-song'),
                'value' => $overview['size'] > 0 ? (string) size_format($overview['size'], 1) : '—',
                'meta' => __('Audio on another site is not counted', 'a-ripple-song'),
            ]);
            ?>
        </div>
    </section>

    <section class="ars-dash-section" aria-labelledby="ars-dash-listening">
        <div class="ars-dash-section__head">
            <h2 id="ars-dash-listening" class="ars-dash-section__title"><?php esc_html_e('Listening', 'a-ripple-song'); ?></h2>
            <nav class="ars-dash-range" aria-label="<?php esc_attr_e('Period', 'a-ripple-song'); ?>">
                <?php foreach ($ranges as $days => $label) : ?>
                    <a
                        href="<?php echo esc_url(add_query_arg('range', $days, $pageUrl)); ?>"
                        class="ars-dash-range__item"
                        <?php echo $days === $stats->days() ? 'aria-current="page"' : ''; ?>
                    ><?php echo esc_html($label); ?></a>
                <?php endforeach; ?>
            </nav>
        </div>

        <?php if ($listening['since'] === null) : ?>
            <div class="ars-dash-note">
                <span class="dashicons dashicons-info-outline" aria-hidden="true"></span>
                <p><?php esc_html_e('No plays recorded by date yet. Daily statistics start with the next play; plays counted before this update only show up in the totals.', 'a-ripple-song'); ?></p>
            </div>
        <?php elseif ($listening['partial']) : ?>
            <div class="ars-dash-note">
                <span class="dashicons dashicons-info-outline" aria-hidden="true"></span>
                <p>
                    <?php
                    /* translators: %s: date of the first recorded play. */
                    echo esc_html(sprintf(__('Plays are recorded by date since %s, so this period is incomplete.', 'a-ripple-song'), wp_date($dateFormat, strtotime($listening['since']))));
                    ?>
                </p>
            </div>
        <?php endif; ?>

        <div class="ars-dash-tiles">
            <?php
            $template->render('dashboard/tile', [
                'icon' => 'chart-line',
                'label' => __('Plays', 'a-ripple-song'),
                'value' => number_format_i18n($listening['plays']),
                'delta' => $delta,
                'meta' => $deltaMeta,
            ]);
            $template->render('dashboard/tile', [
                'icon' => 'calendar-alt',
                'label' => __('Daily average', 'a-ripple-song'),
                'value' => number_format_i18n($listening['dailyAverage'], 1),
                'meta' => $periodLabel,
            ]);
            $template->render('dashboard/tile', [
                'icon' => 'star-filled',
                'label' => __('Best day', 'a-ripple-song'),
                'value' => $listening['best'] !== null ? number_format_i18n($listening['best']['plays']) : '—',
                'meta' => $listening['best'] !== null ? wp_date($dateFormat, strtotime($listening['best']['date'])) : '',
            ]);
            $template->render('dashboard/tile', [
                'icon' => 'playlist-audio',
                'label' => __('Episodes played', 'a-ripple-song'),
                'value' => number_format_i18n($listening['episodesPlayed']),
                /* translators: %s: number of published episodes. */
                'meta' => sprintf(__('of %s published', 'a-ripple-song'), number_format_i18n($overview['published'])),
            ]);
            ?>
        </div>

        <div class="ars-dash-grid ars-dash-grid--wide">
            <div class="ars-dash-card">
                <div class="ars-dash-card__head">
                    <h3 class="ars-dash-card__title"><?php echo esc_html($stats->days() > 90 ? __('Plays per week', 'a-ripple-song') : __('Plays per day', 'a-ripple-song')); ?></h3>
                    <span class="ars-dash-card__meta"><?php echo esc_html($periodLabel); ?></span>
                </div>
                <div
                    class="ars-chart"
                    data-ars-chart="area"
                    data-ars-series="<?php esc_attr_e('Plays', 'a-ripple-song'); ?>"
                    data-ars-points="<?php echo esc_attr((string) wp_json_encode($trend)); ?>"
                    data-ars-label="<?php echo esc_attr(sprintf(
                        /* translators: 1: period such as "Last 30 days", 2: number of plays. */
                        __('Plays chart, %1$s: %2$s in total', 'a-ripple-song'),
                        $periodLabel,
                        number_format_i18n($listening['plays'])
                    )); ?>"
                ></div>
                <?php $chartTable($trend, $stats->days() > 90 ? __('Week', 'a-ripple-song') : __('Day', 'a-ripple-song'), __('Plays', 'a-ripple-song')); ?>
            </div>

            <div class="ars-dash-card">
                <div class="ars-dash-card__head">
                    <h3 class="ars-dash-card__title"><?php esc_html_e('Top episodes', 'a-ripple-song'); ?></h3>
                    <span class="ars-dash-card__meta"><?php echo esc_html($periodLabel); ?></span>
                </div>
                <?php if ($periodTop === []) : ?>
                    <p class="ars-dash-empty"><?php esc_html_e('No plays in this period.', 'a-ripple-song'); ?></p>
                <?php else : ?>
                    <ol class="ars-dash-bars">
                        <?php foreach ($periodTop as $row) : ?>
                            <li class="ars-dash-bars__item">
                                <div class="ars-dash-bars__text">
                                    <a href="<?php echo esc_url((string) get_edit_post_link($row['post'])); ?>" class="ars-dash-bars__label"><?php echo esc_html(get_the_title($row['post'])); ?></a>
                                    <span class="ars-dash-bars__value"><?php echo esc_html(number_format_i18n($row['plays'])); ?></span>
                                </div>
                                <span class="ars-dash-bars__track" aria-hidden="true">
                                    <span class="ars-dash-bars__fill" style="width: <?php echo esc_attr((string) round($row['plays'] / $periodTopMax * 100, 2)); ?>%"></span>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="ars-dash-section" aria-labelledby="ars-dash-library">
        <h2 id="ars-dash-library" class="ars-dash-section__title"><?php esc_html_e('Library', 'a-ripple-song'); ?></h2>

        <div class="ars-dash-grid">
            <div class="ars-dash-card">
                <div class="ars-dash-card__head">
                    <h3 class="ars-dash-card__title"><?php esc_html_e('Episodes published per month', 'a-ripple-song'); ?></h3>
                    <span class="ars-dash-card__meta"><?php esc_html_e('Last 12 months', 'a-ripple-song'); ?></span>
                </div>
                <div
                    class="ars-chart"
                    data-ars-chart="column"
                    data-ars-series="<?php esc_attr_e('Episodes published', 'a-ripple-song'); ?>"
                    data-ars-points="<?php echo esc_attr((string) wp_json_encode($monthly)); ?>"
                    data-ars-label="<?php echo esc_attr(sprintf(
                        /* translators: %s: number of episodes. */
                        __('Episodes published per month over the last 12 months: %s in total', 'a-ripple-song'),
                        number_format_i18n(array_sum(array_column($monthly, 'value')))
                    )); ?>"
                ></div>
                <?php $chartTable($monthly, __('Month', 'a-ripple-song'), __('Episodes published', 'a-ripple-song')); ?>
            </div>

            <div class="ars-dash-card">
                <div class="ars-dash-card__head">
                    <h3 class="ars-dash-card__title"><?php esc_html_e('Episode types', 'a-ripple-song'); ?></h3>
                    <span class="ars-dash-card__meta"><?php echo esc_html($episodeCount($typesTotal)); ?></span>
                </div>
                <?php if ($typesTotal === 0) : ?>
                    <p class="ars-dash-empty"><?php esc_html_e('No published episodes yet.', 'a-ripple-song'); ?></p>
                <?php else : ?>
                    <div class="ars-dash-stack" aria-hidden="true">
                        <?php foreach ($types as $type => $row) : ?>
                            <?php if ($row['count'] > 0) : ?>
                                <span class="ars-dash-stack__part ars-dash-stack__part--<?php echo esc_attr($type); ?>" style="flex-grow: <?php echo esc_attr((string) $row['count']); ?>"></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <ul class="ars-dash-legend">
                        <?php foreach ($types as $type => $row) : ?>
                            <li class="ars-dash-legend__item">
                                <span class="ars-dash-legend__swatch ars-dash-stack__part--<?php echo esc_attr($type); ?>" aria-hidden="true"></span>
                                <span class="ars-dash-legend__label"><?php echo esc_html($row['label']); ?></span>
                                <span class="ars-dash-legend__value"><?php echo esc_html(number_format_i18n($row['count'])); ?></span>
                                <span class="ars-dash-legend__share"><?php echo esc_html(number_format_i18n($row['count'] / $typesTotal * 100) . '%'); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <div class="ars-dash-card">
            <div class="ars-dash-card__head">
                <h3 class="ars-dash-card__title"><?php esc_html_e('Most played episodes', 'a-ripple-song'); ?></h3>
                <span class="ars-dash-card__meta"><?php esc_html_e('All time', 'a-ripple-song'); ?></span>
            </div>
            <?php if ($allTimeTop === []) : ?>
                <p class="ars-dash-empty"><?php esc_html_e('No published episodes yet.', 'a-ripple-song'); ?></p>
            <?php else : ?>
                <div class="ars-dash-scroll">
                    <table class="widefat striped ars-dash-ranking">
                        <thead>
                            <tr>
                                <th scope="col" class="ars-dash-ranking__rank">#</th>
                                <th scope="col"><?php esc_html_e('Episode', 'a-ripple-song'); ?></th>
                                <th scope="col"><?php esc_html_e('Published', 'a-ripple-song'); ?></th>
                                <th scope="col" class="num"><?php esc_html_e('Length', 'a-ripple-song'); ?></th>
                                <th scope="col" class="num"><?php echo esc_html($periodLabel); ?></th>
                                <th scope="col" class="num"><?php esc_html_e('Total plays', 'a-ripple-song'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allTimeTop as $index => $row) : ?>
                                <tr>
                                    <td class="ars-dash-ranking__rank"><?php echo esc_html(number_format_i18n($index + 1)); ?></td>
                                    <td>
                                        <a href="<?php echo esc_url((string) get_edit_post_link($row['post'])); ?>" class="ars-dash-ranking__title"><?php echo esc_html(get_the_title($row['post'])); ?></a>
                                        <a href="<?php echo esc_url((string) get_permalink($row['post'])); ?>" class="ars-dash-ranking__view"><?php esc_html_e('View', 'a-ripple-song'); ?></a>
                                    </td>
                                    <td><?php echo esc_html(get_the_date($dateFormat, $row['post'])); ?></td>
                                    <td class="num"><?php echo esc_html(DashboardStats::formatDuration($row['duration'])); ?></td>
                                    <td class="num"><?php echo esc_html(number_format_i18n($row['periodPlays'])); ?></td>
                                    <td class="num"><strong><?php echo esc_html(number_format_i18n($row['plays'])); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="ars-dash-section" aria-labelledby="ars-dash-health">
        <h2 id="ars-dash-health" class="ars-dash-section__title"><?php esc_html_e('Feed health', 'a-ripple-song'); ?></h2>

        <div class="ars-dash-grid ars-dash-grid--narrow">
            <div class="ars-dash-card">
                <div class="ars-dash-card__head">
                    <h3 class="ars-dash-card__title"><?php esc_html_e('Show settings', 'a-ripple-song'); ?></h3>
                    <a class="ars-dash-card__meta" href="<?php echo esc_url(add_query_arg('page', (new PodcastSetting())->slug(), admin_url('admin.php'))); ?>"><?php esc_html_e('Edit', 'a-ripple-song'); ?></a>
                </div>
                <ul class="ars-dash-checks">
                    <?php foreach ($showChecks as $check) : ?>
                        <?php $status = $check['ok'] ? 'good' : $check['severity']; ?>
                        <li class="ars-dash-check">
                            <span class="ars-dash-status ars-dash-status--<?php echo esc_attr($status); ?>">
                                <span class="dashicons dashicons-<?php echo esc_attr($check['ok'] ? 'yes-alt' : $severityIcons[$check['severity']]); ?>" aria-hidden="true"></span>
                                <?php echo esc_html($check['ok'] ? __('Set', 'a-ripple-song') : __('Missing', 'a-ripple-song')); ?>
                            </span>
                            <span class="ars-dash-check__label"><?php echo esc_html($check['label']); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="ars-dash-card">
                <div class="ars-dash-card__head">
                    <h3 class="ars-dash-card__title"><?php esc_html_e('Episode fields', 'a-ripple-song'); ?></h3>
                    <span class="ars-dash-card__meta"><?php esc_html_e('Published episodes missing each field', 'a-ripple-song'); ?></span>
                </div>
                <ul class="ars-dash-checks">
                    <?php foreach ($episodeChecks as $check) : ?>
                        <?php $status = $check['count'] === 0 ? 'good' : $check['severity']; ?>
                        <li class="ars-dash-check ars-dash-check--episodes">
                            <span class="ars-dash-status ars-dash-status--<?php echo esc_attr($status); ?>">
                                <span class="dashicons dashicons-<?php echo esc_attr($check['count'] === 0 ? 'yes-alt' : $severityIcons[$check['severity']]); ?>" aria-hidden="true"></span>
                                <?php
                                echo esc_html($check['count'] === 0
                                    ? __('All set', 'a-ripple-song')
                                    /* translators: 1: episodes missing the field, 2: published episodes. */
                                    : sprintf(__('%1$s of %2$s', 'a-ripple-song'), number_format_i18n($check['count']), number_format_i18n($overview['published'])));
                                ?>
                            </span>
                            <div class="ars-dash-check__body">
                                <span class="ars-dash-check__label"><?php echo esc_html($check['label']); ?></span>
                                <span class="ars-dash-check__help"><?php echo esc_html($check['help']); ?></span>
                                <?php if ($check['episodes'] !== []) : ?>
                                    <details class="ars-dash-check__list">
                                        <summary><?php esc_html_e('Show episodes', 'a-ripple-song'); ?></summary>
                                        <ul>
                                            <?php foreach ($check['episodes'] as $episode) : ?>
                                                <li><a href="<?php echo esc_url((string) get_edit_post_link($episode)); ?>"><?php echo esc_html(get_the_title($episode)); ?></a></li>
                                            <?php endforeach; ?>
                                            <?php if ($check['count'] > count($check['episodes'])) : ?>
                                                <li class="ars-dash-check__more">
                                                    <?php
                                                    /* translators: %s: number of further episodes. */
                                                    echo esc_html(sprintf(__('and %s more', 'a-ripple-song'), number_format_i18n($check['count'] - count($check['episodes']))));
                                                    ?>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </details>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </section>
</div>
