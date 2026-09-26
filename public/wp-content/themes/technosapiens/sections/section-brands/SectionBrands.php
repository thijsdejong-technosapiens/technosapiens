<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Link;
use TechnoSapiens\Core\Singleton;

class SectionBrands extends Singleton {
    use SectionReferenceTrait;

    public const SECTION_ID = 'section_section_brands';
    public const SECTION_SLUG = 'section-brands';

    public const IMAGE_BRAND_LOGO = 'section-brands-logo';
    public const IMAGE_BRAND_LOGO_X2 = 'section-brands-logo-x2';

    private const ALLOWED_HEADING_TAGS = ['h2', 'h3', 'h4'];

    protected function __construct() {
        $supports = [];
        $postTypes = [];

        $this->initImageSizes();

        $this->registerReferenceSection(
            __('Brands section.', Theme::TEXT_DOMAIN),
            'images-alt',
            false,
            $supports,
            $postTypes
        );
    }

    public function initImageSizes(): void {
        add_image_size(self::IMAGE_BRAND_LOGO, 256, 0, false);
        add_image_size(self::IMAGE_BRAND_LOGO_X2, 512, 0, false);
    }

    public static function getSectionLabel(): string {
        return __('Brands', Theme::TEXT_DOMAIN);
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

    public function getBrands(): array {
        $items = get_field('brands') ?? [];

        if (!is_array($items)) {
            return [];
        }

        $brands = [];

        foreach ($items as $item) {
            $logo = $item['brand_logo'] ?? [];
            $logoId = !empty($logo['ID']) ? (int) $logo['ID'] : 0;

            if (!$logoId) {
                continue;
            }

            $brands[] = [
                'logoId' => $logoId,
                'link' => $this->parseBrandLink($item['brand_link'] ?? null),
            ];
        }

        return $brands;
    }

    private function parseBrandLink(mixed $brandLink): array {
        if (!$brandLink || !is_array($brandLink)) {
            return [];
        }

        $url = Link::parseLink($brandLink['url'] ?? '') ?: '';

        if (!$url) {
            return [];
        }

        $target = '_blank';
        $rel = Link::getLinkRel($url, $target);

        return [
            'url' => $url,
            'target' => $target,
            'rel' => $rel,
        ];
    }
}
