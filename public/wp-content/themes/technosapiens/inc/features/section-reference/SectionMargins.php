<?php

namespace TechnoSapiens;

class SectionMargins {

    private const TOP_FIELD = 'section_margin_top';
    private const BOTTOM_FIELD = 'section_margin_bottom';

    private const MARGIN_MAP = [
        'xs' => 'smaller',
        's' => 'small',
        'm' => 'regular',
        'lg' => 'large',
        'xl' => 'larger',
    ];

    /**
     * Get the margin-top data attribute value for the current section.
     *
     * @return string
     */
    public static function getMarginTop(): string {
        return self::getMargin(self::TOP_FIELD);
    }

    /**
     * Get the margin-bottom data attribute value for the current section.
     *
     * @return string
     */
    public static function getMarginBottom(): string {
        return self::getMargin(self::BOTTOM_FIELD);
    }

    /**
     * Get margin-top and margin-bottom data attribute values for the current section.
     *
     * @return array{top: string, bottom: string}
     */
    public static function getMargins(): array {
        return [
            'top' => self::getMarginTop(),
            'bottom' => self::getMarginBottom(),
        ];
    }

    /**
     * Map an ACF section margin field value to a data-mt/data-mb value.
     *
     * @param string $field
     * @return string
     */
    private static function getMargin(string $field): string {
        $value = get_field($field);

        if (!is_string($value) || $value === '') {
            return '';
        }

        if (preg_match('/^(?:mt|mb)-(.+)$/', $value, $matches) !== 1) {
            return '';
        }

        if ($matches[1] === '0') {
            return '';
        }

        return self::MARGIN_MAP[$matches[1]] ?? '';
    }
}
