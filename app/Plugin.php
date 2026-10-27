<?php


namespace Jiejia\ARippleSong;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Contracts\ServiceProvider;

/**
 * Main plugin application class.
 */
class Plugin
{
    public const SLUG = 'a-ripple-song';

    public const NAME = 'A Ripple Song';

    public const VERSION = '0.5.1';

    public const DIR = A_RIPPLE_SONG_PLUGIN_DIR;

    /**
     * Service provider classes registered by this plugin.
     *
     * @var array<int,class-string<ServiceProvider>>
     */
    private array $providers = [
    ];

    /**
     * Service provider instances created during plugin boot.
     *
     * @var array<int,ServiceProvider>
     */
    private array $providerInstances = [];

    /**
     * Create the plugin application and boot its providers.
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Initialize the plugin lifecycle.
     *
     * @return void
     */
    public function init(): void
    {
        $this->registerProviders();
        $this->bootProviders();
    }

    /**
     * Register all configured service providers.
     *
     * @return void
     */
    private function registerProviders(): void
    {
        foreach ($this->providers as $providerClass) {
            // Keep provider instances so boot() runs on the same objects.
            $provider = new $providerClass();
            $provider->register();

            $this->providerInstances[] = $provider;
        }
    }

    /**
     * Boot all registered service providers.
     *
     * @return void
     */
    private function bootProviders(): void
    {
        foreach ($this->providerInstances as $provider) {
            // Allow providers to run logic after every provider is registered.
            $provider->boot();
        }
    }
}
