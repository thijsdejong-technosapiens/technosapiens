<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Singleton;

class SectionGrandTitle extends Singleton {
    use SectionReferenceTrait;

    public const SECTION_ID = 'section_section_grand_title';
    public const SECTION_SLUG = 'section-grand-title';

    protected function __construct() {
        $supports = [];
        $postTypes = [];

        $this->registerReferenceSection(
            __('Grand title section.', Theme::TEXT_DOMAIN),
            'heading',
            false,
            $supports,
            $postTypes
        );
    }

    public static function getSectionLabel(): string {
        return __('Grand title', Theme::TEXT_DOMAIN);
    }

    public static function getTitleText(): string {
        return get_field('title_text');
    }

    public static function getTitleTag(): string {
        return get_field('title_heading_tag') ?: 'h2';
    }
}
