<?php

/**
 * Theme Options using Carbon Fields.
 * 
 * This file registers all theme options using Carbon Fields library.
 * Creates a top-level menu "Theme Settings" with sub-pages for different options.
 */

namespace App;

use Carbon_Fields\Container;
use Carbon_Fields\Field;
use Carbon_Fields\Carbon_Fields;

/**
 * Boot Carbon Fields library.
 *
 * @return void
 */
add_action('after_setup_theme', function () {
    Carbon_Fields::boot();
});

/**
 * Register all Carbon Fields containers and fields.
 *
 * @return void
 */
add_action('carbon_fields_register_fields', function () {
    // Main Theme Settings container (top-level menu)
    $theme_settings = Container::make('theme_options', __('Theme Settings', 'sage'))
        ->set_icon('dashicons-admin-settings')
        ->set_page_menu_position(60)
        ->add_fields([
            Field::make('image', 'crb_site_logo', __('Site Logo', 'sage'))
                ->set_value_type('url')
                ->set_help_text(__('Upload a logo image. If no logo is set, the site title with icon will be displayed.', 'sage')),
            Field::make('html', 'crb_light_theme_picker', __('Light Theme', 'sage'))
                ->set_html(
                    sprintf(
                        '<div class="crb-theme-heading">%s</div>%s',
                        esc_html__('Light Theme', 'sage'),
                        crb_render_daisyui_theme_picker('light')
                    )
                )
                ->set_help_text(__('Click any card to choose the light theme.', 'sage')),
            Field::make('select', 'crb_light_theme', __('Light Theme (fallback)', 'sage'))
                ->set_options(crb_get_daisyui_light_themes())
                ->set_default_value('retro')
                ->set_help_text(__('If the card picker is unavailable, use this dropdown (default: retro).', 'sage'))
                ->set_classes('crb-theme-select')
                ->set_attribute('data-theme-target', 'light'),
            Field::make('html', 'crb_dark_theme_picker', __('Dark Theme', 'sage'))
                ->set_html(
                    sprintf(
                        '<div class="crb-theme-heading">%s</div>%s',
                        esc_html__('Dark Theme', 'sage'),
                        crb_render_daisyui_theme_picker('dark')
                    )
                )
                ->set_help_text(__('Click any card to choose the dark theme.', 'sage')),
            Field::make('select', 'crb_dark_theme', __('Dark Theme (fallback)', 'sage'))
                ->set_options(crb_get_daisyui_dark_themes())
                ->set_default_value('dim')
                ->set_help_text(__('If the card picker is unavailable, use this dropdown (default: dim).', 'sage'))
                ->set_classes('crb-theme-select')
                ->set_attribute('data-theme-target', 'dark'),
            Field::make('header_scripts', 'crb_header_scripts', __('Header Scripts', 'sage'))
                ->set_help_text(esc_html__('Scripts to be added in the <head> section. You can include complete <script> tags for services like Google Analytics.', 'sage')),
            Field::make('footer_scripts', 'crb_footer_scripts', __('Footer Scripts', 'sage'))
                ->set_help_text(esc_html__('Scripts to be added before </body>. You can include complete <script> tags.', 'sage')),
        ]);

    // Social Links sub-page
    Container::make('theme_options', __('Social Links', 'sage'))
        ->set_page_parent($theme_settings)
        ->add_fields(crb_get_social_links_fields());

});

/**
 * Get social links fields configuration.
 *
 * @return array
 */
