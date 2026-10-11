<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Singleton;

class SectionHeading extends Singleton {
    use SectionReferenceTrait;

    public const SECTION_ID = 'section_section_heading';
    public const SECTION_SLUG = 'section-heading';

    protected function __construct() {
        $supports = [];
        $postTypes = [];

        $this->registerReferenceSection(
            __('Heading section.', Theme::TEXT_DOMAIN),
            'heading',
            false,
            $supports,
            $postTypes
        );
    }

    public static function getSectionLabel(): string {
        return __('Heading', Theme::TEXT_DOMAIN);
    }

    public static function getTitleText(): string {
        return get_field('title_text');
    }

    public static function getTitleTag(): string {
        return get_field('title_heading_tag') ?: 'h2';
    }

    public static function getTitleHeadingSize(): string {
        $size = get_field('title_heading_size');

        if (!$size) {
            return 'display';
        }

        $allowed = ['display', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'];

        return in_array($size, $allowed, true) ? $size : 'display';
    }
}
