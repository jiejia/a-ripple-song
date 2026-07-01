<?php

namespace App\Settings;

use App\Abstracts\SettingAbstract;
use App\CustomPostTypes\Episode;
use App\Theme;
use Carbon_Fields\Field;

/**
 * Carbon Fields podcast settings page.
 */
class Podcast extends SettingAbstract
{
    /**
     * Minimum allowed cover dimension in pixels.
     */
    private const COVER_MIN_DIMENSION = 1400;

    /**
     * Maximum allowed cover dimension in pixels.
     */
    private const COVER_MAX_DIMENSION = 3000;

    /**
     * Maximum allowed cover file size in bytes.
     */
    private const COVER_MAX_FILE_SIZE = 524288;

    /**
     * User meta key used to persist cover validation notices across redirects.
     */
    private const COVER_VALIDATION_NOTICE_META_KEY = 'aripplesong_podcast_cover_validation_notice';

    /**
     * Ensure the validation hooks are only registered once per request.
     */
    private static bool $validationHooksRegistered = false;

    /**
     * Preserve a validation notice across Carbon Fields fallback sanitization.
     */
    private static bool $preserveCoverValidationNotice = false;

    /**
     * Return the prefix used for all podcast option keys.
     *
     * @return string
     */
    public function fieldPrefix(): string
    {
        return Theme::PREFIX . '_podcast_settings_';
    }

    /**
     * Return the Carbon Fields page slug.
     *
     * @return string
     */
    public function pageSlug(): string
    {
        return Theme::PREFIX . '_podcast_settings';
    }

    /**
     * Return the podcast settings page title.
     *
     * @return string
     */
    public function pageTitle(): string
    {
        return __('Podcast Settings', 'a-ripple-song');
    }

    /**
     * Return the parent menu slug for this settings page.
     *
     * @return string
     */
    public function parentPageSlug(): string
    {
        return Theme::SLUG;
    }

