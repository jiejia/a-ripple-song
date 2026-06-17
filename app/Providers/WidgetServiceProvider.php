<?php

namespace App\Providers;

use App\Theme;
use Carbon_Fields\Carbon_Fields;
use Illuminate\Support\ServiceProvider;

/**
 * Registers custom widgets and their admin assets.
 */
class WidgetServiceProvider extends ServiceProvider
{
    /**
     * Widget class files loaded by this provider.
     *
     * @var array<int,string>
     */
    private array $widgetFiles = [
        'BannerCarouselWidget.php',
        'PodcastListWidget.php',
        'BlogListWidget.php',
        'SubscribeLinksWidget.php',
        'TagsCloudWidget.php',
        'AuthorsWidget.php',
        'FooterLinksWidget.php',
    ];

    /**
     * Widget class names registered with WordPress.
     *
     * @var array<int,class-string>
     */
    private array $widgetClasses = [
        'BannerCarouselWidget',
        'PodcastListWidget',
        'BlogListWidget',
        'SubscribeLinksWidget',
        'TagsCloudWidget',
        'AuthorsWidget',
        'FooterLinksWidget',
    ];

    /**
     * Register widget hooks and load legacy global widget classes.
     */
    public function register(): void
    {
        $this->loadWidgetClasses();

        add_action('widgets_init', [$this, 'registerWidgets']);
        add_action('after_switch_theme', [$this, 'setDefaultHomeWidgets']);
        add_action('widgets_init', [$this, 'maybeSetDefaultHomeWidgets'], 100);
        add_action('admin_print_footer_scripts', [$this, 'disableCarbonFieldsWidgetAutoInitialize'], 1);
        add_action('rest_api_init', [$this, 'registerCarbonFieldsWidgetContainers'], 20);
        add_action('admin_enqueue_scripts', [$this, 'enqueueWidgetAdminAssets']);
        add_action('admin_footer', [$this, 'printWidgetAdminThemeScript']);
        add_action('admin_print_footer_scripts', [$this, 'printCarbonFieldsBlockWidgetBridge'], 10000);
        add_action('wp_head', [$this, 'printCustomizerPreviewThemeScript'], 999);
    }

    /**
     * Bootstrap widget services.
     */
    public function boot(): void {}

    /**
     * Load global widget classes from the Widgets directory.
     */
    private function loadWidgetClasses(): void
    {
        foreach ($this->widgetFiles as $widgetFile) {
            $path = Theme::DIR.'/app/Widgets/'.$widgetFile;

            if (file_exists($path)) {
                require_once $path;
            }
        }
    }

    /**
     * Register all custom widgets with WordPress.
     */
    public function registerWidgets(): void
    {
        foreach ($this->widgetClasses as $widgetClass) {
            if (class_exists($widgetClass)) {
                register_widget($widgetClass);
            }
        }
    }

    /**
     * Set default homepage widgets on theme activation.
     */
    public function setDefaultHomeWidgets(): void
    {
        $defaultWidgetsSet = get_option('aripplesong_home_widgets_set', false);

        if ($defaultWidgetsSet) {
            return;
        }

        $sidebarsWidgets = get_option('sidebars_widgets', []);

        if (! empty($sidebarsWidgets[Theme::SIDEBAR_HOME_MAIN])) {
            return;
        }

        if (! class_exists('BannerCarouselWidget') || ! class_exists('PodcastListWidget') || ! class_exists('BlogListWidget')) {
            return;
        }

        $bannerOptions = get_option('widget_banner_carousel_widget', []);
        $bannerInstanceId = count($bannerOptions) + 1;
        $bannerOptions[$bannerInstanceId] = [
            'slides' => [],
        ];
        update_option('widget_banner_carousel_widget', $bannerOptions);

        $podcastOptions = get_option('widget_podcast_list_widget', []);
        $podcastInstanceId = count($podcastOptions) + 1;
        $podcastOptions[$podcastInstanceId] = [
            'title' => 'PODCAST',
            'posts_per_page' => 3,
            'show_see_all' => 1,
        ];
        update_option('widget_podcast_list_widget', $podcastOptions);

        $blogOptions = get_option('widget_blog_list_widget', []);
        $blogInstanceId = count($blogOptions) + 1;
        $blogOptions[$blogInstanceId] = [
            'title' => 'BLOG',
            'posts_per_page' => 6,
            'show_see_all' => 1,
            'columns' => 3,
        ];
        update_option('widget_blog_list_widget', $blogOptions);

        $sidebarsWidgets[Theme::SIDEBAR_HOME_MAIN] = [
            'banner_carousel_widget-'.$bannerInstanceId,
            'podcast_list_widget-'.$podcastInstanceId,
            'blog_list_widget-'.$blogInstanceId,
        ];

        update_option('sidebars_widgets', $sidebarsWidgets);
        update_option('aripplesong_home_widgets_set', true);
    }

