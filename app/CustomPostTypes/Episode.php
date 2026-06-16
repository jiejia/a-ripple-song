<?php

namespace App\CustomPostTypes;

use App\Abstracts\CustomPostTypeAbstract;
use App\Theme;
use Carbon_Fields\Field;

/**
 * Defines the podcast episode custom post type.
 */
class Episode extends CustomPostTypeAbstract
{
    /**
     * Return the stored meta key for the last audio metadata extraction error.
     *
     * @return string
     */
    public static function audioMetaLastErrorKey(): string
    {
        return self::storedFieldKey('podcast_audio_meta_last_error');
    }

    /**
     * Return the WordPress custom post type key.
     *
     * @return string
     */
    public static function slug(): string
    {
        return Theme::PREFIX . '_episode';
    }

    /**
     * Return the singular custom post type name.
     *
     * @return string
     */
    public static function singularName(): string
    {
        return __('Episode', 'sage');
    }

    /**
     * Return the plural custom post type name.
     *
     * @return string
     */
    public static function pluralName(): string
    {
        return __('Episodes', 'sage');
    }

    /**
     * Return WordPress registration arguments.
     *
     * @return array<string,mixed>
     */
    public function args(): array
    {
        $args = parent::args();
        $args['taxonomies'] = ['post_tag'];
        $args['show_in_nav_menus'] = true;
        $args['rewrite'] = ['slug' => 'podcasts'];

        return $args;
    }