    /**
     * Return all podcast setting fields.
     *
     * @return array<int,\Carbon_Fields\Field\Field>
     */
    public function fields(): array
    {
        $this->registerCoverValidationHooks();
        $this->maybeMigrateCoverValueToAttachmentId();

        // Reuse option lists across related select fields.
        $notSetOptions = ['' => __('(not set)', 'a-ripple-song')];
        // Reuse yes/no choices for iTunes boolean tags.
        $yesNoOptions = ['no' => __('no', 'a-ripple-song'), 'yes' => __('yes', 'a-ripple-song')];

        /** @var \Carbon_Fields\Field\Complex_Field $fundingField */
        $fundingField = Field::make('complex', $this->fieldName('funding'), __('Podcasting 2.0 Funding Links (podcast:funding)', 'a-ripple-song'));
        $fundingField->set_help_text(__('Optional. If empty, no podcast:funding tags will be generated. URLs should be https.', 'a-ripple-song'));
        $fundingField->add_fields([
            Field::make('text', 'url', __('URL', 'a-ripple-song'))
                ->set_attribute('type', 'url')
                ->set_width(60),
            Field::make('text', 'label', __('Label', 'a-ripple-song'))->set_width(40),
        ]);

        /** @var \Carbon_Fields\Field\Html_Field $rssUrlField */
        $rssUrlField = Field::make('html', $this->fieldName('rss_url'), __('Podcast RSS URL', 'a-ripple-song'));
        $rssUrlField->set_html([$this, 'renderPodcastFeedUrlField']);

        /** @var \Carbon_Fields\Field\Image_Field $coverField */
        $coverField = Field::make('image', $this->fieldName('cover'), __('Podcast Cover (1400-3000px square)', 'a-ripple-song'));
        $coverField
            ->set_value_type('id')
            ->set_help_text(__('Required. Square JPG/PNG between 1400-3000px for itunes:image. File size must not exceed 512KB. The saved value is the media attachment ID.', 'a-ripple-song'));

        /** @var \Carbon_Fields\Field\Select_Field $explicitField */
        $explicitField = Field::make('select', $this->fieldName('explicit'), __('Default Explicit Flag', 'a-ripple-song'));
        $explicitField
            ->set_options([
                'clean' => __('clean (no explicit content)', 'a-ripple-song'),
                'explicit' => __('explicit', 'a-ripple-song'),
            ])
            ->set_default_value('clean')
            ->set_help_text(__('Required. Single-episode value can override.', 'a-ripple-song'));

        /** @var \Carbon_Fields\Field\Select_Field $languageField */
        $languageField = Field::make('select', $this->fieldName('language'), __('Language (RFC 5646)', 'a-ripple-song'));
        $languageField
            ->set_options($this->podcastLanguageOptions())
            ->set_default_value((string) (get_bloginfo('language') ?: 'en-US'))
            ->set_help_text(__('Required. Typically en-US, zh-CN, etc.', 'a-ripple-song'));

        /** @var \Carbon_Fields\Field\Select_Field $primaryCategoryField */
        $primaryCategoryField = Field::make('select', $this->fieldName('category_primary'), __('Primary Category (Apple Podcasts)', 'a-ripple-song'));
        $primaryCategoryField
            ->set_options($notSetOptions + $this->itunesCategories())
            ->set_help_text(__('Required by Apple Podcasts. Choose at least a primary category.', 'a-ripple-song'));

        /** @var \Carbon_Fields\Field\Select_Field $secondaryCategoryField */
        $secondaryCategoryField = Field::make('select', $this->fieldName('category_secondary'), __('Secondary Category (optional)', 'a-ripple-song'));
        $secondaryCategoryField
            ->set_options($notSetOptions + $this->itunesCategories())
            ->set_help_text(__('Optional. Some directories support a second category.', 'a-ripple-song'));

        /** @var \Carbon_Fields\Field\Select_Field $itunesTypeField */
        $itunesTypeField = Field::make('select', $this->fieldName('itunes_type'), __('iTunes Type (itunes:type)', 'a-ripple-song'));
        $itunesTypeField
            ->set_options($notSetOptions + [
                'episodic' => __('episodic', 'a-ripple-song'),
                'serial' => __('serial', 'a-ripple-song'),
            ])
            ->set_help_text(__('Optional. Apple Podcasts: episodic or serial.', 'a-ripple-song'));

        /** @var \Carbon_Fields\Field\Select_Field $itunesBlockField */
        $itunesBlockField = Field::make('select', $this->fieldName('itunes_block'), __('iTunes Block (itunes:block)', 'a-ripple-song'));
        $itunesBlockField
            ->set_options($yesNoOptions)
            ->set_default_value('no')
            ->set_help_text(__('Optional. yes = hide this show in Apple Podcasts.', 'a-ripple-song'));

        /** @var \Carbon_Fields\Field\Select_Field $itunesCompleteField */
        $itunesCompleteField = Field::make('select', $this->fieldName('itunes_complete'), __('iTunes Complete (itunes:complete)', 'a-ripple-song'));
        $itunesCompleteField
            ->set_options($yesNoOptions)
            ->set_default_value('no')
            ->set_help_text(__('Optional. yes = this show is complete with no more episodes.', 'a-ripple-song'));

        /** @var \Carbon_Fields\Field\Select_Field $lockedField */
        $lockedField = Field::make('select', $this->fieldName('locked'), __('podcast:locked', 'a-ripple-song'));
        $lockedField
            ->set_options([
                'yes' => __('yes (recommended, prevents unauthorized moves)', 'a-ripple-song'),
                'no' => __('no', 'a-ripple-song'),
            ])
            ->set_default_value('yes')
            ->set_help_text(__('Podcasting 2.0: lock feed to this publisher.', 'a-ripple-song'));

        return [
            $rssUrlField,
            Field::make('text', $this->fieldName('title'), __('Podcast Title', 'a-ripple-song'))
                ->set_default_value((string) get_bloginfo('name'))
                ->set_help_text(__('Required. If empty, falls back to site title.', 'a-ripple-song')),
            Field::make('text', $this->fieldName('subtitle'), __('Podcast Subtitle', 'a-ripple-song'))
                ->set_help_text(__('Short tagline shown in some apps.', 'a-ripple-song')),
            Field::make('textarea', $this->fieldName('description'), __('Podcast Description', 'a-ripple-song'))
                ->set_default_value((string) get_bloginfo('description'))
                ->set_help_text(__('Required. Plain text description of the show.', 'a-ripple-song')),
            Field::make('text', $this->fieldName('author'), __('Podcast Author (itunes:author)', 'a-ripple-song'))
                ->set_default_value((string) get_bloginfo('name'))
                ->set_help_text(__('Required. Displayed as show author in directories.', 'a-ripple-song')),
            Field::make('text', $this->fieldName('owner_name'), __('Owner Name', 'a-ripple-song'))
                ->set_default_value((string) get_bloginfo('name'))
                ->set_help_text(__('Required. For itunes:owner itunes:name.', 'a-ripple-song')),
            Field::make('text', $this->fieldName('owner_email'), __('Owner Email', 'a-ripple-song'))
                ->set_attribute('type', 'email')
                ->set_default_value((string) get_bloginfo('admin_email'))
                ->set_help_text(__('Required. For itunes:owner itunes:email. Use a monitored inbox.', 'a-ripple-song')),
            $coverField,
            $explicitField,
            $languageField,
            $primaryCategoryField,
            $secondaryCategoryField,
            Field::make('text', $this->fieldName('copyright'), __('Copyright (optional)', 'a-ripple-song'))
                ->set_help_text(__('Optional. For copyright.', 'a-ripple-song')),
            $itunesTypeField,
            Field::make('text', $this->fieldName('itunes_title'), __('iTunes Title (optional)', 'a-ripple-song'))
                ->set_help_text(__('Optional. Use only if you need a separate Apple-facing title.', 'a-ripple-song')),
            $itunesBlockField,
            $itunesCompleteField,
            Field::make('text', $this->fieldName('itunes_new_feed_url'), __('iTunes New Feed URL (itunes:new-feed-url)', 'a-ripple-song'))
                ->set_attribute('type', 'url')
                ->set_help_text(__('Optional. Only for moving your show to a new RSS feed URL.', 'a-ripple-song')),
            $lockedField,
            Field::make('text', $this->fieldName('locked_owner'), __('podcast:locked owner (optional)', 'a-ripple-song'))
                ->set_attribute('type', 'email')
                ->set_help_text(__('Optional. Podcasting 2.0: email used to verify ownership during moves.', 'a-ripple-song')),
            Field::make('text', $this->fieldName('guid'), __('podcast:guid (optional)', 'a-ripple-song'))
                ->set_default_value(home_url('/'))
                ->set_help_text(__('Podcasting 2.0 GUID. If empty, feed will use site URL as fallback.', 'a-ripple-song')),
            Field::make('text', $this->fieldName('apple_verify'), __('Apple Podcasts Verify Code', 'a-ripple-song'))
                ->set_help_text(__('Optional. Used by Apple Podcasts to verify feed ownership.', 'a-ripple-song')),
            $fundingField,
            Field::make('text', $this->fieldName('generator'), __('Generator (optional)', 'a-ripple-song'))
                ->set_help_text(__('Optional. If empty, generator tag will not be included.', 'a-ripple-song')),
        ];
    }

