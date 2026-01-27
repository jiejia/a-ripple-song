<?php

/**
 * Theme Options using Carbon Fields.
 * 
 * This file registers all theme options using Carbon Fields library.
 * Creates a top-level menu "Theme Settings" with sub-pages for different options.
 */

namespace App\ThemeOptions;

use Carbon_Fields\Container;
use Carbon_Fields\Field;
use Carbon_Fields\Carbon_Fields;

class GeneralOptions
{
    protected const THEME_CONTAINER_ID = 'carbon_fields_container_theme_settings';

    /**
     * Hold the main General container instance for child pages.
     *
     * @var \Carbon_Fields\Container\Container|null
     */
    protected static $themeContainer = null;

    /**
     * Entry point to register hooks.
     */
    public static function boot(): void
    {
        add_action('after_setup_theme', [static::class, 'bootCarbon']);
        add_action('carbon_fields_register_fields', [static::class, 'registerFields']);
        add_action('admin_menu', [static::class, 'adjustAdminMenu'], 20);
        add_action('admin_head', [static::class, 'outputPickerAssets']);
        add_action('admin_head', [static::class, 'outputLogoAssets']);
        add_action('wp_ajax_crb_crop_logo', [static::class, 'handleCropLogoAjax']);
        add_action('carbon_fields_theme_options_container_saved', [static::class, 'syncLogoOption'], 10, 1);
    }

    /**
     * Boot Carbon Fields.
     */
    public static function bootCarbon(): void
    {
        Carbon_Fields::boot();
    }

    /**
     * Register General and Social Links pages.
     */
    public static function registerFields(): void
    {
        $theme_settings = Container::make('theme_options', static::THEME_CONTAINER_ID, __('General', 'a-ripple-song'))
            ->set_page_menu_title(__('Theme Settings', 'a-ripple-song'))
            ->set_icon('dashicons-admin-settings')
            ->set_page_menu_position(60)
            ->add_fields([
                Field::make('html', 'crb_site_logo_uploader', __('Site Logo', 'a-ripple-song'))
                    ->set_html(static::renderLogoUploader())
                    ->set_help_text(__('Upload a logo image (220px × 32px). You will be able to crop the image after upload.', 'a-ripple-song')),
                Field::make('text', 'crb_site_logo', '')
                    ->set_attribute('type', 'hidden')
                    ->set_attribute('data-logo-field', 'true')
                    ->set_classes('crb-logo-carbon-field'),
                Field::make('html', 'crb_light_theme_picker', __('Light Theme', 'a-ripple-song'))
                    ->set_html(
                        sprintf(
                            '<div class="crb-theme-heading">%s</div>%s',
                            esc_html__('Light Theme', 'a-ripple-song'),
                            static::renderDaisyUiThemePicker('light')
                        )
                    )
                    ->set_help_text(__('Click any card to choose the light theme.', 'a-ripple-song')),
                Field::make('select', 'crb_light_theme', __('Light Theme (fallback)', 'a-ripple-song'))
                    ->set_options(static::getDaisyUiLightThemes())
                    ->set_default_value('retro')
                    ->set_help_text(__('If the card picker is unavailable, use this dropdown (default: retro).', 'a-ripple-song'))
                    ->set_classes('crb-theme-select')
                    ->set_attribute('data-theme-target', 'light'),
                Field::make('html', 'crb_dark_theme_picker', __('Dark Theme', 'a-ripple-song'))
                    ->set_html(
                        sprintf(
                            '<div class="crb-theme-heading">%s</div>%s',
                            esc_html__('Dark Theme', 'a-ripple-song'),
                            static::renderDaisyUiThemePicker('dark')
                        )
                    )
                    ->set_help_text(__('Click any card to choose the dark theme.', 'a-ripple-song')),
                Field::make('select', 'crb_dark_theme', __('Dark Theme (fallback)', 'a-ripple-song'))
                    ->set_options(static::getDaisyUiDarkThemes())
                    ->set_default_value('dim')
                    ->set_help_text(__('If the card picker is unavailable, use this dropdown (default: dim).', 'a-ripple-song'))
                    ->set_classes('crb-theme-select')
                    ->set_attribute('data-theme-target', 'dark'),
                Field::make('textarea', 'crb_footer_copyright', __('Footer Copyright', 'a-ripple-song'))
                    ->set_rows(2)
                    ->set_attribute('placeholder', __('Powered by A Ripple Song Theme', 'a-ripple-song'))
                    ->set_help_text(__('Overrides the footer copyright line. Leave empty to use the default.', 'a-ripple-song')),
                Field::make('header_scripts', 'crb_header_scripts', __('Header Scripts', 'a-ripple-song'))
                    ->set_help_text(esc_html__('Scripts to be added in the <head> section. You can include complete <script> tags for services like Google Analytics.', 'a-ripple-song')),
                Field::make('footer_scripts', 'crb_footer_scripts', __('Footer Scripts', 'a-ripple-song'))
                    ->set_help_text(esc_html__('Scripts to be added before </body>. You can include complete <script> tags.', 'a-ripple-song')),
            ]);

        static::$themeContainer = $theme_settings;

        // Backward compatibility for other files.
        $GLOBALS['crb_theme_settings_container'] = $theme_settings;

        Container::make('theme_options', __('Social Links', 'a-ripple-song'))
            ->set_page_parent($theme_settings)
            ->add_fields(static::getSocialLinksFields());
    }

    public static function adjustAdminMenu(): void
    {
        $menuSlug = 'crb_carbon_fields_container_theme_settings.php';

        if (static::$themeContainer && method_exists(static::$themeContainer, 'get_page_file')) {
            $resolved = static::$themeContainer->get_page_file();
            if (is_string($resolved) && $resolved !== '') {
                $menuSlug = $resolved;
            }
        }

        global $submenu;

        if (!is_array($submenu) || !isset($submenu[$menuSlug]) || !is_array($submenu[$menuSlug])) {
            return;
        }

        foreach ($submenu[$menuSlug] as $index => $item) {
            if (!is_array($item) || !isset($item[2]) || $item[2] !== $menuSlug) {
                continue;
            }

            $submenu[$menuSlug][$index][0] = __('General', 'a-ripple-song');
            $submenu[$menuSlug][$index][3] = __('General', 'a-ripple-song');
            break;
        }
    }

    /**
     * Expose General container for child pages.
     */
    public static function getThemeContainer()
    {
        return static::$themeContainer;
    }

    /**
     * Proxy: render logo uploader HTML.
     */
    public static function renderLogoUploader(): string
    {
        return crb_render_logo_uploader();
    }

    /**
     * Proxy: DaisyUI theme picker render.
     */
    public static function renderDaisyUiThemePicker(string $mode): string
    {
        return crb_render_daisyui_theme_picker($mode);
    }

    /**
     * Proxy: DaisyUI light theme list.
     *
     * @return array<string, string>
     */
    public static function getDaisyUiLightThemes(): array
    {
        return crb_get_daisyui_light_themes();
    }

    /**
     * Proxy: DaisyUI dark theme list.
     *
     * @return array<string, string>
     */
    public static function getDaisyUiDarkThemes(): array
    {
        return crb_get_daisyui_dark_themes();
    }

    /**
     * Proxy: Social links fields.
     */
    public static function getSocialLinksFields(): array
    {
        return crb_get_social_links_fields();
    }

    /**
     * Output inline assets for the DaisyUI picker.
     */
    public static function outputPickerAssets(): void
    {
        crb_output_daisyui_theme_picker_assets();
    }

    /**
     * Output inline assets for the logo uploader/cropper.
     */
    public static function outputLogoAssets(): void
    {
        crb_output_logo_uploader_assets();
    }

    /**
     * AJAX handler for cropping logo.
     */
    public static function handleCropLogoAjax(): void
    {
        crb_handle_crop_logo_ajax();
    }

    /**
     * Sync custom logo value back into Carbon Fields when General page saves.
     */
    public static function syncLogoOption($container): void
    {
        if (!is_object($container) || !method_exists($container, 'get_id')) {
            return;
        }

        if ($container->get_id() !== static::THEME_CONTAINER_ID) {
            return;
        }

        if (isset($_POST['_crb_site_logo'])) {
            $logo_url = esc_url_raw(wp_unslash($_POST['_crb_site_logo']));
            carbon_set_theme_option('crb_site_logo', $logo_url);
        }
    }
}

// Bootstrap this options module.
GeneralOptions::boot();

/**
 * Render custom logo uploader with cropper.
 *
 * @return string
 */
