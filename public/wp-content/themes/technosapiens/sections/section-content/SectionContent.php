<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Singleton;

class SectionContent extends Singleton {
    use SectionReferenceTrait;

    public const SECTION_ID = 'section_section_content';
    public const SECTION_SLUG = 'section-content';

    private const ALLOWED_HEADING_TAGS = ['h2', 'h3', 'h4'];

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

    public function getSectionTitle(): string {
        return get_field('section_title') ?? '';
    }

    public function getTitleHeadingTag(): string {
        $tag = get_field('title_heading_tag') ?: 'h2';

        if (!in_array($tag, self::ALLOWED_HEADING_TAGS, true)) {
            return 'h2';
        }

        return $tag;
    }

    public static function getContentText(): string {
        return get_field('content_text');
    }
}
