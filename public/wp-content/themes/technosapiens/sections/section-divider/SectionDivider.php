<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Singleton;

class SectionDivider extends Singleton {
    use SectionReferenceTrait;

    public const SECTION_ID = 'section_section_divider';
    public const SECTION_SLUG = 'section-divider';

    protected function __construct() {
        $supports = [];
        $postTypes = [];

        $this->registerReferenceSection(
            __('Divider section.', Theme::TEXT_DOMAIN),
            'minus',
            false,
            $supports,
            $postTypes
        );
    }

    public static function getSectionLabel(): string {
        return __('Divider', Theme::TEXT_DOMAIN);
    }
}
