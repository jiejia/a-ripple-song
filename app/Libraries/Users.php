<?php

namespace Jiejia\ARippleSong\Libraries;

defined('ABSPATH') || exit;

/**
 * WordPress users as field options, for the episode member / guest fields and the podcast member setting.
 */
final class Users
{
    /**
     * Return selectable WordPress users for multiselect fields, keyed by user ID.
     *
     * @return array<int,array{label:string,image:string,meta:string}>
     */
    public static function options(): array
    {
        $options = [];
        $roleNames = wp_roles()->get_names();
        $users = get_users([
            'orderby' => 'display_name',
            'order' => 'ASC',
            'number' => 500,
        ]);

        foreach ($users as $user) {
            $roles = array_map(
                static fn (string $role): string => isset($roleNames[$role]) ? translate_user_role($roleNames[$role]) : $role,
                (array) $user->roles
            );

            $options[(int) $user->ID] = [
                'label' => $user->display_name ? $user->display_name : $user->user_login,
                'image' => (string) get_avatar_url($user->ID, ['size' => 48]),
                'meta' => implode(', ', $roles),
            ];
        }

        return $options;
    }
}
