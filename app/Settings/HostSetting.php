<?php

namespace Jiejia\ARippleSong\Settings;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Abstracts\SettingAbstract;
use Jiejia\ARippleSong\App;
use Jiejia\ARippleSong\Libraries\Fields\Field;
use Jiejia\ARippleSong\Libraries\Users;

/**
 * Host and guest settings page: the WordPress users who host the podcast and appear as guests, stored in the ars_host_settings option.
 *
 * Episodes can only pick their hosts and guests from the users chosen here; the role fields narrow which users can be chosen.
 */
class HostSetting extends SettingAbstract
{
    /**
     * Ajax and nonce action that reloads a user list after its roles change.
     */
    private const REFRESH_ACTION = App::PREFIX . 'host_setting_users';

    public string $slug = 'host_settings';

    /**
     * {@inheritDoc}
     */
    public function register(): void
    {
        add_action('wp_ajax_' . self::REFRESH_ACTION, [$this, 'refreshUsers']);
    }

    /**
     * Ajax: return the users a list offers for the roles currently ticked on the page.
     *
     * @return void
     */
    public function refreshUsers(): void
    {
        check_ajax_referer(self::REFRESH_ACTION, 'nonce');

        if (! current_user_can('manage_options')) {
            wp_send_json_error(null, 403);
        }

        $key = isset($_POST['field']) ? sanitize_key(wp_unslash($_POST['field'])) : '';

        if (! in_array($key, ['hosts', 'guests'], true)) {
            wp_send_json_error(null, 400);
        }

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Checked against the site's roles in validRoles().
        $roles = $this->validRoles(wp_unslash($_POST['source'] ?? []));
        $options = Users::inRoles($roles, $this->ids($key));

        wp_send_json_success(array_map(
            static fn (int $id, array $option): array => ['value' => $id] + $option,
            array_keys($options),
            $options
        ));
    }

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
                'help' => __('Only users with these roles can be chosen as hosts.', 'a-ripple-song'),
            ],
            [
                'type' => 'multiselect',
                'key' => 'hosts',
                'label' => __('Hosts', 'a-ripple-song'),
                'width' => 'half',
                'options' => fn (): array => Users::inRoles($this->roles('host_roles'), $this->ids('hosts')),
                'refresh_from' => 'host_roles',
                'refresh_action' => self::REFRESH_ACTION,
                'sanitize' => fn (mixed $value, Field $field, array $input): array => $this->sanitizeUsers($value, $input['host_roles'] ?? []),
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
                'help' => __('Only users with these roles can be chosen as guests.', 'a-ripple-song'),
            ],
            [
                'type' => 'multiselect',
                'key' => 'guests',
                'label' => __('Guests', 'a-ripple-song'),
                'width' => 'half',
                'options' => fn (): array => Users::inRoles($this->roles('guest_roles'), $this->ids('guests')),
                'refresh_from' => 'guest_roles',
                'refresh_action' => self::REFRESH_ACTION,
                'sanitize' => fn (mixed $value, Field $field, array $input): array => $this->sanitizeUsers($value, $input['guest_roles'] ?? []),
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
     * Keep the submitted users that hold one of the submitted roles.
     *
     * The roles come from the same submission, so a role and its users can be picked in one save.
     *
     * @param mixed $value Submitted user IDs.
     * @param mixed $roles Submitted role slugs.
     * @return array<int,int>
     */
    private function sanitizeUsers(mixed $value, mixed $roles): array
    {
        $submitted = array_map('absint', array_filter(is_array($value) ? $value : [], 'is_scalar'));
        // Submitted users go first so the list cap never cuts them off.
        $allowed = array_keys(Users::inRoles($this->validRoles($roles), $submitted));

        return array_values(array_unique(array_intersect($submitted, $allowed)));
    }

    /**
     * Return the submitted role slugs that exist on this site.
     *
     * @param mixed $roles Submitted role slugs.
     * @return array<int,string>
     */
    private function validRoles(mixed $roles): array
    {
        $submitted = array_map('strval', array_filter(is_array($roles) ? $roles : [], 'is_scalar'));

        return array_values(array_intersect($submitted, array_keys(Users::roleOptions())));
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