function crb_render_logo_uploader(): string
{
    $current_logo = carbon_get_theme_option('crb_site_logo');
    $preview_html = '';

    if (!empty($current_logo)) {
        $preview_html = sprintf(
            '<div class="crb-logo-preview" style="margin-top: 12px;">
                <img src="%s" alt="%s" style="max-width: 220px; height: auto; border: 1px solid #ddd; padding: 8px; background: #f9f9f9;">
            </div>',
            esc_url($current_logo),
            esc_attr__('Site Logo', 'a-ripple-song')
        );
    }

    return sprintf(
        '<div class="crb-logo-uploader-wrapper">
            <button type="button" class="button button-primary crb-logo-upload-btn" data-logo-width="220" data-logo-height="32">
                %s
            </button>
            <button type="button" class="button crb-logo-remove-btn" style="margin-left: 8px; %s">
                %s
            </button>
            <input type="hidden" name="_crb_site_logo" class="crb-site-logo-input" id="crb_site_logo_field" value="%s" data-current-value="%s">
            %s
        </div>',
        esc_html__('Upload / Change Logo', 'a-ripple-song'),
        empty($current_logo) ? 'display: none;' : '',
        esc_html__('Remove Logo', 'a-ripple-song'),
        esc_attr($current_logo),
        esc_attr($current_logo),
        $preview_html
    );
}

/**
 * Get social links fields configuration.
 *
 * @return array
 */
function crb_get_social_links_fields()
{
    $platforms = [
        'facebook' => [
            'label' => __('Facebook', 'a-ripple-song'),
            'placeholder' => 'https://facebook.com/yourpage',
        ],
        'twitter' => [
            'label' => __('Twitter / X', 'a-ripple-song'),
            'placeholder' => 'https://twitter.com/yourhandle',
        ],
        'instagram' => [
            'label' => __('Instagram', 'a-ripple-song'),
            'placeholder' => 'https://instagram.com/yourhandle',
        ],
        'linkedin' => [
            'label' => __('LinkedIn', 'a-ripple-song'),
            'placeholder' => 'https://linkedin.com/in/yourprofile',
        ],
        'youtube' => [
            'label' => __('YouTube', 'a-ripple-song'),
            'placeholder' => 'https://youtube.com/@yourchannel',
        ],
        'tiktok' => [
            'label' => __('TikTok', 'a-ripple-song'),
            'placeholder' => 'https://tiktok.com/@yourhandle',
        ],
        'pinterest' => [
            'label' => __('Pinterest', 'a-ripple-song'),
            'placeholder' => 'https://pinterest.com/yourhandle',
        ],
        'threads' => [
            'label' => __('Threads', 'a-ripple-song'),
            'placeholder' => 'https://threads.net/@yourhandle',
        ],
        'weibo' => [
            'label' => __('Weibo', 'a-ripple-song'),
            'placeholder' => 'https://weibo.com/yourpage',
        ],
        'wechat' => [
            'label' => __('WeChat', 'a-ripple-song'),
            'placeholder' => 'WeChat ID or QR code link',
        ],
        'rss' => [
            'label' => __('RSS Feed', 'a-ripple-song'),
            'placeholder' => '/feed/',
        ],
    ];

    $fields = [
        Field::make('html', 'crb_social_links_info')
            ->set_html(sprintf(
                '<p>%s</p>',
                __('Configure your social media links. Leave empty to hide a platform.', 'a-ripple-song')
            )),
    ];

    foreach ($platforms as $key => $platform) {
        $fields[] = Field::make('text', 'crb_social_' . $key, $platform['label'])
            ->set_attribute('placeholder', $platform['placeholder'])
            ->set_attribute('type', 'url');
    }

    return $fields;
}

/**
 * DaisyUI light themes list keyed by slug.
 *
 * @return array<string, string>
 */
function crb_get_daisyui_light_themes(): array
{
    return [
        'retro' => 'retro',
        'pastel-breeze' => 'Pastel Breeze',
        'soft-sand' => 'Soft Sand',
        'mint-cream' => 'Mint Cream',
        'blush-mist' => 'Blush Mist',
        'sky-peach' => 'Sky Peach',
        'lemon-fizz' => 'Lemon Fizz',
        'lavender-fog' => 'Lavender Fog',
        'coral-sunset' => 'Coral Sunset',
        'sea-glass' => 'Sea Glass',
        'apricot-sorbet' => 'Apricot Sorbet',
        'cotton-candy' => 'Cotton Candy',
        'pear-spritz' => 'Pear Spritz',
        'cloud-latte' => 'Cloud Latte',
        'dew-frost' => 'Dew Frost',
        'peach-foam' => 'Peach Foam',
        'lilac-ice' => 'Lilac Ice',
        'sage-mint' => 'Sage Mint',
        'buttercup' => 'Buttercup',
        'powder-blue' => 'Powder Blue',
        'melon-ice' => 'Melon Ice',
        'hazy-rose' => 'Hazy Rose',
        'calm-water' => 'Calm Water',
        'honey-milk' => 'Honey Milk',
        'arctic-mint' => 'Arctic Mint',
        'vanilla-berry' => 'Vanilla Berry',
        'morning-sun' => 'Morning Sun',
        'matcha-cream' => 'Matcha Cream',
    ];
}

/**
 * DaisyUI dark themes list keyed by slug.
 *
 * @return array<string, string>
 */
function crb_get_daisyui_dark_themes(): array
{
    return [
        'dim' => 'dim',
        'midnight-aurora' => 'Midnight Aurora',
        'neon-plasma' => 'Neon Plasma',
        'cyber-grape' => 'Cyber Grape',
        'velvet-ember' => 'Velvet Ember',
        'ink-cyan' => 'Ink Cyan',
        'dusk-rose' => 'Dusk Rose',
        'obsidian-gold' => 'Obsidian Gold',
        'deep-space' => 'Deep Space',
        'ocean-night' => 'Ocean Night',
        'noir-mint' => 'Noir Mint',
        'plum-neon' => 'Plum Neon',
        'cobalt-flare' => 'Cobalt Flare',
        'dusk-marine' => 'Dusk Marine',
        'ember-glow' => 'Ember Glow',
        'midnight-teal' => 'Midnight Teal',
        'aurora-mist' => 'Aurora Mist',
        'shadow-berry' => 'Shadow Berry',
        'neon-blush' => 'Neon Blush',
        'abyss-blue' => 'Abyss Blue',
        'charcoal-mint' => 'Charcoal Mint',
        'galaxy-candy' => 'Galaxy Candy',
        'violet-storm' => 'Violet Storm',
        'magma-ice' => 'Magma Ice',
        'stormy-sea' => 'Stormy Sea',
        'lunar-mauve' => 'Lunar Mauve',
        'acid-jungle' => 'Acid Jungle',
        'carbon-ember' => 'Carbon Ember',
    ];
}

/**
 * Render DaisyUI theme cards for Carbon Fields picker.
 *
 * @param string $mode light|dark.
 * @return string
 */
function crb_render_daisyui_theme_picker(string $mode): string
{
    $themes = $mode === 'dark' ? crb_get_daisyui_dark_themes() : crb_get_daisyui_light_themes();
    $palette = crb_get_daisyui_theme_palette(array_keys($themes));

    if (empty($themes)) {
        return '';
    }

    $cards = array_map(function ($slug, $label) use ($mode, $palette) {
        $colors = $palette[$slug] ?? [];
        $style = sprintf(
            '--crb-base-100:%1$s;--crb-base-200:%2$s;--crb-base-300:%3$s;--crb-base-content:%4$s;--crb-primary:%5$s;--crb-primary-content:%6$s;--crb-secondary:%7$s;--crb-secondary-content:%8$s;--crb-accent:%9$s;--crb-accent-content:%10$s;--crb-neutral:%11$s;--crb-neutral-content:%12$s;',
            esc_attr($colors['base100'] ?? '#f3f4f6'),
            esc_attr($colors['base200'] ?? '#e5e7eb'),
            esc_attr($colors['base300'] ?? '#d1d5db'),
            esc_attr($colors['baseContent'] ?? '#111827'),
            esc_attr($colors['primary'] ?? '#570df8'),
            esc_attr($colors['primaryContent'] ?? '#ffffff'),
            esc_attr($colors['secondary'] ?? '#f000b8'),
            esc_attr($colors['secondaryContent'] ?? '#ffffff'),
            esc_attr($colors['accent'] ?? '#37cdbe'),
            esc_attr($colors['accentContent'] ?? '#ffffff'),
            esc_attr($colors['neutral'] ?? '#3d4451'),
            esc_attr($colors['neutralContent'] ?? '#f3f4f6')
        );

        return sprintf(
            '<button type="button" class="crb-theme-card" data-value="%1$s" data-theme-target="%2$s" style="%3$s">
                <span class="crb-theme-card__grid">
                    <span class="crb-theme-card__base crb-theme-card__base--top"></span>
                    <span class="crb-theme-card__base crb-theme-card__base--bottom"></span>
                    <span class="crb-theme-card__body">
                        <span class="crb-theme-card__name">%4$s</span>
                        <span class="crb-theme-card__colors">
                            <span class="crb-theme-card__color is-primary"><span class="crb-theme-card__color-text">A</span></span>
                            <span class="crb-theme-card__color is-secondary"><span class="crb-theme-card__color-text">A</span></span>
                            <span class="crb-theme-card__color is-accent"><span class="crb-theme-card__color-text">A</span></span>
                            <span class="crb-theme-card__color is-neutral"><span class="crb-theme-card__color-text">A</span></span>
                        </span>
                    </span>
                </span>
            </button>',
            esc_attr($slug),
            esc_attr($mode),
            $style,
            esc_html($label)
        );
    }, array_keys($themes), $themes);

    return sprintf(
        '<div class="crb-theme-picker" data-theme-target="%1$s">%2$s</div>',
        esc_attr($mode),
        implode('', $cards)
    );
}

