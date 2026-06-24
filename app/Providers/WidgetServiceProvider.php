<?php

namespace App\Providers;

use App\Abstracts\WidgetAbstract;
use App\Constants\ThemeConstant;
use App\Customizers\ThemeColor;
use App\Theme;
use App\Widgets\AuthorsWidget;
use App\Widgets\BannerCarouselWidget;
use App\Widgets\BlogListWidget;
use App\Widgets\FooterLinksWidget;
use App\Widgets\PodcastListWidget;
use App\Widgets\SubscribeLinksWidget;
use App\Widgets\TagsCloudWidget;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

/**
 * Registers custom widgets and their admin assets.
 */
class WidgetServiceProvider extends ServiceProvider
{
    /**
     * Widget classes registered by this provider.
     *
     * @var array<int,class-string>
     */
    private array $widgets = [
        BannerCarouselWidget::class,
        PodcastListWidget::class,
        BlogListWidget::class,
        SubscribeLinksWidget::class,
        TagsCloudWidget::class,
        AuthorsWidget::class,
        FooterLinksWidget::class,
    ];

    /**
     * Style handle used for widget editor and preview screens.
     */
    private const WIDGET_STYLE_HANDLE = 'aripplesong-widget-editor';

    /**
     * Style handle used for scoped widget admin screen styles.
     */
    private const WIDGET_ADMIN_STYLE_HANDLE = 'aripplesong-widget-admin';

    /**
     * Script handle used for widget preview iframes.
     */
    private const WIDGET_PREVIEW_HANDLE = 'aripplesong-widget-preview';

    /**
     * DaisyUI palette keys mapped to CSS custom properties.
     *
     * @var array<string,string>
     */
    private array $paletteColorVariables = [
        'base100' => '--color-base-100',
        'base200' => '--color-base-200',
        'base300' => '--color-base-300',
        'baseContent' => '--color-base-content',
        'primary' => '--color-primary',
        'primaryContent' => '--color-primary-content',
        'secondary' => '--color-secondary',
        'secondaryContent' => '--color-secondary-content',
        'accent' => '--color-accent',
        'accentContent' => '--color-accent-content',
        'neutral' => '--color-neutral',
        'neutralContent' => '--color-neutral-content',
    ];

    /**
     * Track whether widget editor assets were already enqueued.
     */
    private bool $widgetEditorAssetsLoaded = false;

    /**
     * Register widget hooks.
     */
    public function register(): void
    {
        add_action('widgets_init', [$this, 'registerWidgets']);
        add_action('after_switch_theme', [$this, 'setDefaultHomeWidgets']);
        add_action('widgets_init', [$this, 'maybeSetDefaultHomeWidgets'], 100);
        add_filter('widget_form_callback', [$this, 'normalizeWidgetInstance'], 10, 3);
        add_filter('widget_update_callback', [$this, 'normalizeWidgetInstanceOnUpdate'], 10, 4);
        add_action('wp_enqueue_scripts', [$this, 'enqueueLegacyWidgetPreviewAssets'], 5);
        add_action('admin_enqueue_scripts', [$this, 'enqueueWidgetAdminAssets']);
        add_action('admin_print_styles-widgets.php', [$this, 'enqueueWidgetMediaModalStyles']);
        add_action('enqueue_block_assets', [$this, 'enqueueWidgetAdminAssets']);
        add_action('wp_head', [$this, 'printCustomizerPreviewThemeScript'], 999);
        add_filter('script_loader_tag', [$this, 'filterWidgetPreviewScriptTag'], 10, 3);
    }

    /**
     * Bootstrap widget services.
     */
    public function boot(): void {}

