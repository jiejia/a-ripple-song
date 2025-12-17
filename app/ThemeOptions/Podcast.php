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
        add_filter('carbon_fields_should_save_field_value', [static::class, 'validateCoverFieldValue'], 10, 3);
        add_action('admin_notices', [static::class, 'displayCoverValidationErrors']);
        add_filter('carbon_fields_attachment_not_found_metadata', [static::class, 'previewExternalCoverUrl'], 10, 3);
        // Intercept REST API to validate before save
        add_filter('rest_pre_dispatch', [static::class, 'validateCoverOnRestSave'], 10, 3);
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
                ->set_help_text(__('Required. Square JPG/PNG between 1400–3000px for <itunes:image>. Apple recommends keeping the file under 512KB. Will be validated on save.', 'sage'))
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
     * Validate podcast cover field value before saving.
     *
     * @param mixed $save Whether to save the field value
     * @param mixed $value The field value being saved
     * @param \Carbon_Fields\Field\Field $field The field object
     * @return mixed
     */
    public static function validateCoverFieldValue($save, $value, $field)
    {
        // Only validate the podcast cover field
        if (!is_object($field) || !method_exists($field, 'get_base_name')) {
            return $save;
        }

        $field_name = $field->get_base_name();

        // Carbon Fields prefixes field names with _
        if ($field_name !== 'crb_podcast_cover' && $field_name !== '_crb_podcast_cover') {
            return $save;
        }

        // If value is empty, let Carbon Fields handle required validation
        if (empty($value)) {
            return $save;
        }

        $validation_result = static::validateCoverImage((string) $value);

        if (is_wp_error($validation_result)) {
            // Store error in transient for display
            $user_id = get_current_user_id();
            set_transient('crb_podcast_cover_error_' . $user_id, $validation_result->get_error_message(), 120);

            // Return false to prevent saving this field
            return false;
        }

        // Clear any previous error for this user
        delete_transient('crb_podcast_cover_error_' . get_current_user_id());

        return $save;
    }

    /**
     * Validate podcast cover on REST API save request.
     *
     * @param mixed $result Response to replace the requested version with
     * @param \WP_REST_Server $server Server instance
     * @param \WP_REST_Request $request Request used to generate the response
     * @return mixed
     */
    public static function validateCoverOnRestSave($result, $server, $request)
    {
        // Only intercept Carbon Fields save requests
        $route = $request->get_route();
        if (strpos($route, '/carbon-fields/') === false) {
            return $result;
        }

        // Only intercept POST/PUT requests (save operations)
        $method = $request->get_method();
        if (!in_array($method, ['POST', 'PUT'], true)) {
            return $result;
        }

        // Get the request parameters (Carbon Fields sends data as JSON body or form data)
        $params = $request->get_json_params();
        if (empty($params)) {
            $params = $request->get_body_params();
        }
        if (empty($params)) {
            $body = $request->get_body();
            $params = json_decode($body, true) ?: [];
        }

        if (!is_array($params)) {
            return $result;
        }

        // Look for podcast cover field in the data
        $cover_url = static::findCoverUrlInData($params);

        // If no cover URL found, let the request proceed
        if (empty($cover_url)) {
            return $result;
        }

        // Validate the cover image
        $validation_result = static::validateCoverImage((string) $cover_url);

        if (is_wp_error($validation_result)) {
            // Return error response
            return new \WP_Error(
                'podcast_cover_validation_failed',
                $validation_result->get_error_message(),
                ['status' => 400]
            );
        }

        return $result;
    }

    /**
     * Find podcast cover URL in request data (supports various data structures).
     *
     * @param array $data Request data
     * @return string|null Cover URL or null if not found
     */
    private static function findCoverUrlInData(array $data): ?string
    {
        // Direct field keys
        $field_keys = ['_crb_podcast_cover', 'crb_podcast_cover'];
        foreach ($field_keys as $key) {
            if (isset($data[$key]) && is_string($data[$key]) && $data[$key] !== '') {
                return $data[$key];
            }
        }

        // Check in 'fields' array (common Carbon Fields structure)
        if (isset($data['fields']) && is_array($data['fields'])) {
            foreach ($data['fields'] as $field) {
                if (!is_array($field)) {
                    continue;
                }
                $name = $field['name'] ?? ($field['base_name'] ?? ($field['field_name'] ?? ''));
                if (in_array($name, $field_keys, true) && isset($field['value'])) {
                    return (string) $field['value'];
                }
            }
        }

        // Check in nested containers (Theme Options may have container ID as key)
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $found = static::findCoverUrlInData($value);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }

    /**
     * Display validation errors as admin notices.
     */
    public static function displayCoverValidationErrors(): void
    {
        $user_id = get_current_user_id();
        $error = get_transient('crb_podcast_cover_error_' . $user_id);

        if (!$error) {
            return;
        }

        printf(
            '<div class="notice notice-error is-dismissible"><p><strong>%s</strong> %s</p></div>',
            esc_html__('Podcast Cover validation failed:', 'sage'),
            esc_html($error)
        );

        // Clear the error after displaying
        delete_transient('crb_podcast_cover_error_' . $user_id);
    }

    /**
     * Validate cover image from URL (local or remote).
     *
     * Requirements:
     * - Resolution: 1400x1400 to 3000x3000 pixels
     * - Must be square (width === height)
     * - File size: < 512KB
     * - Format: JPG or PNG
     *
     * @param string $url Image URL (local or remote)
     * @return true|\WP_Error True on success, WP_Error on failure
     */
    public static function validateCoverImage(string $url)
    {
        $max_bytes = 512 * 1024; // 512KB
        $min_dimension = 1400;
        $max_dimension = 3000;
        $allowed_mimes = ['image/jpeg', 'image/png'];

        // Try to resolve as local file first
        $file_path = static::resolveLocalFilePath($url);

        if ($file_path !== null && file_exists($file_path)) {
            // Local file validation
            return static::validateLocalCoverFile($file_path, $max_bytes, $min_dimension, $max_dimension, $allowed_mimes);
        }

        // Remote URL validation
        if (!preg_match('~^https?://~i', $url)) {
            return new \WP_Error('invalid_url', __('Podcast Cover URL is invalid.', 'sage'));
        }

        return static::validateRemoteCoverUrl($url, $max_bytes, $min_dimension, $max_dimension, $allowed_mimes);
    }

    /**
     * Try to resolve URL to a local file path.
     *
     * @param string $url
     * @return string|null Local file path or null if not found
     */
    private static function resolveLocalFilePath(string $url): ?string
    {
        $upload_dir = wp_get_upload_dir();

        // Check if URL matches upload directory
        if (strpos($url, $upload_dir['baseurl']) === 0) {
            $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $url);
            if (file_exists($file_path)) {
                return $file_path;
            }
        }

        // Try site URL match
        $site_url = site_url();
        if (strpos($url, $site_url) === 0) {
            $relative_path = str_replace($site_url, '', $url);
            $file_path = ABSPATH . ltrim($relative_path, '/');
            if (file_exists($file_path)) {
                return $file_path;
            }
        }

        // Try home URL match
        $home_url = home_url();
        if (strpos($url, $home_url) === 0) {
            $relative_path = str_replace($home_url, '', $url);
            $file_path = ABSPATH . ltrim($relative_path, '/');
            if (file_exists($file_path)) {
                return $file_path;
            }
        }

        // Parse URL path as fallback
        $parsed = wp_parse_url($url);
        if (isset($parsed['path'])) {
            $file_path = ABSPATH . ltrim($parsed['path'], '/');
            if (file_exists($file_path)) {
                return $file_path;
            }
        }

        return null;
    }

    /**
     * Validate a local cover file.
     *
     * @param string $file_path
     * @param int $max_bytes
     * @param int $min_dimension
     * @param int $max_dimension
     * @param array $allowed_mimes
     * @return true|\WP_Error
     */
    private static function validateLocalCoverFile(
        string $file_path,
        int $max_bytes,
        int $min_dimension,
        int $max_dimension,
        array $allowed_mimes
    ) {
        // Check file size
        $file_size = @filesize($file_path);
        if (is_int($file_size) && $file_size > $max_bytes) {
            return new \WP_Error(
                'file_too_large',
                sprintf(
                    __('Podcast Cover file is too large (%1$s). Please compress it to under %2$s.', 'sage'),
                    size_format($file_size),
                    size_format($max_bytes)
                )
            );
        }

        // Get image info
        $image_info = @getimagesize($file_path);
        if (!$image_info) {
            return new \WP_Error('invalid_image', __('Podcast Cover is not a valid image.', 'sage'));
        }

        return static::validateImageDimensions($image_info, $min_dimension, $max_dimension, $allowed_mimes);
    }

    /**
     * Validate a remote cover URL.
     *
     * @param string $url
     * @param int $max_bytes
     * @param int $min_dimension
     * @param int $max_dimension
     * @param array $allowed_mimes
     * @return true|\WP_Error
     */
    private static function validateRemoteCoverUrl(
        string $url,
        int $max_bytes,
        int $min_dimension,
        int $max_dimension,
        array $allowed_mimes
    ) {
        // First, try HEAD request to check Content-Length
        $head_response = wp_remote_head($url, [
            'timeout' => 10,
            'redirection' => 5,
            'sslverify' => false,
        ]);

        if (!is_wp_error($head_response)) {
            $content_length = wp_remote_retrieve_header($head_response, 'content-length');
            if (!empty($content_length) && (int) $content_length > $max_bytes) {
                return new \WP_Error(
                    'file_too_large',
                    sprintf(
                        __('Podcast Cover file is too large (%1$s). Please compress it to under %2$s.', 'sage'),
                        size_format((int) $content_length),
                        size_format($max_bytes)
                    )
                );
            }
        }

        // Download file to temp location for validation
        $temp_file = download_url($url, 30);

        if (is_wp_error($temp_file)) {
            return new \WP_Error(
                'download_failed',
                sprintf(
                    __('Could not download Podcast Cover for validation: %s', 'sage'),
                    $temp_file->get_error_message()
                )
            );
        }

        // Validate downloaded file
        $result = static::validateLocalCoverFile($temp_file, $max_bytes, $min_dimension, $max_dimension, $allowed_mimes);

        // Clean up temp file
        @unlink($temp_file);

        return $result;
    }

    /**
     * Validate image dimensions and format.
     *
     * @param array $image_info Result from getimagesize()
     * @param int $min_dimension
     * @param int $max_dimension
     * @param array $allowed_mimes
     * @return true|\WP_Error
     */
    private static function validateImageDimensions(
        array $image_info,
        int $min_dimension,
        int $max_dimension,
        array $allowed_mimes
    ) {
        [$width, $height] = $image_info;
        $mime = $image_info['mime'] ?? '';

        // Check MIME type
        if (!in_array($mime, $allowed_mimes, true)) {
            return new \WP_Error(
                'invalid_format',
                __('Podcast Cover must be JPG or PNG.', 'sage')
            );
        }

        // Check if square
        if ($width !== $height) {
            return new \WP_Error(
                'not_square',
                sprintf(
                    __('Podcast Cover must be square. Current dimensions: %1$d × %2$d px.', 'sage'),
                    $width,
                    $height
                )
            );
        }

        // Check minimum dimension
        if ($width < $min_dimension) {
            return new \WP_Error(
                'too_small',
                sprintf(
                    __('Podcast Cover resolution is too small. Minimum: %1$d × %1$d px. Current: %2$d × %2$d px.', 'sage'),
                    $min_dimension,
                    $width
                )
            );
        }

        // Check maximum dimension
        if ($width > $max_dimension) {
            return new \WP_Error(
                'too_large',
                sprintf(
                    __('Podcast Cover resolution is too large. Maximum: %1$d × %1$d px. Current: %2$d × %2$d px.', 'sage'),
                    $max_dimension,
                    $width
                )
            );
        }

        return true;
    }
}

// Bootstrap this options module.
PodcastOptions::boot();
