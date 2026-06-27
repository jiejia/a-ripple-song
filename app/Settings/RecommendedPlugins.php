<?php

namespace App\Settings;

use App\Theme;
use Automatic_Upgrader_Skin;
use Plugin_Upgrader;
use WP_Error;

/**
 * Recommended plugin settings page helpers.
 */
class RecommendedPlugins
{
    /**
     * Return the recommended plugins page slug.
     *
     * @return string
     */
    public function pageSlug(): string
    {
        return Theme::PREFIX . '_recommended_plugins';
    }

    /**
     * Return the recommended plugins page title.
     *
     * @return string
     */
    public function pageTitle(): string
    {
        return __('Recommended Plugins', 'a-ripple-song');
    }

    /**
     * Return the parent menu slug for this page.
     *
     * @return string
     */
    public function parentPageSlug(): string
    {
        return Theme::SLUG;
    }

    /**
     * Return recommended plugin definitions.
     *
     * @return array<int,array{name:string,slug:string,description:string}>
     */
    public function plugins(): array
    {
        return [
            [
                'name' => 'One Click Demo Import',
                'slug' => 'one-click-demo-import',
                'description' => __('Import demo content, widgets, and Customizer settings for theme setup.', 'a-ripple-song'),
            ],
            [
                'name' => 'Advanced Media Offloader',
                'slug' => 'advanced-media-offloader',
                'description' => __('Offload WordPress media files to external storage and serve optimized media URLs.', 'a-ripple-song'),
            ],
        ];
    }

    /**
     * Return one recommended plugin by slug.
     *
     * @param string $slug Plugin slug.
     * @return array{name:string,slug:string,description:string}|null
     */
    public function pluginBySlug(string $slug): ?array
    {
        foreach ($this->plugins() as $plugin) {
            // Match only known recommended plugin slugs.
            if ($plugin['slug'] === $slug) {
                return $plugin;
            }
        }

        return null;
    }

    /**
     * Return installed plugin file for a slug.
     *
     * @param string $slug Plugin slug.
     * @return string|null
     */
    public function installedPluginFile(string $slug): ?string
    {
        $this->loadPluginFunctions();

        /** @var array<string,array<string,string>> $installedPlugins */
        $installedPlugins = get_plugins();

        foreach (array_keys($installedPlugins) as $pluginFile) {
            // WordPress.org plugins normally live in a directory named after the slug.
            if (dirname($pluginFile) === $slug || $pluginFile === $slug . '.php') {
                return $pluginFile;
            }
        }

        return null;
    }

    /**
     * Return plugin status for display and actions.
     *
     * @param string $slug Plugin slug.
     * @return array{code:string,label:string,plugin_file:string|null}
     */
    public function pluginStatus(string $slug): array
    {
        $this->loadPluginFunctions();

        $pluginFile = $this->installedPluginFile($slug);
        if (! $pluginFile) {
            return [
                'code' => 'not_installed',
                'label' => __('Not installed', 'a-ripple-song'),
                'plugin_file' => null,
            ];
        }

        if (is_plugin_active($pluginFile)) {
            return [
                'code' => 'active',
                'label' => __('Active', 'a-ripple-song'),
                'plugin_file' => $pluginFile,
            ];
        }

        return [
            'code' => 'inactive',
            'label' => __('Installed, inactive', 'a-ripple-song'),
            'plugin_file' => $pluginFile,
        ];
    }

    /**
     * Return an admin-post action URL.
     *
     * @param string $action Recommended plugin action.
     * @param string $slug Plugin slug.
     * @return string
     */
    public function actionUrl(string $action, string $slug): string
    {
        $url = add_query_arg(
            [
                'action' => 'aripplesong_recommended_plugin_' . $action,
                'plugin_slug' => $slug,
            ],
            admin_url('admin-post.php')
        );

        return wp_nonce_url($url, $this->nonceAction($action, $slug));
    }

    /**
     * Handle recommended plugin installation requests.
     *
     * @return void
     */
    public static function handleInstallAction(): void
    {
        (new self())->handleAction('install');
    }