    /**
     * Return Carbon Fields registered for podcast episodes.
     *
     * @return array<int,\Carbon_Fields\Field\Field>
     */
    public function fields(): array
    {
        /** @var \Carbon_Fields\Field\File_Field $audioFileField */
        $audioFileField = Field::make('file', self::fieldKey('audio_file'), __('Audio File', 'sage'));
        $audioFileField
            ->set_type('audio')
            ->set_value_type('url')
            ->set_help_text(__('Required. Upload an audio file; the saved value is the audio file URL.', 'sage'))
            ->set_required(true);

        /** @var \Carbon_Fields\Field\Select_Field $explicitField */
        $explicitField = Field::make('select', self::fieldKey('episode_explicit'), __('Explicit', 'sage'));
        $explicitField
            ->set_options([
                'clean' => __('clean', 'sage'),
                'explicit' => __('explicit', 'sage'),
            ])
            ->set_default_value('clean')
            ->set_help_text(__('Required. clean / explicit.', 'sage'))
            ->set_required(true);

        /** @var \Carbon_Fields\Field\Select_Field $episodeTypeField */
        $episodeTypeField = Field::make('select', self::fieldKey('episode_type'), __('Episode Type', 'sage'));
        $episodeTypeField
            ->set_options([
                'full' => __('full', 'sage'),
                'trailer' => __('trailer', 'sage'),
                'bonus' => __('bonus', 'sage'),
            ])
            ->set_default_value('full')
            ->set_help_text(__('Required. full / trailer / bonus.', 'sage'));

        /** @var \Carbon_Fields\Field\Image_Field $episodeImageField */
        $episodeImageField = Field::make('image', self::fieldKey('episode_image'), __('Episode Cover (square)', 'sage'));
        $episodeImageField
            ->set_value_type('url')
            ->set_help_text(__('Optional. Square 1400-3000px. Overrides channel cover.', 'sage'));

        /** @var \Carbon_Fields\Field\Select_Field $episodeBlockField */
        $episodeBlockField = Field::make('select', self::fieldKey('episode_block'), __('iTunes Block', 'sage'));
        $episodeBlockField
            ->set_options([
                'no' => __('no', 'sage'),
                'yes' => __('yes', 'sage'),
            ])
            ->set_default_value('no')
            ->set_help_text(__('Optional. yes = hide this episode in Apple Podcasts.', 'sage'));

        /** @var \Carbon_Fields\Field\Complex_Field $soundbitesField */
        $soundbitesField = Field::make('complex', self::fieldKey('episode_soundbites'), __('Soundbites', 'sage'));
        $soundbitesField->set_help_text(__('Optional. Adds one or more <podcast:soundbite> tags.', 'sage'));
        $soundbitesField->set_header_template('<%- ' . self::fieldKey('title') . ' || "' . esc_html__('Soundbite', 'sage') . '" %>');
        $soundbitesField->add_fields([
            Field::make('text', self::fieldKey('start_time'), __('Start Time (seconds)', 'sage'))
                ->set_attribute('type', 'number')
                ->set_attribute('min', '0')
                ->set_attribute('step', '0.01')
                ->set_width(33),
            Field::make('text', self::fieldKey('duration'), __('Duration (seconds)', 'sage'))
                ->set_attribute('type', 'number')
                ->set_attribute('min', '0.01')
                ->set_attribute('step', '0.01')
                ->set_width(33),
            Field::make('text', self::fieldKey('title'), __('Title (optional)', 'sage'))->set_width(33),
        ]);

        /** @var \Carbon_Fields\Field\Multiselect_Field $membersField */
        $membersField = Field::make('multiselect', self::fieldKey('members'), __('Members', 'sage'));
        $membersField
            ->set_options([$this, 'userOptions'])
            ->set_default_value($this->defaultMemberIds())
            ->set_help_text(__('Select episode members (administrators, authors, editors).', 'sage'));

        /** @var \Carbon_Fields\Field\Multiselect_Field $guestsField */
        $guestsField = Field::make('multiselect', self::fieldKey('guests'), __('Guests', 'sage'));
        $guestsField
            ->set_options([$this, 'userOptions'])
            ->set_help_text(__('Select episode guests (contributors).', 'sage'));

        return [
            Field::make('separator', self::fieldKey('episode_audio_separator'), __('Audio', 'sage')),
            $audioFileField,
            Field::make('text', self::fieldKey('duration'), __('Duration (seconds)', 'sage'))
                ->set_attribute('type', 'number')
                ->set_attribute('min', '0')
                ->set_attribute('step', '1')
                ->set_attribute('readOnly', 'readOnly')
                ->set_help_text(__('Auto detected from "Audio File" after saving.', 'sage')),
            Field::make('text', self::fieldKey('audio_length'), __('Audio Length (bytes)', 'sage'))
                ->set_attribute('type', 'number')
                ->set_attribute('min', '0')
                ->set_attribute('step', '1')
                ->set_attribute('readOnly', 'readOnly')
                ->set_help_text(__('Auto detected from "Audio File" after saving.', 'sage')),
            Field::make('text', self::fieldKey('audio_mime'), __('Audio MIME Type', 'sage'))
                ->set_default_value('audio/mpeg')
                ->set_attribute('readOnly', 'readOnly')
                ->set_help_text(__('Auto detected from "Audio File" after saving.', 'sage')),
            Field::make('separator', self::fieldKey('episode_metadata_separator'), __('Podcast Metadata', 'sage')),
            $explicitField,
            $episodeTypeField,
            Field::make('text', self::fieldKey('episode_number'), __('Episode Number', 'sage'))
                ->set_attribute('type', 'number')
                ->set_attribute('min', '0')
                ->set_attribute('step', '1')
                ->set_help_text(__('Optional but recommended. Integer.', 'sage')),
            Field::make('text', self::fieldKey('season_number'), __('Season Number', 'sage'))
                ->set_attribute('type', 'number')
                ->set_attribute('min', '0')
                ->set_attribute('step', '1')
                ->set_help_text(__('Optional. Integer.', 'sage')),
            Field::make('text', self::fieldKey('episode_author'), __('Episode Author (override)', 'sage'))
                ->set_help_text(__('Optional. Overrides channel author for this episode.', 'sage')),
            $episodeImageField,
            Field::make('text', self::fieldKey('episode_transcript'), __('Transcript (optional)', 'sage'))
                ->set_attribute('type', 'url')
                ->set_help_text(__('Optional. Transcript file URL (vtt/srt/txt/pdf).', 'sage')),
            Field::make('text', self::fieldKey('itunes_title'), __('iTunes Title (optional)', 'sage'))
                ->set_help_text(__('Optional. Apple Podcasts: overrides the episode title for <itunes:title>.', 'sage')),
            Field::make('text', self::fieldKey('episode_chapters'), __('Chapters (Podcasting 2.0)', 'sage'))
                ->set_attribute('type', 'url')
                ->set_help_text(__('Optional. Provide a chapters JSON URL/file for <podcast:chapters>.', 'sage')),
            Field::make('text', self::fieldKey('episode_chapters_type'), __('Chapters MIME Type', 'sage'))
                ->set_default_value('application/json+chapters')
                ->set_help_text(__('Optional. Defaults to application/json+chapters.', 'sage')),
            Field::make('textarea', self::fieldKey('episode_subtitle'), __('Subtitle', 'sage'))
                ->set_help_text(__('Optional. Short subtitle for iTunes.', 'sage')),
            Field::make('textarea', self::fieldKey('episode_summary'), __('Summary', 'sage'))
                ->set_help_text(__('Optional. Plain text summary for iTunes.', 'sage')),
            Field::make('text', self::fieldKey('episode_guid'), __('Custom GUID (optional)', 'sage'))
                ->set_help_text(__('Optional. If empty, feed uses WP permalink as GUID.', 'sage')),
            $episodeBlockField,
            $soundbitesField,
            Field::make('separator', self::fieldKey('episode_people_separator'), __('People', 'sage')),
            $membersField,
            $guestsField,
        ];
    }

