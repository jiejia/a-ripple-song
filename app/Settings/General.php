<?php

namespace App\Settings;

use App\Abstracts\SettingAbstract;
use App\Theme;
use Carbon_Fields\Field;

/**
 * Carbon Fields general theme settings page.
 */
class General extends SettingAbstract
{
    public const PLAYER_TYPE_HOWLER = 'howler';

    public const PLAYER_TYPE_WAVESURFER = 'wavesurfer';

    /**
     * Return the prefix used for all general option keys.
     *
     * @return string
     */
    public function fieldPrefix(): string
    {
        return Theme::PREFIX . '_general_settings_';
    }

    /**
     * Return the Carbon Fields page slug.
     *
     * @return string
     */
    public function pageSlug(): string
    {
        return Theme::PREFIX . '_general_settings';
    }

    /**
     * Return the general settings page title.
     *
     * @return string
     */
    public function pageTitle(): string
    {
        return __('General Settings', 'a-ripple-song');
    }

    /**
     * Return the parent menu slug for this settings page.
     *
     * @return string
     */
    public function parentPageSlug(): string
    {
        return Theme::SLUG;
    }

    /**
     * Return all general setting fields.
     *
     * @return array<int,\Carbon_Fields\Field\Field>
     */
    public function fields(): array
    {
        return [
            Field::make('select', $this->fieldName('player_type'), __('Player Type', 'a-ripple-song'))
                ->set_options([
                    self::PLAYER_TYPE_HOWLER => __('Howler', 'a-ripple-song'),
                    self::PLAYER_TYPE_WAVESURFER => __('WaveSurfer', 'a-ripple-song'),
                ])
                ->set_default_value(self::PLAYER_TYPE_HOWLER)
                ->set_help_text(__('Choose the audio player engine used on the frontend.', 'a-ripple-song')),
        ];
    }

    /**
     * Return default general settings.
     *
     * @return array<string,mixed>
     */
    public function defaultSettings(): array
    {
        return [
            'player_type' => self::PLAYER_TYPE_HOWLER,
        ];
    }

    /**
     * Return a validated frontend player type.
     *
     * @return string
     */
    public function playerType(): string
    {
        $playerType = (string) $this->getSetting('player_type', self::PLAYER_TYPE_HOWLER);

        return in_array($playerType, $this->playerTypes(), true)
            ? $playerType
            : self::PLAYER_TYPE_HOWLER;
    }

    /**
     * Return the supported player type values.
     *
     * @return array<int,string>
     */
    private function playerTypes(): array
    {
        return [
            self::PLAYER_TYPE_HOWLER,
            self::PLAYER_TYPE_WAVESURFER,
        ];
    }
}
