<?php

use Carbon_Fields\Field;
use Carbon_Fields\Widget;

/**
 * Authors Widget
 * Display the authors list (members and guests).
 */
class AuthorsWidget extends Widget
{
    /**
     * Keep the original WordPress widget id for existing widget instances.
     *
     * @var string
     */
    protected $widget_id_prefix = '';

    /**
     * Create the widget and its Carbon Fields admin form.
     */
    public function __construct()
    {
        $this->setup(
            'authors_widget',
            __('aripplesong - Authors List', 'sage'),
            __('Display members and guest authors list', 'sage'),
            [
                Field::make('text', 'authors_members_title', __('Members Title:', 'sage'))
                    ->set_default_value('Members'),
                Field::make('checkbox', 'authors_show_members', __('Show Members (Administrators, Editors, Authors)', 'sage'))
                    ->set_option_value('1')
                    ->set_default_value(true),
                Field::make('text', 'authors_guests_title', __('Guests Title:', 'sage'))
                    ->set_default_value('Guests'),
                Field::make('checkbox', 'authors_show_guests', __('Show Guests (Contributors)', 'sage'))
                    ->set_option_value('1')
                    ->set_default_value(true),
            ]
        );
    }

    /**
     * Render the Carbon Fields form with legacy checkbox values normalized.
     *
     * @param  array<string,mixed>  $instance  Saved widget instance values.
     */
    public function form($instance)
    {
        $instance = $this->withLegacyAliases($instance, [
            'authors_members_title' => 'members_title',
            'authors_show_members' => 'show_members',
            'authors_guests_title' => 'guests_title',
            'authors_show_guests' => 'show_guests',
        ]);

        parent::form($this->normalizeCheckboxes($instance, ['authors_show_members', 'authors_show_guests']));
    }

    /**
     * Render the widget with values normalized from legacy and Carbon Fields storage.
     *
     * @param  array<string,mixed>  $args  Widget wrapper arguments.
     * @param  array<string,mixed>  $instance  Saved widget instance values.
     */
    public function widget($args, $instance)
    {
        echo $args['before_widget'];

        $members_title = $this->textValue($instance, ['authors_members_title', 'members_title'], 'Members');
        $guests_title = $this->textValue($instance, ['authors_guests_title', 'guests_title'], 'Guests');
        $show_members = $this->booleanValue($instance, ['authors_show_members', 'show_members'], true);
        $show_guests = $this->booleanValue($instance, ['authors_show_guests', 'show_guests'], true);

        // Members (administrators, editors, authors).
        $members = get_users([
            'role__in' => ['administrator', 'editor', 'author'],
            'orderby' => 'display_name',
            'order' => 'ASC',
        ]);

        // Guests (contributors).
        $contributors = get_users([
            'role' => 'contributor',
            'orderby' => 'display_name',
            'order' => 'ASC',
        ]);

        // Precompute base post counts to avoid repeated queries inside the loops.
        $post_counts_by_user = [];
        $podcast_counts_by_user = [];
        if (function_exists('count_many_users_posts')) {
            $all_users = array_merge($members ?: [], $contributors ?: []);
            $user_ids = array_values(array_unique(array_map(static function ($user) {
                return (int) $user->ID;
            }, $all_users)));

            if (! empty($user_ids)) {
                $post_counts_by_user = count_many_users_posts($user_ids, 'post', true);
                $podcast_counts_by_user = count_many_users_posts($user_ids, 'podcast', true);
            }
        }

        echo \Roots\view('widgets.authors', [
            'members_title' => $members_title,
            'guests_title' => $guests_title,
            'show_members' => $show_members,
            'show_guests' => $show_guests,
            'members' => $members,
            'contributors' => $contributors,
            'post_counts_by_user' => $post_counts_by_user,
            'podcast_counts_by_user' => $podcast_counts_by_user,
        ])->render();

        echo $args['after_widget'];
    }

    /**
     * Return a text setting with a fallback.
     *
     * @param  array<string,mixed>  $instance  Saved widget instance values.
     * @param  string  $key  Instance key.
     * @param  string  $default  Fallback value.
     */
    private function textValue(array $instance, string|array $keys, string $default): string
    {
        foreach ((array) $keys as $key) {
            if (isset($instance[$key]) && $instance[$key] !== '') {
                return sanitize_text_field($instance[$key]);
            }
        }

        return $default;
    }

    /**
     * Return a checkbox setting from legacy and Carbon Fields values.
     *
     * @param  array<string,mixed>  $instance  Saved widget instance values.
     * @param  string  $key  Instance key.
     * @param  bool  $default  Fallback value.
     */
    private function booleanValue(array $instance, string|array $keys, bool $default): bool
    {
        foreach ((array) $keys as $key) {
            if (array_key_exists($key, $instance)) {
                return in_array($instance[$key], [true, 1, '1', 'yes', 'on'], true);
            }
        }

        return $default;
    }

    /**
     * Normalize legacy checkbox values before Carbon Fields renders the form.
     *
     * @param  array<string,mixed>  $instance  Saved widget instance values.
     * @param  array<int,string>  $keys  Checkbox keys.
     * @return array<string,mixed>
     */
    private function normalizeCheckboxes(array $instance, array $keys): array
    {
        foreach ($keys as $key) {
            if (! array_key_exists($key, $instance)) {
                continue;
            }

            $instance[$key] = $this->booleanValue($instance, $key, false) ? '1' : '';
        }

        return $instance;
    }

    /**
     * Copy legacy instance values to unique Carbon Fields keys for editing.
     *
     * @param  array<string,mixed>  $instance  Saved widget instance values.
     * @param  array<string,string>  $aliases  New key to legacy key map.
     * @return array<string,mixed>
     */
    private function withLegacyAliases(array $instance, array $aliases): array
    {
        foreach ($aliases as $newKey => $legacyKey) {
            if (! array_key_exists($newKey, $instance) && array_key_exists($legacyKey, $instance)) {
                $instance[$newKey] = $instance[$legacyKey];
            }
        }

        return $instance;
    }
}
