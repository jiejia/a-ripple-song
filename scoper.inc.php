<?php

declare(strict_types=1);

use Symfony\Component\Finder\Finder;

$arsInputDir = getenv('ARS_SCOPER_INPUT_DIR') ?: __DIR__;
$arsThemePrefix = 'aripplesong';
$arsExcludedFiles = [];

$arsCarbonFieldsTemplatesDir = $arsInputDir . '/vendor/htmlburger/carbon-fields/templates';
if (is_dir($arsCarbonFieldsTemplatesDir)) {
    // Carbon Fields templates can start with HTML before the first PHP tag.
    // Prefixing those files injects a namespace after output and breaks parsing.
    $arsExcludedFiles = array_merge(
        $arsExcludedFiles,
        array_map(
            static fn (SplFileInfo $fileInfo): string => $fileInfo->getPathname(),
            iterator_to_array(
                Finder::create()
                    ->files()
                    ->in($arsCarbonFieldsTemplatesDir)
                    ->name('*.php'),
                false
            )
        )
    );
}

$arsVendorDir = $arsInputDir . '/vendor';
if (is_dir($arsVendorDir)) {
    // Some vendor PHP view templates intentionally start with HTML before PHP.
    // PHP-Scoper cannot safely inject a namespace into those files.
    $arsExcludedFiles = array_merge(
        $arsExcludedFiles,
        array_map(
            static fn (SplFileInfo $fileInfo): string => $fileInfo->getPathname(),
            array_filter(
                iterator_to_array(
                    Finder::create()
                        ->files()
                        ->in($arsVendorDir)
                        ->name('*.php'),
                    false
                ),
                static function (SplFileInfo $fileInfo): bool {
                    $contents = file_get_contents($fileInfo->getPathname(), false, null, 0, 256);

                    return is_string($contents) && ! str_starts_with(ltrim($contents), '<?php');
                }
            )
        )
    );
}

