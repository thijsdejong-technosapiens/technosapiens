<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Singleton;

class SectionCtaBanner extends Singleton {
    use SectionReferenceTrait;

    public const SECTION_ID = 'section_section_cta_banner';
    public const SECTION_SLUG = 'section-cta-banner';

    protected function __construct() {
        $supports = [];
        $postTypes = [];

        $this->registerReferenceSection(
            __('CTA banner reference section.', Theme::TEXT_DOMAIN),
            'megaphone',
            false,
            $supports,
            $postTypes
        );
    }

    public static function getSectionData(): array {
        return [
            'title' => get_field('title'),
            'content' => get_field('content'),
        ];
    }

    public static function getSectionLabel(): string {
        return __('CTA banner', Theme::TEXT_DOMAIN);
    }
}
