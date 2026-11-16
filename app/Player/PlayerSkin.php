<?php

namespace Jiejia\ARippleSong\Player;

defined('ABSPATH') || exit;

/**
 * Selectable looks of the global player.
 *
 * Each skin has a template (resources/templates/player/skins/{value}.php) and a stylesheet
 * (public/dist/skins/{value}.css). All but Default were converted from the
 * ~/workspace/podcast theme prototypes. Values carry the ars- prefix; fromStored() still reads
 * the unprefixed values saved before the rename.
 */
enum PlayerSkin: string
{
    case Default = 'ars-default';
    case Broadcast = 'ars-broadcast';
    case EveningPaper = 'ars-evening-paper';
    case Pulse = 'ars-pulse';
    case Cassette = 'ars-cassette';
    case Aurora = 'ars-aurora';
    case Terminal = 'ars-terminal';
    case Zine = 'ars-zine';
    case Soft = 'ars-soft';
    case Circle = 'ars-circle';
    case Coral = 'ars-coral';
    case Wisteria = 'ars-wisteria';
    case Petal = 'ars-petal';
    case Prisma = 'ars-prisma';
    case Bazaar = 'ars-bazaar';

    /**
     * Return the name shown in the settings.
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::Default => __('Default: plain bar', 'a-ripple-song'),
            self::Broadcast => __('Broadcast: Swiss brutalist, ON AIR light', 'a-ripple-song'),
            self::EveningPaper => __('Evening Paper: newspaper, serif', 'a-ripple-song'),
            self::Pulse => __('Pulse: dark app, electric blue', 'a-ripple-song'),
            self::Cassette => __('Cassette: 70s tape deck, VU meters', 'a-ripple-song'),
            self::Aurora => __('Aurora: frosted glass capsule', 'a-ripple-song'),
            self::Terminal => __('Terminal: CRT command line', 'a-ripple-song'),
            self::Zine => __('Zine: risograph collage', 'a-ripple-song'),
            self::Soft => __('Soft: neumorphic radio, volume knob', 'a-ripple-song'),
            self::Circle => __('Circle: social feed card', 'a-ripple-song'),
            self::Coral => __('Coral: listening dashboard', 'a-ripple-song'),
            self::Wisteria => __('Wisteria: soft purple mobile app', 'a-ripple-song'),
            self::Petal => __('Petal: component-library site, floating glass bar', 'a-ripple-song'),
            self::Prisma => __('Prisma: black and white minimal, thin-line capsule', 'a-ripple-song'),
            self::Bazaar => __('Bazaar: shopping app, electric purple floating bar', 'a-ripple-song'),
        };
    }

    /**
     * Return the local font stylesheet for this skin, if any.
     *
     * @return string
     */
    public function fontsUrl(): string
    {
        return $this === self::Default
            ? ''
            : A_RIPPLE_SONG_PLUGIN_URL . 'public/fonts/skins/' . $this->value . '.css';
    }

    /**
     * Return the template name for Template::renderThemeable().
     *
     * @return string
     */
    public function template(): string
    {
        return 'player/skins/' . $this->value;
    }

    /**
     * Return the built stylesheet, relative to the plugin directory.
     *
     * @return string
     */
    public function stylesheet(): string
    {
        return 'public/dist/skins/' . $this->value . '.css';
    }

    /**
     * Return the skin for a saved setting value, accepting the unprefixed values saved before the ars- rename.
     * Unknown or removed skins (night-signal) fall back to Default.
     *
     * @param string $value
     * @return self
     */
    public static function fromStored(string $value): self
    {
        return self::tryFrom($value) ?? self::tryFrom('ars-' . $value) ?? self::Default;
    }

    /**
     * Return skin labels keyed by value, for a select field.
     *
     * @return array<string,string>
     */
    public static function options(): array
    {
        return array_column(array_map(static fn (self $skin): array => [$skin->value, $skin->label()], self::cases()), 1, 0);
    }
}
