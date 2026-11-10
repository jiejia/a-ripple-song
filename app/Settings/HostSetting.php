<?php

namespace Jiejia\ARippleSong\Settings;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Abstracts\SettingAbstract;
use Jiejia\ARippleSong\Libraries\Users;

/**
 * Host and guest settings page: the WordPress users who host the podcast and appear as guests, stored in the ars_host_settings option.
 *
 * Episodes can only pick their hosts and guests from the users chosen here; the role fields narrow which users can be chosen.
 */
class HostSetting extends SettingAbstract
{
    public string $slug = 'host_settings';

    public function title(): string
    {
        return __('Host & Guest Settings', 'a-ripple-song');
    }

    /**
     * Return host and guest setting field definitions.
     *
     * @return array<int,array<string,mixed>>
     */
    public function fields(): array
    {
        return [
            [
                'type' => 'separator',
                'label' => __('Podcast Hosts', 'a-ripple-song'),
            ],
            [
                'type' => 'multiselect',
                'key' => 'host_roles',
                'label' => __('Host Roles', 'a-ripple-song'),
                'width' => 'half',
                'options' => Users::roleOptions(...),
                'default' => ['administrator', 'editor', 'author'],
                'help' => __('Only users with these roles can be chosen as hosts. Save to refresh the host list.', 'a-ripple-song'),
            ],
            [
                'type' => 'multiselect',
                'key' => 'hosts',
                'label' => __('Hosts', 'a-ripple-song'),
                'width' => 'half',
                'options' => fn (): array => Users::options($this->roles('host_roles'), $this->ids('hosts')),
                'help' => __('Select one or more WordPress users who host this podcast. Episodes can only choose hosts from this list.', 'a-ripple-song'),
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
     * Return the podcast hosts in the saved order, skipping users that no longer exist.
     *
     * @return array<int,\WP_User>
     */
    public function users(): array
    {
        $users = array_map(
            static fn (int $id): \WP_User|false => get_userdata($id),
            $this->ids('hosts')
        );

        return array_values(array_filter($users));
    }

    /**
     * Return the saved user IDs of a user list field.
     *
     * @param string $key Either hosts or guests.
     * @return array<int,int>
     */
    public function ids(string $key): array
    {
        return array_values(array_filter(array_map('absint', (array) $this->value($key))));
    }

    /**
     * Return the saved role slugs of a role field.
     *
     * @param string $key Either host_roles or guest_roles.
     * @return array<int,string>
     */
    private function roles(string $key): array
    {
        return array_values(array_filter(array_map('strval', (array) $this->value($key))));
    }
}
