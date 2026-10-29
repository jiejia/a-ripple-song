<?php

namespace Jiejia\ARippleSong\Settings;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Abstracts\SettingAbstract;
use Jiejia\ARippleSong\Libraries\Template;
use Jiejia\ARippleSong\Statistics\DashboardStats;

/**
 * Statistics dashboard: the plugin's top-level page.
 */
class DashboardSetting extends SettingAbstract
{
    public string $slug = 'dashboard';


    public function isTopLevelMenu(): bool
    {
        return true;
    }


    public function title(): string
    {
        return __('Dashboard', 'a-ripple-song');
    }

    public function fields(): array
    {
        return [];
    }

    /**
     * Output the statistics dashboard instead of a settings form.
     *
     * @return void
     */
    public function page(): void
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only period switch.
        $range = isset($_GET['range']) ? absint(wp_unslash($_GET['range'])) : DashboardStats::DEFAULT_RANGE;

        (new Template())->render('dashboard/page', [
            'title' => $this->title(),
            'pageUrl' => add_query_arg('page', $this->slug(), admin_url('admin.php')),
            'stats' => new DashboardStats($range),
        ]);
    }
}