/**
 * Build a palette map for the themes we expose in Carbon Fields.
 *
 * @param array<string> $slugs
 * @return array<string, array<string, string>>
 */
function crb_get_daisyui_theme_palette(array $slugs): array
{
    static $cache = [];

    // if ($cache === null) {
    //     $path = get_template_directory() . '/app/daisyui-colors.json';
    //     $data = file_exists($path) ? json_decode(file_get_contents($path), true) : null;
    //     $cache = $data['default'] ?? [];
    // }

    $custom_palette = [
        'retro' => [
            'base100' => 'oklch(91.637% 0.034 90.515)',
            'base200' => 'oklch(88.272% 0.049 91.774)',
            'base300' => 'oklch(84.133% 0.065 90.856)',
            'baseContent' => 'oklch(41% 0.112 45.904)',
            'primary' => 'oklch(80% 0.114 19.571)',
            'primaryContent' => 'oklch(39% 0.141 25.723)',
            'secondary' => 'oklch(92% 0.084 155.995)',
            'secondaryContent' => 'oklch(44% 0.119 151.328)',
            'accent' => 'oklch(68% 0.162 75.834)',
            'accentContent' => 'oklch(41% 0.112 45.904)',
            'neutral' => 'oklch(44% 0.011 73.639)',
            'neutralContent' => 'oklch(86% 0.005 56.366)',
        ],
        'pastel-breeze' => [
            'base100' => '#f1f5fa',
            'base200' => '#e5edf6',
            'base300' => '#d6e3f0',
            'baseContent' => '#1e293b',
            'primary' => '#7c9cff',
            'primaryContent' => '#0f172a',
            'secondary' => '#8fd3ff',
            'secondaryContent' => '#0c4a6e',
            'accent' => '#ffb3c6',
            'accentContent' => '#4a0e2a',
            'neutral' => '#94a3b8',
            'neutralContent' => '#0f172a',
        ],
        'soft-sand' => [
            'base100' => '#f5ebdf',
            'base200' => '#e9ddcd',
            'base300' => '#dccfbf',
            'baseContent' => '#3f2d20',
            'primary' => '#d58b3b',
            'primaryContent' => '#331904',
            'secondary' => '#f0c987',
            'secondaryContent' => '#42210b',
            'accent' => '#84c7ae',
            'accentContent' => '#0f2f24',
            'neutral' => '#c7b8a3',
            'neutralContent' => '#2b1d12',
        ],
        'mint-cream' => [
            'base100' => '#e9f7f0',
            'base200' => '#d7eddf',
            'base300' => '#c4e0cf',
            'baseContent' => '#0f261c',
            'primary' => '#4bc0a9',
            'primaryContent' => '#05241c',
            'secondary' => '#9ee6c3',
            'secondaryContent' => '#0f2e1f',
            'accent' => '#ffd66b',
            'accentContent' => '#3c2900',
            'neutral' => '#93a59c',
            'neutralContent' => '#0b1b14',
        ],
        'blush-mist' => [
            'base100' => '#faeaf0',
            'base200' => '#f1d7e0',
            'base300' => '#e3c0cc',
            'baseContent' => '#31111d',
            'primary' => '#f472b6',
            'primaryContent' => '#3f0a1c',
            'secondary' => '#fbb1bd',
            'secondaryContent' => '#3c0e1e',
            'accent' => '#9fb4ff',
            'accentContent' => '#0b1b4d',
            'neutral' => '#c7b6be',
            'neutralContent' => '#2a1b24',
        ],
        'sky-peach' => [
            'base100' => '#f5ebe5',
            'base200' => '#e8d8cf',
            'base300' => '#d7c3b7',
            'baseContent' => '#2f1f18',
            'primary' => '#ff9e7a',
            'primaryContent' => '#3a1307',
            'secondary' => '#6ec3ff',
            'secondaryContent' => '#0b1f36',
            'accent' => '#ffd76f',
            'accentContent' => '#3c2a00',
            'neutral' => '#b8a99d',
            'neutralContent' => '#1f160f',
        ],
        'lemon-fizz' => [
            'base100' => '#fbf4d9',
            'base200' => '#e9e0b8',
            'base300' => '#d2c99d',
            'baseContent' => '#3a3004',
            'primary' => '#f8d03f',
            'primaryContent' => '#3a3004',
            'secondary' => '#7ed9a4',
            'secondaryContent' => '#0c2a1b',
            'accent' => '#ff9fb2',
            'accentContent' => '#3c0e1e',
            'neutral' => '#c4bb8c',
            'neutralContent' => '#1f1b0c',
        ],
        'lavender-fog' => [
            'base100' => '#f1ecff',
            'base200' => '#e1d8ff',
            'base300' => '#cbbff7',
            'baseContent' => '#24164a',
            'primary' => '#b399ff',
            'primaryContent' => '#130b2e',
            'secondary' => '#ffb4e6',
            'secondaryContent' => '#2c0822',
            'accent' => '#8fe0f0',
            'accentContent' => '#062730',
            'neutral' => '#b8b1c6',
            'neutralContent' => '#1b1630',
        ],
        'coral-sunset' => [
            'base100' => '#ffece3',
            'base200' => '#f9d6c7',
            'base300' => '#edc2ae',
            'baseContent' => '#35170e',
            'primary' => '#ff7f6e',
            'primaryContent' => '#2f0d08',
            'secondary' => '#ffc27a',
            'secondaryContent' => '#3a2206',
            'accent' => '#74c8f8',
            'accentContent' => '#0b2336',
            'neutral' => '#c9b4a9',
            'neutralContent' => '#221612',
        ],
        'sea-glass' => [
            'base100' => '#e7f4f2',
            'base200' => '#d0e7e3',
            'base300' => '#b9d7d3',
            'baseContent' => '#0f2320',
            'primary' => '#59c9b9',
            'primaryContent' => '#062621',
            'secondary' => '#7fb7ff',
            'secondaryContent' => '#0b1e36',
            'accent' => '#ffd66f',
            'accentContent' => '#382400',
            'neutral' => '#9bb3ad',
            'neutralContent' => '#0f1f1c',
        ],
        'apricot-sorbet' => [
            'base100' => '#ffeeda',
            'base200' => '#f9d9bc',
            'base300' => '#eac3a4',
            'baseContent' => '#301a0d',
            'primary' => '#ffad60',
            'primaryContent' => '#3a1c05',
            'secondary' => '#ff8fb1',
            'secondaryContent' => '#380b1d',
            'accent' => '#7dd3fc',
            'accentContent' => '#0b2436',
            'neutral' => '#c2b19f',
            'neutralContent' => '#1f160f',
        ],
        'cotton-candy' => [
            'base100' => '#f7eaff',
            'base200' => '#e9d2ff',
            'base300' => '#d4b8f2',
            'baseContent' => '#2b0f3c',
            'primary' => '#ff9fd6',
            'primaryContent' => '#3a0b22',
            'secondary' => '#9ed2ff',
            'secondaryContent' => '#0d2138',
            'accent' => '#ffe07a',
            'accentContent' => '#332400',
            'neutral' => '#c8b8d5',
            'neutralContent' => '#1f142d',
        ],
        'pear-spritz' => [
            'base100' => '#eef6e2',
            'base200' => '#dbe8cc',
            'base300' => '#c4d3b4',
            'baseContent' => '#1f2713',
            'primary' => '#9ad354',
            'primaryContent' => '#1e2c0d',
            'secondary' => '#6cc4a1',
            'secondaryContent' => '#0c2a1b',
            'accent' => '#ffdba1',
            'accentContent' => '#3c2a00',
            'neutral' => '#a8b59a',
            'neutralContent' => '#161d0f',
        ],
        'cloud-latte' => [
            'base100' => '#f3eae0',
            'base200' => '#e5d9c9',
            'base300' => '#d4c6b7',
            'baseContent' => '#2f2419',
            'primary' => '#c0a27a',
            'primaryContent' => '#24190c',
            'secondary' => '#8ac8ff',
            'secondaryContent' => '#0b2238',
            'accent' => '#ffb8b1',
            'accentContent' => '#3c1412',
            'neutral' => '#b9afa2',
            'neutralContent' => '#1f1912',
        ],
        'dew-frost' => [
            'base100' => '#e4f0fb',
            'base200' => '#cadeef',
            'base300' => '#b3cbe0',
            'baseContent' => '#112438',
            'primary' => '#7db7ff',
            'primaryContent' => '#0b1f36',
            'secondary' => '#73e2c7',
            'secondaryContent' => '#0f2e24',
            'accent' => '#ffd480',
            'accentContent' => '#3c2700',
            'neutral' => '#9db1c6',
            'neutralContent' => '#0f1a26',
        ],
        'peach-foam' => [
            'base100' => '#fce8ea',
            'base200' => '#f3d1d7',
            'base300' => '#e4b7bf',
            'baseContent' => '#2f1119',
            'primary' => '#ff9f9f',
            'primaryContent' => '#3a0c0c',
            'secondary' => '#ffd479',
            'secondaryContent' => '#3c2500',
            'accent' => '#9ad8ff',
            'accentContent' => '#0b1f32',
            'neutral' => '#c8b2b8',
            'neutralContent' => '#20151c',
        ],
        'lilac-ice' => [
            'base100' => '#eef3fc',
            'base200' => '#dbe2f3',
            'base300' => '#c6d0e6',
            'baseContent' => '#171d2f',
            'primary' => '#98b6ff',
            'primaryContent' => '#0f1a38',
            'secondary' => '#f5a8ff',
            'secondaryContent' => '#2e0a2e',
            'accent' => '#7de0c3',
            'accentContent' => '#0d2a22',
            'neutral' => '#aab6c9',
            'neutralContent' => '#141a28',
        ],
        'sage-mint' => [
            'base100' => '#e6f3e9',
            'base200' => '#d1e4d5',
            'base300' => '#bbd3c1',
            'baseContent' => '#1a261f',
            'primary' => '#7abf8a',
            'primaryContent' => '#0e2415',
            'secondary' => '#b0d99f',
            'secondaryContent' => '#132610',
            'accent' => '#5ec7d9',
            'accentContent' => '#0c2230',
            'neutral' => '#a2b3a8',
            'neutralContent' => '#132019',
        ],
        'buttercup' => [
            'base100' => '#fdf2d6',
            'base200' => '#eed69f',
            'base300' => '#d5be8a',
            'baseContent' => '#352807',
            'primary' => '#f7c948',
            'primaryContent' => '#2f2204',
            'secondary' => '#82d7c9',
            'secondaryContent' => '#0c2520',
            'accent' => '#ff9fb5',
            'accentContent' => '#3c0e1e',
            'neutral' => '#c5b479',
            'neutralContent' => '#1e1607',
        ],
        'powder-blue' => [
            'base100' => '#edf3ff',
            'base200' => '#d7e2fb',
            'base300' => '#c3d1f1',
            'baseContent' => '#14203a',
            'primary' => '#88b7ff',
            'primaryContent' => '#0b1932',
            'secondary' => '#9fe3ff',
            'secondaryContent' => '#0b2336',
            'accent' => '#ffc78f',
            'accentContent' => '#3c2205',
            'neutral' => '#a7b4c8',
            'neutralContent' => '#0f1726',
        ],
        'melon-ice' => [
            'base100' => '#eaf8ee',
            'base200' => '#d6eddc',
            'base300' => '#c1dfc9',
            'baseContent' => '#132015',
            'primary' => '#77d6a0',
            'primaryContent' => '#0a2414',
            'secondary' => '#ffd88f',
            'secondaryContent' => '#3c2a05',
            'accent' => '#76c8ff',
            'accentContent' => '#0d2338',
            'neutral' => '#a7bcae',
            'neutralContent' => '#0f1c14',
        ],
        'hazy-rose' => [
            'base100' => '#f6eaf0',
            'base200' => '#e6d4df',
            'base300' => '#d2bcc9',
            'baseContent' => '#2e1a22',
            'primary' => '#f08ca5',
            'primaryContent' => '#330c1c',
            'secondary' => '#b9c7ff',
            'secondaryContent' => '#0e1d38',
            'accent' => '#ffd28f',
            'accentContent' => '#3c2505',
            'neutral' => '#c4b3be',
            'neutralContent' => '#1a1116',
        ],
        'calm-water' => [
            'base100' => '#e8f4ff',
            'base200' => '#d1e5f6',
            'base300' => '#b7d0e6',
            'baseContent' => '#102133',
            'primary' => '#6fbaf8',
            'primaryContent' => '#0b1d32',
            'secondary' => '#83d8c7',
            'secondaryContent' => '#0f2b22',
            'accent' => '#ffd48a',
            'accentContent' => '#3b2605',
            'neutral' => '#9db3c3',
            'neutralContent' => '#101b26',
        ],
        'honey-milk' => [
            'base100' => '#f5eade',
            'base200' => '#e8d7c2',
            'base300' => '#d4c2aa',
            'baseContent' => '#2f2215',
            'primary' => '#e7b464',
            'primaryContent' => '#332206',
            'secondary' => '#8fded3',
            'secondaryContent' => '#0d2722',
            'accent' => '#f79fb7',
            'accentContent' => '#3a0f1c',
            'neutral' => '#c1b09b',
            'neutralContent' => '#1f160e',
        ],
        'arctic-mint' => [
            'base100' => '#e5f6fa',
            'base200' => '#cce8ef',
            'base300' => '#b5d7de',
            'baseContent' => '#0f2229',
            'primary' => '#6ad1e3',
            'primaryContent' => '#082329',
            'secondary' => '#7ae1b8',
            'secondaryContent' => '#0c2b22',
            'accent' => '#ffd88f',
            'accentContent' => '#3c2705',
            'neutral' => '#9cb8c0',
            'neutralContent' => '#0f1a22',
        ],
        'vanilla-berry' => [
            'base100' => '#f8e9e1',
            'base200' => '#ead4c9',
            'base300' => '#d7beb2',
            'baseContent' => '#2f1b14',
            'primary' => '#f39c82',
            'primaryContent' => '#381408',
            'secondary' => '#d2a6ff',
            'secondaryContent' => '#1f0b38',
            'accent' => '#7cc8f7',
            'accentContent' => '#0b2336',
            'neutral' => '#c3b0a8',
            'neutralContent' => '#1c130f',
        ],
        'morning-sun' => [
            'base100' => '#f9efd4',
            'base200' => '#ead9ae',
            'base300' => '#d5c293',
            'baseContent' => '#372808',
            'primary' => '#ffc857',
            'primaryContent' => '#2f2206',
            'secondary' => '#6ec9f2',
            'secondaryContent' => '#0b2336',
            'accent' => '#ff9fb8',
            'accentContent' => '#3b0f1e',
            'neutral' => '#c7b88f',
            'neutralContent' => '#20180a',
        ],
        'matcha-cream' => [
            'base100' => '#eaf4e3',
            'base200' => '#d7e5ce',
            'base300' => '#c3d3ba',
            'baseContent' => '#152114',
            'primary' => '#7ccf82',
            'primaryContent' => '#0e2313',
            'secondary' => '#9ed9c7',
            'secondaryContent' => '#0c251e',
            'accent' => '#ffdba1',
            'accentContent' => '#3c2a05',
            'neutral' => '#a7b8a3',
            'neutralContent' => '#0f1b13',
        ],
        'dim' => [
            'base100' => '#1f2933',
            'base200' => '#111827',
            'base300' => '#0b1220',
            'baseContent' => '#e5e7eb',
            'primary' => '#8b5cf6',
            'primaryContent' => '#f5f3ff',
            'secondary' => '#22d3ee',
            'secondaryContent' => '#051921',
            'accent' => '#f59e0b',
            'accentContent' => '#1b1204',
            'neutral' => '#0f172a',
            'neutralContent' => '#e5e7eb',
        ],
        'midnight-aurora' => [
            'base100' => '#2c4562',
            'base200' => '#36506d',
            'base300' => '#3f5b78',
            'baseContent' => '#d9ecff',
            'primary' => '#5fb7ff',
            'primaryContent' => '#0a1b30',
            'secondary' => '#6fe7c8',
            'secondaryContent' => '#0a241c',
            'accent' => '#f7b267',
            'accentContent' => '#2c1705',
            'neutral' => '#1b2738',
            'neutralContent' => '#dbe6f5',
        ],
        'neon-plasma' => [
            'base100' => '#3a315c',
            'base200' => '#463c6a',
            'base300' => '#514678',
            'baseContent' => '#f6e9ff',
            'primary' => '#b86bff',
            'primaryContent' => '#1d0a3a',
            'secondary' => '#ff7edb',
            'secondaryContent' => '#320720',
            'accent' => '#4ff1d6',
            'accentContent' => '#042019',
            'neutral' => '#2e1c4a',
            'neutralContent' => '#efe4ff',
        ],
        'cyber-grape' => [
            'base100' => '#3a3158',
            'base200' => '#443b66',
            'base300' => '#4e4574',
            'baseContent' => '#f2eaff',
            'primary' => '#7c6bff',
            'primaryContent' => '#130e35',
            'secondary' => '#f589d6',
            'secondaryContent' => '#2f0a23',
            'accent' => '#5fd4ff',
            'accentContent' => '#082133',
            'neutral' => '#2c1f45',
            'neutralContent' => '#e9ddff',
        ],
        'velvet-ember' => [
            'base100' => '#453040',
            'base200' => '#50394b',
            'base300' => '#5b4356',
            'baseContent' => '#fde9e4',
            'primary' => '#ff7f73',
            'primaryContent' => '#2e0c07',
            'secondary' => '#ffbd5a',
            'secondaryContent' => '#2c1905',
            'accent' => '#7ec9ff',
            'accentContent' => '#081b2f',
            'neutral' => '#3a1923',
            'neutralContent' => '#f5dedd',
        ],
        'ink-cyan' => [
            'base100' => '#31545e',
            'base200' => '#3b5f69',
            'base300' => '#456974',
            'baseContent' => '#d9f5ff',
            'primary' => '#31c5ff',
            'primaryContent' => '#031724',
            'secondary' => '#7ef3d2',
            'secondaryContent' => '#062019',
            'accent' => '#ffbb66',
            'accentContent' => '#2a1502',
            'neutral' => '#0c3a44',
            'neutralContent' => '#d3ecf2',
        ],
        'dusk-rose' => [
            'base100' => '#503349',
            'base200' => '#5a3d55',
            'base300' => '#644761',
            'baseContent' => '#fde9f3',
            'primary' => '#ff82b4',
            'primaryContent' => '#2f0c1e',
            'secondary' => '#9fb1ff',
            'secondaryContent' => '#0d1638',
            'accent' => '#f6c86f',
            'accentContent' => '#2c1b05',
            'neutral' => '#4a1f35',
            'neutralContent' => '#f3dce7',
        ],
        'obsidian-gold' => [
            'base100' => '#453533',
            'base200' => '#503f3e',
            'base300' => '#5a4848',
            'baseContent' => '#f8f1e5',
            'primary' => '#e3b755',
            'primaryContent' => '#241703',
            'secondary' => '#7dd8c8',
            'secondaryContent' => '#07241d',
            'accent' => '#ff95b5',
            'accentContent' => '#320716',
            'neutral' => '#3d2916',
            'neutralContent' => '#f1e4d6',
        ],
        'deep-space' => [
            'base100' => '#2e4b66',
            'base200' => '#375571',
            'base300' => '#415f7c',
            'baseContent' => '#e1ebf8',
            'primary' => '#6fb5ff',
            'primaryContent' => '#0b1b33',
            'secondary' => '#8ff0d5',
            'secondaryContent' => '#0a231b',
            'accent' => '#f8b66f',
            'accentContent' => '#2b1a05',
            'neutral' => '#173651',
            'neutralContent' => '#d9e7f5',
        ],
        'ocean-night' => [
            'base100' => '#2e4955',
            'base200' => '#385360',
            'base300' => '#425c6b',
            'baseContent' => '#e3f4fb',
            'primary' => '#55b8ff',
            'primaryContent' => '#04182a',
            'secondary' => '#52e0c5',
            'secondaryContent' => '#04211a',
            'accent' => '#ffd36f',
            'accentContent' => '#2f1f06',
            'neutral' => '#123642',
            'neutralContent' => '#d6e8ee',
        ],
        'noir-mint' => [
            'base100' => '#2f433f',
            'base200' => '#394d4a',
            'base300' => '#435755',
            'baseContent' => '#e8f7f0',
            'primary' => '#52e1b6',
            'primaryContent' => '#062217',
            'secondary' => '#7cc9ff',
            'secondaryContent' => '#0b1c2c',
            'accent' => '#ffb36c',
            'accentContent' => '#2f1c05',
            'neutral' => '#1a372c',
            'neutralContent' => '#d7ede5',
        ],
        'plum-neon' => [
            'base100' => '#413358',
            'base200' => '#4c3d65',
            'base300' => '#574772',
            'baseContent' => '#f5e8fb',
            'primary' => '#c58bff',
            'primaryContent' => '#1f0f32',
            'secondary' => '#72f2d7',
            'secondaryContent' => '#05241a',
            'accent' => '#ff9ac4',
            'accentContent' => '#300c1c',
            'neutral' => '#34184d',
            'neutralContent' => '#eeddf7',
        ],
        'cobalt-flare' => [
            'base100' => '#324364',
            'base200' => '#3c4d6f',
            'base300' => '#455779',
            'baseContent' => '#e5eefb',
            'primary' => '#5f9cff',
            'primaryContent' => '#0a1a36',
            'secondary' => '#ff8fc5',
            'secondaryContent' => '#300c1e',
            'accent' => '#6ff0ce',
            'accentContent' => '#031c14',
            'neutral' => '#132f52',
            'neutralContent' => '#d8e6f6',
        ],
        'dusk-marine' => [
            'base100' => '#30474c',
            'base200' => '#3b5257',
            'base300' => '#455c62',
            'baseContent' => '#e5f1f4',
            'primary' => '#5baee6',
            'primaryContent' => '#0b1c31',
            'secondary' => '#73e1c8',
            'secondaryContent' => '#06241c',
            'accent' => '#ffb36f',
            'accentContent' => '#2f1c05',
            'neutral' => '#13373c',
            'neutralContent' => '#d6e7eb',
        ],
        'ember-glow' => [
            'base100' => '#503632',
            'base200' => '#5a403c',
            'base300' => '#654a46',
            'baseContent' => '#f7eae3',
            'primary' => '#ff9966',
            'primaryContent' => '#2f1307',
            'secondary' => '#ffc75f',
            'secondaryContent' => '#2f1d05',
            'accent' => '#7ad2ff',
            'accentContent' => '#081c2b',
            'neutral' => '#3f1f14',
            'neutralContent' => '#f1e1d8',
        ],
        'midnight-teal' => [
            'base100' => '#30474a',
            'base200' => '#3a5154',
            'base300' => '#445b5f',
            'baseContent' => '#e1f5f3',
            'primary' => '#3ccfca',
            'primaryContent' => '#041d1e',
            'secondary' => '#6fc3ff',
            'secondaryContent' => '#0a1c2b',
            'accent' => '#ffc978',
            'accentContent' => '#2f2006',
            'neutral' => '#114145',
            'neutralContent' => '#d6ecea',
        ],
        'aurora-mist' => [
            'base100' => '#2f4152',
            'base200' => '#394b5d',
            'base300' => '#435568',
            'baseContent' => '#e6f3fb',
            'primary' => '#7ab9ff',
            'primaryContent' => '#0a1a36',
            'secondary' => '#9ff1db',
            'secondaryContent' => '#0a241c',
            'accent' => '#f7c873',
            'accentContent' => '#2f1d05',
            'neutral' => '#12354a',
            'neutralContent' => '#d7e7f4',
        ],
        'shadow-berry' => [
            'base100' => '#433352',
            'base200' => '#4d3d5f',
            'base300' => '#57466b',
            'baseContent' => '#f4e9f6',
            'primary' => '#b293ff',
            'primaryContent' => '#1f1533',
            'secondary' => '#ff9dd1',
            'secondaryContent' => '#2c0c1d',
            'accent' => '#6fe8c7',
            'accentContent' => '#041c14',
            'neutral' => '#2f1b3f',
            'neutralContent' => '#ebddef',
        ],
        'neon-blush' => [
            'base100' => '#443041',
            'base200' => '#4e3a4c',
            'base300' => '#584457',
            'baseContent' => '#f9e9f0',
            'primary' => '#ff77b2',
            'primaryContent' => '#2f0b1e',
            'secondary' => '#7df0c9',
            'secondaryContent' => '#062419',
            'accent' => '#ffc96f',
            'accentContent' => '#2f1c05',
            'neutral' => '#381627',
            'neutralContent' => '#eedee7',
        ],
        'abyss-blue' => [
            'base100' => '#2f415b',
            'base200' => '#394b66',
            'base300' => '#435571',
            'baseContent' => '#dfe8f6',
            'primary' => '#4f95ff',
            'primaryContent' => '#071835',
            'secondary' => '#6ee0d0',
            'secondaryContent' => '#06241e',
            'accent' => '#f7c058',
            'accentContent' => '#2e1c05',
            'neutral' => '#122f47',
            'neutralContent' => '#d2ddec',
        ],
        'charcoal-mint' => [
            'base100' => '#334440',
            'base200' => '#3d4f4b',
            'base300' => '#475956',
            'baseContent' => '#e7f5f1',
            'primary' => '#5ad5b8',
            'primaryContent' => '#042019',
            'secondary' => '#8bc8ff',
            'secondaryContent' => '#0a1b2c',
            'accent' => '#ffbe72',
            'accentContent' => '#2f1d05',
            'neutral' => '#1a372e',
            'neutralContent' => '#d6ece6',
        ],
        'galaxy-candy' => [
            'base100' => '#3d3259',
            'base200' => '#473c66',
            'base300' => '#514671',
            'baseContent' => '#f0e7fb',
            'primary' => '#cf8cff',
            'primaryContent' => '#210f2e',
            'secondary' => '#7de4ff',
            'secondaryContent' => '#081c2b',
            'accent' => '#ff9ac0',
            'accentContent' => '#2f0d1c',
            'neutral' => '#311b4c',
            'neutralContent' => '#eadff6',
        ],
        'violet-storm' => [
            'base100' => '#343c59',
            'base200' => '#3e4664',
            'base300' => '#48506f',
            'baseContent' => '#e8eafb',
            'primary' => '#8da0ff',
            'primaryContent' => '#0b142f',
            'secondary' => '#ff97d0',
            'secondaryContent' => '#2c0c1d',
            'accent' => '#6ee8c5',
            'accentContent' => '#041c14',
            'neutral' => '#162544',
            'neutralContent' => '#d8dff2',
        ],
        'magma-ice' => [
            'base100' => '#3f3748',
            'base200' => '#494254',
            'base300' => '#534c5f',
            'baseContent' => '#f3f1f5',
            'primary' => '#ff817a',
            'primaryContent' => '#2f0c0c',
            'secondary' => '#7be9f4',
            'secondaryContent' => '#072126',
            'accent' => '#ffc96a',
            'accentContent' => '#2f1f05',
            'neutral' => '#352738',
            'neutralContent' => '#ece7ed',
        ],
        'stormy-sea' => [
            'base100' => '#2f4352',
            'base200' => '#394d5d',
            'base300' => '#435668',
            'baseContent' => '#e4eef6',
            'primary' => '#66b6e8',
            'primaryContent' => '#0a1828',
            'secondary' => '#65d6c4',
            'secondaryContent' => '#07221b',
            'accent' => '#f6c869',
            'accentContent' => '#2e1c05',
            'neutral' => '#132f3d',
            'neutralContent' => '#d5e4ec',
        ],
        'lunar-mauve' => [
            'base100' => '#3e3452',
            'base200' => '#483e5e',
            'base300' => '#52486a',
            'baseContent' => '#f1e9f8',
            'primary' => '#c9a1ff',
            'primaryContent' => '#1f0e2e',
            'secondary' => '#ff9dc4',
            'secondaryContent' => '#2c0c1d',
            'accent' => '#84ead7',
            'accentContent' => '#06231a',
            'neutral' => '#311d47',
            'neutralContent' => '#ecdfef',
        ],
        'acid-jungle' => [
            'base100' => '#314134',
            'base200' => '#3b4b3f',
            'base300' => '#45544a',
            'baseContent' => '#e7f6e9',
            'primary' => '#86f05f',
            'primaryContent' => '#10260b',
            'secondary' => '#4fd8c6',
            'secondaryContent' => '#07231d',
            'accent' => '#ffd55f',
            'accentContent' => '#2f1e05',
            'neutral' => '#163615',
            'neutralContent' => '#deecdf',
        ],
        'carbon-ember' => [
            'base100' => '#3f414e',
            'base200' => '#484b57',
            'base300' => '#525462',
            'baseContent' => '#e9eef5',
            'primary' => '#ff7a63',
            'primaryContent' => '#2f0c0a',
            'secondary' => '#6fc8f4',
            'secondaryContent' => '#082332',
            'accent' => '#ffc65a',
            'accentContent' => '#2f1d05',
            'neutral' => '#22262d',
            'neutralContent' => '#e0e5ec',
        ],
    ];

    $palette_source = array_merge($custom_palette, $cache);
    $palette = [];

    foreach ($slugs as $slug) {
        if (!isset($palette_source[$slug])) {
            continue;
        }

        $theme = $palette_source[$slug];

        $palette[$slug] = [
            'base100' => $theme['--color-base-100'] ?? $theme['base100'] ?? '#f3f4f6',
            'base200' => $theme['--color-base-200'] ?? $theme['base200'] ?? '#e5e7eb',
            'base300' => $theme['--color-base-300'] ?? $theme['base300'] ?? '#d1d5db',
            'baseContent' => $theme['--color-base-content'] ?? $theme['baseContent'] ?? '#111827',
            'primary' => $theme['--color-primary'] ?? $theme['primary'] ?? '#570df8',
            'primaryContent' => $theme['--color-primary-content'] ?? $theme['primaryContent'] ?? '#ffffff',
            'secondary' => $theme['--color-secondary'] ?? $theme['secondary'] ?? '#f000b8',
            'secondaryContent' => $theme['--color-secondary-content'] ?? $theme['secondaryContent'] ?? '#ffffff',
            'accent' => $theme['--color-accent'] ?? $theme['accent'] ?? '#37cdbe',
            'accentContent' => $theme['--color-accent-content'] ?? $theme['accentContent'] ?? '#ffffff',
            'neutral' => $theme['--color-neutral'] ?? $theme['neutral'] ?? '#3d4451',
            'neutralContent' => $theme['--color-neutral-content'] ?? $theme['neutralContent'] ?? '#f3f4f6',
        ];
    }

    return $palette;
}

