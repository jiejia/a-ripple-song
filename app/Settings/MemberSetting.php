<?php

namespace Jiejia\ARippleSong\Settings;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Abstracts\SettingAbstract;
use Jiejia\ARippleSong\Libraries\Users;

/**
 * Member settings page: the WordPress users who make the podcast and appear as guests, stored in the ars_member_settings option.
 *
 * Episodes can only pick their members and guests from the users chosen here; the role fields narrow which users can be chosen.
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
                'key' => 'member_roles',
                'label' => __('Member Roles', 'a-ripple-song'),
                'width' => 'half',
                'options' => Users::roleOptions(...),
                'default' => ['administrator', 'editor', 'author'],
                'help' => __('Only users with these roles can be chosen as members. Save to refresh the member list.', 'a-ripple-song'),
            ],
            [
                'type' => 'multiselect',
                'key' => 'members',
                'label' => __('Members', 'a-ripple-song'),
                'width' => 'half',
                'options' => fn (): array => Users::options($this->roles('member_roles'), $this->ids('members')),
                'help' => __('Select one or more WordPress users who make this podcast. Episodes can only choose members from this list.', 'a-ripple-song'),
            ],
            [
                'type' => 'separator',
                'label' => __('Podcast Guests', 'a-ripple-song'),
            ],
            [
                'type' => 'multiselect',
                'key' => 'guest_roles',
                'label' => __('Guest Roles', 'a-ripple-song'),
                'width' => 'half',
                'options' => Users::roleOptions(...),
                'default' => ['contributor'],
                'help' => __('Only users with these roles can be chosen as guests. Save to refresh the guest list.', 'a-ripple-song'),
            ],
            [
                'type' => 'multiselect',
                'key' => 'guests',
                'label' => __('Guests', 'a-ripple-song'),
                'width' => 'half',
                'options' => fn (): array => Users::options($this->roles('guest_roles'), $this->ids('guests')),
                'help' => __('Select the WordPress users who appear as guests. Episodes can only choose guests from this list.', 'a-ripple-song'),
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
            static fn (int $id): \WP_User|false => get_userdata($id),
            $this->ids('members')
        );

        return array_values(array_filter($users));
    }

    /**
     * Return the saved user IDs of a user list field.
     *
     * @param string $key Either members or guests.
     * @return array<int,int>
     */
    public function ids(string $key): array
    {
        return array_values(array_filter(array_map('absint', (array) $this->value($key))));
    }

    /**
     * Return the saved role slugs of a role field.
     *
     * @param string $key Either member_roles or guest_roles.
     * @return array<int,string>
     */
    private function roles(string $key): array
    {
        return array_values(array_filter(array_map('strval', (array) $this->value($key))));
    }
}
