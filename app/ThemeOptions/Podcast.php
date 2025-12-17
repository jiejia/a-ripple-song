<?php

namespace App\ThemeOptions;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class PodcastOptions
{
    /**
     * Register hooks.
     */
    public static function boot(): void
    {
        add_action('carbon_fields_register_fields', [static::class, 'registerFields']);
        add_action('carbon_fields_theme_options_container_saved', [static::class, 'validateCover'], 10, 1);
        add_filter('carbon_fields_attachment_not_found_metadata', [static::class, 'previewExternalCoverUrl'], 10, 3);
    }

    /**
     * Ensure Carbon Fields "image" fields using value_type=url can still preview external URLs.
     *
     * Carbon Fields only renders a preview when the attachment metadata contains a truthy `id`.
     * For non-local URLs, Carbon cannot resolve an attachment post, so we provide lightweight
     * metadata based on the URL itself.
     *
     * @param array $attachment_metadata
     * @param int|string $id
     * @param string $type
     * @return array
     */
    public static function previewExternalCoverUrl(array $attachment_metadata, $id, string $type): array
    {
        if ($type !== 'url') {
            return $attachment_metadata;
        }

        $url = $attachment_metadata['thumb_url'] ?? '';
        if (!is_string($url) || $url === '') {
            return $attachment_metadata;
        }

        if (!preg_match('~^https?://~i', $url)) {
            return $attachment_metadata;
        }

        $path = parse_url($url, PHP_URL_PATH);
        $fileName = is_string($path) && $path !== '' ? wp_basename($path) : wp_basename($url);

        $fileTypeInfo = wp_check_filetype($fileName);
        $mime = $fileTypeInfo['type'] ?? '';

        $attachment_metadata['id'] = -1;
        $attachment_metadata['file_url'] = $url;
        $attachment_metadata['thumb_url'] = $url;
        $attachment_metadata['file_name'] = $fileName;

        if (is_string($mime) && strpos($mime, 'image/') === 0) {
            $attachment_metadata['filetype'] = $fileTypeInfo;
            $attachment_metadata['file_type'] = 'image';
        }

        return $attachment_metadata;
    }