/**
 * Check if current admin page is the Carbon Fields Theme Settings screen.
 *
 * @return bool
 */
function crb_is_carbon_fields_theme_page(): bool
{
    if (!is_admin()) {
        return false;
    }

    $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';

    return strpos($page, 'crb_carbon_fields_container_theme_settings') !== false;
}

/**
 * Output inline styles and scripts for the DaisyUI theme picker cards.
 *
 * @return void
 */
function crb_output_daisyui_theme_picker_assets(): void
{
    ?>
    <style>
        .crb-theme-picker {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 12px;
            margin-top: 8px;
        }

        .crb-theme-heading {
            font-weight: 700;
            font-size: 14px;
            margin: 6px 0 4px;
            color: #111827;
        }

        .crb-theme-card {
            position: relative;
            border: 1px solid #dcdde0;
            border-radius: 12px;
            padding: 0;
            background: var(--crb-base-100, #fff);
            cursor: pointer;
            text-align: left;
            transition: box-shadow 0.2s ease, border-color 0.2s ease, transform 0.1s ease;
            overflow: hidden;
        }

        .crb-theme-card::before {
            content: '';
            position: absolute;
            inset: 0 auto 0 0;
            width: 20px;
            background: transparent;
        }

        .crb-theme-card:hover {
            border-color: #4f46e5;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            transform: translateY(-1px);
        }

        .crb-theme-card.is-active {
            border-color: #6366f1;
            box-shadow:
                0 0 0 2px rgba(99, 102, 241, 0.4),
                0 10px 24px rgba(0, 0, 0, 0.16);
            transform: translateY(-1px);
        }

        .crb-theme-card.is-active::after {
            content: '✓';
            position: absolute;
            top: 8px;
            right: 8px;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.9), rgba(14, 165, 233, 0.9));
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 800;
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.9);
            z-index: 2;
        }

        .crb-theme-card__grid {
            position: relative;
            display: grid;
            grid-template-columns: 20px 1fr;
            grid-template-rows: repeat(3, 1fr);
            min-height: 78px;
        }

        .crb-theme-card__base {
            grid-column: 1 / 2;
        }

        .crb-theme-card__base--top {
            grid-row: 1 / 3;
            background: var(--crb-base-200);
        }

        .crb-theme-card__base--bottom {
            grid-row: 3 / 4;
            background: var(--crb-base-300);
        }

        .crb-theme-card__body {
            grid-column: 2 / 3;
            grid-row: 1 / 4;
            background: var(--crb-base-100);
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 10px;
            color: var(--crb-base-content, #111827);
        }

        .crb-theme-card__name {
            font-weight: 700;
            font-size: 14px;
            line-height: 1.2;
            text-transform: lowercase;
        }

        .crb-theme-card__colors {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .crb-theme-card__color {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 12px;
        }

        .crb-theme-card__color.is-primary { background: var(--crb-primary); color: var(--crb-primary-content, #fff); }
        .crb-theme-card__color.is-secondary { background: var(--crb-secondary); color: var(--crb-secondary-content, #fff); }
        .crb-theme-card__color.is-accent { background: var(--crb-accent); color: var(--crb-accent-content, #fff); }
        .crb-theme-card__color.is-neutral { background: var(--crb-neutral); color: var(--crb-neutral-content, #fff); }

        .crb-theme-select {
            position: absolute;
            left: -9999px;
            height: 1px;
            width: 1px;
            overflow: hidden;
        }
    </style>
    <script>
        (() => {
            const syncSelection = (picker, select) => {
                const current = select.value;
                picker.querySelectorAll('.crb-theme-card').forEach((card) => {
                    card.classList.toggle('is-active', card.dataset.value === current);
                });
            };

            const findSelect = (target) => {
                let select = document.querySelector(`select.crb-theme-select[data-theme-target="${target}"]`);
                if (!select) {
                    select = document.querySelector(`select[name*="[crb_${target}_theme]"]`) ||
                        document.querySelector(`select[name*="crb_${target}_theme"]`);
                    if (select) {
                        select.dataset.themeTarget = target;
                        select.classList.add('crb-theme-select');
                    }
                }
                return select;
            };

            const bindPicker = (picker) => {
                const target = picker.dataset.themeTarget;
                const select = findSelect(target);
                if (!select) return;

                picker.querySelectorAll('.crb-theme-card').forEach((card) => {
                    card.addEventListener('click', () => {
                        const value = card.dataset.value;
                        if (!value) return;
                        select.value = value;
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                        select.dispatchEvent(new Event('input', { bubbles: true }));
                        syncSelection(picker, select);
                    }, { passive: true });
                });

                select.addEventListener('change', () => syncSelection(picker, select));
                syncSelection(picker, select);
                // 隐藏原下拉但保留可访问性
                select.style.position = 'absolute';
                select.style.left = '-9999px';
                select.style.height = '1px';
                select.style.width = '1px';
                select.style.overflow = 'hidden';
            };

            const initPicker = () => {
                document.querySelectorAll('.crb-theme-picker').forEach(bindPicker);
            };

            const observe = () => {
                const observer = new MutationObserver(() => {
                    initPicker();
                });
                observer.observe(document.body, { childList: true, subtree: true });
            };

            document.addEventListener('DOMContentLoaded', () => {
                initPicker();
                observe();
            });
        })();
    </script>
    <?php
}

/**
 * Output inline scripts for the logo uploader with cropper.
 *
 * @return void
 */
function crb_output_logo_uploader_assets(): void
{
    ?>
    <script>
        jQuery(document).ready(function($) {
            if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
                console.error('wp.media is not available');
                return;
            }

            let logoCropperFrame = null;
            let isInitialized = false;

            function initLogoUploader() {
                const uploadBtn = document.querySelector('.crb-logo-upload-btn');
                const removeBtn = document.querySelector('.crb-logo-remove-btn');
                const hiddenField = document.getElementById('crb_site_logo_field');
                const previewContainer = document.querySelector('.crb-logo-preview');
                const findCarbonField = () => document.querySelector('input[data-logo-field="true"]') ||
                    document.querySelector('.crb-logo-carbon-field input') ||
                    document.querySelector('input[name*="crb_site_logo"]');

                if (!uploadBtn || !hiddenField) {
                    console.log('Upload button or hidden field not found');
                    return;
                }

                if (isInitialized) {
                    console.log('Already initialized');
                    return;
                }

                isInitialized = true;
                console.log('Initializing logo uploader');

                const logoWidth = parseInt(uploadBtn.dataset.logoWidth) || 220;
                const logoHeight = parseInt(uploadBtn.dataset.logoHeight) || 32;

                // Remove existing event listeners
                const newUploadBtn = uploadBtn.cloneNode(true);
                uploadBtn.parentNode.replaceChild(newUploadBtn, uploadBtn);
                const ensurePreview = (url) => {
                    if (!url) return;
                    const existingPreview = document.querySelector('.crb-logo-preview');
                    if (existingPreview) {
                        const img = existingPreview.querySelector('img');
                        if (img) {
                            img.src = url;
                        }
                        return;
                    }
                    const newPreview = document.createElement('div');
                    newPreview.className = 'crb-logo-preview';
                    newPreview.style.marginTop = '12px';
                    newPreview.innerHTML = '<img src="' + url + '" alt="<?php echo esc_js(__('Site Logo', 'a-ripple-song')); ?>" style="max-width: 220px; height: auto; border: 1px solid #ddd; padding: 8px; background: #f9f9f9;">';
                    newUploadBtn.parentElement.appendChild(newPreview);
                };

                // Sync initial value from Carbon Fields (fallback if PHP preview failed)
                const carbonField = findCarbonField();
                const initialValue = (hiddenField.value || hiddenField.dataset.currentValue || '').trim() ||
                    (carbonField && carbonField.value ? carbonField.value.trim() : '');
                if (initialValue) {
                    hiddenField.value = initialValue;
                    if (carbonField) {
                        carbonField.value = initialValue;
                    }
                    ensurePreview(initialValue);
                    if (removeBtn) {
                        removeBtn.style.display = 'inline-block';
                    }
                }

                newUploadBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Upload button clicked');

                    // Create media frame for selecting image
                    logoCropperFrame = wp.media({
                        title: '<?php echo esc_js(__('Choose Logo Image', 'a-ripple-song')); ?>',
                        button: {
                            text: '<?php echo esc_js(__('Crop Image', 'a-ripple-song')); ?>'
                        },
                        multiple: false,
                        library: {
                            type: 'image'
                        }
                    });

                    logoCropperFrame.on('select', function() {
                        const attachment = logoCropperFrame.state().get('selection').first().toJSON();
                        console.log('Image selected:', attachment);

                        if (attachment.width < logoWidth || attachment.height < logoHeight) {
                            alert('<?php echo esc_js(sprintf(__('Image is too small. Minimum size: %1$dpx × %2$dpx', 'a-ripple-song'), 220, 32)); ?>');
                            return;
                        }

                        // Open cropper
                        openCropper(attachment);
                    });

                    function openCropper(attachment) {
                        const ratio = logoWidth / logoHeight;
                        const realWidth = attachment.width;
                        const realHeight = attachment.height;

                        // Calculate initial crop box
                        let cropWidth = realWidth;
                        let cropHeight = realHeight;

                        if (realWidth / realHeight > ratio) {
                            cropWidth = realHeight * ratio;
                        } else {
                            cropHeight = realWidth / ratio;
                        }

                        // Create cropper modal
                        const modal = $('<div class="crb-crop-modal" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); z-index: 160000; display: flex; align-items: center; justify-content: center;"></div>');
                        const container = $('<div style="background: white; padding: 20px; border-radius: 8px; max-width: 90%; max-height: 90%; overflow: auto;"></div>');
                        const img = $('<img src="' + attachment.url + '" style="max-width: 100%; display: block;">');
                        const btnContainer = $('<div style="margin-top: 15px; text-align: right;"></div>');
                        const cropBtn = $('<button class="button button-primary" style="margin-right: 10px;"><?php echo esc_js(__('Crop and Save', 'a-ripple-song')); ?></button>');
                        const cancelBtn = $('<button class="button"><?php echo esc_js(__('Cancel', 'a-ripple-song')); ?></button>');

                        btnContainer.append(cropBtn).append(cancelBtn);
                        container.append(img).append(btnContainer);
                        modal.append(container);
                        $('body').append(modal);

                        // Initialize imgAreaSelect
                        const ias = img.imgAreaSelect({
                            aspectRatio: logoWidth + ':' + logoHeight,
                            handles: true,
                            instance: true,
                            persistent: true,
                            imageWidth: realWidth,
                            imageHeight: realHeight,
                            minWidth: logoWidth,
                            minHeight: logoHeight,
                            x1: 0,
                            y1: 0,
                            x2: cropWidth,
                            y2: cropHeight
                        });

                        cancelBtn.on('click', function() {
                            ias.cancelSelection();
                            ias.remove();
                            modal.remove();
                        });

                        cropBtn.on('click', function() {
                            const selection = ias.getSelection();
                            console.log('Crop selection:', selection);

                            if (!selection.width || !selection.height) {
                                alert('<?php echo esc_js(__('Please select a crop area.', 'a-ripple-song')); ?>');
                                return;
                            }

                            cropBtn.prop('disabled', true).text('<?php echo esc_js(__('Cropping...', 'a-ripple-song')); ?>');

                            const nonce = '<?php echo esc_js(wp_create_nonce('crb_crop_logo')); ?>';

                            // Send AJAX request to crop image
                            $.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'crb_crop_logo',
                                    nonce: nonce,
                                    id: attachment.id,
                                    cropDetails: {
                                        x1: selection.x1,
                                        y1: selection.y1,
                                        width: selection.width,
                                        height: selection.height,
                                        dst_width: logoWidth,
                                        dst_height: logoHeight
                                    }
                                },
                                success: function(response) {
                                    console.log('Crop response:', response);

                                    if (response.success) {
                                        hiddenField.value = response.data.url;

                                        // Update Carbon Fields hidden field
                                        const carbonField = document.querySelector('input[data-logo-field="true"]') ||
                                                          document.querySelector('.crb-logo-carbon-field input') ||
                                                          document.querySelector('input[name*="crb_site_logo"]');
                                        if (carbonField) {
                                            carbonField.value = response.data.url;
                                            // Trigger change event for Carbon Fields
                                            $(carbonField).trigger('change').trigger('input');
                                            console.log('Updated Carbon Fields field:', carbonField.value);
                                        } else {
                                            console.warn('Carbon Fields field not found');
                                        }

                                        // Update preview
                                        ensurePreview(response.data.url);

                                        const currentRemoveBtn = document.querySelector('.crb-logo-remove-btn');
                                        if (currentRemoveBtn) {
                                            currentRemoveBtn.style.display = 'inline-block';
                                        }

                                        ias.cancelSelection();
                                        ias.remove();
                                        modal.remove();
                                    } else {
                                        alert('<?php echo esc_js(__('Error cropping image:', 'a-ripple-song')); ?> ' + (response.data.message || ''));
                                        cropBtn.prop('disabled', false).text('<?php echo esc_js(__('Crop and Save', 'a-ripple-song')); ?>');
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error('AJAX error:', xhr, status, error);
                                    alert('<?php echo esc_js(__('Error cropping image. Please try again.', 'a-ripple-song')); ?>');
                                    cropBtn.prop('disabled', false).text('<?php echo esc_js(__('Crop and Save', 'a-ripple-song')); ?>');
                                }
                            });
                        });
                    }

                    console.log('Opening media frame');
                    logoCropperFrame.open();
                });

                // Handle remove button
                if (removeBtn) {
                    const newRemoveBtn = removeBtn.cloneNode(true);
                    removeBtn.parentNode.replaceChild(newRemoveBtn, removeBtn);

                    newRemoveBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        hiddenField.value = '';

                        // Update Carbon Fields hidden field
                        const carbonField = document.querySelector('input[data-logo-field="true"]') ||
                                          document.querySelector('.crb-logo-carbon-field input') ||
                                          document.querySelector('input[name*="crb_site_logo"]');
                        if (carbonField) {
                            carbonField.value = '';
                            $(carbonField).trigger('change').trigger('input');
                            console.log('Cleared Carbon Fields field');
                        }

                        const preview = document.querySelector('.crb-logo-preview');
                        if (preview) {
                            preview.remove();
                        }
                        newRemoveBtn.style.display = 'none';
                    });
                }
            }

            // Initialize on page load
            initLogoUploader();

            // Re-initialize when Carbon Fields reloads content
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes.length) {
                        mutation.addedNodes.forEach(function(node) {
                            if (node.nodeType === 1 && (node.classList.contains('crb-logo-upload-btn') || node.querySelector('.crb-logo-upload-btn'))) {
                                isInitialized = false;
                                setTimeout(initLogoUploader, 100);
                            }
                        });
                    }
                });
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        });
    </script>
    <?php
}