    /**
     * Set defaults if the home sidebar is still empty during first widget boot.
     */
    public function maybeSetDefaultHomeWidgets(): void
    {
        if (! did_action('after_switch_theme')) {
            $this->setDefaultHomeWidgets();
        }
    }

    /**
     * Enqueue widget admin styles.
     *
     * @param  string  $hook  Current admin page hook.
     */
    public function enqueueWidgetAdminAssets(string $hook): void
    {
        if ($hook !== 'widgets.php' && $hook !== 'customize.php') {
            return;
        }

        wp_add_inline_style('wp-admin', $this->widgetAdminCss());
    }

    /**
     * Register Carbon Fields widget containers for REST requests.
     *
     * Carbon Fields only auto-registers widgets whose ids use the carbon_fields_ prefix.
     * Our widgets keep legacy ids, so we mirror the library registration here.
     */
    public function registerCarbonFieldsWidgetContainers(): void
    {
        if (! Carbon_Fields::is_booted()) {
            return;
        }

        global $wp_registered_widgets;

        $widgetIdBases = [
            'banner_carousel_widget',
            'podcast_list_widget',
            'blog_list_widget',
            'subscribe_links_widget',
            'tags_cloud_widget',
            'authors_widget',
            'footer_links_widget',
        ];

        $sidebarWidgets = wp_get_sidebars_widgets();
        $usedWidgetIds = [];

        foreach ($sidebarWidgets as $sidebarWidgetIds) {
            if (! is_array($sidebarWidgetIds)) {
                continue;
            }

            $usedWidgetIds = array_merge($usedWidgetIds, $sidebarWidgetIds);
        }

        foreach (array_unique($usedWidgetIds) as $widgetId) {
            $widgetIdBase = preg_replace('/-\d+$/', '', (string) $widgetId);

            if (! in_array($widgetIdBase, $widgetIdBases, true)) {
                continue;
            }

            if (! isset($wp_registered_widgets[$widgetId])) {
                continue;
            }

            $widgetClass = $wp_registered_widgets[$widgetId]['callback'][0] ?? null;

            if (! is_object($widgetClass) || ! method_exists($widgetClass, 'register_container')) {
                continue;
            }

            $widgetNumber = $wp_registered_widgets[$widgetId]['params'][0]['number'] ?? null;

            if ($widgetNumber === null) {
                continue;
            }

            $widgetClass->_set($widgetNumber);
            $widgetClass->register_container();
        }
    }

    /**
     * Disable Carbon Fields' eager widget initialization in the block widgets editor.
     */
    public function disableCarbonFieldsWidgetAutoInitialize(): void
    {
        $screen = get_current_screen();

        if (! $screen || $screen->id !== 'widgets' || ! Carbon_Fields::is_booted()) {
            return;
        }

        $loader = Carbon_Fields::resolve('loader');

        remove_action('admin_print_footer_scripts', [$loader, 'initialize_ui'], 9999);
    }

