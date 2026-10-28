<?php

namespace Jiejia\ARippleSong\Providers;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Abstracts\ServiceProviderAbstract;
use Jiejia\ARippleSong\Plugin;

/**
 * Loads the plugin translations bundled in resources/lang.
 */
class TranslationServiceProvider extends ServiceProviderAbstract
{
    /**
     * Register the translation loader hook.
     *
     * @return void
     */
    public function register(): void
    {
        // Priority 0 so the strings are ready before other init callbacks (post type labels) use them.
        add_action('init', [$this, 'loadTextdomain'], 0);
    }

    /**
     * Load the plugin textdomain; files in wp-content/languages/plugins take precedence.
     *
     * @return void
     */
    public function loadTextdomain(): void
    {
        load_plugin_textdomain(Plugin::SLUG, false, basename(Plugin::DIR) . '/resources/lang');
    }
}