/**
 * Handle AJAX request for cropping logo image.
 */
function crb_handle_crop_logo_ajax(): void
{
    $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
    if (!$nonce || !wp_verify_nonce($nonce, 'crb_crop_logo')) {
        wp_send_json_error(['message' => __('Invalid request.', 'a-ripple-song')], 403);
    }

    // Check user permissions
    if (!current_user_can('upload_files')) {
        wp_send_json_error(['message' => __('You do not have permission to upload files.', 'a-ripple-song')], 403);
    }

    // Verify required parameters
    if (!isset($_POST['id'])) {
        wp_send_json_error(['message' => __('Missing attachment ID.', 'a-ripple-song')], 400);
    }

    if (!isset($_POST['cropDetails'])) {
        wp_send_json_error(['message' => __('Missing crop details.', 'a-ripple-song')], 400);
    }

    $attachment_id = absint($_POST['id']);

    if (!$attachment_id) {
        wp_send_json_error(['message' => __('Invalid attachment ID.', 'a-ripple-song')], 400);
    }

    if (!current_user_can('edit_post', $attachment_id)) {
        wp_send_json_error(['message' => __('You do not have permission to edit this attachment.', 'a-ripple-song')], 403);
    }

    $crop_details = wp_unslash($_POST['cropDetails']);
    if (!is_array($crop_details)) {
        wp_send_json_error(['message' => __('Invalid crop details.', 'a-ripple-song')], 400);
    }

    $required_keys = ['x1', 'y1', 'width', 'height', 'dst_width', 'dst_height'];
    foreach ($required_keys as $required_key) {
        if (!array_key_exists($required_key, $crop_details)) {
            wp_send_json_error(['message' => __('Missing crop details.', 'a-ripple-song')], 400);
        }
    }

    $x1 = max(0, (int) $crop_details['x1']);
    $y1 = max(0, (int) $crop_details['y1']);
    $width = max(1, (int) $crop_details['width']);
    $height = max(1, (int) $crop_details['height']);
    $dst_width = max(1, (int) $crop_details['dst_width']);
    $dst_height = max(1, (int) $crop_details['dst_height']);

    // Get the original image path
    $original_path = get_attached_file($attachment_id);
    if (!$original_path || !file_exists($original_path)) {
        wp_send_json_error(['message' => __('Original image not found.', 'a-ripple-song')], 404);
    }

    // Perform the crop
    $cropped = wp_crop_image(
        $attachment_id,
        $x1,
        $y1,
        $width,
        $height,
        $dst_width,
        $dst_height
    );

    if (is_wp_error($cropped)) {
        wp_send_json_error(['message' => $cropped->get_error_message()], 500);
    }

    // Get the parent URL and construct the cropped image URL
    $parent_url = wp_get_attachment_url($attachment_id);
    if (!$parent_url) {
        @unlink($cropped);
        wp_send_json_error(['message' => __('Unable to determine attachment URL.', 'a-ripple-song')], 500);
    }
    $url = str_replace(basename($parent_url), basename($cropped), $parent_url);

    // Get image info
    $size = @getimagesize($cropped);
    $image_type = ($size) ? $size['mime'] : 'image/jpeg';

    // Create attachment for the cropped image
    $attachment = [
        'post_title' => 'crb-logo-' . basename($cropped),
        'post_content' => '',
        'post_mime_type' => $image_type,
        'guid' => $url,
        'post_parent' => $attachment_id
    ];

    $cropped_id = wp_insert_attachment($attachment, $cropped);

    if (is_wp_error($cropped_id)) {
        @unlink($cropped);
        wp_send_json_error(['message' => $cropped_id->get_error_message()], 500);
    }

    // Generate metadata
    $metadata = wp_generate_attachment_metadata($cropped_id, $cropped);
    wp_update_attachment_metadata($cropped_id, $metadata);

    wp_send_json_success([
        'url' => $url,
        'attachment_id' => $cropped_id,
        'width' => isset($metadata['width']) ? $metadata['width'] : $dst_width,
        'height' => isset($metadata['height']) ? $metadata['height'] : $dst_height
    ]);
}
