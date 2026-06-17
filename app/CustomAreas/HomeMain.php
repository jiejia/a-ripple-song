<?php

namespace App\CustomAreas;

use App\Abstracts\CustomAreaAbstract;

/**
 * Home main widget area definition.
 */
class HomeMain extends CustomAreaAbstract
{
    /**
     * Return the sidebar ID.
     *
     * @return string
     */
    public function id(): string
    {
        return 'home-main';
    }

    /**
     * Return the translated sidebar name.
     *
     * @return string
     */
    public function name(): string
    {
        return __('Home Main', 'sage');
    }

    /**
     * Return the translated sidebar description.
     *
     * @return string
     */
    public function description(): string
    {
        return __('Main area of the homepage for displaying various content modules', 'sage');
    }
}
