<?php

namespace Jiejia\ARippleSong\Statistics;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\CPTs\PodcastCPT;
use Jiejia\ARippleSong\Settings\PodcastSetting;

/**
 * Collects the numbers shown on the plugin dashboard.
 *
 * All-time numbers come from the published episodes and their play count meta;
 * the listening period comes from PlayLog.
 */
final class DashboardStats
{
    /**
     * Selectable listening periods in days; 365 is shown in weekly buckets.
     */
    public const RANGES = [7, 30, 90, 365];

    public const DEFAULT_RANGE = 30;

    /**
     * Episodes listed in the top lists.
     */
    private const TOP_LIMIT = 10;

    /**
     * Episodes named under each feed check; the rest are summed up.
     */
    private const HEALTH_LIST_LIMIT = 5;

    private PodcastCPT $podcast;

    private PlayLog $log;

    private int $days;

    private \DateTimeImmutable $from;

    private \DateTimeImmutable $to;

    /**
     * Published episodes, newest first.
     *
     * @var array<int,\WP_Post>|null
     */
    private ?array $episodes = null;

    /**
     * Plays per episode in the period, most played first.
     *
     * @var array<int,int>|null
     */
    private ?array $periodPlays = null;

    /**
     * Plays per day in the period.
     *
     * @var array<string,int>|null
     */
    private ?array $dailyPlays = null;

    /**
     * @param int $days Listening period in days; unknown values fall back to the default.
     */
    public function __construct(int $days)
    {
        $this->podcast = new PodcastCPT();
        $this->log = new PlayLog();
        $this->days = in_array($days, self::RANGES, true) ? $days : self::DEFAULT_RANGE;
        $this->to = new \DateTimeImmutable('today', wp_timezone());
        $this->from = $this->to->modify(sprintf('-%d days', $this->days - 1));
    }

    /**
     * Return the selected period in days.
     *
     * @return int
     */
    public function days(): int
    {
        return $this->days;
    }

    /**
     * Return all-time library numbers.
     *
     * @return array{published:int,drafts:int,scheduled:int,latest:?\WP_Post,plays:int,avgPlays:float,duration:int,avgDuration:int,size:int}
     */
    public function overview(): array
    {
        $counts = wp_count_posts($this->podcast->slug());
        $episodes = $this->episodes();
        $plays = 0;
        $duration = 0;
        $timed = 0;
        $size = 0;

        foreach ($episodes as $episode) {
            $plays += $this->podcast->playCount($episode->ID);
            $seconds = (int) $this->meta($episode, 'duration');
            $duration += max(0, $seconds);
            $timed += $seconds > 0 ? 1 : 0;
            $size += max(0, (int) $this->meta($episode, 'audio_length'));
        }

        return [
            'published' => count($episodes),
            'drafts' => (int) ($counts->draft ?? 0) + (int) ($counts->pending ?? 0),
            'scheduled' => (int) ($counts->future ?? 0),
            'latest' => $episodes !== [] ? reset($episodes) : null,
            'plays' => $plays,
            'avgPlays' => $episodes !== [] ? $plays / count($episodes) : 0.0,
            'duration' => $duration,
            'avgDuration' => $timed > 0 ? (int) round($duration / $timed) : 0,
            'size' => $size,
        ];
    }

    /**
     * Return the listening numbers of the selected period.
     *
     * "previous" is null when the log does not cover the whole previous period,
     * so a missing history never shows up as growth.
     *
     * @return array{since:?string,partial:bool,plays:int,previous:?int,dailyAverage:float,best:?array{date:string,plays:int},episodesPlayed:int}
     */
    public function listening(): array
    {
        $since = $this->log->firstDate();
        $daily = $this->dailyPlays();
        $plays = array_sum($daily);
        $previous = null;

        $previousTo = $this->from->modify('-1 day');
        $previousFrom = $previousTo->modify(sprintf('-%d days', $this->days - 1));

        if ($since !== null && $since <= $this->ymd($previousFrom)) {
            $previous = $this->log->total($this->ymd($previousFrom), $this->ymd($previousTo));
        }

        $best = null;

        if ($daily !== []) {
            $bestPlays = max($daily);
            $best = ['date' => (string) array_search($bestPlays, $daily, true), 'plays' => $bestPlays];
        }

        return [
            'since' => $since,
            // The period starts before the first recorded play, so its early days are unknown rather than zero.
            'partial' => $since === null || $since > $this->ymd($this->from),
            'plays' => $plays,
            'previous' => $previous,
            'dailyAverage' => $plays / $this->days,
            'best' => $best,
            'episodesPlayed' => count(array_filter(
                $this->periodPlays(),
                fn (int $count, int $postId): bool => $count > 0 && isset($this->episodes()[$postId]),
                ARRAY_FILTER_USE_BOTH
            )),
        ];
    }