    /**
     * Register extra hooks required by podcast episodes.
     *
     * @return void
     */
    public function registerHooks(): void
    {
        add_action('init', [$this, 'registerPlayCountMeta']);
        add_action('init', [$this, 'registerEpisodeMeta']);
        add_action('rest_api_init', [$this, 'registerEpisodeRestFields']);
        add_action('save_post', [$this, 'ensurePlayCountDefault'], 10, 2);
        add_filter('wp_insert_post_data', [$this, 'setDefaultCommentStatus'], 10, 2);
        add_filter('upload_mimes', [$this, 'allowUploadMimes']);
        add_filter('wp_check_filetype_and_ext', [$this, 'fixFiletypeAndExt'], 10, 4);
        add_action('carbon_fields_post_meta_container_saved', [$this, 'onPostMetaSaved'], 10, 2);
        add_action('aripplesong_carbon_fields_post_meta_container_saved', [$this, 'onPostMetaSaved'], 10, 2);
        add_action('admin_notices', [$this, 'showAudioMetaErrorNotice']);
    }

    /**
     * Register the episode play count meta field.
     *
     * @return void
     */
    public function registerPlayCountMeta(): void
    {
        register_post_meta(self::slug(), self::storedFieldKey('play_count'), [
            'type' => 'integer',
            'single' => true,
            'default' => 0,
            'show_in_rest' => false,
            'auth_callback' => static function ($allowed, $metaKey, $postId): bool {
                return current_user_can('edit_post', $postId);
            },
        ]);
    }

    /**
     * Ensure episode play count default exists after an episode is saved.
     *
     * @param int $postId Post ID.
     * @param \WP_Post $post Saved post object.
     * @return void
     */
    public function ensurePlayCountDefault(int $postId, \WP_Post $post): void
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (! current_user_can('edit_post', $postId)) {
            return;
        }

