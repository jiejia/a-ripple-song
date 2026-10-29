<?php

namespace Jiejia\ARippleSong\CPTs;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Abstracts\CPTAbstract;
use Jiejia\ARippleSong\Feeds\PodcastFeed;
use Jiejia\ARippleSong\Libraries\Users;

/**
 * Defines the podcast episode custom post type.
 */
class PodcastCPT extends CPTAbstract
{
    public string $slug = 'podcast';

    /**
     * Return the singular custom post type name.
     *
     * @return string
     */
    public  function singularName(): string
    {
        return __('Episode', 'a-ripple-song');
    }

    /**
     * Return the plural custom post type name.
     *
     * @return string
     */
    public  function pluralName(): string
    {
        return __('Episodes', 'a-ripple-song');
    }

    /**
     * Return field definitions rendered in the episode meta box.
     *
     * Keys are short names; PostMetaStore stores them as _ars_podcast_{key}.
     *
     * @return array<int,array<string,mixed>>
     */
    public function fields(): array
    {
        return [
            [
                'type' => 'separator',
                'label' => __('Audio', 'a-ripple-song'),
            ],
            [
                'type' => 'file',
                'key' => 'audio_file',
                'label' => __('Audio File', 'a-ripple-song'),
                'media_type' => 'audio',
                'required' => true,
                // Instant preview when picking from the media library; detectAudioMeta() stores the real values on save.
                'autofill' => [
                    'duration' => 'duration',
                    'audio_length' => 'filesize',
                    'audio_mime' => 'mime',
                ],
                'help' => __('Required. Upload an audio file; the saved value is the audio file URL.', 'a-ripple-song'),
            ],
            [
                'type' => 'number',
                'key' => 'duration',
                'label' => __('Duration (seconds)', 'a-ripple-song'),
                'width' => 'third',
                'readonly' => true,
                'attributes' => ['min' => 0, 'step' => 1],
                'help' => __('Auto detected from "Audio File".', 'a-ripple-song'),
            ],
            [
                'type' => 'number',
                'key' => 'audio_length',
                'label' => __('Audio Length (bytes)', 'a-ripple-song'),
                'width' => 'third',
                'readonly' => true,
                'attributes' => ['min' => 0, 'step' => 1],
                'help' => __('Auto detected from "Audio File".', 'a-ripple-song'),
            ],
            [
                'type' => 'text',
                'key' => 'audio_mime',
                'label' => __('Audio MIME Type', 'a-ripple-song'),
                'width' => 'third',
                'readonly' => true,
                'default' => 'audio/mpeg',
                'help' => __('Auto detected from "Audio File".', 'a-ripple-song'),
            ],

            [
                'type' => 'separator',
                'label' => __('Podcast Metadata', 'a-ripple-song'),
            ],
            [
                'type' => 'image',
                'key' => 'episode_image',
                'label' => __('Episode Cover (square)', 'a-ripple-song'),
                ...PodcastFeed::ARTWORK_RULES,
                'help' => __('Optional. Square JPG/PNG between 1400-3000px, at most 512 KB, without transparency. Overrides channel cover.', 'a-ripple-song'),
            ],
            [
                'type' => 'select',
                'key' => 'episode_type',
                'label' => __('Episode Type', 'a-ripple-song'),
                'width' => 'third',
                'options' => [
                    'full' => __('full', 'a-ripple-song'),
                    'trailer' => __('trailer', 'a-ripple-song'),
                    'bonus' => __('bonus', 'a-ripple-song'),
                ],
                'default' => 'full',
                'help' => __('Required. full / trailer / bonus.', 'a-ripple-song'),
            ],
            [
                'type' => 'number',
                'key' => 'episode_number',
                'label' => __('Episode Number', 'a-ripple-song'),
                'width' => 'third',
                'attributes' => ['min' => 0, 'step' => 1],
                'help' => __('Optional but recommended. Integer.', 'a-ripple-song'),
            ],
            [
                'type' => 'number',
                'key' => 'season_number',
                'label' => __('Season Number', 'a-ripple-song'),
                'width' => 'third',
                'attributes' => ['min' => 0, 'step' => 1],
                'help' => __('Optional. Integer.', 'a-ripple-song'),
            ],
            [
                'type' => 'select',
                'key' => 'episode_explicit',
                'label' => __('Explicit', 'a-ripple-song'),
                'width' => 'half',
                'required' => true,
                'options' => [
                    'clean' => __('clean', 'a-ripple-song'),
                    'explicit' => __('explicit', 'a-ripple-song'),
                ],
                'default' => 'clean',
                'help' => __('Required. clean / explicit.', 'a-ripple-song'),
            ],
            [
                'type' => 'switch',
                'key' => 'episode_block',
                'label' => __('iTunes Block', 'a-ripple-song'),
                'width' => 'half',
                'default' => 'no',
                'help' => __('Optional. Turn on to hide this episode in Apple Podcasts.', 'a-ripple-song'),
            ],
            [
                'type' => 'text',
                'key' => 'itunes_title',
                'label' => __('iTunes Title (optional)', 'a-ripple-song'),
                'width' => 'half',
                'help' => __('Optional. Apple Podcasts: overrides the episode title for <itunes:title>.', 'a-ripple-song'),
            ],
            [
                'type' => 'text',
                'key' => 'episode_author',
                'label' => __('Episode Author (override)', 'a-ripple-song'),
                'width' => 'half',
                'help' => __('Optional. Overrides channel author for this episode.', 'a-ripple-song'),
            ],
            [
                'type' => 'textarea',
                'key' => 'episode_subtitle',
                'label' => __('Subtitle', 'a-ripple-song'),
                'help' => __('Optional. Short subtitle for iTunes.', 'a-ripple-song'),
            ],
            [
                'type' => 'textarea',
                'key' => 'episode_summary',
                'label' => __('Summary', 'a-ripple-song'),
                'help' => __('Optional. Plain text summary for iTunes.', 'a-ripple-song'),
            ],

            [
                'type' => 'separator',
                'label' => __('Podcasting 2.0 & Advanced', 'a-ripple-song'),
            ],
            [
                'type' => 'url',
                'key' => 'episode_transcript',
                'label' => __('Transcript (optional)', 'a-ripple-song'),
                'width' => 'half',
                'help' => __('Optional. Transcript file URL (vtt/srt/txt/pdf).', 'a-ripple-song'),
            ],
            [
                'type' => 'text',
                'key' => 'episode_guid',
                'label' => __('Custom GUID (optional)', 'a-ripple-song'),
                'width' => 'half',
                'help' => __('Optional. If empty, feed uses WP permalink as GUID.', 'a-ripple-song'),
            ],
            [
                'type' => 'url',
                'key' => 'episode_chapters',
                'label' => __('Chapters (Podcasting 2.0)', 'a-ripple-song'),
                'width' => 'half',
                'help' => __('Optional. Provide a chapters JSON URL/file for <podcast:chapters>.', 'a-ripple-song'),
            ],
            [
                'type' => 'text',
                'key' => 'episode_chapters_type',
                'label' => __('Chapters MIME Type', 'a-ripple-song'),
                'width' => 'half',
                'default' => 'application/json+chapters',
                'help' => __('Optional. Defaults to application/json+chapters.', 'a-ripple-song'),
            ],
            [
                'type' => 'complex',
                'key' => 'episode_soundbites',
                'label' => __('Soundbites', 'a-ripple-song'),
                'row_label' => __('Soundbite', 'a-ripple-song'),
                'row_title_field' => 'title',
                'help' => __('Optional. Adds one or more <podcast:soundbite> tags.', 'a-ripple-song'),
                'fields' => [
                    [
                        'type' => 'number',
                        'key' => 'start_time',
                        'label' => __('Start Time (seconds)', 'a-ripple-song'),
                        'width' => 'third',
                        'attributes' => ['min' => 0, 'step' => '0.01'],
                    ],
                    [
                        'type' => 'number',
                        'key' => 'duration',
                        'label' => __('Duration (seconds)', 'a-ripple-song'),
                        'width' => 'third',
                        'attributes' => ['min' => '0.01', 'step' => '0.01'],
                    ],
                    [
                        'type' => 'text',
                        'key' => 'title',
                        'label' => __('Title (optional)', 'a-ripple-song'),
                        'width' => 'third',
                    ],
                ],
            ],

            [
                'type' => 'separator',
                'label' => __('People', 'a-ripple-song'),
            ],
            [
                'type' => 'multiselect',
                'key' => 'members',
                'label' => __('Members', 'a-ripple-song'),
                'width' => 'half',
                'options' => Users::options(...),
                'default' => fn (): array => $this->defaultMemberIds(),
                'help' => __('Select episode members (administrators, authors, editors).', 'a-ripple-song'),
            ],
            [
                'type' => 'multiselect',
                'key' => 'guests',
                'label' => __('Guests', 'a-ripple-song'),
                'width' => 'half',
                'options' => Users::options(...),
                'help' => __('Select episode guests (contributors).', 'a-ripple-song'),
            ],
        ];
    }