    /**
     * Return the plays chart series: daily, or weekly for the 12 month period.
     *
     * @return array<int,array{label:string,title:string,value:int}>
     */
    public function trend(): array
    {
        $daily = $this->dailyPlays();
        $bucket = $this->days > 90 ? 7 : 1;
        $dateFormat = (string) get_option('date_format');
        $points = [];

        // Buckets run back from today, so the last one always ends today and the first may be cut short.
        for ($end = $this->to; $end >= $this->from; $end = $end->modify(sprintf('-%d days', $bucket))) {
            $start = max($this->from, $end->modify(sprintf('-%d days', $bucket - 1)));
            $value = 0;

            for ($day = $start; $day <= $end; $day = $day->modify('+1 day')) {
                $value += $daily[$this->ymd($day)] ?? 0;
            }

            $points[] = [
                /* translators: Short date on the chart axis, see https://www.php.net/manual/datetime.format.php */
                'label' => wp_date(__('n/j', 'a-ripple-song'), $start->getTimestamp()),
                'title' => $bucket === 1
                    ? wp_date($dateFormat, $start->getTimestamp())
                    /* translators: 1: first day, 2: last day of a week. */
                    : sprintf(__('%1$s – %2$s', 'a-ripple-song'), wp_date($dateFormat, $start->getTimestamp()), wp_date($dateFormat, $end->getTimestamp())),
                'value' => $value,
            ];
        }

        return array_reverse($points);
    }

    /**
     * Return the most played episodes of the period.
     *
     * @return array<int,array{post:\WP_Post,plays:int}>
     */
    public function periodTop(): array
    {
        $top = [];

        foreach ($this->periodPlays() as $postId => $plays) {
            // Episodes unpublished since their plays drop out.
            if ($plays > 0 && isset($this->episodes()[$postId])) {
                $top[] = ['post' => $this->episodes()[$postId], 'plays' => $plays];
            }

            if (count($top) === self::TOP_LIMIT) {
                break;
            }
        }

        return $top;
    }

    /**
     * Return the most played episodes of all time.
     *
     * @return array<int,array{post:\WP_Post,plays:int,periodPlays:int,duration:int}>
     */
    public function allTimeTop(): array
    {
        $rows = [];

        foreach ($this->episodes() as $episode) {
            $rows[] = [
                'post' => $episode,
                'plays' => $this->podcast->playCount($episode->ID),
                'periodPlays' => $this->periodPlays()[$episode->ID] ?? 0,
                'duration' => max(0, (int) $this->meta($episode, 'duration')),
            ];
        }

        // Ties keep the newest episode first; usort is stable since PHP 8.0.
        usort($rows, static fn (array $a, array $b): int => $b['plays'] <=> $a['plays']);

        return array_slice($rows, 0, self::TOP_LIMIT);
    }

    /**
     * Return episodes published per month over the last 12 months.
     *
     * @return array<int,array{label:string,title:string,value:int}>
     */
    public function monthly(): array
    {
        $months = [];
        $first = $this->to->modify('first day of this month')->modify('-11 months');

        for ($month = $first; $month <= $this->to; $month = $month->modify('+1 month')) {
            $months[$month->format('Y-m')] = [
                /* translators: Month on the chart axis, see https://www.php.net/manual/datetime.format.php */
                'label' => wp_date(__('M', 'a-ripple-song'), $month->getTimestamp()),
                /* translators: Month and year in the chart tooltip, see https://www.php.net/manual/datetime.format.php */
                'title' => wp_date(__('F Y', 'a-ripple-song'), $month->getTimestamp()),
                'value' => 0,
            ];
        }

        foreach ($this->episodes() as $episode) {
            // post_date is already in the site timezone.
            $key = substr($episode->post_date, 0, 7);

            if (isset($months[$key])) {
                $months[$key]['value']++;
            }
        }

        return array_values($months);
    }

    /**
     * Return the number of published episodes per episode type.
     *
     * @return array<string,array{label:string,count:int}>
     */
    public function types(): array
    {
        $types = [
            'full' => ['label' => __('Full', 'a-ripple-song'), 'count' => 0],
            'trailer' => ['label' => __('Trailer', 'a-ripple-song'), 'count' => 0],
            'bonus' => ['label' => __('Bonus', 'a-ripple-song'), 'count' => 0],
        ];

        foreach ($this->episodes() as $episode) {
            // The feed treats an empty or unknown type as "full".
            $type = (string) $this->meta($episode, 'episode_type');
            $types[isset($types[$type]) ? $type : 'full']['count']++;
        }

        return $types;
    }

    /**
     * Return the show settings the feed needs, with whether each one is filled in.
     *
     * @return array<int,array{label:string,ok:bool,severity:string}>
     */
    public function showChecks(): array
    {
        $setting = new PodcastSetting();
        $checks = [
            'cover' => [__('Show cover', 'a-ripple-song'), 'critical'],
            'category_primary' => [__('Primary category', 'a-ripple-song'), 'critical'],
            'description' => [__('Show description', 'a-ripple-song'), 'warning'],
            'author' => [__('Author', 'a-ripple-song'), 'warning'],
            'owner_email' => [__('Owner email', 'a-ripple-song'), 'warning'],
        ];

        $result = [];

        foreach ($checks as $key => [$label, $severity]) {
            $value = $setting->value($key);
            $result[] = [
                'label' => $label,
                'ok' => is_scalar($value) && trim((string) $value) !== '',
                'severity' => $severity,
            ];
        }

        return $result;
    }

