<?php

namespace App\CustomAreas;

use App\Abstracts\CustomAreaAbstract;

/**
 * Footer links widget area definition.
 */
class FooterLinks extends CustomAreaAbstract
{
    /**
     * Return the sidebar ID.
     *
     * @return string
     */
    public function id(): string
    {
        return 'footer-links';
    }

    /**
     * Return the translated sidebar name.
     *
     * @return string
     */
    public function name(): string
    {
        return __('Footer Links', 'a-ripple-song');
    }

    /**
     * Return the translated sidebar description.
     *
     * @return string
     */
    public function description(): string
    {
        return __('Footer links area for displaying link columns', 'a-ripple-song');
    }

    /**
     * Return markup printed before each widget.
     *
     * @return string
     */
    public function beforeWidget(): string
    {
        return '';
    }

    /**
     * Return markup printed after each widget.
     *
     * @return string
     */
    public function afterWidget(): string
    {
        return '';
    }

    /**
     * Return markup printed before each widget title.
     *
     * @return string
     */
    public function beforeTitle(): string
    {
        return '';
    }

    /**
     * Return markup printed after each widget title.
     *
     * @return string
     */
    public function afterTitle(): string
    {
        return '';
    }
}
