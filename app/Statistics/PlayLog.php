<?php

namespace Jiejia\ARippleSong\Statistics;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Plugin;

/**
 * Daily play counts per episode, stored in the {prefix}ars_play_stats table.
 *
 * The _ars_podcast_play_count meta keeps the all-time total; this table only
 * adds the dates, so trends start with the first play recorded after install.
 */
final class PlayLog
{
    /**
     * Bump when the table schema changes; install() then runs dbDelta again.
     */
    private const DB_VERSION = '1';

    private const VERSION_OPTION = Plugin::PREFIX . 'play_log_db_version';

    /**
     * Return the table name.
     *
     * @return string
     */
    public static function table(): string
    {
        global $wpdb;

        return $wpdb->prefix . Plugin::PREFIX . 'play_stats';
    }

    /**
     * Create or upgrade the table when the stored schema version is outdated.
     *
     * @return void
     */
    public function install(): void
    {
        if (get_option(self::VERSION_OPTION) === self::DB_VERSION) {
            return;
        }

        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $table = self::table();

        // dbDelta needs two spaces after PRIMARY KEY.
        dbDelta("CREATE TABLE {$table} (
            post_id bigint(20) unsigned NOT NULL,
            play_date date NOT NULL,
            plays int(10) unsigned NOT NULL DEFAULT 0,
            PRIMARY KEY  (post_id,play_date),
            KEY play_date (play_date)
        ) {$wpdb->get_charset_collate()};");

        update_option(self::VERSION_OPTION, self::DB_VERSION);
    }

    /**
     * Add one play of an episode to today's row (site timezone).
     *
     * @param int $postId Episode ID.
     * @return void
     */
    public function record(int $postId): void
    {
        global $wpdb;

        $table = self::table();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Custom table; one upsert keeps concurrent plays.
        $wpdb->query($wpdb->prepare(
            "INSERT INTO %i (post_id, play_date, plays) VALUES (%d, %s, 1) ON DUPLICATE KEY UPDATE plays = plays + 1",
            $table,
            $postId,
            wp_date('Y-m-d')
        ));
    }

    /**
     * Remove all rows of an episode.
     *
     * @param int $postId Episode ID.
     * @return void
     */
    public function forget(int $postId): void
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Custom table.
        $wpdb->delete(self::table(), ['post_id' => $postId], ['%d']);
    }

    /**
     * Return the first date with a recorded play, or null before the first play.
     *
     * @return string|null Y-m-d.
     */
    public function firstDate(): ?string
    {
        global $wpdb;

        $table = self::table();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Custom table.
        $date = $wpdb->get_var($wpdb->prepare("SELECT MIN(play_date) FROM %i", $table));

        return is_string($date) && $date !== '' ? $date : null;
    }

    /**
     * Return plays per day; days without plays are left out.
     *
     * @param string $from First day, Y-m-d.
     * @param string $to Last day, Y-m-d.
     * @return array<string,int> Plays keyed by Y-m-d.
     */
    public function daily(string $from, string $to): array
    {
        global $wpdb;

        $table = self::table();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Custom table.
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT play_date, SUM(plays) AS plays FROM %i WHERE play_date BETWEEN %s AND %s GROUP BY play_date",
            $table,
            $from,
            $to
        ));

        $days = [];

        foreach ((array) $rows as $row) {
            $days[(string) $row->play_date] = (int) $row->plays;
        }

        return $days;
    }

    /**
     * Return the number of plays in a period.
     *
     * @param string $from First day, Y-m-d.
     * @param string $to Last day, Y-m-d.
     * @return int
     */
    public function total(string $from, string $to): int
    {
        global $wpdb;

        $table = self::table();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Custom table.
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(plays) FROM %i WHERE play_date BETWEEN %s AND %s",
            $table,
            $from,
            $to
        ));
    }

    /**
     * Return plays per episode in a period, most played first.
     *
     * @param string $from First day, Y-m-d.
     * @param string $to Last day, Y-m-d.
     * @return array<int,int> Plays keyed by episode ID.
     */
    public function byEpisode(string $from, string $to): array
    {
        global $wpdb;

        $table = self::table();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Custom table.
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT post_id, SUM(plays) AS plays FROM %i WHERE play_date BETWEEN %s AND %s GROUP BY post_id ORDER BY plays DESC",
            $table,
            $from,
            $to
        ));

        $episodes = [];

        foreach ((array) $rows as $row) {
            $episodes[(int) $row->post_id] = (int) $row->plays;
        }

        return $episodes;
    }
}