    /**
     * Register Podcast Settings page.
     */
    public static function registerFields(): void
    {
        $parent = class_exists(GeneralOptions::class) ? GeneralOptions::getThemeContainer() : ($GLOBALS['crb_theme_settings_container'] ?? null);

        $podcast = Container::make('theme_options', __('Podcast Settings', 'sage'));

        if ($parent) {
            $podcast->set_page_parent($parent);
        }

        $podcast->add_fields([
            Field::make('text', 'crb_podcast_title', __('Podcast Title', 'sage'))
                ->set_help_text(__('Required. If empty, falls back to site title.', 'sage'))
                ->set_required(true),
            Field::make('text', 'crb_podcast_subtitle', __('Podcast Subtitle', 'sage'))
                ->set_help_text(__('Short tagline shown in some apps.', 'sage')),
            Field::make('textarea', 'crb_podcast_description', __('Podcast Description', 'sage'))
                ->set_help_text(__('Required. Plain text description of the show.', 'sage'))
                ->set_required(true),
            Field::make('text', 'crb_podcast_author', __('Podcast Author (itunes:author)', 'sage'))
                ->set_help_text(__('Required. Displayed as show author in directories.', 'sage'))
                ->set_required(true),
            Field::make('text', 'crb_podcast_owner_name', __('Owner Name', 'sage'))
                ->set_help_text(__('Required. For <itunes:owner><itunes:name>.', 'sage'))
                ->set_required(true),
            Field::make('text', 'crb_podcast_owner_email', __('Owner Email', 'sage'))
                ->set_attribute('type', 'email')
                ->set_attribute('pattern', '[^@\\s]+@[^@\\s]+\\.[^@\\s]+')
                ->set_help_text(__('Required. For <itunes:owner><itunes:email>. Use a monitored inbox.', 'sage'))
                ->set_required(true),
            Field::make('image', 'crb_podcast_cover', __('Podcast Cover (1400–3000px square)', 'sage'))
                ->set_value_type('url')
                ->set_help_text(__('Required. Square JPG/PNG between 1400–3000px for <itunes:image>. Will be validated on save.', 'sage'))
                ->set_required(true),
            Field::make('select', 'crb_podcast_explicit', __('Default Explicit Flag', 'sage'))
                ->set_options([
                    'clean' => __('clean (no explicit content)', 'sage'),
                    'explicit' => __('explicit', 'sage'),
                ])
                ->set_default_value('clean')
                ->set_help_text(__('Required. Single-episode value can override.', 'sage'))
                ->set_required(true),
            Field::make('select', 'crb_podcast_language', __('Language (RSS <language>)', 'sage'))
                ->set_options(static::getPodcastLanguageOptions())
                ->set_default_value(get_bloginfo('language') ?: 'en-US')
                ->set_help_text(__('RFC 5646 language tag, e.g. en-US, zh-CN.', 'sage'))
                ->set_required(true),
            Field::make('select', 'crb_podcast_category_primary', __('Primary Category', 'sage'))
                ->set_options(static::getItunesCategories())
                ->set_help_text(__('Required. Use Apple’s category list.', 'sage'))
                ->set_required(true),
            Field::make('select', 'crb_podcast_category_secondary', __('Secondary Category (optional)', 'sage'))
                ->set_options(static::getItunesCategories())
                ->set_help_text(__('Optional second category.', 'sage')),
            Field::make('text', 'crb_podcast_copyright', __('Copyright (optional)', 'sage'))
                ->set_help_text(__('Example: © 2025 Your Studio. Plain text.', 'sage')),
            Field::make('select', 'crb_podcast_locked', __('podcast:locked', 'sage'))
                ->set_options([
                    'yes' => __('yes (recommended, prevents unauthorized moves)', 'sage'),
                    'no' => __('no', 'sage'),
                ])
                ->set_default_value('yes')
                ->set_help_text(__('Podcasting 2.0: lock feed to this publisher.', 'sage')),
            Field::make('text', 'crb_podcast_guid', __('podcast:guid (optional)', 'sage'))
                ->set_help_text(__('Podcasting 2.0 GUID. If empty, feed will use site URL as fallback.', 'sage')),
        ]);
    }

    /**
     * Podcast language options.
     *
     * @return array<string, string>
     */
    public static function getPodcastLanguageOptions(): array
    {
        return [
            'en-US' => 'en-US',
            'en-GB' => 'en-GB',
            'en-AU' => 'en-AU',
            'en-CA' => 'en-CA',
            'zh-CN' => 'zh-CN',
            'zh-TW' => 'zh-TW',
            'ja-JP' => 'ja-JP',
            'ko-KR' => 'ko-KR',
            'fr-FR' => 'fr-FR',
            'de-DE' => 'de-DE',
            'es-ES' => 'es-ES',
            'es-MX' => 'es-MX',
            'pt-BR' => 'pt-BR',
            'ru-RU' => 'ru-RU',
        ];
    }

