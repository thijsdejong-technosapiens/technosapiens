<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Singleton;

class SectionContent extends Singleton {
    use SectionReferenceTrait;

    public const SECTION_ID = 'section_section_content';
    public const SECTION_SLUG = 'section-content';

    protected function __construct() {
        $supports = [];
        $postTypes = [];

        $this->registerReferenceSection(
            __('Content section.', Theme::TEXT_DOMAIN),
            'text',
            false,
            $supports,
            $postTypes
        );
    }

    public static function getSectionLabel(): string {
        return __('Content', Theme::TEXT_DOMAIN);
    }
}