    /**
     * Return the episode fields the feed uses, with the published episodes missing each.
     *
     * @return array<int,array{label:string,help:string,severity:string,count:int,episodes:array<int,\WP_Post>}>
     */
    public function episodeChecks(): array
    {
        $checks = [
            'audio_file' => [
                __('Audio file', 'a-ripple-song'),
                __('Podcast apps cannot play an episode without one.', 'a-ripple-song'),
                'critical',
            ],
            'duration' => [
                __('Duration', 'a-ripple-song'),
                __('Detected when the episode is saved; audio on another site cannot be measured.', 'a-ripple-song'),
                'warning',
            ],
            'episode_number' => [
                __('Episode number', 'a-ripple-song'),
                __('Recommended by Apple Podcasts.', 'a-ripple-song'),
                'warning',
            ],
            'episode_image' => [
                __('Episode cover', 'a-ripple-song'),
                __('Optional. Episodes without one use the show cover.', 'a-ripple-song'),
                'notice',
            ],
            'episode_transcript' => [
                __('Transcript', 'a-ripple-song'),
                __('Optional. Shown by apps that support Podcasting 2.0.', 'a-ripple-song'),
                'notice',
            ],
            'episode_chapters' => [
                __('Chapters', 'a-ripple-song'),
                __('Optional. Shown by apps that support Podcasting 2.0.', 'a-ripple-song'),
                'notice',
            ],
        ];

        $result = [];

        foreach ($checks as $key => [$label, $help, $severity]) {
            $missing = array_values(array_filter(
                $this->episodes(),
                fn (\WP_Post $episode): bool => $key === 'duration' || $key === 'episode_number'
                    ? (int) $this->meta($episode, $key) <= 0
                    : trim((string) $this->meta($episode, $key)) === ''
            ));

            $result[] = [
                'label' => $label,
                'help' => $help,
                'severity' => $severity,
                'count' => count($missing),
                'episodes' => array_slice($missing, 0, self::HEALTH_LIST_LIMIT),
            ];
        }

        return $result;
    }

    /**
     * Format seconds as hours and minutes, e.g. "12 h 5 min".
     *
     * @param int $seconds Seconds.
     * @return string
     */
    public static function formatDuration(int $seconds): string
    {
        if ($seconds <= 0) {
            return '—';
        }

        $minutes = (int) round($seconds / MINUTE_IN_SECONDS);

        if ($minutes < 60) {
            /* translators: %s: number of minutes. */
            return sprintf(__('%s min', 'a-ripple-song'), number_format_i18n(max(1, $minutes)));
        }

        /* translators: 1: number of hours, 2: number of minutes. */
        return sprintf(__('%1$s h %2$s min', 'a-ripple-song'), number_format_i18n(intdiv($minutes, 60)), number_format_i18n($minutes % 60));
    }

    /**
     * Return published episodes keyed by ID, newest first, with their meta cached.
     *
     * @return array<int,\WP_Post>
     */
    private function episodes(): array
    {
        if ($this->episodes === null) {
            $posts = get_posts([
                'post_type' => $this->podcast->slug(),
                'post_status' => 'publish',
                'numberposts' => -1,
                'orderby' => 'date',
                'order' => 'DESC',
                'update_post_term_cache' => false,
            ]);

            $this->episodes = [];

            foreach ($posts as $post) {
                $this->episodes[$post->ID] = $post;
            }
        }

        return $this->episodes;
    }

    /**
     * Return plays per episode in the period, most played first.
     *
     * @return array<int,int>
     */
    private function periodPlays(): array
    {
        return $this->periodPlays ??= $this->log->byEpisode($this->ymd($this->from), $this->ymd($this->to));
    }

    /**
     * Return plays per day in the period.
     *
     * @return array<string,int>
     */
    private function dailyPlays(): array
    {
        return $this->dailyPlays ??= $this->log->daily($this->ymd($this->from), $this->ymd($this->to));
    }

    /**
     * Return one episode field value.
     *
     * @param \WP_Post $episode Episode.
     * @param string $key Field key.
     * @return mixed
     */
    private function meta(\WP_Post $episode, string $key): mixed
    {
        return get_post_meta($episode->ID, $this->podcast->fieldMetaKey($key), true);
    }

    /**
     * Format a date as Y-m-d.
     *
     * @param \DateTimeImmutable $date Date.
     * @return string
     */
    private function ymd(\DateTimeImmutable $date): string
    {
        return $date->format('Y-m-d');
    }
}