        if ($post->post_type === self::slug() && ! metadata_exists('post', $postId, self::storedFieldKey('play_count'))) {
            update_post_meta($postId, self::storedFieldKey('play_count'), 0);
        }
    }

    /**
     * Default comment and ping status to open for new episodes.
     *
     * @param array<string,mixed> $data Post data.
     * @param array<string,mixed> $postarr Raw post array.
     * @return array<string,mixed>
     */
    public function setDefaultCommentStatus(array $data, array $postarr): array
    {
        if (isset($data['post_type']) && $data['post_type'] === self::slug() && empty($postarr['ID'])) {
            $data['comment_status'] = 'open';
            $data['ping_status'] = 'open';
        }

        return $data;
    }

    /**
     * Update audio metadata after Carbon Fields saves episode fields.
     *
     * @param int $postId Post ID.
     * @param mixed $container Carbon Fields container.
     * @return void
     */
    public function onPostMetaSaved(int $postId, mixed $container): void
    {
        if (get_post_type($postId) !== self::slug()) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (! current_user_can('edit_post', $postId)) {
            return;
        }

        $this->autoFillAudioMeta($postId);
    }

    /**
     * Register Episode Details meta keys for REST output.
     *
     * @return void
     */
    public function registerEpisodeMeta(): void
    {
        $postType = self::slug();
        $this->registerStringMeta($postType, 'audio_file', true);
        $this->registerIntMeta($postType, 'duration');
        $this->registerIntMeta($postType, 'audio_length');
        $this->registerStringMeta($postType, 'audio_mime');
        $this->registerStringMeta($postType, 'episode_explicit');
        $this->registerStringMeta($postType, 'episode_type');
        $this->registerIntMeta($postType, 'episode_number');
        $this->registerIntMeta($postType, 'season_number');
        $this->registerStringMeta($postType, 'episode_author');
        $this->registerStringMeta($postType, 'episode_image', true);
        $this->registerStringMeta($postType, 'episode_transcript', true);
        $this->registerStringMeta($postType, 'itunes_title');
        $this->registerStringMeta($postType, 'episode_chapters', true);
        $this->registerStringMeta($postType, 'episode_chapters_type');
        $this->registerStringMeta($postType, 'episode_subtitle');
        $this->registerStringMeta($postType, 'episode_summary');
        $this->registerStringMeta($postType, 'episode_guid');
        $this->registerStringMeta($postType, 'episode_block');
        $this->registerArrayMeta($postType, 'members', ['type' => 'integer']);
        $this->registerArrayMeta($postType, 'guests', ['type' => 'integer']);
        $this->registerArrayMeta($postType, 'episode_soundbites', [
            'type' => 'object',
            'properties' => [
                self::fieldKey('start_time') => ['type' => 'number'],
                self::fieldKey('duration') => ['type' => 'number'],
                self::fieldKey('title') => ['type' => 'string'],
            ],
        ]);
    }

    /**
     * Expose selected Episode Details fields as top-level REST fields.
     *
     * @return void
     */
    public function registerEpisodeRestFields(): void
    {
        $postType = self::slug();
        register_rest_field($postType, 'title_text', [
            'get_callback' => static function (array $post): string {
                $postId = isset($post['id']) ? (int) $post['id'] : 0;
                $title = $postId > 0 ? (string) get_post_field('post_title', $postId) : '';

                return wp_strip_all_tags(html_entity_decode($title, ENT_QUOTES, 'UTF-8'), true);
            },
            'schema' => [
                'description' => __('Episode title (plain text)', 'sage'),
                'type' => 'string',
            ],
        ]);
        register_rest_field($postType, 'audio_file', [
            'get_callback' => static function (array $post): string {
                return (string) self::getStoredPostMetaValue((int) $post['id'], 'audio_file', '');
            },
            'schema' => [
                'description' => __('Audio file URL', 'sage'),
                'type' => 'string',
            ],
        ]);
        register_rest_field($postType, 'duration', [
            'get_callback' => static function (array $post): int {
                return (int) self::getStoredPostMetaValue((int) $post['id'], 'duration', 0);
            },
            'schema' => [
                'description' => __('Audio duration (seconds)', 'sage'),
                'type' => 'integer',
            ],
        ]);
        register_rest_field($postType, 'episode_transcript', [
            'get_callback' => static function (array $post): string {
                return (string) self::getStoredPostMetaValue((int) $post['id'], 'episode_transcript', '');
            },
            'schema' => [
                'description' => __('Episode transcript URL', 'sage'),
                'type' => 'string',
            ],
        ]);
    }

    /**
     * Allow additional audio file types to be uploaded.
     *
     * @param array<string,string> $mimes Existing allowed mime types.
     * @return array<string,string>
     */
    public function allowUploadMimes(array $mimes): array
    {
        $mimes['mp3'] = 'audio/mpeg';
        $mimes['m4a'] = 'audio/mp4';

        return $mimes;
    }

    /**
     * Fix file type detection for custom audio mime types.
     *
     * @param array<string,mixed> $data File data.
     * @param string $file Full path to the file.
     * @param string $filename Uploaded file name.
     * @param array<string,string>|null $mimes Allowed mime types.
     * @return array<string,mixed>
     */
    public function fixFiletypeAndExt(array $data, string $file, string $filename, ?array $mimes): array
    {
        $ext = strtolower((string) pathinfo($filename, PATHINFO_EXTENSION));

        if ($ext === 'mp3' && (empty($data['type']) || empty($data['ext']))) {
            $data['ext'] = 'mp3';
            $data['type'] = 'audio/mpeg';
        }

        if ($ext === 'm4a') {
            $detectedType = is_string($data['type'] ?? null) ? (string) $data['type'] : '';
            $allowedTypes = ['', 'audio/aac', 'audio/mp4', 'audio/mpeg', 'audio/x-m4a', 'video/mp4', 'application/octet-stream'];

            if (in_array($detectedType, $allowedTypes, true) || empty($data['ext'])) {
                $data['ext'] = 'm4a';
                $data['type'] = 'audio/mp4';
            }
        }

        return $data;
    }

    /**
     * Return selectable WordPress users for member and guest fields.
     *
     * @return array<int,string>
     */
    public function userOptions(): array
    {
        $options = [];
        $users = get_users([
            'orderby' => 'display_name',
            'order' => 'ASC',
            'fields' => ['ID', 'display_name', 'user_login'],
            'number' => 500,
        ]);

        foreach ($users as $user) {
            $options[(int) $user->ID] = $user->display_name ? $user->display_name : $user->user_login;
        }

        return $options;
    }

    /**
     * Return editor feature support for podcast episodes.
     *
     * @return array<int,string>
     */
    protected function supports(): array
    {
        return ['title', 'editor', 'thumbnail', 'excerpt', 'author', 'comments', 'trackbacks'];
    }

    /**
     * Return default member IDs for newly created episodes.
     *
     * @return array<int,int>
     */
    private function defaultMemberIds(): array
    {
        $currentUserId = get_current_user_id();

        if (! $currentUserId) {
            return [];
        }

        $currentUser = get_userdata($currentUserId);

        if (! $currentUser) {
            return [];
        }

        $allowedRoles = ['administrator', 'author', 'editor'];

        if (empty(array_intersect($allowedRoles, (array) $currentUser->roles))) {
            return [];
        }

        return [(int) $currentUserId];
    }

    /**
     * Auto calculate and save audio metadata after the episode is saved.
     *
     * @param int $postId Post ID.
     * @return void
     */
    private function autoFillAudioMeta(int $postId): void
    {
        $autoMeta = $this->calculateAudioMeta($postId);

        if (! empty($autoMeta['duration'])) {
            $this->setEpisodeFieldValue($postId, 'duration', (int) $autoMeta['duration']);
        }

        if (! empty($autoMeta['length'])) {
            $this->setEpisodeFieldValue($postId, 'audio_length', (int) $autoMeta['length']);
        }

        if (! empty($autoMeta['mime'])) {
            $this->setEpisodeFieldValue($postId, 'audio_mime', (string) $autoMeta['mime']);
        }
    }

    /**
     * Calculate podcast audio metadata via WordPress bundled getID3.
     *
     * @param int $postId Post ID.
     * @return array{duration:int|null,length:int|null,mime:string|null}
     */
    private function calculateAudioMeta(int $postId): array
    {
        $result = ['duration' => null, 'length' => null, 'mime' => null];
        $audioUrl = $this->getEpisodeFieldValue($postId, 'audio_file');

        if ($audioUrl === '') {
            return $result;
        }

        if (! class_exists('getID3')) {
            $maybe = ABSPATH . WPINC . '/ID3/getid3.php';

            if (file_exists($maybe)) {
                require_once $maybe;
            }
        }

        if (! class_exists('getID3')) {
            $this->setAudioMetaLastError($postId, 'getid3_missing');

            return $result;
        }

        $filePath = $this->resolveAudioFilePath($audioUrl);

        if ($filePath === '') {
            $this->setAudioMetaLastError($postId, 'audio_file_missing');

            return $result;
        }

        try {
            $getId3 = new \getID3();
            $fileInfo = $getId3->analyze($filePath);

            if (isset($fileInfo['playtime_seconds'])) {
                $result['duration'] = (int) round($fileInfo['playtime_seconds']);
            }

            if (! empty($fileInfo['filesize'])) {
                $result['length'] = (int) $fileInfo['filesize'];
            } else {
                $fileSize = @filesize($filePath);
                $result['length'] = $fileSize !== false ? (int) $fileSize : null;
            }

            if (! empty($fileInfo['mime_type'])) {
                $result['mime'] = (string) $fileInfo['mime_type'];
            }
        } catch (\Exception $exception) {
            $this->setAudioMetaLastError($postId, 'getid3_error', $exception->getMessage());

            return $result;
        }

        if (! empty($result['duration'])) {
            $this->clearAudioMetaLastError($postId);
        }

        $result['mime'] = $this->normalizeAudioMimeForUrl($result['mime'], $audioUrl, $filePath);

        return $result;
    }

    /**
     * Resolve an audio URL or local path to a readable file path.
     *
     * @param string $audioUrl Audio URL or local path.
     * @return string
     */
    private function resolveAudioFilePath(string $audioUrl): string
    {
        if ($audioUrl === '') {
            return '';
        }

        if (file_exists($audioUrl)) {
            return $audioUrl;
        }

        $uploadDir = wp_get_upload_dir();
        $baseUrl = isset($uploadDir['baseurl']) ? (string) $uploadDir['baseurl'] : '';
        $baseDir = isset($uploadDir['basedir']) ? (string) $uploadDir['basedir'] : '';

        if ($baseUrl !== '' && $baseDir !== '' && str_starts_with($audioUrl, $baseUrl)) {
            $relativePath = ltrim(substr($audioUrl, strlen($baseUrl)), '/');
            $candidate = trailingslashit($baseDir) . rawurldecode($relativePath);

            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        $urlPath = (string) wp_parse_url($audioUrl, PHP_URL_PATH);

        if ($urlPath !== '') {
            $candidate = ABSPATH . ltrim(rawurldecode($urlPath), '/');

            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        return '';
    }

    /**
     * Normalize detected MIME type based on file extension.
     *
     * @param string|null $mime Detected MIME type.
     * @param string $audioUrl Audio URL.
     * @param string $filePath Local file path.
     * @return string|null
     */
    private function normalizeAudioMimeForUrl(?string $mime, string $audioUrl, string $filePath = ''): ?string
    {
        $path = (string) wp_parse_url($audioUrl, PHP_URL_PATH);
        $path = $path !== '' ? $path : $audioUrl;
        $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

        if ($ext === '' && $filePath !== '') {
            $ext = strtolower((string) pathinfo($filePath, PATHINFO_EXTENSION));
        }

        if ($ext === 'm4a') {
            return 'audio/x-m4a';
        }

        return $mime !== '' ? $mime : null;
    }

    /**
     * Read an Episode Details field from native post meta.
     *
     * @param int $postId Post ID.
     * @param string $key Meta key without the leading underscore.
     * @return string
     */
    private function getEpisodeFieldValue(int $postId, string $key): string
    {
        $value = self::getStoredPostMetaValue($postId, $key, '');

        if (is_string($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        return '';
    }

    /**
     * Persist an Episode Details field using native post meta.
     *
     * @param int $postId Post ID.
     * @param string $key Meta key without the leading underscore.
     * @param string|int $value Meta value.
     * @return void
     */
    private function setEpisodeFieldValue(int $postId, string $key, string|int $value): void
    {
        update_post_meta($postId, self::storedFieldKey($key), $value);
    }

    /**
     * Persist the last audio metadata error in a structured way.
     *
     * @param int $postId Post ID.
     * @param string $code Error code.
     * @param string $detail Error detail.
     * @return void
     */
    private function setAudioMetaLastError(int $postId, string $code, string $detail = ''): void
    {
        update_post_meta($postId, self::audioMetaLastErrorKey(), [
            'code' => $code,
            'detail' => $detail,
        ]);
    }

    /**
     * Clear the last audio metadata error.
     *
     * @param int $postId Post ID.
     * @return void
     */
    private function clearAudioMetaLastError(int $postId): void
    {
        delete_post_meta($postId, self::audioMetaLastErrorKey());
    }

    /**
     * Show the last audio metadata extraction error on the editor screen.
     *
     * @return void
     */
    public function showAudioMetaErrorNotice(): void
    {
        if (! is_admin() || ! function_exists('get_current_screen')) {
            return;
        }

        $screen = get_current_screen();

        if (! $screen || $screen->post_type !== self::slug() || ! current_user_can('manage_options')) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only reading the current post ID for an admin notice.
        $postId = isset($_GET['post']) ? (int) $_GET['post'] : 0;

        if ($postId <= 0) {
            return;
        }

        $lastError = get_post_meta($postId, self::audioMetaLastErrorKey(), true);

        if (empty($lastError)) {
            return;
        }

        $message = $this->audioMetaErrorMessage($postId, $lastError);

        if ($message === '') {
            return;
        }

        echo '<div class="notice notice-warning"><p>' . esc_html($message) . '</p></div>';
    }

    /**
     * Return a translated audio metadata error message.
     *
     * @param int $postId Post ID.
     * @param mixed $lastError Stored error payload.
     * @return string
     */
    private function audioMetaErrorMessage(int $postId, mixed $lastError): string
    {
        if (! is_array($lastError) || empty($lastError['code'])) {
            return (string) $lastError;
        }

        $code = (string) $lastError['code'];
        $detail = isset($lastError['detail']) ? (string) $lastError['detail'] : '';

        return match ($code) {
            'getid3_missing' => sprintf(
                /* translators: %d: episode post ID */
                __('Episode #%d: getID3 not available', 'sage'),
                $postId
            ),
            'audio_file_missing' => sprintf(
                /* translators: %d: episode post ID */
                __('Episode #%d: audio file missing for duration/size/mime detection', 'sage'),
                $postId
            ),
            'getid3_error' => sprintf(
                /* translators: 1: episode post ID, 2: error message */
                __('Episode #%1$d: getID3 error - %2$s', 'sage'),
                $postId,
                $detail
            ),
            default => $detail,
        };
    }

    /**
     * Read one current-format Episode Details meta value.
     *
     * @param int $postId Post ID.
     * @param string $key Meta key without leading underscore.
     * @param mixed $default Default value.
     * @return mixed
     */
    private static function getStoredPostMetaValue(int $postId, string $key, mixed $default = null): mixed
    {
        $value = get_post_meta($postId, self::storedFieldKey($key), true);

        return $value !== '' ? $value : $default;
    }

    /**
     * Register a string post meta key.
     *
     * @param string $postType Post type key.
     * @param string $key Meta key.
     * @param bool $isUrl Whether the value is a URL.
     * @return void
     */
    private function registerStringMeta(string $postType, string $key, bool $isUrl = false): void
    {
        $schema = [
            'type' => 'string',
            'default' => '',
            'context' => ['view', 'edit'],
        ];

        if ($isUrl) {
            $schema['format'] = 'uri';
        }

        register_post_meta($postType, self::storedFieldKey($key), [
            'type' => 'string',
            'single' => true,
            'sanitize_callback' => $isUrl ? 'esc_url_raw' : 'sanitize_text_field',
            'auth_callback' => '__return_true',
            'show_in_rest' => ['schema' => $schema],
        ]);
    }

    /**
     * Register an integer post meta key.
     *
     * @param string $postType Post type key.
     * @param string $key Meta key.
     * @return void
     */
    private function registerIntMeta(string $postType, string $key): void
    {
        register_post_meta($postType, self::storedFieldKey($key), [
            'type' => 'integer',
            'single' => true,
            'sanitize_callback' => 'absint',
            'auth_callback' => '__return_true',
            'show_in_rest' => [
                'schema' => [
                    'type' => 'integer',
                    'default' => 0,
                    'context' => ['view', 'edit'],
                ],
            ],
        ]);
    }

    /**
     * Register an array post meta key.
     *
     * @param string $postType Post type key.
     * @param string $key Meta key.
     * @param array<string,mixed> $itemsSchema REST item schema.
     * @return void
     */
    private function registerArrayMeta(string $postType, string $key, array $itemsSchema): void
    {
        register_post_meta($postType, self::storedFieldKey($key), [
            'type' => 'array',
            'single' => true,
            'auth_callback' => '__return_true',
            'show_in_rest' => [
                'schema' => [
                    'type' => 'array',
                    'default' => [],
                    'context' => ['view', 'edit'],
                    'items' => $itemsSchema,
                ],
            ],
        ]);
    }
}
