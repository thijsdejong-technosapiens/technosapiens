<?php

namespace TechnoSapiens;

class SectionBackground {

    private const TYPE_FIELD = 'background_type';
    private const BANDS_AXIS_FIELD = 'background_bands_axis';
    private const BANDS_ANIM_DIR_FIELD = 'background_bands_anim_dir';

    private const TYPE_BANDS = 'bands';

    private const BANDS_AXES = [
        'horizontal',
        'vertical',
    ];

    private const BANDS_ANIM_DIR_MAP = [
        'top-bottom' => 'start',
        'bottom-top' => 'end',
        'center' => 'center',
    ];

    /**
     * Get the background type for the current section.
     *
     * @return string
     */
    public static function getBackgroundType(): string {
        $value = get_field(self::TYPE_FIELD);

        if (!is_string($value) || $value === '') {
            return '';
        }

        if ($value !== self::TYPE_BANDS) {
            return '';
        }

        return $value;
    }

    /**
     * Get the bands axis for the current section.
     *
     * @return string
     */
    public static function getBackgroundBandsAxis(): string {
        if (self::getBackgroundType() !== self::TYPE_BANDS) {
            return '';
        }

        $value = get_field(self::BANDS_AXIS_FIELD);

        if (!is_string($value) || $value === '') {
            return '';
        }

        if (!in_array($value, self::BANDS_AXES, true)) {
            return '';
        }

        return $value;
    }

    /**
     * Get the bands animation direction mapped for GradientBandsComponent.
     *
     * @return string
     */
    public static function getBackgroundBandsAnimDir(): string {
        if (self::getBackgroundType() !== self::TYPE_BANDS) {
            return '';
        }

        $value = get_field(self::BANDS_ANIM_DIR_FIELD);

        if (!is_string($value) || $value === '') {
            return '';
        }

        return self::BANDS_ANIM_DIR_MAP[$value] ?? '';
    }

    /**
     * Get background settings for the current section.
     *
     * @return array{type: string, bandsAxis: string, bandsAnimDir: string}
     */
    public static function getBackground(): array {
        return [
            'type' => self::getBackgroundType(),
            'bandsAxis' => self::getBackgroundBandsAxis(),
            'bandsAnimDir' => self::getBackgroundBandsAnimDir(),
        ];
    }
}
