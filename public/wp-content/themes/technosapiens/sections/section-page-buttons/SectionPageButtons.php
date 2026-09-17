<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Singleton;

class SectionPageButtons extends Singleton {
    use SectionReferenceTrait;

    public const SECTION_ID = 'section_section_page_buttons';
    public const SECTION_SLUG = 'section-page-buttons';

    protected function __construct() {
        $supports = [];
        $postTypes = [];

        $this->registerReferenceSection(
            __('Page buttons reference section.', Theme::TEXT_DOMAIN),
            'button',
            false,
            $supports,
            $postTypes
        );
    }

    public function getPageButtons(): array {
        $pages = get_field('pages') ?? [];
        if (empty($pages)) {
            return [];
        }

        $pageButtons = [];
        foreach ($pages as $page) {
            if (!$page instanceof \WP_Post) {
                continue;
            }

            $pageButtons[] = [
                'url' => get_permalink($page),
                'label' => get_the_title($page),
            ];
        }

        return $pageButtons;
    }

    public static function getSectionLabel(): string {
        return __('Page buttons', Theme::TEXT_DOMAIN);
    }
}