return [
    'prefix' => 'Jiejia\\ARippleSong\\Vendor',

    'output-dir' => 'build/scoped',

    'finders' => [
        Finder::create()
            ->files()
            ->ignoreVCS(true)
            ->exclude([
                '.github',
                '.idea',
                'bin',
                'build',
                'dist',
                'node_modules',
                'tests',
                'Tests',
                'vendor-bin',
            ])
            ->name('*.php')
            ->notName('*.blade.php')
            ->notName('scoper.inc.php')
            ->in($arsInputDir),
    ],

    'exclude-files' => [
        ...$arsExcludedFiles,
    ],

    'expose-global-classes' => true,
    'expose-global-functions' => true,
    'expose-global-constants' => true,

    'exclude-namespaces' => [
        // Keep theme source classes stable for Blade templates and WordPress callbacks.
        'App',
    ],

    'exclude-classes' => [
        // WordPress core classes must resolve from the global namespace.
        '~^Walker_~',
        '~^WP_~',
        'getID3',
        'wpdb',
    ],

    'exclude-functions' => [
        // WordPress core functions must resolve from the global namespace.
        '__',
        '_e',
        '_n',
        '_x',
        'absint',
        'add_action',
        'add_filter',
        'add_query_arg',
        'admin_url',
        'apply_filters',
        'body_class',
        'checked',
        'comments_template',
        'content_url',
        'current_time',
        'delete_post_meta',
        'did_action',
        'do_action',
        'esc_attr',
        'esc_html',
        'esc_url',
        'get_author_posts_url',
        'get_avatar_url',
        'get_bloginfo',
        'get_categories',
        'get_comment_date',
        'get_comment_time',
        'get_comments_number',
        'get_current_screen',
        'get_current_user_id',
        'get_edit_post_link',
        'get_locale',
        'get_option',
        'get_permalink',
        'get_post',
        'get_post_meta',
        'get_post_time',
        'get_post_type',
        'get_post_type_archive_link',
        'get_post_type_object',
        'get_posts',
        'get_queried_object',
        'get_query_var',
        'get_search_form',
        'get_stylesheet_directory',
        'get_template_directory',
        'get_template_directory_uri',
        'get_the_author',
        'get_the_author_meta',
        'get_the_category',
        'get_the_date',
        'get_the_excerpt',
        'get_the_ID',
        'get_the_post_thumbnail_url',
        'get_the_tags',
        'get_the_title',
        'get_the_terms',
        'get_the_time',
        'get_theme_mod',
        'get_transient',
        'home_url',
        'is_admin',
        'is_archive',
        'is_author',
        'is_category',
        'is_feed',
        'is_front_page',
        'is_home',
        'is_page',
        'is_search',
        'is_single',
        'is_singular',
        'is_tag',
        'is_tax',
        'is_user_logged_in',
        'load_theme_textdomain',
        'locate_template',
        'metadata_exists',
        'nocache_headers',
        'paginate_links',
        'post_class',
        'register_nav_menus',
        'register_post_meta',
        'register_post_type',
        'register_rest_route',
        'register_sidebar',
        'register_taxonomy',
        'remove_action',
        'remove_submenu_page',
        'sanitize_key',
        'sanitize_text_field',
        'set_transient',
        'single_cat_title',
        'single_tag_title',
        'single_term_title',
        'status_header',
        'trailingslashit',
        'untrailingslashit',
        'update_option',
        'update_post_meta',
        'wp_add_inline_script',
        'wp_body_open',
        'wp_create_nonce',
        'wp_die',
        'wp_enqueue_media',
        'wp_enqueue_script',
        'wp_enqueue_style',
        'wp_get_attachment_image_url',
        'wp_get_current_user',
        'wp_get_nav_menu_items',
        'wp_get_post_terms',
        'wp_get_theme',
        'wp_head',
        'wp_json_encode',
        'wp_localize_script',
        'wp_next_scheduled',
        'wp_parse_args',
        'wp_reset_postdata',
        'wp_safe_redirect',
        'wp_schedule_event',
        'wp_set_script_translations',
        'wp_strip_all_tags',
        'wp_unslash',
        // Theme helper functions are intentionally public globals.
        'aripplesong_bump_participation_cache_version',
        'aripplesong_episode_post_type',
        'aripplesong_extract_multicheck_user_ids',
        'aripplesong_get_episode_meta',
        'aripplesong_get_latest_playlist_data',
        'aripplesong_get_participated_podcast_ids',
        'aripplesong_include_episode_post_type_in_tag_archive',
        'aripplesong_truncate_excerpt',
        'calculate_user_post_count',
        'get_episode_data',
        'get_localized_comment_date',
        'get_localized_date',
        'get_post_all_authors',
        'get_primary_navigation_menu_items',
        'get_user_all_post_ids',
        'is_menu_item_active',
        'modify_author_archive_query',
    ],

    'exclude-constants' => [
        // WordPress environment constants must resolve from the global namespace.
        'ABSPATH',
        '~^DOING_~',
        '~^WP_~',
        'SCRIPT_DEBUG',
        'SITE_ID_CURRENT_SITE',
    ],

    'patchers' => [
        static function (string $filePath, string $prefix, string $contents) use ($arsThemePrefix): string {
            $normalizedPath = str_replace('\\', '/', $filePath);

            if (
                preg_match('~/((functions|index|searchform)\\.php)$~', $normalizedPath) === 1
                || str_ends_with($normalizedPath, '/app/helpers.php')
            ) {
                // Keep WordPress entry/template files in the global namespace.
                $contents = str_replace(
                    "namespace Jiejia\\ARippleSong\\Vendor;\n\n",
                    '',
                    $contents
                );
            }

            if (str_ends_with($normalizedPath, '/vendor/composer/autoload_real.php')) {
                // Composer's ClassLoader is scoped, so the generated callback must compare against the scoped class name.
                $contents = str_replace(
                    "if ('Composer\\Autoload\\ClassLoader' === \$class) {",
                    "if (__NAMESPACE__ . '\\Composer\\Autoload\\ClassLoader' === \$class || 'Composer\\Autoload\\ClassLoader' === \$class) {",
                    $contents
                );

                $contents = preg_replace(
                    "/spl_autoload_(register|unregister)\\(array\\([^)]*'loadClassLoader'\\)(, \\\\true, \\\\true)?\\)/",
                    "spl_autoload_$1(array(__CLASS__, 'loadClassLoader')$2)",
                    $contents
                ) ?? $contents;
            }

            if (str_ends_with($normalizedPath, '/app/Abstracts/SettingAbstract.php')) {
                // Keep Carbon Fields helper calls aligned with the scoped Carbon Fields function namespace.
                $contents = str_replace(
                    'carbon_get_theme_option($this->fieldName((string) $settingKey))',
                    '\\Jiejia\\ARippleSong\\Vendor\\carbon_get_theme_option($this->fieldName((string) $settingKey))',
                    $contents
                );
            }

            if (str_contains($normalizedPath, '/vendor/htmlburger/carbon-fields/')) {
                // Isolate Carbon Fields internals without double-prefixing theme-owned hooks.
                return preg_replace(
                    '/(?<!' . preg_quote($arsThemePrefix, '/') . '_)carbon_fields_(?!core__)/',
                    $arsThemePrefix . '_carbon_fields_',
                    $contents
                ) ?? $contents;
            }

            return $contents;
        },
    ],
];
