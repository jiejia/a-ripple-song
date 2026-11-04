<?php

namespace Jiejia\ARippleSong\Settings;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Abstracts\SettingAbstract;
use Jiejia\ARippleSong\Libraries\Users;

/**
 * Member settings page: the WordPress users who make the podcast, stored in the ars_member_settings option.
 */
class MemberSetting extends SettingAbstract
{
    public string $slug = 'member_settings';

    public function title(): string
    {
        return __('Member Settings', 'a-ripple-song');
    }

    /**
     * Return member setting field definitions.
     *
     * @return array<int,array<string,mixed>>
     */
    public function fields(): array
    {
        return [
            [
                'type' => 'separator',
                'label' => __('Podcast Members', 'a-ripple-song'),
            ],
            [
                'type' => 'multiselect',
                'key' => 'members',
                'label' => __('Members', 'a-ripple-song'),
                'options' => Users::options(...),
                'help' => __('Select one or more WordPress users who make this podcast.', 'a-ripple-song'),
            ],
        ];
    }

    /**
     * Return the podcast members in the saved order, skipping users that no longer exist.
     *
     * @return array<int,\WP_User>
     */
    public function users(): array
    {
        $users = array_map(
            static fn (mixed $id): \WP_User|false => get_userdata((int) $id),
            (array) $this->value('members')
        );

        return array_values(array_filter($users));
    }
}
