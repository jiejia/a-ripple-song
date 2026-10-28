<?php

namespace Jiejia\ARippleSong\CPTs;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Abstracts\CPTAbstract;

/**
 * Defines the podcast episode custom post type.
 */
class PodcastCPT extends CPTAbstract
{
    /**
     * Return the WordPress custom post type key.
     *
     * @return string
     */
    public function slug(): string
    {
        return 'podcast';
    }

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
