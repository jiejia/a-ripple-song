<?php

namespace App\Abstracts;

use App\Contracts\CustomAreaInterface;
use App\Theme;

/**
 * Base class for WordPress widget area definitions.
 */
abstract class CustomAreaAbstract implements CustomAreaInterface
{
    /**
     * Return markup printed before each widget.
     *
     * @return string
     */
    public function beforeWidget(): string
    {
        return '<div class="widget %1$s %2$s mb-4">';
    }

    /**
     * Return markup printed after each widget.
     *
     * @return string
     */
    public function afterWidget(): string
    {
        return '</div>';
    }

    /**
     * Return markup printed before each widget title.
     *
     * @return string
     */
    public function beforeTitle(): string
    {
        return '<h2 class="widget-title text-lg font-bold mb-2">';
    }

    /**
     * Return markup printed after each widget title.
     *
     * @return string
     */
    public function afterTitle(): string
    {
        return '</h2>';
    }

    /**
     * Return the complete WordPress sidebar registration arguments.
     *
     * @return array<string,mixed>
     */
    /**
     * Return the WordPress sidebar ID used for registration and rendering.
     *
     * @return string
     */
    protected function registeredSidebarId(): string
    {
        $sidebarMap = Theme::sidebars();

        return $sidebarMap[$this->id()] ?? Theme::prefixed($this->id());
    }

    public function args(): array
    {
        return [
            'name' => $this->name(),
            'id' => $this->registeredSidebarId(),
            'description' => $this->description(),
            'before_widget' => $this->beforeWidget(),
            'after_widget' => $this->afterWidget(),
            'before_title' => $this->beforeTitle(),
            'after_title' => $this->afterTitle(),
        ];
    }
}
