<?php

namespace App\Widgets;

use App\Abstracts\WidgetAbstract;
use App\CustomPostTypes\Episode;

/**
 * Authors Widget
 * Display the authors list (members and guests).
 */
class AuthorsWidget extends WidgetAbstract
{
    /**
     * Return the WordPress widget id base.
     */
    public static function idBase(): string
    {
        return 'authors_widget';
    }

    /**
     * Return Carbon-prefixed instance keys mapped to standard widget keys.
     *
     * @return array<string,string>
     */
    public static function instanceAliases(): array
    {
        return [
            'authors_members_title' => 'members_title',
            'authors_show_members' => 'show_members',
            'authors_guests_title' => 'guests_title',
            'authors_show_guests' => 'show_guests',
        ];
    }

    /**
     * Register widget with WordPress.
     */
    public function __construct()
    {
        parent::__construct(
            static::idBase(),
            __('aripplesong - Authors List', 'sage'),
            ['description' => __('Display members and guest authors list', 'sage')]
        );
    }

    /**
     * Front-end display of widget.
     *
     * @param  array<string,mixed>  $args  Widget wrapper arguments.
     * @param  array<string,mixed>  $instance  Saved widget option values.
     */
    public function widget($args, $instance): void
    {
        echo $args['before_widget'];

        $membersTitle = ! empty($instance['members_title'])
            ? sanitize_text_field((string) $instance['members_title'])
            : __('Members', 'sage');

        $guestsTitle = ! empty($instance['guests_title'])
            ? sanitize_text_field((string) $instance['guests_title'])
            : __('Guests', 'sage');

        $showMembers = isset($instance['show_members']) ? (bool) $instance['show_members'] : true;
        $showGuests = isset($instance['show_guests']) ? (bool) $instance['show_guests'] : true;

        $members = get_users([
            'role__in' => ['administrator', 'editor', 'author'],
            'orderby' => 'display_name',
            'order' => 'ASC',
        ]);

        $contributors = get_users([
            'role' => 'contributor',
            'orderby' => 'display_name',
            'order' => 'ASC',
        ]);

        $postCountsByUser = [];
        $podcastCountsByUser = [];

        if (function_exists('count_many_users_posts')) {
            $allUsers = array_merge($members ?: [], $contributors ?: []);
            $userIds = array_values(array_unique(array_map(static function ($user) {
                return (int) $user->ID;
            }, $allUsers)));

            if (! empty($userIds)) {
                $postCountsByUser = count_many_users_posts($userIds, 'post', true);
                $podcastCountsByUser = count_many_users_posts($userIds, Episode::slug(), true);
            }
        }

        echo \Roots\view('widgets.authors', [
            'members_title' => $membersTitle,
            'guests_title' => $guestsTitle,
            'show_members' => $showMembers,
            'show_guests' => $showGuests,
            'members' => $members,
            'contributors' => $contributors,
            'post_counts_by_user' => $postCountsByUser,
            'podcast_counts_by_user' => $podcastCountsByUser,
        ])->render();

        echo $args['after_widget'];
    }

    /**
     * Back-end widget form displayed in the WordPress admin.
     *
     * @param  array<string,mixed>  $instance  Current widget settings.
     */
    public function form($instance): void
    {
        $membersTitle = ! empty($instance['members_title']) ? $instance['members_title'] : __('Members', 'sage');
        $guestsTitle = ! empty($instance['guests_title']) ? $instance['guests_title'] : __('Guests', 'sage');
        $showMembers = isset($instance['show_members']) ? (bool) $instance['show_members'] : true;
        $showGuests = isset($instance['show_guests']) ? (bool) $instance['show_guests'] : true;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('members_title')); ?>">
                <?php esc_html_e('Members Title:', 'sage'); ?>
            </label>
            <input class="widefat"
                   id="<?php echo esc_attr($this->get_field_id('members_title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('members_title')); ?>"
                   type="text"
                   value="<?php echo esc_attr($membersTitle); ?>">
        </p>

        <p>
            <input class="checkbox"
                   type="checkbox"
                   <?php checked($showMembers); ?>
                   id="<?php echo esc_attr($this->get_field_id('show_members')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('show_members')); ?>">
            <label for="<?php echo esc_attr($this->get_field_id('show_members')); ?>">
                <?php esc_html_e('Show Members (Administrators, Editors, Authors)', 'sage'); ?>
            </label>
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('guests_title')); ?>">
                <?php esc_html_e('Guests Title:', 'sage'); ?>
            </label>
            <input class="widefat"
                   id="<?php echo esc_attr($this->get_field_id('guests_title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('guests_title')); ?>"
                   type="text"
                   value="<?php echo esc_attr($guestsTitle); ?>">
        </p>

        <p>
            <input class="checkbox"
                   type="checkbox"
                   <?php checked($showGuests); ?>
                   id="<?php echo esc_attr($this->get_field_id('show_guests')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('show_guests')); ?>">
            <label for="<?php echo esc_attr($this->get_field_id('show_guests')); ?>">
                <?php esc_html_e('Show Guests (Contributors)', 'sage'); ?>
            </label>
        </p>
        <?php
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @param  array<string,mixed>  $newInstance  New widget settings submitted from the form.
     * @param  array<string,mixed>  $oldInstance  Previous widget settings.
     * @return array<string,mixed>
     */
    public function update($newInstance, $oldInstance): array
    {
        return [
            'members_title' => ! empty($newInstance['members_title'])
                ? sanitize_text_field((string) $newInstance['members_title'])
                : '',
            'guests_title' => ! empty($newInstance['guests_title'])
                ? sanitize_text_field((string) $newInstance['guests_title'])
                : '',
            'show_members' => ! empty($newInstance['show_members']) ? 1 : 0,
            'show_guests' => ! empty($newInstance['show_guests']) ? 1 : 0,
        ];
    }
}
