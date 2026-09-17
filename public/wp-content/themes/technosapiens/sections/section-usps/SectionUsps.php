<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Singleton;

class SectionUsps extends Singleton {
    use SectionReferenceTrait;

    public const SECTION_ID = 'section_section_usps';
    public const SECTION_SLUG = 'section-usps';

    protected function __construct() {
        $supports = [];
        $postTypes = [];

        $this->registerReferenceSection(
            __('USPs reference section.', Theme::TEXT_DOMAIN),
            'yes-alt',
            false,
            $supports,
            $postTypes
        );
    }

    public function getUspsTitle(): string {
        return get_field('usps_title') ?? '';
    }

    public function getUspsImageId(): int {
        $image = get_field('usps_image');

        if (is_numeric($image)) {
            return (int) $image;
        }

        if (is_array($image) && !empty($image['ID'])) {
            return (int) $image['ID'];
        }

        return 0;
    }

    public function getUspsItems(): array {
        $items = get_field('usps_items') ?? [];

        if (!is_array($items)) {
            return [];
        }

        $uspsItems = [];

        foreach ($items as $item) {
            $text = $item['usp'] ?? $item['text'] ?? '';

            if (!$text) {
                continue;
            }

            $uspsItems[] = [
                'text' => $text,
            ];
        }

        return $uspsItems;
    }

    public static function getSectionLabel(): string {
        return __('USPs section', Theme::TEXT_DOMAIN);
    }
}