    /**
     * Render the readonly podcast feed URL field.
     *
     * @return string
     */
    public function renderPodcastFeedUrlField(): string
    {
        // Render a copy-friendly readonly URL field.
        return sprintf(
            '<input type="text" class="regular-text" value="%1$s" readonly onclick="this.select();" /><p class="description">%2$s</p>',
            esc_attr($this->podcastFeedUrl()),
            esc_html__('Your podcast RSS feed URL. Click to select and copy.', 'a-ripple-song')
        );
    }

    /**
     * Return default podcast settings.
     *
     * @return array<string,mixed>
     */
    public function defaultSettings(): array
    {
        // Keep defaults aligned with the old plugin settings page.
        return [
            'title' => get_bloginfo('name'),
            'subtitle' => '',
            'description' => get_bloginfo('description'),
            'author' => get_bloginfo('name'),
            'owner_name' => get_bloginfo('name'),
            'owner_email' => get_bloginfo('admin_email'),
            'cover' => '',
            'explicit' => 'clean',
            'language' => get_bloginfo('language') ?: 'en-US',
            'category_primary' => '',
            'category_secondary' => '',
            'copyright' => '',
            'itunes_type' => '',
            'itunes_title' => '',
            'itunes_block' => 'no',
            'itunes_complete' => 'no',
            'itunes_new_feed_url' => '',
            'locked' => 'yes',
            'locked_owner' => '',
            'guid' => home_url('/'),
            'apple_verify' => '',
            'funding' => [],
            'generator' => '',
        ];
    }

