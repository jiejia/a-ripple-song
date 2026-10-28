<?php

namespace Jiejia\ARippleSong\Settings;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Abstracts\SettingAbstract;
/**
 * Carbon Fields podcast settings page.
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
}