    /**
     * Handle recommended plugin activation requests.
     *
     * @return void
     */
    public static function handleActivateAction(): void
    {
        (new self())->handleAction('activate');
    }

    /**
     * Render the recommended plugins admin page.
     *
     * @return void
     */
    public function renderPage(): void
    {
        if (! current_user_can('install_plugins') && ! current_user_can('activate_plugins')) {
            wp_die(esc_html__('You do not have permission to manage plugins.', 'a-ripple-song'));
        }

        $notice = isset($_GET['aripplesong_recommended_plugin_notice'])
            ? sanitize_key((string) wp_unslash($_GET['aripplesong_recommended_plugin_notice']))
            : '';

        echo '<div class="wrap">';
        echo '<h1>' . esc_html($this->pageTitle()) . '</h1>';
        $this->renderNotice($notice);
        echo '<table class="widefat striped">';
        echo '<thead><tr>';
        echo '<th>' . esc_html__('Plugin Name', 'a-ripple-song') . '</th>';
        echo '<th>' . esc_html__('Plugin Slug', 'a-ripple-song') . '</th>';
        echo '<th>' . esc_html__('Main Feature', 'a-ripple-song') . '</th>';
        echo '<th>' . esc_html__('Installation Status', 'a-ripple-song') . '</th>';
        echo '<th>' . esc_html__('Action', 'a-ripple-song') . '</th>';
        echo '</tr></thead>';
        echo '<tbody>';

        foreach ($this->plugins() as $plugin) {
            // Render one recommendation row with status-aware action controls.
            $this->renderPluginRow($plugin);
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    }

    /**
     * Handle a plugin management action.
     *
     * @param string $action Recommended plugin action.
     * @return void
     */
    private function handleAction(string $action): void
    {
        $capability = $action === 'install' ? 'install_plugins' : 'activate_plugins';
        if (! current_user_can($capability)) {
            wp_die(esc_html__('You do not have permission to manage plugins.', 'a-ripple-song'));
        }

        $slug = isset($_GET['plugin_slug']) ? sanitize_key((string) wp_unslash($_GET['plugin_slug'])) : '';
        $plugin = $this->pluginBySlug($slug);
        if (! $plugin) {
            $this->redirectWithNotice('invalid_plugin');
        }

        check_admin_referer($this->nonceAction($action, $slug));

        if ($action === 'install') {
            $notice = $this->installPlugin($slug);
            $this->redirectWithNotice($notice);
        }

        if ($action === 'activate') {
            $notice = $this->activatePlugin($slug);
            $this->redirectWithNotice($notice);
        }

        $this->redirectWithNotice('invalid_action');
    }

    /**
     * Install a recommended plugin from WordPress.org.
     *
     * @param string $slug Plugin slug.
     * @return string Notice code.
     */
    private function installPlugin(string $slug): string
    {
        if ($this->installedPluginFile($slug)) {
            return 'already_installed';
        }

        $this->loadPluginInstallFunctions();

        $api = plugins_api(
            'plugin_information',
            [
                'slug' => $slug,
                'fields' => [
                    'sections' => false,
                ],
            ]
        );

        if ($api instanceof WP_Error || empty($api->download_link)) {
            return 'install_failed';
        }

        $upgrader = new Plugin_Upgrader(new Automatic_Upgrader_Skin());
        $result = $upgrader->install($api->download_link);

        if ($result instanceof WP_Error || ! $result) {
            return 'install_failed';
        }

        return 'installed';
    }

    /**
     * Activate an installed recommended plugin.
     *
     * @param string $slug Plugin slug.
     * @return string Notice code.
     */
    private function activatePlugin(string $slug): string
    {
        $pluginFile = $this->installedPluginFile($slug);
        if (! $pluginFile) {
            return 'not_installed';
        }

        if (is_plugin_active($pluginFile)) {
            return 'already_active';
        }

        $result = activate_plugin($pluginFile);
        if ($result instanceof WP_Error) {
            return 'activation_failed';
        }

        return 'activated';
    }

    /**
     * Render a single plugin recommendation row.
     *
     * @param array{name:string,slug:string,description:string} $plugin Plugin definition.
     * @return void
     */
    private function renderPluginRow(array $plugin): void
    {
        $status = $this->pluginStatus($plugin['slug']);

        echo '<tr>';
        echo '<td><strong>' . esc_html($plugin['name']) . '</strong></td>';
        echo '<td><code>' . esc_html($plugin['slug']) . '</code></td>';
        echo '<td>' . esc_html($plugin['description']) . '</td>';
        echo '<td>' . esc_html($status['label']) . '</td>';
        echo '<td>' . $this->actionHtml($plugin['slug'], $status['code']) . '</td>';
        echo '</tr>';
    }

    /**
     * Return action button HTML for a plugin status.
     *
     * @param string $slug Plugin slug.
     * @param string $statusCode Plugin status code.
     * @return string
     */
    private function actionHtml(string $slug, string $statusCode): string
    {
        if ($statusCode === 'not_installed') {
            return sprintf(
                '<a class="button button-primary" href="%1$s">%2$s</a>',
                esc_url($this->actionUrl('install', $slug)),
                esc_html__('Install', 'a-ripple-song')
            );
        }

        if ($statusCode === 'inactive') {
            return sprintf(
                '<a class="button button-primary" href="%1$s">%2$s</a>',
                esc_url($this->actionUrl('activate', $slug)),
                esc_html__('Activate', 'a-ripple-song')
            );
        }

        return '<span class="button disabled" aria-disabled="true">' . esc_html__('Active', 'a-ripple-song') . '</span>';
    }

    /**
     * Render an admin notice from a notice code.
     *
     * @param string $notice Notice code.
     * @return void
     */
    private function renderNotice(string $notice): void
    {
        if ($notice === '') {
            return;
        }

        $messages = [
            'installed' => __('Plugin installed successfully.', 'a-ripple-song'),
            'activated' => __('Plugin activated successfully.', 'a-ripple-song'),
            'already_installed' => __('Plugin is already installed.', 'a-ripple-song'),
            'already_active' => __('Plugin is already active.', 'a-ripple-song'),
            'not_installed' => __('Plugin is not installed.', 'a-ripple-song'),
            'invalid_plugin' => __('Invalid recommended plugin.', 'a-ripple-song'),
            'invalid_action' => __('Invalid plugin action.', 'a-ripple-song'),
            'install_failed' => __('Plugin installation failed.', 'a-ripple-song'),
            'activation_failed' => __('Plugin activation failed.', 'a-ripple-song'),
        ];

        if (! isset($messages[$notice])) {
            return;
        }

        $noticeClass = str_contains($notice, 'failed') || str_contains($notice, 'invalid') ? 'notice-error' : 'notice-success';
        echo '<div class="notice ' . esc_attr($noticeClass) . ' is-dismissible"><p>' . esc_html($messages[$notice]) . '</p></div>';
    }

    /**
     * Redirect back to this page with a notice code.
     *
     * @param string $notice Notice code.
     * @return never
     */
    private function redirectWithNotice(string $notice): never
    {
        wp_safe_redirect(
            add_query_arg(
                [
                    'page' => $this->pageSlug(),
                    'aripplesong_recommended_plugin_notice' => $notice,
                ],
                admin_url('admin.php')
            )
        );
        exit;
    }

    /**
     * Return nonce action for a plugin action.
     *
     * @param string $action Recommended plugin action.
     * @param string $slug Plugin slug.
     * @return string
     */
    private function nonceAction(string $action, string $slug): string
    {
        return 'aripplesong_recommended_plugin_' . $action . '_' . $slug;
    }

    /**
     * Load WordPress plugin functions when needed.
     *
     * @return void
     */
    private function loadPluginFunctions(): void
    {
        if (! function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
    }

    /**
     * Load WordPress plugin installation functions when needed.
     *
     * @return void
     */
    private function loadPluginInstallFunctions(): void
    {
        $this->loadPluginFunctions();

        if (! function_exists('plugins_api')) {
            require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        }

        if (! class_exists('Plugin_Upgrader')) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        }
    }
}