    /**
     * Print theme setup script for the widgets admin screens.
     */
    public function printWidgetAdminThemeScript(): void
    {
        $screen = get_current_screen();

        if (! $screen || ($screen->id !== 'widgets' && $screen->id !== 'customize')) {
            return;
        }
        ?>
        <script>
        (function() {
            function setupTheme(doc) {
                if (doc && doc.documentElement) {
                    doc.documentElement.setAttribute('data-theme', 'retro');
                }
                if (doc && doc.body) {
                    doc.body.classList.add('bg-base-200');
                }
            }

            setupTheme(document);

            function setupWidgetContainers() {
                const widgetsEditor = document.getElementById('widgets-editor');
                if (widgetsEditor) {
                    widgetsEditor.setAttribute('data-theme', 'retro');
                }

                const widgetContainers = document.querySelectorAll('.widgets-holder-wrap, .widget-inside');
                widgetContainers.forEach(function(container) {
                    container.setAttribute('data-theme', 'retro');
                });
            }

            function setupIframeTheme() {
                const iframes = document.querySelectorAll('iframe');
                iframes.forEach(function(iframe) {
                    try {
                        const iframeDoc = iframe.contentDocument || iframe.contentWindow?.document;
                        if (iframeDoc) {
                            setupTheme(iframeDoc);
                        }
                    } catch (error) {
                        // Ignore cross-origin iframe access errors.
                    }
                });
            }

            setupWidgetContainers();
            setupIframeTheme();

            const observer = new MutationObserver(function() {
                setupWidgetContainers();
                setupIframeTheme();

                if (typeof lucide !== 'undefined' && lucide.createIcons) {
                    lucide.createIcons();
                }
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        })();
        </script>
        <?php
    }

    /**
     * Print a bridge that mounts Carbon Fields forms inside the block widgets editor.
     */
    public function printCarbonFieldsBlockWidgetBridge(): void
    {
        $screen = get_current_screen();

        if (! $screen || $screen->id !== 'widgets') {
            return;
        }
        ?>
        <script>
        (function() {
            var containerRoots = {};
            var mountTimer = null;

            /**
             * Return whether Carbon Fields assets are ready for widget mounting.
             */
            function canMountCarbonFields() {
                return window.cf &&
                    window.cf.metaboxes &&
                    typeof window.cf.metaboxes.getContainerType === 'function' &&
                    typeof window.cf.core.uniqueId === 'function' &&
                    window.wp &&
                    window.wp.data &&
                    window.wp.element &&
                    typeof window.wp.data.dispatch === 'function' &&
                    typeof window.wp.data.select === 'function';
            }

            /**
             * Decode a Carbon Fields container payload from a widget fieldset.
             */
            function decodeContainer(fieldset) {
                try {
                    return JSON.parse(decodeURIComponent(fieldset.getAttribute('data-json') || ''));
                } catch (error) {
                    console.error('[aripplesong] Failed to decode Carbon Fields widget container.', error);
                    return null;
                }
            }

            /**
             * Flatten a Carbon Fields field tree for the metabox store.
             */
            function flattenField(field, containerId, fields) {
                var clonedField = Object.assign({}, field);

                clonedField.id = window.cf.core.uniqueId();
                clonedField.container_id = containerId;

                if (clonedField.type === 'complex' && Array.isArray(clonedField.value)) {
                    clonedField.value = clonedField.value.map(function(group) {
                        var clonedGroup = Object.assign({}, group);

                        clonedGroup.id = window.cf.core.uniqueId();
                        clonedGroup.container_id = containerId;
                        clonedGroup.fields = Array.isArray(clonedGroup.fields)
                            ? clonedGroup.fields.map(function(groupField) {
                                return flattenField(groupField, containerId, fields);
                            })
                            : [];

                        return clonedGroup;
                    });
                }

                fields.push(clonedField);

                return {
                    id: clonedField.id,
                    type: clonedField.type,
                    name: clonedField.name,
                    base_name: clonedField.base_name
                };
            }

            /**
             * Unmount a rendered Carbon Fields widget container.
             */
            function unmountWidgetContainer(containerId) {
                if (containerRoots[containerId]) {
                    containerRoots[containerId].unmount();
                    delete containerRoots[containerId];
                }

                var node = document.querySelector('.container-' + containerId);

                if (node) {
                    delete node.dataset.cfMounted;
                }
            }

            /**
             * Remove a widget container and its fields from the Carbon Fields store.
             */
            function removeContainerFromStore(containerId) {
                if (! canMountCarbonFields()) {
                    return;
                }

                var select = window.wp.data.select('carbon-fields/metaboxes');
                var dispatch = window.wp.data.dispatch('carbon-fields/metaboxes');
                var container = select.getContainerById(containerId);

                if (! container) {
                    return;
                }

                var fieldIds = container.fields.map(function(field) {
                    return field.id;
                });

                dispatch.removeContainer(containerId);
                dispatch.removeFields(fieldIds);
            }

            /**
             * Render a single Carbon Fields widget container into its fieldset node.
             */
            function mountWidgetFieldset(fieldset) {
                if (! canMountCarbonFields() || ! fieldset) {
                    return false;
                }

                if (fieldset.dataset.aripplesongMounted === 'true') {
                    return true;
                }

                var container = decodeContainer(fieldset);

                if (! container || ! container.id) {
                    return false;
                }

                var node = fieldset.classList.contains('container-' + container.id)
                    ? fieldset
                    : document.querySelector('.container-' + container.id);

                if (! node) {
                    return false;
                }

                var select = window.wp.data.select('carbon-fields/metaboxes');
                var dispatch = window.wp.data.dispatch('carbon-fields/metaboxes');

                if (! select.getContainerById(container.id)) {
                    var fields = [];
                    var clonedContainer = Object.assign({}, container);

                    clonedContainer.fields = Array.isArray(clonedContainer.fields)
                        ? clonedContainer.fields.map(function(field) {
                            return flattenField(field, clonedContainer.id, fields);
                        })
                        : [];

                    dispatch.addFields(fields);
                    dispatch.addContainer(clonedContainer);
                }

                var Component = window.cf.metaboxes.getContainerType(container.type, 'classic');

                if (! Component) {
                    return false;
                }

                unmountWidgetContainer(container.id);

                var element = window.wp.element.createElement(Component, { id: container.id });

                if (window.wp.element.createRoot) {
                    var root = window.wp.element.createRoot(node);
                    root.render(element);
                    containerRoots[container.id] = root;
                } else {
                    window.wp.element.render(element, node);
                }

                node.dataset.cfMounted = 'true';
                fieldset.dataset.aripplesongMounted = 'true';

                return true;
            }

            /**
             * Mount Carbon Fields forms inside a legacy widget control element.
             */
            function mountWidgetControl(widgetElement) {
                if (! widgetElement) {
                    return;
                }

                widgetElement.querySelectorAll('fieldset[data-json]').forEach(function(fieldset) {
                    mountWidgetFieldset(fieldset);
                });
            }

            /**
             * Mount all visible Carbon Fields widget forms in the block editor.
             */
            function mountVisibleCarbonFieldsWidgets() {
                if (! canMountCarbonFields()) {
                    return;
                }

                document.querySelectorAll('.wp-block-legacy-widget__edit-form fieldset[data-json]').forEach(function(fieldset) {
                    mountWidgetFieldset(fieldset);
                });
            }

            /**
             * Debounce widget mounting to avoid racing the async block editor form render.
             */
            function scheduleMountVisibleCarbonFieldsWidgets() {
                window.clearTimeout(mountTimer);
                mountTimer = window.setTimeout(mountVisibleCarbonFieldsWidgets, 50);
            }

            if (window.jQuery) {
                window.jQuery(document).on('widget-added', function(event, $widget) {
                    mountWidgetControl($widget[0]);
                });

                window.jQuery(document).on('widget-updated', function(event, $widget) {
                    $widget.find('fieldset[data-json]').each(function() {
                        var container = decodeContainer(this);

                        if (container && container.id) {
                            removeContainerFromStore(container.id);
                            unmountWidgetContainer(container.id);
                            delete this.dataset.aripplesongMounted;
                        }
                    });

                    mountWidgetControl($widget[0]);
                });
            }

            var observer = new MutationObserver(scheduleMountVisibleCarbonFieldsWidgets);

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', scheduleMountVisibleCarbonFieldsWidgets);
            } else {
                scheduleMountVisibleCarbonFieldsWidgets();
            }
        })();
        </script>
        <?php
    }

    /**
     * Print theme setup script for the Customizer preview iframe.
     */
    public function printCustomizerPreviewThemeScript(): void
    {
        if (! is_customize_preview()) {
            return;
        }
        ?>
        <script>
        (function() {
            if (document.documentElement) {
                document.documentElement.setAttribute('data-theme', 'retro');
            }
            if (document.body) {
                document.body.classList.add('bg-base-200');
            }
        })();
        </script>
        <?php
    }

    /**
     * Return widget admin CSS for preview and edit screens.
     */
    private function widgetAdminCss(): string
    {
        return '
            #widgets-editor,
            .widgets-holder-wrap,
            .widget-inside {
                --fallback-b1: oklch(0.9686 0.0124 105.4518);
                --fallback-bc: oklch(0.278078 0.029596 256.848);
                --fallback-b2: oklch(0.9529 0.0116 104.8327);
                --fallback-b3: oklch(0.9333 0.0108 103.7534);
            }

            .widget .widget-inside .widget-content {
                max-width: 100%;
                overflow-x: auto;
            }

            .widget-content {
                background: oklch(0.9686 0.0124 105.4518);
            }
        ';
    }
}