    /**
     * Return default member IDs for newly created episodes.
     *
     * @return array<int,int>
     */
    private function defaultMemberIds(): array
    {
        $currentUser = wp_get_current_user();
        $allowedRoles = ['administrator', 'author', 'editor'];

        if (! $currentUser->exists() || array_intersect($allowedRoles, (array) $currentUser->roles) === []) {
            return [];
        }

        return [(int) $currentUser->ID];
    }

    /**
     * Register extra hooks required by podcast episodes.
     *
     * @return void
     */
    public function registerHooks(): void
    {
        add_action('a_ripple_song_post_fields_saved', [$this, 'fillAudioMeta'], 10, 2);
        add_action('init', [$this, 'registerPlayCountMeta']);
        add_action('save_post_' . $this->slug(), [$this, 'ensurePlayCount']);
        add_action('add_meta_boxes_' . $this->slug(), [$this, 'addStatisticsMetaBox']);
        add_filter('upload_mimes', [$this, 'addM4aMime']);
        add_filter('wp_check_filetype_and_ext', [$this, 'acceptM4aUpload'], 10, 5);
    }

    /**
     * Register .m4a / .m4b as audio/x-m4a, the type Apple Podcasts expects, instead of core's audio/mpeg (MP3).
     *
     * wp_check_filetype() takes the first matching entry, so it goes before core's "mp3|m4a|m4b".
     *
     * @param array<string,string> $mimes Allowed MIME types keyed by extension pattern.
     * @return array<string,string>
     */
    public function addM4aMime(array $mimes): array
    {
        // Only where audio uploads are allowed at all.
        if (! in_array('audio/mpeg', $mimes, true)) {
            return $mimes;
        }

        return ['m4a|m4b' => 'audio/x-m4a'] + $mimes;
    }