    /**
     * Simplified Apple Podcasts categories.
     *
     * @return array<string, string>
     */
    public static function getItunesCategories(): array
    {
        return [
            'Arts' => 'Arts',
            'Arts::Books' => 'Arts → Books',
            'Arts::Design' => 'Arts → Design',
            'Arts::Fashion & Beauty' => 'Arts → Fashion & Beauty',
            'Arts::Food' => 'Arts → Food',
            'Arts::Performing Arts' => 'Arts → Performing Arts',
            'Arts::Visual Arts' => 'Arts → Visual Arts',
            'Business' => 'Business',
            'Business::Careers' => 'Business → Careers',
            'Business::Entrepreneurship' => 'Business → Entrepreneurship',
            'Business::Investing' => 'Business → Investing',
            'Business::Management' => 'Business → Management',
            'Business::Marketing' => 'Business → Marketing',
            'Business::Non-Profit' => 'Business → Non-Profit',
            'Comedy' => 'Comedy',
            'Comedy::Comedy Interviews' => 'Comedy → Comedy Interviews',
            'Comedy::Improv' => 'Comedy → Improv',
            'Comedy::Stand-Up' => 'Comedy → Stand-Up',
            'Education' => 'Education',
            'Education::Courses' => 'Education → Courses',
            'Education::How To' => 'Education → How To',
            'Education::Language Learning' => 'Education → Language Learning',
            'Education::Self-Improvement' => 'Education → Self-Improvement',
            'Fiction' => 'Fiction',
            'Fiction::Comedy Fiction' => 'Fiction → Comedy Fiction',
            'Fiction::Drama' => 'Fiction → Drama',
            'Fiction::Science Fiction' => 'Fiction → Science Fiction',
            'Government' => 'Government',
            'History' => 'History',
            'Health & Fitness' => 'Health & Fitness',
            'Health & Fitness::Alternative Health' => 'Health & Fitness → Alternative Health',
            'Health & Fitness::Fitness' => 'Health & Fitness → Fitness',
            'Health & Fitness::Medicine' => 'Health & Fitness → Medicine',
            'Health & Fitness::Mental Health' => 'Health & Fitness → Mental Health',
            'Health & Fitness::Nutrition' => 'Health & Fitness → Nutrition',
            'Health & Fitness::Sexuality' => 'Health & Fitness → Sexuality',
            'Kids & Family' => 'Kids & Family',
            'Kids & Family::Education for Kids' => 'Kids & Family → Education for Kids',
            'Kids & Family::Parenting' => 'Kids & Family → Parenting',
            'Kids & Family::Pets & Animals' => 'Kids & Family → Pets & Animals',
            'Kids & Family::Stories for Kids' => 'Kids & Family → Stories for Kids',
            'Leisure' => 'Leisure',
            'Leisure::Animation & Manga' => 'Leisure → Animation & Manga',
            'Leisure::Automotive' => 'Leisure → Automotive',
            'Leisure::Aviation' => 'Leisure → Aviation',
            'Leisure::Crafts' => 'Leisure → Crafts',
            'Leisure::Games' => 'Leisure → Games',
            'Leisure::Hobbies' => 'Leisure → Hobbies',
            'Leisure::Home & Garden' => 'Leisure → Home & Garden',
            'Leisure::Video Games' => 'Leisure → Video Games',
            'Music' => 'Music',
            'Music::Music Commentary' => 'Music → Music Commentary',
            'Music::Music History' => 'Music → Music History',
            'Music::Music Interviews' => 'Music → Music Interviews',
            'News' => 'News',
            'News::Business News' => 'News → Business News',
            'News::Daily News' => 'News → Daily News',
            'News::Entertainment News' => 'News → Entertainment News',
            'News::Politics' => 'News → Politics',
            'News::Sports News' => 'News → Sports News',
            'News::Tech News' => 'News → Tech News',
            'Religion & Spirituality' => 'Religion & Spirituality',
            'Religion & Spirituality::Buddhism' => 'Religion & Spirituality → Buddhism',
            'Religion & Spirituality::Christianity' => 'Religion & Spirituality → Christianity',
            'Religion & Spirituality::Hinduism' => 'Religion & Spirituality → Hinduism',
            'Religion & Spirituality::Islam' => 'Religion & Spirituality → Islam',
            'Religion & Spirituality::Judaism' => 'Religion & Spirituality → Judaism',
            'Religion & Spirituality::Religion' => 'Religion & Spirituality → Religion',
            'Religion & Spirituality::Spirituality' => 'Religion & Spirituality → Spirituality',
            'Science' => 'Science',
            'Science::Astronomy' => 'Science → Astronomy',
            'Science::Chemistry' => 'Science → Chemistry',
            'Science::Earth Sciences' => 'Science → Earth Sciences',
            'Science::Life Sciences' => 'Science → Life Sciences',
            'Science::Mathematics' => 'Science → Mathematics',
            'Science::Natural Sciences' => 'Science → Natural Sciences',
            'Science::Nature' => 'Science → Nature',
            'Science::Physics' => 'Science → Physics',
            'Society & Culture' => 'Society & Culture',
            'Society & Culture::Documentary' => 'Society & Culture → Documentary',
            'Society & Culture::Personal Journals' => 'Society & Culture → Personal Journals',
            'Society & Culture::Philosophy' => 'Society & Culture → Philosophy',
            'Society & Culture::Places & Travel' => 'Society & Culture → Places & Travel',
            'Society & Culture::Relationships' => 'Society & Culture → Relationships',
            'Sports' => 'Sports',
            'Sports::Baseball' => 'Sports → Baseball',
            'Sports::Basketball' => 'Sports → Basketball',
            'Sports::Cricket' => 'Sports → Cricket',
            'Sports::Fantasy Sports' => 'Sports → Fantasy Sports',
            'Sports::Football' => 'Sports → Football',
            'Sports::Golf' => 'Sports → Golf',
            'Sports::Hockey' => 'Sports → Hockey',
            'Sports::Rugby' => 'Sports → Rugby',
            'Sports::Running' => 'Sports → Running',
            'Sports::Soccer' => 'Sports → Soccer',
            'Sports::Swimming' => 'Sports → Swimming',
            'Sports::Tennis' => 'Sports → Tennis',
            'Sports::Volleyball' => 'Sports → Volleyball',
            'Sports::Wilderness' => 'Sports → Wilderness',
            'Sports::Wrestling' => 'Sports → Wrestling',
            'Technology' => 'Technology',
            'True Crime' => 'True Crime',
            'TV & Film' => 'TV & Film',
            'TV & Film::After Shows' => 'TV & Film → After Shows',
            'TV & Film::Film History' => 'TV & Film → Film History',
            'TV & Film::Film Interviews' => 'TV & Film → Film Interviews',
            'TV & Film::Film Reviews' => 'TV & Film → Film Reviews',
            'TV & Film::TV Reviews' => 'TV & Film → TV Reviews',
        ];
    }