    /**
     * Return saved podcast settings with the cover normalized to a public URL.
     *
     * @return array<string,mixed>
     */
    public function getSettings(): array
    {
        $settings = parent::getSettings();
        $settings['cover'] = Episode::resolveStoredMediaFileValue($settings['cover'] ?? '');

        return $settings;
    }

    /**
     * Register the podcast cover validation hooks.
     *
     * @return void
     */
    private function registerCoverValidationHooks(): void
    {
        if (self::$validationHooksRegistered) {
            return;
        }

        add_filter('sanitize_option_' . $this->coverStorageOptionName(), [$this, 'validateCoverOptionBeforeSave'], 10, 3);
        add_filter('pre_update_option_' . $this->coverStorageOptionName(), [$this, 'validateCoverOptionBeforeUpdate'], 10, 3);
        add_action('admin_notices', [$this, 'renderCoverValidationNotice']);

        self::$validationHooksRegistered = true;
    }

    /**
     * Validate the podcast cover attachment before the option is saved.
     *
     * @param mixed $newValue New option value being saved.
     * @param string $option Option name being sanitized.
     * @param mixed $originalValue Original option value before sanitization.
     * @return mixed
     */
    public function validateCoverOptionBeforeSave(mixed $newValue, string $option, mixed $originalValue): mixed
    {
        if ($option !== $this->coverStorageOptionName()) {
            return $newValue;
        }

        if (self::$preserveCoverValidationNotice) {
            self::$preserveCoverValidationNotice = false;

            return $newValue;
        }

        // Let update_option() requests be handled by the dedicated pre-update hook.
        if (get_option($option, null) !== null) {
            return $newValue;
        }

        $this->clearCoverValidationNotice();

        if ($newValue === '' || $newValue === null) {
            return $newValue;
        }

        $attachmentId = is_numeric($newValue) ? (int) $newValue : 0;

        if ($attachmentId <= 0) {
            return $newValue;
        }

        $validationError = $this->validateCoverAttachment($attachmentId);

        if ($validationError === null) {
            return $newValue;
        }

        $this->storeCoverValidationNotice($validationError);
        self::$preserveCoverValidationNotice = true;

        $existingValue = get_option($option, '');

        return $existingValue !== false ? $existingValue : '';
    }

    /**
     * Validate the podcast cover before an existing option is updated.
     *
     * @param mixed $newValue New option value being saved.
     * @param mixed $oldValue Previous option value.
     * @param string $option Option name being updated.
     * @return mixed
     */
    public function validateCoverOptionBeforeUpdate(mixed $newValue, mixed $oldValue, string $option): mixed
    {
        if ($option !== $this->coverStorageOptionName()) {
            return $newValue;
        }

        $this->clearCoverValidationNotice();

        if ($newValue === '' || $newValue === null) {
            return $newValue;
        }

        $attachmentId = is_numeric($newValue) ? (int) $newValue : 0;

        if ($attachmentId <= 0) {
            return $newValue;
        }

        $validationError = $this->validateCoverAttachment($attachmentId);

        if ($validationError === null) {
            return $newValue;
        }

        $this->storeCoverValidationNotice($validationError);

        return $oldValue;
    }

    /**
     * Render the persisted cover validation notice on the podcast settings page.
     *
     * @return void
     */
    public function renderCoverValidationNotice(): void
    {
        if (! $this->isPodcastSettingsPage()) {
            return;
        }

        $userId = get_current_user_id();

        if ($userId <= 0) {
            return;
        }

        $notice = get_user_meta($userId, self::COVER_VALIDATION_NOTICE_META_KEY, true);

        if (! is_string($notice) || $notice === '') {
            return;
        }

        delete_user_meta($userId, self::COVER_VALIDATION_NOTICE_META_KEY);

        printf(
            '<div class="notice notice-error is-dismissible"><p>%s</p></div>',
            esc_html($notice)
        );
    }

    /**
     * Migrate a legacy cover URL value to an attachment ID when possible.
     *
     * @return void
     */
    private function maybeMigrateCoverValueToAttachmentId(): void
    {
        if (! function_exists('carbon_get_theme_option') || ! function_exists('carbon_set_theme_option')) {
            return;
        }

        $optionKey = $this->fieldName('cover');
        $storedValue = carbon_get_theme_option($optionKey);

        if (is_numeric($storedValue) || ! is_string($storedValue) || $storedValue === '') {
            return;
        }

        $attachmentId = $this->resolveAttachmentIdFromMediaUrl($storedValue);
        if ($attachmentId > 0) {
            carbon_set_theme_option($optionKey, $attachmentId);
        }
    }

