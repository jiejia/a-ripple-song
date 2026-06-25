<?php

namespace App\CustomAreas;

use App\Abstracts\CustomAreaAbstract;

/**
 * Rightbar primary widget area definition.
 */
class RightbarPrimary extends CustomAreaAbstract
{
    /**
     * Return the sidebar ID.
     *
     * @return string
     */
    public function id(): string
    {
        return 'sidebar-primary';
    }

    /**
     * Return the translated sidebar name.
     *
     * @return string
     */
    public function name(): string
    {
        return __('Rightbar Primary', 'a-ripple-song');
    }

    /**
     * Return the translated sidebar description.
     *
     * @return string
     */
    public function description(): string
    {
        return __('Primary right sidebar area for displaying various content modules', 'a-ripple-song');
    }
}