    /**
     * Validate podcast cover size (1400–3000px square) before save.
     */
    public static function validateCover($container): void
    {
        if (!is_object($container) || !method_exists($container, 'get_title')) {
            return;
        }
        if ($container->get_title() !== __('Podcast Settings', 'sage')) {
            return;
        }

        $cover_url = carbon_get_theme_option('crb_podcast_cover');
        if (empty($cover_url)) {
            return;
        }

        $upload_dir = wp_get_upload_dir();
        $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $cover_url);

        if (!file_exists($file_path)) {
            $parsed = parse_url($cover_url);
            if (isset($parsed['path'])) {
                $file_path = ABSPATH . ltrim($parsed['path'], '/');
            }
        }

        if (!file_exists($file_path)) {
            wp_die(
                __('Podcast Cover file not found. Please re-upload.', 'sage'),
                __('Podcast Cover validation failed', 'sage'),
                ['back_link' => true]
            );
        }

        $image_info = @getimagesize($file_path);
        if (!$image_info) {
            wp_die(
                __('Podcast Cover is not a valid image.', 'sage'),
                __('Podcast Cover validation failed', 'sage'),
                ['back_link' => true]
            );
        }

        [$width, $height, $type] = $image_info;
        $min = 1400;
        $max = 3000;

        $mime = $image_info['mime'] ?? '';
        $allowed = ['image/jpeg', 'image/png'];
        if (!in_array($mime, $allowed, true)) {
            wp_die(
                __('Podcast Cover must be JPG or PNG.', 'sage'),
                __('Podcast Cover validation failed', 'sage'),
                ['back_link' => true]
            );
        }

        if ($width !== $height) {
            wp_die(
                sprintf(__('Podcast Cover must be square. Current: %1$dx%2$d.', 'sage'), $width, $height),
                __('Podcast Cover validation failed', 'sage'),
                ['back_link' => true]
            );
        }

        if ($width < $min || $width > $max) {
            wp_die(
                sprintf(__('Podcast Cover must be between %1$d and %2$d px. Current: %3$d px.', 'sage'), $min, $max, $width),
                __('Podcast Cover validation failed', 'sage'),
                ['back_link' => true]
            );
        }
    }
}

// Bootstrap this options module.
PodcastOptions::boot();
