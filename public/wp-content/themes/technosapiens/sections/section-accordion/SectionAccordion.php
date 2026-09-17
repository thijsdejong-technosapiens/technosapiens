<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Singleton;

class SectionAccordion extends Singleton {
    use SectionReferenceTrait;

    public const SECTION_ID = 'section_section_accordion';
    public const SECTION_SLUG = 'section-accordion';

    protected function __construct() {
        $supports = [];
        $postTypes = [];

        $this->registerReferenceSection(
            __('Accordion reference section.', Theme::TEXT_DOMAIN),
            'arrow-down-alt2',
            false,
            $supports,
            $postTypes
        );
    }

    public static function getSectionLabel(): string {
        return __('Accordion', Theme::TEXT_DOMAIN);
    }
}
