<?php

namespace App\Providers;

use App\Metas\PostViewCount;
use Illuminate\Support\ServiceProvider;

/**
 * Registers shared post meta features.
 */
class MetaServiceProvider extends ServiceProvider
{
    /**
     * Meta feature classes registered by this provider.
     *
     * @var array<int,class-string>
     */
    private array $metas = [PostViewCount::class];

    /**
     * Register shared meta hooks.
     *
     * @return void
     */
    public function register(): void
    {
        foreach ($this->metas as $metaClass) {
            // Allow each meta feature to attach its own supporting hooks.
            (new $metaClass())->registerHooks();
        }
    }
}
