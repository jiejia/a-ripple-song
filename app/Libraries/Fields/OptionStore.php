<?php

namespace Jiejia\ARippleSong\Libraries\Fields;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Abstracts\SettingAbstract;
use Jiejia\ARippleSong\Libraries\Template;

/**
 * Renders a settings page's fields and stores them in a single option.
 *
 * The option name is the setting slug (e.g. ars_podcast_settings) and its value
 * is an array keyed by field key.
 */
final class OptionStore extends FieldStore
{
    /**
     * @param SettingAbstract $setting Settings page whose fields() are stored.
     */
    public function __construct(private readonly SettingAbstract $setting)
    {
    }

    /**
     * Register save and asset hooks.
     *
     * @return void
     */
    public function register(): void
    {
        add_action('admin_post_' . $this->optionName(), [$this, 'save']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    /**
     * Return the option name the settings are stored under.
     *
     * @return string
     */
    public function optionName(): string
    {
        return $this->setting->slug();
    }

    /**
     * Return stored values; fields never saved are left out so the renderer uses their defaults.
     *
     * @return array<string,mixed>
     */
    public function values(): array
    {
        $stored = get_option($this->optionName(), []);

        return is_array($stored) ? $stored : [];
    }

    /**
     * Return one stored value, falling back to the field default.
     *
     * @param string $key Field key.
     * @return mixed Null when no field uses the key.
     */
    public function value(string $key): mixed
    {
        $values = $this->values();

        return array_key_exists($key, $values) ? $values[$key] : Field::find($this->fields(), $key)?->defaultValue();
    }

    /**
     * Render the settings page.
     *
     * @return void
     */
    public function renderPage(): void
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        $flash = $this->pullFlash();
        $errors = $flash['errors'] ?? [];

        (new Template())->render('fields/settings-page', [
            'title' => $this->setting->title(),
            'saved' => ! empty($flash['saved']),
            'errors' => $errors,
            'action' => $this->optionName(),
            'nonceAction' => $this->nonceAction(),
            'hasFields' => $this->fields() !== [],
            // After a failed save, show what was submitted instead of the stored values.
            'renderFields' => fn () => $this->renderFields($flash['input'] ?? $this->values(), $errors),
        ]);
    }

    /**
     * Handle the settings form submission.
     *
     * @return void
     */
    public function save(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('You are not allowed to manage these settings.', 'a-ripple-song'), 403);
        }

        check_admin_referer($this->nonceAction());

        $submission = $this->submission();
        // Keep keys the form does not submit, such as read-only fields.
        $values = array_merge($this->values(), $submission->values);

        if ($submission->isValid()) {
            update_option($this->optionName(), $values);

            /**
             * Fires after a settings page has been saved.
             *
             * @param SettingAbstract $setting Settings page definition.
             * @param array<string,mixed> $values Saved values keyed by field key.
             */
            do_action('a_ripple_song_settings_saved', $this->setting, $values);

            $this->setFlash(['saved' => true]);
        } else {
            $this->setFlash(['errors' => $submission->errors, 'input' => $values]);
        }

        wp_safe_redirect(add_query_arg('page', $this->setting->slug(), admin_url('admin.php')));
        exit;
    }

    /**
     * {@inheritDoc}
     */
    protected function formName(): string
    {
        return $this->optionName();
    }

    /**
     * {@inheritDoc}
     */
    protected function definitions(): array
    {
        return $this->setting->fields();
    }

    /**
     * Only this settings page shows the form.
     *
     * {@inheritDoc}
     */
    protected function isFormScreen(string $hookSuffix): bool
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only comparing the current admin page.
        $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';

        return $page === $this->setting->slug();
    }

    /**
     * Store a one-time message for the next page load of the current user.
     *
     * @param array<string,mixed> $flash Flash data.
     * @return void
     */
    private function setFlash(array $flash): void
    {
        set_transient($this->flashKey(), $flash, MINUTE_IN_SECONDS);
    }

    /**
     * Read and clear the one-time message.
     *
     * @return array<string,mixed>
     */
    private function pullFlash(): array
    {
        $flash = get_transient($this->flashKey());
        delete_transient($this->flashKey());

        return is_array($flash) ? $flash : [];
    }

    /**
     * Return the per-user transient key of the flash message.
     *
     * @return string
     */
    private function flashKey(): string
    {
        return $this->optionName() . '_flash_' . get_current_user_id();
    }

    /**
     * Return the nonce action of this settings form.
     *
     * @return string
     */
    private function nonceAction(): string
    {
        return $this->optionName() . '_save';
    }
}
