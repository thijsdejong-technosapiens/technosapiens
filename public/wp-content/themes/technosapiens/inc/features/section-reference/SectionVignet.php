<?php

namespace TechnoSapiens;

class SectionVignet {

    private const TOP_FIELD = 'section_vignet_top';
    private const BOTTOM_FIELD = 'section_vignet_bottom';

    /**
     * Get the vignet-top data attribute value for the current section.
     *
     * @return string
     */
    public static function getVignetTop(): string {
        return self::getVignet(self::TOP_FIELD);
    }

    /**
     * Get the vignet-bottom data attribute value for the current section.
     *
     * @return string
     */
    public static function getVignetBottom(): string {
        return self::getVignet(self::BOTTOM_FIELD);
    }

    /**
     * Get vignet-top and vignet-bottom data attribute values for the current section.
     *
     * @return array{top: string, bottom: string}
     */
    public static function getVignets(): array {
        return [
            'top' => self::getVignetTop(),
            'bottom' => self::getVignetBottom(),
        ];
    }

    /**
     * Map an ACF section vignet field value to a data-vignet-top/data-vignet-bottom value.
     *
     * @param string $field
     * @return string
     */
    private static function getVignet(string $field): string {
        $value = get_field($field);

        if (!$value) {
            return '';
        }

        return 'true';
    }
}
