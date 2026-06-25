<?php

namespace App\CustomAreas;

use App\Abstracts\CustomAreaAbstract;

/**
 * Leftbar primary widget area definition.
 */
class LeftbarPrimary extends CustomAreaAbstract
{
    /**
     * Return the sidebar ID.
     *
     * @return string
     */
    public function id(): string
    {
        return 'leftbar-primary';
    }

    /**
     * Return the translated sidebar name.
     *
     * @return string
     */
    public function name(): string
    {
        return __('Leftbar Primary', 'a-ripple-song');
    }

    /**
     * Return the translated sidebar description.
     *
     * @return string
     */
    public function description(): string
    {
        return __('Primary left sidebar area for displaying various content modules', 'a-ripple-song');
    }
}
