<?php

namespace Jiejia\ARippleSong\Libraries;

defined('ABSPATH') || exit;

/**
 * WordPress users and roles as field options, for the host settings and the episode host / guest fields.
 */
final class Users
{
    /**
     * Return selectable WordPress users for multiselect fields, keyed by user ID.
     *
     * The users in $ids come first, in the given order, followed by every other user holding one of $roles.
     * Pass no roles to list only $ids.
     *
     * @param array<int,string> $roles Role slugs whose users are listed.
     * @param array<int,int|string> $ids User IDs that are always listed, e.g. the current selection.
     * @return array<int,array{label:string,image:string,meta:string}>
     */
    public static function options(array $roles = [], array $ids = []): array
    {
        $ids = array_values(array_unique(array_filter(array_map('absint', $ids))));
        $users = $ids === [] ? [] : get_users(['include' => $ids, 'orderby' => 'include']);

        if ($roles !== []) {
            $users = array_merge($users, get_users([
                'role__in' => $roles,
                'exclude' => $ids,
                'orderby' => 'display_name',
                'order' => 'ASC',
                'number' => 500,
            ]));
        }

        $roleNames = wp_roles()->get_names();
        $options = [];

        foreach ($users as $user) {
            $userRoles = array_map(
                static fn (string $role): string => isset($roleNames[$role]) ? translate_user_role($roleNames[$role]) : $role,
                (array) $user->roles
            );

            $options[(int) $user->ID] = [
                'label' => $user->display_name ? $user->display_name : $user->user_login,
                'image' => (string) get_avatar_url($user->ID, ['size' => 48]),
                'meta' => implode(', ', $userRoles),
            ];
        }

        return $options;
    }

    /**
     * Return the site's roles as options, keyed by role slug.
     *
     * @return array<string,string>
     */
    public static function roleOptions(): array
    {
        return array_map('translate_user_role', wp_roles()->get_names());
    }
}