    /**
     * Resolve a media URL or CDN URL to a local attachment ID.
     *
     * @param string $mediaUrl Attachment URL or CDN URL.
     * @return int
     */
    private function resolveAttachmentIdFromMediaUrl(string $mediaUrl): int
    {
        $attachmentId = attachment_url_to_postid($mediaUrl);
        if ($attachmentId > 0) {
            return $attachmentId;
        }

        $uploadDir = wp_get_upload_dir();
        $baseUrl = isset($uploadDir['baseurl']) ? (string) $uploadDir['baseurl'] : '';
        $urlPath = $this->extractNormalizedMediaUrlPath($mediaUrl);

        if ($urlPath === '' || $baseUrl === '') {
            return 0;
        }

        $relativeUploadPath = ltrim(rawurldecode($urlPath), '/');
        $normalizedLocalUrl = trailingslashit($baseUrl) . ltrim($relativeUploadPath, '/');
        $attachmentId = attachment_url_to_postid($normalizedLocalUrl);

        if ($attachmentId > 0) {
            return $attachmentId;
        }

        $attachment = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key' => '_wp_attached_file',
                    'value' => ltrim($relativeUploadPath, '/'),
                ],
            ],
        ]);

        return isset($attachment[0]) ? (int) $attachment[0] : 0;
    }

    /**
     * Return a normalized URL path for local and CDN media URLs.
     *
     * @param string $mediaUrl Attachment URL or CDN URL.
     * @return string
     */
    private function extractNormalizedMediaUrlPath(string $mediaUrl): string
    {
        if ($mediaUrl === '') {
            return '';
        }

        $encodedUrl = preg_replace_callback(
            '#^(https?://[^/]+)(/.*)$#u',
            static function (array $matches): string {
                $segments = explode('/', (string) $matches[2]);

                foreach ($segments as $index => $segment) {
                    if ($segment === '') {
                        continue;
                    }

                    $segments[$index] = rawurlencode(rawurldecode($segment));
                }

                return (string) $matches[1] . implode('/', $segments);
            },
            $mediaUrl
        );

        if (! is_string($encodedUrl) || $encodedUrl === '') {
            return '';
        }

        $path = (string) wp_parse_url($encodedUrl, PHP_URL_PATH);

        return $path !== '' ? $path : '';
    }

    /**
     * Return a validation error for the selected cover attachment, or null when valid.
     *
     * @param int $attachmentId Attachment ID selected in the settings page.
     * @return string|null
     */
    private function validateCoverAttachment(int $attachmentId): ?string
    {
        [$width, $height] = $this->coverAttachmentDimensions($attachmentId);

        if ($width <= 0 || $height <= 0) {
            return __('Unable to validate the selected podcast cover dimensions. Please choose an image from the WordPress Media Library.', 'a-ripple-song');
        }

        if ($width !== $height || $width < self::COVER_MIN_DIMENSION || $width > self::COVER_MAX_DIMENSION) {
            return __('Podcast cover must be a square image between 1400x1400 and 3000x3000 pixels.', 'a-ripple-song');
        }

        $fileSize = $this->coverAttachmentFileSize($attachmentId);

        if ($fileSize <= 0) {
            return __('Unable to validate the selected podcast cover file size. Please choose an image stored locally in the WordPress Media Library.', 'a-ripple-song');
        }

        if ($fileSize > self::COVER_MAX_FILE_SIZE) {
            return __('Podcast cover image must be 512 KB or smaller.', 'a-ripple-song');
        }

        return null;
    }

    /**
     * Return the Carbon Fields storage key used for the cover option in wp_options.
     *
     * @return string
     */
    private function coverStorageOptionName(): string
    {
        return '_' . $this->fieldName('cover');
    }

    /**
     * Return the selected cover dimensions in pixels.
     *
     * @param int $attachmentId Attachment ID selected in the settings page.
     * @return array{0:int,1:int}
     */
    private function coverAttachmentDimensions(int $attachmentId): array
    {
        $metadata = wp_get_attachment_metadata($attachmentId);
        $width = is_array($metadata) && isset($metadata['width']) ? (int) $metadata['width'] : 0;
        $height = is_array($metadata) && isset($metadata['height']) ? (int) $metadata['height'] : 0;

        if ($width > 0 && $height > 0) {
            return [$width, $height];
        }

        $filePath = get_attached_file($attachmentId);

        if (! is_string($filePath) || $filePath === '' || ! file_exists($filePath)) {
            return [0, 0];
        }

        $imageSize = wp_getimagesize($filePath);

        if (! is_array($imageSize)) {
            return [0, 0];
        }

        return [(int) ($imageSize[0] ?? 0), (int) ($imageSize[1] ?? 0)];
    }

    /**
     * Return the selected cover file size in bytes.
     *
     * @param int $attachmentId Attachment ID selected in the settings page.
     * @return int
     */
    private function coverAttachmentFileSize(int $attachmentId): int
    {
        $metadata = wp_get_attachment_metadata($attachmentId);

        if (is_array($metadata) && isset($metadata['filesize']) && is_numeric($metadata['filesize'])) {
            return (int) $metadata['filesize'];
        }

        $filePath = get_attached_file($attachmentId);

        if (! is_string($filePath) || $filePath === '' || ! file_exists($filePath)) {
            return 0;
        }

        $fileSize = filesize($filePath);

        return $fileSize !== false ? (int) $fileSize : 0;
    }

    /**
     * Persist a validation notice for the current user.
     *
     * @param string $message Validation error message.
     * @return void
     */
    private function storeCoverValidationNotice(string $message): void
    {
        $userId = get_current_user_id();

        if ($userId <= 0) {
            return;
        }

        update_user_meta($userId, self::COVER_VALIDATION_NOTICE_META_KEY, $message);
    }

    /**
     * Clear any persisted validation notice for the current user.
     *
     * @return void
     */
    private function clearCoverValidationNotice(): void
    {
        $userId = get_current_user_id();

        if ($userId <= 0) {
            return;
        }

        delete_user_meta($userId, self::COVER_VALIDATION_NOTICE_META_KEY);
    }

    /**
     * Return whether the current admin request is the podcast settings page.
     *
     * @return bool
     */
    private function isPodcastSettingsPage(): bool
    {
        if (! is_admin()) {
            return false;
        }

        $page = isset($_GET['page']) ? sanitize_key((string) wp_unslash($_GET['page'])) : '';

        return $page === $this->pageSlug();
    }

    /**
     * Return the podcast feed URL for the current permalink mode.
     *
     * @return string
     */
    private function podcastFeedUrl(): string
    {
        // Build the feed URL using the site's permalink structure.
        $permalinkStructure = get_option('permalink_structure');

        if (empty($permalinkStructure)) {
            return home_url('/?feed=podcast');
        }

        if (strpos((string) $permalinkStructure, '/index.php/') === 0) {
            return home_url('/index.php/feed/podcast/');
        }

        return home_url('/feed/podcast/');
    }

    /**
     * Return supported podcast language options.
     *
     * @return array<string,string>
     */
    private function podcastLanguageOptions(): array
    {
        // Keep the common language list from the previous plugin.
        return [
            'en-US' => 'en-US',
            'zh-CN' => 'zh-CN',
        ];
    }

    /**
     * Return Apple Podcasts category options.
     *
     * @return array<string,string>
     */
    private function itunesCategories(): array
    {
        // Keep values compatible with the previous plugin's feed rendering.
        return [
            'Arts' => __('Arts', 'a-ripple-song'),
            'Arts::Books' => __('Arts > Books', 'a-ripple-song'),
            'Arts::Design' => __('Arts > Design', 'a-ripple-song'),
            'Arts::Fashion & Beauty' => __('Arts > Fashion & Beauty', 'a-ripple-song'),
            'Arts::Food' => __('Arts > Food', 'a-ripple-song'),
            'Arts::Performing Arts' => __('Arts > Performing Arts', 'a-ripple-song'),
            'Arts::Visual Arts' => __('Arts > Visual Arts', 'a-ripple-song'),
            'Business' => __('Business', 'a-ripple-song'),
            'Business::Careers' => __('Business > Careers', 'a-ripple-song'),
            'Business::Entrepreneurship' => __('Business > Entrepreneurship', 'a-ripple-song'),
            'Business::Investing' => __('Business > Investing', 'a-ripple-song'),
            'Business::Management' => __('Business > Management', 'a-ripple-song'),
            'Business::Marketing' => __('Business > Marketing', 'a-ripple-song'),
            'Business::Non-Profit' => __('Business > Non-Profit', 'a-ripple-song'),
            'Comedy' => __('Comedy', 'a-ripple-song'),
            'Comedy::Comedy Interviews' => __('Comedy > Comedy Interviews', 'a-ripple-song'),
            'Comedy::Improv' => __('Comedy > Improv', 'a-ripple-song'),
            'Comedy::Stand-Up' => __('Comedy > Stand-Up', 'a-ripple-song'),
            'Education' => __('Education', 'a-ripple-song'),
            'Education::Courses' => __('Education > Courses', 'a-ripple-song'),
            'Education::How To' => __('Education > How To', 'a-ripple-song'),
            'Education::Language Learning' => __('Education > Language Learning', 'a-ripple-song'),
            'Education::Self-Improvement' => __('Education > Self-Improvement', 'a-ripple-song'),
            'Fiction' => __('Fiction', 'a-ripple-song'),
            'Fiction::Comedy Fiction' => __('Fiction > Comedy Fiction', 'a-ripple-song'),
            'Fiction::Drama' => __('Fiction > Drama', 'a-ripple-song'),
            'Fiction::Science Fiction' => __('Fiction > Science Fiction', 'a-ripple-song'),
            'Government' => __('Government', 'a-ripple-song'),
            'History' => __('History', 'a-ripple-song'),
            'Health & Fitness' => __('Health & Fitness', 'a-ripple-song'),
            'Health & Fitness::Alternative Health' => __('Health & Fitness > Alternative Health', 'a-ripple-song'),
            'Health & Fitness::Fitness' => __('Health & Fitness > Fitness', 'a-ripple-song'),
            'Health & Fitness::Medicine' => __('Health & Fitness > Medicine', 'a-ripple-song'),
            'Health & Fitness::Mental Health' => __('Health & Fitness > Mental Health', 'a-ripple-song'),
            'Health & Fitness::Nutrition' => __('Health & Fitness > Nutrition', 'a-ripple-song'),
            'Health & Fitness::Sexuality' => __('Health & Fitness > Sexuality', 'a-ripple-song'),
            'Kids & Family' => __('Kids & Family', 'a-ripple-song'),
            'Kids & Family::Education for Kids' => __('Kids & Family > Education for Kids', 'a-ripple-song'),
            'Kids & Family::Parenting' => __('Kids & Family > Parenting', 'a-ripple-song'),
            'Kids & Family::Pets & Animals' => __('Kids & Family > Pets & Animals', 'a-ripple-song'),
            'Kids & Family::Stories for Kids' => __('Kids & Family > Stories for Kids', 'a-ripple-song'),
            'Leisure' => __('Leisure', 'a-ripple-song'),
            'Leisure::Animation & Manga' => __('Leisure > Animation & Manga', 'a-ripple-song'),
            'Leisure::Automotive' => __('Leisure > Automotive', 'a-ripple-song'),
            'Leisure::Aviation' => __('Leisure > Aviation', 'a-ripple-song'),
            'Leisure::Crafts' => __('Leisure > Crafts', 'a-ripple-song'),
            'Leisure::Games' => __('Leisure > Games', 'a-ripple-song'),
            'Leisure::Hobbies' => __('Leisure > Hobbies', 'a-ripple-song'),
            'Leisure::Home & Garden' => __('Leisure > Home & Garden', 'a-ripple-song'),
            'Leisure::Video Games' => __('Leisure > Video Games', 'a-ripple-song'),
            'Music' => __('Music', 'a-ripple-song'),
            'Music::Music Commentary' => __('Music > Music Commentary', 'a-ripple-song'),
            'Music::Music History' => __('Music > Music History', 'a-ripple-song'),
            'Music::Music Interviews' => __('Music > Music Interviews', 'a-ripple-song'),
            'News' => __('News', 'a-ripple-song'),
            'News::Business News' => __('News > Business News', 'a-ripple-song'),
            'News::Daily News' => __('News > Daily News', 'a-ripple-song'),
            'News::Entertainment News' => __('News > Entertainment News', 'a-ripple-song'),
            'News::News Commentary' => __('News > News Commentary', 'a-ripple-song'),
            'News::Politics' => __('News > Politics', 'a-ripple-song'),
            'News::Sports News' => __('News > Sports News', 'a-ripple-song'),
            'News::Tech News' => __('News > Tech News', 'a-ripple-song'),
            'Religion & Spirituality' => __('Religion & Spirituality', 'a-ripple-song'),
            'Religion & Spirituality::Buddhism' => __('Religion & Spirituality > Buddhism', 'a-ripple-song'),
            'Religion & Spirituality::Christianity' => __('Religion & Spirituality > Christianity', 'a-ripple-song'),
            'Religion & Spirituality::Hinduism' => __('Religion & Spirituality > Hinduism', 'a-ripple-song'),
            'Religion & Spirituality::Islam' => __('Religion & Spirituality > Islam', 'a-ripple-song'),
            'Religion & Spirituality::Judaism' => __('Religion & Spirituality > Judaism', 'a-ripple-song'),
            'Religion & Spirituality::Religion' => __('Religion & Spirituality > Religion', 'a-ripple-song'),
            'Religion & Spirituality::Spirituality' => __('Religion & Spirituality > Spirituality', 'a-ripple-song'),
            'Science' => __('Science', 'a-ripple-song'),
            'Science::Astronomy' => __('Science > Astronomy', 'a-ripple-song'),
            'Science::Chemistry' => __('Science > Chemistry', 'a-ripple-song'),
            'Science::Earth Sciences' => __('Science > Earth Sciences', 'a-ripple-song'),
            'Science::Life Sciences' => __('Science > Life Sciences', 'a-ripple-song'),
            'Science::Mathematics' => __('Science > Mathematics', 'a-ripple-song'),
            'Science::Natural Sciences' => __('Science > Natural Sciences', 'a-ripple-song'),
            'Science::Nature' => __('Science > Nature', 'a-ripple-song'),
            'Science::Physics' => __('Science > Physics', 'a-ripple-song'),
            'Society & Culture' => __('Society & Culture', 'a-ripple-song'),
            'Society & Culture::Documentary' => __('Society & Culture > Documentary', 'a-ripple-song'),
            'Society & Culture::Personal Journals' => __('Society & Culture > Personal Journals', 'a-ripple-song'),
            'Society & Culture::Philosophy' => __('Society & Culture > Philosophy', 'a-ripple-song'),
            'Society & Culture::Places & Travel' => __('Society & Culture > Places & Travel', 'a-ripple-song'),
            'Society & Culture::Relationships' => __('Society & Culture > Relationships', 'a-ripple-song'),
            'Sports' => __('Sports', 'a-ripple-song'),
            'Sports::Baseball' => __('Sports > Baseball', 'a-ripple-song'),
            'Sports::Basketball' => __('Sports > Basketball', 'a-ripple-song'),
            'Sports::Cricket' => __('Sports > Cricket', 'a-ripple-song'),
            'Sports::Fantasy Sports' => __('Sports > Fantasy Sports', 'a-ripple-song'),
            'Sports::Football' => __('Sports > Football', 'a-ripple-song'),
            'Sports::Golf' => __('Sports > Golf', 'a-ripple-song'),
            'Sports::Hockey' => __('Sports > Hockey', 'a-ripple-song'),
            'Sports::Rugby' => __('Sports > Rugby', 'a-ripple-song'),
            'Sports::Running' => __('Sports > Running', 'a-ripple-song'),
            'Sports::Soccer' => __('Sports > Soccer', 'a-ripple-song'),
            'Sports::Swimming' => __('Sports > Swimming', 'a-ripple-song'),
            'Sports::Tennis' => __('Sports > Tennis', 'a-ripple-song'),
            'Sports::Volleyball' => __('Sports > Volleyball', 'a-ripple-song'),
            'Technology' => __('Technology', 'a-ripple-song'),
            'True Crime' => __('True Crime', 'a-ripple-song'),
            'TV & Film' => __('TV & Film', 'a-ripple-song'),
            'TV & Film::After Shows' => __('TV & Film > After Shows', 'a-ripple-song'),
            'TV & Film::Film History' => __('TV & Film > Film History', 'a-ripple-song'),
            'TV & Film::Film Interviews' => __('TV & Film > Film Interviews', 'a-ripple-song'),
            'TV & Film::Film Reviews' => __('TV & Film > Film Reviews', 'a-ripple-song'),
            'TV & Film::TV Reviews' => __('TV & Film > TV Reviews', 'a-ripple-song'),
        ];
    }
}