    /**
     * Register all custom widgets with WordPress.
     */
    public function registerWidgets(): void
    {
        foreach ($this->widgets as $widgetClass) {
            register_widget($widgetClass);
            add_filter('widget_'.$widgetClass::idBase(), [$this, 'normalizeWidgetInstanceForDisplay'], 10, 3);
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

        if (! class_exists(BannerCarouselWidget::class) || ! class_exists(PodcastListWidget::class) || ! class_exists(BlogListWidget::class)) {
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
     * Enqueue widget admin styles, scripts, and preview bridge.
     *
     * @param  string  $hook  Current admin page hook.
     */
    public function enqueueWidgetAdminAssets(string $hook = ''): void
    {
        if (! $this->shouldLoadWidgetEditorAssets($hook)) {
            return;
        }

        if ($this->widgetEditorAssetsLoaded) {
            return;
        }

        $this->widgetEditorAssetsLoaded = true;

        $this->enqueueWidgetAdminStyles();
        wp_enqueue_media();
        wp_enqueue_style('media-views');
        wp_enqueue_script('jquery');
        wp_add_inline_script('jquery', $this->widgetAdminScript(), 'after');
        wp_add_inline_script('jquery', $this->widgetEditorThemeBridgeScript(), 'after');
    }

    /**
     * Enqueue WordPress media modal styles on the parent widgets screen.
     */
    public function enqueueWidgetMediaModalStyles(): void
    {
        wp_enqueue_style('media-views');
    }

    /**
     * Enqueue frontend styles and preview runtime for legacy widget preview iframes.
     */
    public function enqueueLegacyWidgetPreviewAssets(): void
    {
        if (! $this->isLegacyWidgetPreviewRequest()) {
            return;
        }

        $this->enqueueWidgetEditorStyles();

        try {
            $previewScriptUrl = Vite::asset('resources/js/widget-preview.js');

            if ($previewScriptUrl) {
                wp_enqueue_script(self::WIDGET_PREVIEW_HANDLE, $previewScriptUrl, [], null, true);
                wp_add_inline_script(
                    self::WIDGET_PREVIEW_HANDLE,
                    'window.aripplesongData = Object.assign({}, window.aripplesongData || {}, '.$this->widgetPreviewScriptData().');',
                    'before'
                );
            }
        } catch (\Throwable $throwable) {
            error_log('Failed to enqueue widget preview assets: '.$throwable->getMessage());
        }
    }

    /**
     * Mark widget preview scripts as ES modules.
     *
     * @param  string  $tag  Script loader tag.
     * @param  string  $handle  Script handle.
     * @param  string  $src  Script source URL.
     */
    public function filterWidgetPreviewScriptTag(string $tag, string $handle, string $src): string
    {
        if ($handle !== self::WIDGET_PREVIEW_HANDLE) {
            return $tag;
        }

        return str_replace('<script ', '<script type="module" ', $tag);
    }

    /**
     * Enqueue compiled frontend styles used by widget previews.
     */
    private function enqueueWidgetEditorStyles(): void
    {
        if (wp_style_is(self::WIDGET_STYLE_HANDLE, 'enqueued')) {
            return;
        }

        try {
            $cssUrl = Vite::asset('resources/css/app.css');

            if ($cssUrl) {
                wp_enqueue_style(self::WIDGET_STYLE_HANDLE, $cssUrl, [], null);
                wp_add_inline_style(self::WIDGET_STYLE_HANDLE, $this->buildThemePaletteCss());
                wp_add_inline_style(self::WIDGET_STYLE_HANDLE, $this->widgetAdminCss());
            }
        } catch (\Throwable $throwable) {
            error_log('Failed to enqueue widget editor styles: '.$throwable->getMessage());
        }
    }

    /**
     * Enqueue scoped widget admin styles without loading the frontend stylesheet.
     */
    private function enqueueWidgetAdminStyles(): void
    {
        if (! wp_style_is(self::WIDGET_ADMIN_STYLE_HANDLE, 'registered')) {
            wp_register_style(self::WIDGET_ADMIN_STYLE_HANDLE, false, [], null);
        }

        wp_enqueue_style(self::WIDGET_ADMIN_STYLE_HANDLE);
        wp_add_inline_style(self::WIDGET_ADMIN_STYLE_HANDLE, $this->buildThemePaletteCss());
        wp_add_inline_style(self::WIDGET_ADMIN_STYLE_HANDLE, $this->widgetAdminCss());
    }

    /**
     * Return whether the current request should load widget editor assets.
     *
     * @param  string  $hookSuffix  Current admin page hook suffix.
     */
    private function shouldLoadWidgetEditorAssets(string $hookSuffix = ''): bool
    {
        if ($this->isLegacyWidgetPreviewRequest()) {
            return false;
        }

        if (! is_admin()) {
            return false;
        }

        if (in_array($hookSuffix, ['widgets.php', 'customize.php'], true)) {
            return true;
        }

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;

        if ($screen && (
            in_array($screen->id, ['widgets', 'customize'], true)
            || in_array($screen->base, ['widgets', 'customize'], true)
        )) {
            return true;
        }

        $requestUri = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '';

        return str_contains($requestUri, 'widgets.php')
            || str_contains($requestUri, 'customize.php');
    }

    /**
     * Return whether the current request renders a legacy widget preview iframe.
     */
    private function isLegacyWidgetPreviewRequest(): bool
    {
        $requestUri = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '';
        $restRoute = isset($_GET['rest_route']) ? (string) wp_unslash($_GET['rest_route']) : '';

        $isLegacyWidgetRenderRoute = (defined('REST_REQUEST') && REST_REQUEST)
            && (
                (str_contains($requestUri, '/wp/v2/widget-types/') && str_contains($requestUri, '/render'))
                || (str_contains($restRoute, '/wp/v2/widget-types/') && str_contains($restRoute, '/render'))
            );

        return $isLegacyWidgetRenderRoute || ! empty($_GET['legacy-widget-preview']);
    }

    /**
     * Return JSON encoded runtime data for widget preview scripts.
     */
    private function widgetPreviewScriptData(): string
    {
        $scriptData = wp_json_encode([
            'theme' => [
                'lightTheme' => ThemeColor::getLightTheme(),
                'darkTheme' => ThemeColor::getDarkTheme(),
            ],
        ]);

        return is_string($scriptData) ? $scriptData : '{}';
    }

    /**
     * Build CSS rules for custom DaisyUI theme slugs.
     */
    private function buildThemePaletteCss(): string
    {
        $rules = [];

        foreach (ThemeConstant::PALETTE as $themeSlug => $themePalette) {
            if (! is_string($themeSlug) || ! $this->isSafeThemeSlug($themeSlug) || ! is_array($themePalette)) {
                continue;
            }

            $declarations = $this->buildThemeColorSchemeDeclarations($themeSlug);

            foreach ($this->paletteColorVariables as $paletteKey => $cssVariable) {
                $colorValue = $themePalette[$paletteKey] ?? null;

                if (! is_string($colorValue) || ! $this->isSafeCssColorValue($colorValue)) {
                    continue;
                }

                $declarations[] = $cssVariable.':'.$colorValue.';';
            }

            if (count($declarations) <= 3) {
                continue;
            }

            $rules[] = '[data-theme='.$themeSlug.']{'.implode('', $declarations).'}';
        }

        return implode('', $rules);
    }

    /**
     * Build color-scheme declarations matching the theme group.
     *
     * @param  string  $themeSlug  Theme slug.
     * @return array<int,string>
     */
    private function buildThemeColorSchemeDeclarations(string $themeSlug): array
    {
        if (array_key_exists($themeSlug, ThemeConstant::DARK)) {
            return [
                '--lightningcss-light: ;',
                '--lightningcss-dark:initial;',
                'color-scheme:dark;',
            ];
        }

        return [
            '--lightningcss-light:initial;',
            '--lightningcss-dark: ;',
            'color-scheme:light;',
        ];
    }

    /**
     * Return whether a theme slug is safe for CSS attribute selectors.
     *
     * @param  string  $themeSlug  Theme slug.
     */
    private function isSafeThemeSlug(string $themeSlug): bool
    {
        return preg_match('/\A[a-z0-9-]+\z/', $themeSlug) === 1;
    }

    /**
     * Return whether a palette color value is safe to emit into inline CSS.
     *
     * @param  string  $colorValue  CSS color value.
     */
    private function isSafeCssColorValue(string $colorValue): bool
    {
        return preg_match('/\A(?:#[0-9a-fA-F]{3,8}|oklch\([0-9.\s%+-]+\))\z/', $colorValue) === 1;
    }

    /**
     * Normalize widget instance values before the admin form renders.
     *
     * @param  array<string,mixed>|false  $instance  Saved widget instance.
     * @param  \WP_Widget  $widget  Widget object.
     * @param  string|null  $optionName  Widget option name.
     * @return array<string,mixed>|false
     */
    public function normalizeWidgetInstance($instance, \WP_Widget $widget, $optionName = null)
    {
        if ($instance === false || ! is_array($instance) || ! $this->isThemeWidget($widget)) {
            return $instance;
        }

        return $this->applyWidgetInstanceAliases($instance, $widget);
    }

    /**
     * Normalize widget instance values after update.
     *
     * @param  array<string,mixed>|false  $instance  Saved widget instance.
     * @param  array<string,mixed>  $newInstance  Submitted widget values.
     * @param  array<string,mixed>  $oldInstance  Previous widget values.
     * @param  \WP_Widget  $widget  Widget object being saved.
     * @return array<string,mixed>|false
     */
    public function normalizeWidgetInstanceOnUpdate($instance, array $newInstance, array $oldInstance, \WP_Widget $widget)
    {
        if ($instance === false || ! is_array($instance) || ! $this->isThemeWidget($widget)) {
            return $instance;
        }

        return $this->applyWidgetInstanceAliases($instance, $widget);
    }

    /**
     * Normalize stored widget values before rendering on the front-end.
     *
     * @param  array<string,mixed>|false  $instance  Widget instance values.
     * @param  \WP_Widget  $widget  Widget object.
     * @param  array<string,mixed>  $args  Widget wrapper arguments.
     * @return array<string,mixed>|false
     */
    public function normalizeWidgetInstanceForDisplay($instance, \WP_Widget $widget, array $args)
    {
        if ($instance === false || ! is_array($instance) || ! $this->isThemeWidget($widget)) {
            return $instance;
        }

        return $this->applyWidgetInstanceAliases($instance, $widget);
    }

    /**
     * Return whether the widget belongs to this theme.
     *
     * @param  \WP_Widget  $widget  Widget object.
     */
    private function isThemeWidget(\WP_Widget $widget): bool
    {
        return $widget instanceof WidgetAbstract;
    }

    /**
     * Copy legacy and Carbon-prefixed keys to standard widget instance keys.
     *
     * @param  array<string,mixed>  $instance  Widget instance values.
     * @param  WidgetAbstract  $widget  Theme widget object.
     * @return array<string,mixed>
     */
    private function applyWidgetInstanceAliases(array $instance, WidgetAbstract $widget): array
    {
        foreach ($instance as $key => $value) {
            if (! is_string($key) || $key === '_multiwidget' || ! str_starts_with($key, '_')) {
                continue;
            }

            $plainKey = substr($key, 1);

            if ($plainKey !== '' && ! array_key_exists($plainKey, $instance)) {
                $instance[$plainKey] = $value;
            }
        }

        $aliases = $widget::instanceAliases();

        foreach ($aliases as $legacyKey => $standardKey) {
            if (! array_key_exists($standardKey, $instance) && array_key_exists($legacyKey, $instance)) {
                $instance[$standardKey] = $instance[$legacyKey];
            }
        }

        return $instance;
    }

    /**
     * Print theme setup script for the Customizer preview iframe.
     */
    public function printCustomizerPreviewThemeScript(): void
    {
        if (! is_customize_preview()) {
            return;
        }

        $lightTheme = esc_js(ThemeColor::getLightTheme());
        $darkTheme = esc_js(ThemeColor::getDarkTheme());
        ?>
        <script>
        (function() {
            const storageKey = 'theme-mode';
            const lightTheme = '<?php echo $lightTheme; ?>';
            const darkTheme = '<?php echo $darkTheme; ?>';
            const colorSchemeMedia = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;
            let resolvedTheme = lightTheme;

            try {
                const storedMode = window.localStorage.getItem(storageKey);

                if (storedMode === 'dark') {
                    resolvedTheme = darkTheme;
                } else if (storedMode === 'auto' && colorSchemeMedia && colorSchemeMedia.matches) {
                    resolvedTheme = darkTheme;
                }
            } catch (error) {
                resolvedTheme = lightTheme;
            }

            if (document.documentElement) {
                document.documentElement.setAttribute('data-theme', resolvedTheme);
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
            .blocks-widgets-container .edit-widgets-block-editor .editor-styles-wrapper,
            .blocks-widgets-container .wp-block-widget-area__inner-blocks.editor-styles-wrapper,
            .widgets-holder-wrap,
            .widget-inside {
                background-color: var(--color-base-200);
            }

            .blocks-widgets-container .wp-block-widget-area__inner-blocks.editor-styles-wrapper > .block-editor-block-list__layout,
            #widgets-editor .wp-block-widget-area__inner-blocks > .block-editor-block-list__layout,
            .edit-widgets-block-editor .wp-block-widget-area__inner-blocks > .block-editor-block-list__layout {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }

            .blocks-widgets-container .block-editor-block-list__layout > .wp-block[data-type="core/legacy-widget"],
            #widgets-editor .block-editor-block-list__layout > .wp-block[data-type="core/legacy-widget"],
            .edit-widgets-block-editor .block-editor-block-list__layout > .wp-block[data-type="core/legacy-widget"] {
                margin-bottom: 0 !important;
            }

            .wp-block-legacy-widget__edit-preview,
            .wp-block-legacy-widget__edit-preview-iframe {
                border-radius: 0.5rem;
                overflow: hidden;
            }

            .wp-block-legacy-widget__edit-preview {
                background-color: var(--color-base-200);
            }

            .widget .widget-inside .widget-content {
                max-width: 100%;
                overflow-x: auto;
            }

            .widget-content {
                background: var(--color-base-100);
            }
        ';
    }

    /**
     * Return the widget editor theme bridge script.
     */
    private function widgetEditorThemeBridgeScript(): string
    {
        $lightThemeJson = wp_json_encode(ThemeColor::getLightTheme());
        $darkThemeJson = wp_json_encode(ThemeColor::getDarkTheme());

        if (! is_string($lightThemeJson)) {
            $lightThemeJson = '"retro"';
        }

        if (! is_string($darkThemeJson)) {
            $darkThemeJson = '"dim"';
        }

        return <<<JS
(() => {
    'use strict';

    const storageKey = 'theme-mode';
    const lightTheme = {$lightThemeJson};
    const darkTheme = {$darkThemeJson};
    const supportedModes = ['light', 'dark', 'auto'];
    const colorSchemeMedia = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;

    function getStoredMode() {
        try {
            const storedMode = window.localStorage.getItem(storageKey);

            return supportedModes.includes(storedMode) ? storedMode : 'auto';
        } catch (error) {
            return 'auto';
        }
    }

    function getResolvedTheme() {
        const currentMode = getStoredMode();

        if (currentMode === 'light') {
            return lightTheme;
        }

        if (currentMode === 'dark') {
            return darkTheme;
        }

        return colorSchemeMedia && colorSchemeMedia.matches ? darkTheme : lightTheme;
    }

    function applyThemeToDocument(targetDocument) {
        if (!targetDocument || !targetDocument.documentElement) {
            return;
        }

        const resolvedTheme = getResolvedTheme();

        targetDocument.documentElement.setAttribute('data-theme', resolvedTheme);
        targetDocument.documentElement.classList.add('bg-base-200');

        if (targetDocument.body) {
            targetDocument.body.classList.add('bg-base-200');
        }
    }

    function applyThemeToEditorCanvas() {
        const resolvedTheme = getResolvedTheme();
        const canvasSelectors = [
            '#widgets-editor',
            '.edit-widgets-block-editor .editor-styles-wrapper',
            '.edit-widgets-block-editor .wp-block-widget-area__inner-blocks',
            '.wp-block-legacy-widget__edit-preview',
        ];

        document.querySelectorAll(canvasSelectors.join(',')).forEach((element) => {
            if (!(element instanceof HTMLElement)) {
                return;
            }

            element.setAttribute('data-theme', resolvedTheme);
            element.classList.add('bg-base-200');
        });

        applyWidgetBlockSpacing();
    }

    function applyWidgetBlockSpacing() {
        const layoutSelectors = [
            '.blocks-widgets-container .wp-block-widget-area__inner-blocks > .block-editor-block-list__layout',
            '#widgets-editor .wp-block-widget-area__inner-blocks > .block-editor-block-list__layout',
            '.edit-widgets-block-editor .wp-block-widget-area__inner-blocks > .block-editor-block-list__layout',
        ];

        document.querySelectorAll(layoutSelectors.join(',')).forEach((layoutElement) => {
            if (!(layoutElement instanceof HTMLElement)) {
                return;
            }

            layoutElement.style.setProperty('display', 'flex', 'important');
            layoutElement.style.setProperty('flex-direction', 'column', 'important');
            layoutElement.style.setProperty('gap', '1rem', 'important');
        });
    }

    function isLegacyWidgetPreviewFrame(frameElement) {
        if (!(frameElement instanceof HTMLIFrameElement)) {
            return false;
        }

        const frameClassName = String(frameElement.className || '');
        const frameSource = String(frameElement.getAttribute('src') || '');

        return frameClassName.includes('wp-block-legacy-widget__edit-preview-iframe')
            || frameSource.includes('legacy-widget-preview')
            || (frameSource.includes('/wp/v2/widget-types/') && frameSource.includes('/render'));
    }

    function getLegacyWidgetPreviewFrames() {
        return Array.from(document.querySelectorAll('iframe')).filter((frameElement) => isLegacyWidgetPreviewFrame(frameElement));
    }

    function normalizePreviewDocumentLayout(targetDocument) {
        if (!targetDocument || !targetDocument.head) {
            return;
        }

        if (!targetDocument.getElementById('aripplesong-widget-preview-style')) {
            const styleElement = targetDocument.createElement('style');

            styleElement.id = 'aripplesong-widget-preview-style';
            styleElement.textContent = `
                html,
                body,
                #page,
                #content,
                .widget,
                .widget > * {
                    min-height: 0 !important;
                    max-height: none !important;
                    height: auto !important;
                }

                html,
                body {
                    margin: 0 !important;
                    padding: 0 !important;
                    overflow-x: hidden !important;
                    overflow-y: hidden !important;
                    scrollbar-width: none !important;
                }

                html::-webkit-scrollbar,
                body::-webkit-scrollbar {
                    display: none !important;
                    width: 0 !important;
                    height: 0 !important;
                }

                .widget {
                    overflow: hidden !important;
                    border-radius: 0.5rem;
                    margin-bottom: 1rem !important;
                }

                .widget:last-child {
                    margin-bottom: 0 !important;
                }
            `;

            targetDocument.head.appendChild(styleElement);
        }

        [targetDocument.documentElement, targetDocument.body, targetDocument.querySelector('.widget')].filter(Boolean).forEach((node) => {
            node.style.setProperty('height', 'auto', 'important');
            node.style.setProperty('min-height', '0', 'important');
            node.style.setProperty('max-height', 'none', 'important');
            node.style.setProperty('overflow', 'hidden', 'important');
        });
    }

    function getPreviewContentElement(targetDocument) {
        if (!targetDocument) {
            return null;
        }

        const widgetElement = targetDocument.querySelector('.widget');

        if (!widgetElement) {
            return null;
        }

        return widgetElement.firstElementChild || widgetElement;
    }

    function getElementRenderedHeight(targetElement) {
        if (!targetElement) {
            return 0;
        }

        return Math.max(
            Math.ceil(targetElement.getBoundingClientRect().height),
            targetElement.scrollHeight || 0,
            targetElement.offsetHeight || 0
        );
    }

    function getFrameContentHeight(frameElement) {
        try {
            const targetDocument = frameElement.contentDocument;

            if (!targetDocument) {
                return null;
            }

            const contentElement = getPreviewContentElement(targetDocument);
            const widgetElement = targetDocument.querySelector('.widget');
            const height = Math.max(
                getElementRenderedHeight(contentElement),
                getElementRenderedHeight(widgetElement),
                1
            );

            return Number.isFinite(height) ? Math.ceil(height) : null;
        } catch (error) {
            return null;
        }
    }

    function applyFrameHeight(frameElement, contentHeight) {
        if (!Number.isFinite(contentHeight) || contentHeight <= 0) {
            return;
        }

        frameElement.style.height = contentHeight + 'px';
        frameElement.height = String(contentHeight);
        frameElement.setAttribute('scrolling', 'no');
        frameElement.style.display = 'block';
        frameElement.style.width = '100%';
        frameElement.style.maxHeight = 'none';
        frameElement.style.overflow = 'hidden';
        frameElement.style.borderRadius = '0.5rem';
    }

    function syncFrameHeight(frameElement) {
        const contentHeight = getFrameContentHeight(frameElement);

        if (!contentHeight) {
            return;
        }

        applyFrameHeight(frameElement, contentHeight);
    }

    function syncFrameHeightWithDelay(frameElement) {
        syncFrameHeight(frameElement);
        window.requestAnimationFrame(() => {
            syncFrameHeight(frameElement);
        });
        window.setTimeout(() => {
            syncFrameHeight(frameElement);
        }, 150);
        window.setTimeout(() => {
            syncFrameHeight(frameElement);
        }, 500);
    }

    function bindFrameHeightObserver(frameElement) {
        if (!frameElement || frameElement.dataset.aripplesongHeightObserverBound === '1') {
            return;
        }

        try {
            const targetDocument = frameElement.contentDocument;
            const targetBody = targetDocument && targetDocument.body;

            if (!targetDocument || !targetBody || typeof MutationObserver === 'undefined') {
                return;
            }

            const resizeFrame = () => {
                syncFrameHeightWithDelay(frameElement);
            };

            const mutationObserver = new MutationObserver(resizeFrame);
            mutationObserver.observe(targetBody, {
                childList: true,
                subtree: true,
                attributes: true,
                characterData: true,
            });

            if (typeof ResizeObserver !== 'undefined') {
                const resizeObserver = new ResizeObserver(resizeFrame);
                const widgetElement = targetDocument.querySelector('.widget');

                resizeObserver.observe(targetBody);
                resizeObserver.observe(targetDocument.documentElement);

                if (widgetElement) {
                    resizeObserver.observe(widgetElement);
                }
            }

            frameElement.dataset.aripplesongHeightObserverBound = '1';
            resizeFrame();
        } catch (error) {
            // Ignore inaccessible preview frames and continue processing others.
        }
    }

    function bindPreviewMessageListener() {
        if (window.__aripplesongWidgetPreviewMessageBound) {
            return;
        }

        window.__aripplesongWidgetPreviewMessageBound = true;
        window.addEventListener('message', (event) => {
            const message = event.data;

            if (!message || message.type !== 'ars-widget-preview:height') {
                return;
            }

            const matchingFrame = getLegacyWidgetPreviewFrames().find((frameElement) => {
                try {
                    return frameElement.contentWindow === event.source;
                } catch (error) {
                    return false;
                }
            });

            if (!matchingFrame) {
                return;
            }

            applyFrameHeight(matchingFrame, Number(message.height || 0));
        });
    }

    function applyThemeToFrame(frameElement) {
        if (!frameElement) {
            return;
        }

        try {
            if (frameElement.contentDocument) {
                applyThemeToDocument(frameElement.contentDocument);
                normalizePreviewDocumentLayout(frameElement.contentDocument);
                syncFrameHeightWithDelay(frameElement);
                bindFrameHeightObserver(frameElement);
            }
        } catch (error) {
            // Ignore inaccessible preview frames and continue processing others.
        }

        if (!frameElement.dataset.aripplesongThemeBound) {
            frameElement.dataset.aripplesongThemeBound = '1';
            frameElement.addEventListener('load', () => {
                applyThemeToFrame(frameElement);
                syncFrameHeightWithDelay(frameElement);
            });
        }
    }

    function applyThemeEverywhere() {
        applyThemeToDocument(document);
        applyThemeToEditorCanvas();
        getLegacyWidgetPreviewFrames().forEach((frameElement) => {
            applyThemeToFrame(frameElement);
        });

        if (typeof lucide !== 'undefined' && lucide.createIcons) {
            lucide.createIcons();
        }
    }

    function observePreviewFrames() {
        if (!document.body || typeof MutationObserver === 'undefined') {
            return;
        }

        const observer = new MutationObserver(() => {
            applyThemeEverywhere();
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
        });
    }

    function bootstrapPreviewSync() {
        let runCount = 0;
        const intervalId = window.setInterval(() => {
            applyThemeEverywhere();
            runCount += 1;

            if (runCount >= 24) {
                window.clearInterval(intervalId);
            }
        }, 250);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            applyThemeEverywhere();
            bindPreviewMessageListener();
            observePreviewFrames();
            bootstrapPreviewSync();
        }, { once: true });
    } else {
        applyThemeEverywhere();
        bindPreviewMessageListener();
        observePreviewFrames();
        bootstrapPreviewSync();
    }

    if (colorSchemeMedia && typeof colorSchemeMedia.addEventListener === 'function') {
        colorSchemeMedia.addEventListener('change', applyThemeEverywhere);
    }

    window.addEventListener('storage', (event) => {
        if (event.key === storageKey) {
            applyThemeEverywhere();
        }
    });
})();
JS;
    }

    /**
     * Return inline JavaScript for repeatable widget admin fields.
     */
    private function widgetAdminScript(): string
    {
        $labelsJson = wp_json_encode([
            'imageUrl' => __('Image URL:', 'sage'),
            'imageUrlPlaceholder' => __('Image URL', 'sage'),
            'selectImage' => __('Select Image', 'sage'),
            'selectBannerImage' => __('Select Banner Image', 'sage'),
            'useThisImage' => __('Use This Image', 'sage'),
            'linkUrlOptional' => __('Link URL (optional):', 'sage'),
            'linkTarget' => __('Link Target:', 'sage'),
            'currentPage' => __('Current Page', 'sage'),
            'newTab' => __('New Tab', 'sage'),
            'description' => __('Description:', 'sage'),
            'imageDescription' => __('Image description', 'sage'),
            'delete' => __('Delete', 'sage'),
            'text' => __('Text:', 'sage'),
            'displayText' => __('Display text', 'sage'),
            'urlOptionalPlainText' => __('URL (optional - leave empty for plain text):', 'sage'),
            'openInNewTab' => __('Open in new tab', 'sage'),
        ]);

        if (! is_string($labelsJson)) {
            $labelsJson = '{}';
        }

        return <<<JS
(function($) {
    'use strict';

    const labels = {$labelsJson};

    function triggerChangeFlag(widgetForm, selector) {
        const flagInput = widgetForm.find(selector);
        if (flagInput.length) {
            flagInput.trigger('change');
        }
    }

    function updateBannerPreview(slideItem, url) {
        const preview = slideItem.find('.banner-image-preview');
        const safeUrl = (url || '').trim();

        if (!safeUrl) {
            preview.remove();
            return;
        }

        if (preview.length) {
            preview.find('img').attr('src', safeUrl);
            return;
        }

        slideItem.find('.banner-image-url-row').after(
            '<div class="banner-image-preview" style="margin-top: 8px;">' +
                '<img src="' + safeUrl + '" style="max-width: 100%; height: auto; max-height: 150px; border-radius: 4px;">' +
            '</div>'
        );
    }

    $(document).on('click', '.banner-add-slide', function(event) {
        event.preventDefault();

        const button = $(this);
        const widgetForm = button.closest('.banner-carousel-widget-form');
        const fieldNamePrefix = widgetForm.data('field-prefix');
        const container = $('#' + button.data('widget-id') + '_container');
        const slideCount = container.find('.banner-slide-item').length;

        if (!fieldNamePrefix) {
            return;
        }

        const slideHtml = '' +
            '<div class="banner-slide-item" style="margin-bottom: 15px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9;">' +
                '<div class="banner-image-url-row" style="margin-bottom: 8px;">' +
                    '<label style="display: block; margin-bottom: 4px; font-weight: 600;">' + labels.imageUrl + '</label>' +
                    '<div style="display: flex; gap: 5px;">' +
                        '<input type="text" class="widefat banner-image-url" name="' + fieldNamePrefix + '[' + slideCount + '][image]" placeholder="' + labels.imageUrlPlaceholder + '" style="flex: 1;">' +
                        '<button type="button" class="button banner-select-image" style="flex-shrink: 0;">' + labels.selectImage + '</button>' +
                    '</div>' +
                '</div>' +
                '<div style="margin-bottom: 8px;">' +
                    '<label style="display: block; margin-bottom: 4px; font-weight: 600;">' + labels.linkUrlOptional + '</label>' +
                    '<input type="url" class="widefat banner-link-url" name="' + fieldNamePrefix + '[' + slideCount + '][link]" placeholder="https://example.com">' +
                '</div>' +
                '<div style="margin-bottom: 8px;">' +
                    '<label style="display: block; margin-bottom: 4px; font-weight: 600;">' + labels.linkTarget + '</label>' +
                    '<select class="widefat banner-link-target" name="' + fieldNamePrefix + '[' + slideCount + '][link_target]">' +
                        '<option value="_self">' + labels.currentPage + '</option>' +
                        '<option value="_blank">' + labels.newTab + '</option>' +
                    '</select>' +
                '</div>' +
                '<div style="margin-bottom: 8px;">' +
                    '<label style="display: block; margin-bottom: 4px; font-weight: 600;">' + labels.description + '</label>' +
                    '<input type="text" class="widefat banner-description" name="' + fieldNamePrefix + '[' + slideCount + '][description]" placeholder="' + labels.imageDescription + '">' +
                '</div>' +
                '<div style="text-align: right;">' +
                    '<button type="button" class="button button-link button-link-delete banner-remove-slide" style="color: #b32d2e;">' + labels.delete + '</button>' +
                '</div>' +
            '</div>';

        container.append(slideHtml);
        triggerChangeFlag(widgetForm, '.banner-slides-flag');
    });

    $(document).on('click', '.banner-remove-slide', function(event) {
        event.preventDefault();

        const slideItem = $(this).closest('.banner-slide-item');
        const container = slideItem.closest('.banner-slides-container');
        const widgetForm = slideItem.closest('.banner-carousel-widget-form');

        if (container.find('.banner-slide-item').length <= 1) {
            slideItem.find('input').val('');
            slideItem.find('select').val('_self');
            slideItem.find('.banner-image-preview').remove();
        } else {
            slideItem.remove();
        }

        triggerChangeFlag(widgetForm, '.banner-slides-flag');
    });

    $(document).on('click', '.banner-select-image', function(event) {
        event.preventDefault();

        const button = $(this);
        const slideItem = button.closest('.banner-slide-item');
        const imageInput = slideItem.find('.banner-image-url');
        const mediaFrame = wp.media({
            title: labels.selectBannerImage,
            button: {
                text: labels.useThisImage,
            },
            multiple: false,
        });

        mediaFrame.on('select', function() {
            const attachment = mediaFrame.state().get('selection').first().toJSON();
            imageInput.val(attachment.url).trigger('change');
            updateBannerPreview(slideItem, attachment.url);
        });

        mediaFrame.open();
    });

    $(document).on('input', '.banner-image-url', function() {
        const input = $(this);
        const slideItem = input.closest('.banner-slide-item');
        updateBannerPreview(slideItem, input.val());
    });

    $(document).on('click', '.footer-add-link', function(event) {
        event.preventDefault();

        const button = $(this);
        const widgetForm = button.closest('.footer-links-widget-form');
        const fieldNamePrefix = widgetForm.data('field-prefix');
        const container = $('#' + button.data('widget-id') + '_container');
        const itemCount = container.find('.footer-link-item').length;

        if (!fieldNamePrefix) {
            return;
        }

        const itemHtml = '' +
            '<div class="footer-link-item" style="margin-bottom: 10px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9;">' +
                '<div style="margin-bottom: 8px;">' +
                    '<label style="display: block; margin-bottom: 4px; font-weight: 600;">' + labels.text + '</label>' +
                    '<input type="text" class="widefat footer-link-text" name="' + fieldNamePrefix + '[' + itemCount + '][text]" placeholder="' + labels.displayText + '">' +
                '</div>' +
                '<div style="margin-bottom: 8px;">' +
                    '<label style="display: block; margin-bottom: 4px; font-weight: 600;">' + labels.urlOptionalPlainText + '</label>' +
                    '<input type="url" class="widefat footer-link-url" name="' + fieldNamePrefix + '[' + itemCount + '][url]" placeholder="https://example.com">' +
                '</div>' +
                '<div style="margin-bottom: 8px;">' +
                    '<label><input type="checkbox" class="footer-link-new-tab" name="' + fieldNamePrefix + '[' + itemCount + '][new_tab]" value="1"> ' + labels.openInNewTab + '</label>' +
                '</div>' +
                '<div style="text-align: right;">' +
                    '<button type="button" class="button button-link button-link-delete footer-remove-link" style="color: #b32d2e;">' + labels.delete + '</button>' +
                '</div>' +
            '</div>';

        container.append(itemHtml);
        triggerChangeFlag(widgetForm, '.footer-links-flag');
    });

    $(document).on('click', '.footer-remove-link', function(event) {
        event.preventDefault();

        const item = $(this).closest('.footer-link-item');
        const container = item.closest('.footer-links-container');
        const widgetForm = item.closest('.footer-links-widget-form');

        if (container.find('.footer-link-item').length <= 1) {
            item.find('input[type="text"], input[type="url"]').val('');
            item.find('input[type="checkbox"]').prop('checked', false);
        } else {
            item.remove();
        }

        triggerChangeFlag(widgetForm, '.footer-links-flag');
    });
})(jQuery);
JS;
    }
}
