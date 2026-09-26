<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Formatting;
use TechnoSapiens\Core\Singleton;

class Section3dfolio extends Singleton {
    use SectionReferenceTrait;

    public const SECTION_ID = 'section_section_3dfolio';
    public const SECTION_SLUG = 'section-3dfolio';

    private const MAX_CASES = 6;

    protected function __construct() {
        $supports = [];
        $postTypes = [];

        $this->registerReferenceSection(
            __('3D folio section.', Theme::TEXT_DOMAIN),
            'images-alt',
            true,
            $supports,
            $postTypes
        );
    }

    public static function getSectionLabel(): string {
        return __('3D Folio', Theme::TEXT_DOMAIN);
    }

    public function getCases(): array {
        $items = get_field('cases') ?? [];

        if (!is_array($items)) {
            return [];
        }

        $cases = [];

        foreach ($items as $item) {
            $image = $item['case_image'] ?? [];
            $imageId = !empty($image['ID']) ? (int) $image['ID'] : 0;
            $imageUrl = $imageId ? (wp_get_attachment_image_url($imageId, 'large') ?: '') : '';
            $title = $item['case_title'] ?? '';
            $content = $item['case_content'] ?? '';

            if (!$imageUrl && !$title && !$content) {
                continue;
            }

            $cases[] = [
                'imageUrl' => $imageUrl,
                'title' => $title,
                'body' => $content ? wp_kses_post(Formatting::toHtml($content)) : '',
            ];

            if (count($cases) >= self::MAX_CASES) {
                break;
            }
        }

        return $cases;
    }
}
