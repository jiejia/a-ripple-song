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

        return self::toOptions($users);
    }

    /**
     * Return the users holding one of $roles as options, keyed by user ID.
     *
     * Unlike options(), users in $first are never listed for their own sake: they only come first, in the given order.
     *
     * @param array<int,string> $roles Role slugs whose users are listed.
     * @param array<int,int|string> $first User IDs listed first when they hold one of the roles, e.g. the saved selection.
     * @return array<int,array{label:string,image:string,meta:string}>
     */
    public static function inRoles(array $roles, array $first = []): array
    {
        if ($roles === []) {
            return [];
        }

        $first = array_values(array_unique(array_filter(array_map('absint', $first))));
        $users = $first === [] ? [] : get_users(['include' => $first, 'role__in' => $roles, 'orderby' => 'include']);

        return self::toOptions(array_merge($users, get_users([
            'role__in' => $roles,
            'exclude' => $first,
            'orderby' => 'display_name',
            'order' => 'ASC',
            'number' => 500,
        ])));
    }

    /**
     * Turn users into options keyed by user ID, with their avatar and role names.
     *
     * @param array<int,\WP_User> $users Users to list.
     * @return array<int,array{label:string,image:string,meta:string}>
     */
    private static function toOptions(array $users): array
    {
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
