<?php

namespace Jiejia\ARippleSong\Libraries;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\App;
use Jiejia\ARippleSong\CPTs\PodcastCPT;

/**
 * One-time rename of the stored "members" data to "hosts": the settings option and the episode meta.
 */
final class HostsMigration
{
    private const DONE_OPTION = App::PREFIX . 'hosts_migrated';

    private const OLD_OPTION = App::PREFIX . 'member_settings';

    private const NEW_OPTION = App::PREFIX . 'host_settings';

    /**
     * Old setting field keys and their new names.
     */
    private const SETTING_KEYS = ['members' => 'hosts', 'member_roles' => 'host_roles'];

    /**
     * Move the stored data once; a failed query leaves the migration to retry on the next request.
     *
     * @return void
     */
    public function run(): void
    {
        if (get_option(self::DONE_OPTION)) {
            return;
        }

        global $wpdb;

        $podcast = new PodcastCPT();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- One-time bulk rename of a meta key.
        $renamed = $wpdb->update(
            $wpdb->postmeta,
            ['meta_key' => $podcast->fieldMetaKey('hosts')],
            ['meta_key' => $podcast->fieldMetaKey('members')]
        );

        if ($renamed === false) {
            return;
        }

        $settings = get_option(self::OLD_OPTION);

        if (is_array($settings)) {
            $moved = [];

            foreach ($settings as $key => $value) {
                $moved[self::SETTING_KEYS[$key] ?? $key] = $value;
            }

            update_option(self::NEW_OPTION, $moved);
            delete_option(self::OLD_OPTION);
        }

        wp_cache_flush();
        update_option(self::DONE_OPTION, '1');
    }
}