    /**
     * Let .m4a / .m4b files through the upload type check when their content is an MP4 container.
     *
     * Many encoders (ffmpeg, most editors) write an "isom" / "mp42" brand that file type detection reports as
     * video/mp4, and core rejects a video/* file with an audio extension.
     *
     * @param array{ext:string|false,type:string|false,proper_filename:string|false} $data Result of the check.
     * @param string $file Full path of the uploaded file.
     * @param string $filename File name.
     * @param array<string,string>|null $mimes Allowed MIME types, null for the user's defaults.
     * @param string|false $realMime MIME type detected from the content.
     * @return array{ext:string|false,type:string|false,proper_filename:string|false}
     */
    public function acceptM4aUpload(array $data, string $file, string $filename, ?array $mimes, string|false $realMime): array
    {
        $extension = strtolower((string) pathinfo($filename, PATHINFO_EXTENSION));

        if (
            ! empty($data['ext'])
            || ! in_array($extension, ['m4a', 'm4b'], true)
            || ! in_array($realMime, ['video/mp4', 'audio/mp4', 'audio/x-m4a', 'audio/m4a'], true)
            || wp_check_filetype($filename, $mimes)['type'] === false
        ) {
            return $data;
        }

        return ['ext' => $extension, 'type' => 'audio/x-m4a', 'proper_filename' => $data['proper_filename'] ?? false];
    }

    /**
     * Register the play count meta (_ars_podcast_play_count).
     *
     * @return void
     */
    public function registerPlayCountMeta(): void
    {
        register_post_meta($this->slug(), $this->fieldMetaKey('play_count'), [
            'type' => 'integer',
            'single' => true,
            'default' => 0,
            // Only the plays REST endpoint changes it, never the editor.
            'auth_callback' => '__return_false',
        ]);
    }

    /**
     * Store an explicit 0 play count on new episodes.
     *
     * Episodes without the meta row would drop out of "orderby meta_value_num" queries.
     *
     * @param int $postId Post ID.
     * @return void
     */
    public function ensurePlayCount(int $postId): void
    {
        add_post_meta($postId, $this->fieldMetaKey('play_count'), 0, true);
    }

    /**
     * Return the play count of an episode.
     *
     * @param int $postId Post ID.
     * @return int
     */
    public function playCount(int $postId): int
    {
        return max(0, (int) get_post_meta($postId, $this->fieldMetaKey('play_count'), true));
    }

