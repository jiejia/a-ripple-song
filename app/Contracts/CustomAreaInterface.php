<?php

namespace App\Contracts;

/**
 * Defines a WordPress widget area registration contract.
 */
interface CustomAreaInterface
{
    /**
     * Return the sidebar ID.
     *
     * @return string
     */
    public function id(): string;

    /**
     * Return the translated sidebar name.
     *
     * @return string
     */
    public function name(): string;

    /**
     * Return the translated sidebar description.
     *
     * @return string
     */
    public function description(): string;

    /**
     * Return markup printed before each widget.
     *
     * @return string
     */
    public function beforeWidget(): string;

    /**
     * Return markup printed after each widget.
     *
     * @return string
     */
    public function afterWidget(): string;

    /**
     * Return markup printed before each widget title.
     *
     * @return string
     */
    public function beforeTitle(): string;

    /**
     * Return markup printed after each widget title.
     *
     * @return string
     */
    public function afterTitle(): string;

    /**
     * Return the complete WordPress sidebar registration arguments.
     *
     * @return array<string,mixed>
     */
    public function args(): array;
}