function crb_get_social_links_fields()
{
    $platforms = [
        'facebook' => [
            'label' => __('Facebook', 'sage'),
            'placeholder' => 'https://facebook.com/yourpage',
        ],
        'twitter' => [
            'label' => __('Twitter / X', 'sage'),
            'placeholder' => 'https://twitter.com/yourhandle',
        ],
        'instagram' => [
            'label' => __('Instagram', 'sage'),
            'placeholder' => 'https://instagram.com/yourhandle',
        ],
        'linkedin' => [
            'label' => __('LinkedIn', 'sage'),
            'placeholder' => 'https://linkedin.com/in/yourprofile',
        ],
        'youtube' => [
            'label' => __('YouTube', 'sage'),
            'placeholder' => 'https://youtube.com/@yourchannel',
        ],
        'tiktok' => [
            'label' => __('TikTok', 'sage'),
            'placeholder' => 'https://tiktok.com/@yourhandle',
        ],
        'pinterest' => [
            'label' => __('Pinterest', 'sage'),
            'placeholder' => 'https://pinterest.com/yourhandle',
        ],
        'threads' => [
            'label' => __('Threads', 'sage'),
            'placeholder' => 'https://threads.net/@yourhandle',
        ],
        'weibo' => [
            'label' => __('Weibo', 'sage'),
            'placeholder' => 'https://weibo.com/yourpage',
        ],
        'wechat' => [
            'label' => __('WeChat', 'sage'),
            'placeholder' => 'WeChat ID or QR code link',
        ],
        'rss' => [
            'label' => __('RSS Feed', 'sage'),
            'placeholder' => '/feed/',
        ],
    ];

    $fields = [
        Field::make('html', 'crb_social_links_info')
            ->set_html(sprintf(
                '<p>%s</p>',
                __('Configure your social media links. Leave empty to hide a platform.', 'sage')
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
        'light' => 'light',
        'cupcake' => 'cupcake',
        'bumblebee' => 'bumblebee',
        'emerald' => 'emerald',
        'corporate' => 'corporate',
        'retro' => 'retro',
        'valentine' => 'valentine',
        'garden' => 'garden',
        'lofi' => 'lofi',
        'pastel' => 'pastel',
        'fantasy' => 'fantasy',
        'wireframe' => 'wireframe',
        'cmyk' => 'cmyk',
        'autumn' => 'autumn',
        'acid' => 'acid',
        'lemonade' => 'lemonade',
        'winter' => 'winter',
        'caramellatte' => 'caramellatte',
        'silk' => 'silk',
        'cyberpunk' => 'cyberpunk',
        'node' => 'node',
        'nord' => 'nord',
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
        'dark' => 'dark',
        'synthwave' => 'synthwave',
        'aqua' => 'aqua',
        'halloween' => 'halloween',
        'forest' => 'forest',
        'black' => 'black',
        'luxury' => 'luxury',
        'dracula' => 'dracula',
        'business' => 'business',
        'coffee' => 'coffee',
        'dim' => 'dim',
        'abyss' => 'abyss',
        'sunset' => 'sunset',
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
    static $cache = null;

    if ($cache === null) {
        $path = get_template_directory() . '/app/daisyui-colors.json';
        $data = file_exists($path) ? json_decode(file_get_contents($path), true) : null;
        $cache = $data['default'] ?? [];
    }

    $palette = [];

    foreach ($slugs as $slug) {
        if (!isset($cache[$slug])) {
            continue;
        }

        $theme = $cache[$slug];

        $palette[$slug] = [
            'base100' => $theme['--color-base-100'] ?? '#f3f4f6',
            'base200' => $theme['--color-base-200'] ?? '#e5e7eb',
            'base300' => $theme['--color-base-300'] ?? '#d1d5db',
            'baseContent' => $theme['--color-base-content'] ?? '#111827',
            'primary' => $theme['--color-primary'] ?? '#570df8',
            'primaryContent' => $theme['--color-primary-content'] ?? '#ffffff',
            'secondary' => $theme['--color-secondary'] ?? '#f000b8',
            'secondaryContent' => $theme['--color-secondary-content'] ?? '#ffffff',
            'accent' => $theme['--color-accent'] ?? '#37cdbe',
            'accentContent' => $theme['--color-accent-content'] ?? '#ffffff',
            'neutral' => $theme['--color-neutral'] ?? '#3d4451',
            'neutralContent' => $theme['--color-neutral-content'] ?? '#f3f4f6',
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

add_action('admin_head', function () {
    // 在部分非英文语言下，Carbon Fields 选项页的 page 参数可能变化，导致样式未输出。
    // 为确保管理员后台都能看到主题卡片样式，这里直接在 admin 页输出（体量很小，影响可忽略）。
    if (!is_admin()) {
        return;
    }

    crb_output_daisyui_theme_picker_assets();
});