    /**
     * Add one play and return the new count.
     *
     * @param int $postId Post ID.
     * @return int
     */
    public function incrementPlayCount(int $postId): int
    {
        global $wpdb;

        $metaKey = $this->fieldMetaKey('play_count');

        // A single UPDATE keeps concurrent plays from overwriting each other (read + write would lose counts).
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Atomic increment; the post_meta cache is cleared below.
        $updated = $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->postmeta} SET meta_value = CAST(meta_value AS UNSIGNED) + 1 WHERE post_id = %d AND meta_key = %s",
            $postId,
            $metaKey
        ));

        if (! $updated) {
            add_post_meta($postId, $metaKey, 1, true);
        }

        wp_cache_delete($postId, 'post_meta');

        return $this->playCount($postId);
    }

    /**
     * Add the statistics box to the editor sidebar.
     *
     * @return void
     */
    public function addStatisticsMetaBox(): void
    {
        add_meta_box(
            $this->slug() . '_statistics',
            __('Statistics', 'a-ripple-song'),
            [$this, 'renderStatisticsMetaBox'],
            $this->slug(),
            'side'
        );
    }

    /**
     * Render the statistics box.
     *
     * @param \WP_Post $post Current post.
     * @return void
     */
    public function renderStatisticsMetaBox(\WP_Post $post): void
    {
        printf(
            '<div class="ars-stats"><div class="ars-stat"><span class="ars-stat__label"><span class="dashicons dashicons-controls-play" aria-hidden="true"></span>%s</span><span class="ars-stat__value">%s</span></div></div>',
            esc_html__('Plays', 'a-ripple-song'),
            esc_html(number_format_i18n($this->playCount($post->ID)))
        );
    }

    /**
     * Detect duration, size and MIME type of the episode audio after its fields are saved.
     *
     * @param int $postId Post ID.
     * @param CPTAbstract $postType Post type whose fields were saved.
     * @return void
     */
    public function fillAudioMeta(int $postId, CPTAbstract $postType): void
    {
        if ($postType->slug() !== $this->slug()) {
            return;
        }

        $audio = $this->detectAudioMeta((string) get_post_meta($postId, $this->fieldMetaKey('audio_file'), true));

        update_post_meta($postId, $this->fieldMetaKey('duration'), $audio['duration']);
        update_post_meta($postId, $this->fieldMetaKey('audio_length'), $audio['length']);
        update_post_meta($postId, $this->fieldMetaKey('audio_mime'), $audio['mime']);
    }

    /**
     * Read audio metadata of a media library or other local audio file.
     *
     * Values that cannot be detected (e.g. external URLs) are returned as empty strings.
     *
     * @param string $url Audio URL.
     * @return array{duration:int|string,length:int|string,mime:string}
     */
    private function detectAudioMeta(string $url): array
    {
        $result = ['duration' => '', 'length' => '', 'mime' => ''];

        if ($url === '') {
            return $result;
        }

        $attachmentId = attachment_url_to_postid($url);
        $path = $attachmentId > 0 ? (string) get_attached_file($attachmentId) : $this->localAudioPath($url);
        // Attachments were analysed with getID3 on upload; other local files are read now.
        $meta = $attachmentId > 0 ? wp_get_attachment_metadata($attachmentId) : false;

        if (! is_array($meta) && $path !== '') {
            require_once ABSPATH . 'wp-admin/includes/media.php';
            $meta = wp_read_audio_metadata($path);
        }

        if (is_array($meta)) {
            $result['duration'] = isset($meta['length']) ? (int) $meta['length'] : '';
            $result['length'] = isset($meta['filesize']) ? (int) $meta['filesize'] : '';
            $result['mime'] = (string) ($meta['mime_type'] ?? '');
        }

        if ($result['length'] === '' && $path !== '') {
            $result['length'] = (int) filesize($path);
        }

        $extension = strtolower((string) pathinfo((string) wp_parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));

        if ($extension === 'm4a') {
            // Apple Podcasts expects audio/x-m4a for .m4a enclosures.
            $result['mime'] = 'audio/x-m4a';
        } elseif ($result['mime'] === '') {
            $result['mime'] = (string) (wp_check_filetype('file.' . $extension)['type'] ?: '');
        }

        return $result;
    }

    /**
     * Resolve a URL on this site to a readable local file path.
     *
     * @param string $url Audio URL.
     * @return string Empty string when the file is not local.
     */
    private function localAudioPath(string $url): string
    {
        $uploadDir = wp_get_upload_dir();
        $candidates = [];

        if (! empty($uploadDir['baseurl']) && str_starts_with($url, $uploadDir['baseurl'])) {
            $candidates[] = trailingslashit($uploadDir['basedir']) . rawurldecode(ltrim(substr($url, strlen($uploadDir['baseurl'])), '/'));
        }

        if (str_starts_with($url, home_url('/'))) {
            $candidates[] = ABSPATH . ltrim(rawurldecode((string) wp_parse_url($url, PHP_URL_PATH)), '/');
        }

        foreach ($candidates as $candidate) {
            if (is_readable($candidate)) {
                return $candidate;
            }
        }

        return '';
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
        // Place the top-level menu right below Posts (position 5).
        $args['menu_position'] = 6;
        $args['rewrite'] = [
            'slug' => 'podcasts',
        ];

        return $args;
    }
}
