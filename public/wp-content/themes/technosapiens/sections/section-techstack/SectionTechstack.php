<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Singleton;

class SectionTechstack extends Singleton {
    use SectionReferenceTrait;

    public const SECTION_ID = 'section_section_techstack';
    public const SECTION_SLUG = 'section-techstack';

    public const IMAGE_TECHSTACK_LOGO = 'section-techstack-logo';
    public const IMAGE_TECHSTACK_LOGO_X2 = 'section-techstack-logo-x2';

    protected function __construct() {
        $supports = [];
        $postTypes = [];

        $this->initImageSizes();

        $this->registerReferenceSection(
            __('Techstack section.', Theme::TEXT_DOMAIN),
            'images-alt',
            true,
            $supports,
            $postTypes
        );
    }

    public function initImageSizes(): void {
        add_image_size(self::IMAGE_TECHSTACK_LOGO, 256, 0, false);
        add_image_size(self::IMAGE_TECHSTACK_LOGO_X2, 512, 0, false);
    }

    public static function getSectionLabel(): string {
        return __('Techstack', Theme::TEXT_DOMAIN);
    }

    public function getSectionTitle(): string {
        return get_field('section_title') ?? '';
    }

    public function getGroups(): array {
        $items = get_field('techstack_groups') ?? [];

        if (!is_array($items)) {
            return [];
        }

        $groups = [];

        foreach ($items as $item) {
            $logos = $this->parseGroupLogos($item['group_logos'] ?? []);

            if (empty($logos)) {
                continue;
            }

            $groupText = $item['group_text'] ?? '';
            $groupText = is_string($groupText) ? trim(wp_strip_all_tags($groupText)) : '';

            $groups[] = [
                'title' => $item['group_title'] ?? '',
                'text' => $groupText,
                'logos' => $logos,
            ];
        }

        return $groups;
    }

    private function parseGroupLogos(mixed $logos): array {
        if (!is_array($logos)) {
            return [];
        }

        $parsed = [];

        foreach ($logos as $logo) {
            if (!is_array($logo)) {
                continue;
            }

            $logoId = !empty($logo['ID']) ? (int) $logo['ID'] : (!empty($logo['id']) ? (int) $logo['id'] : 0);

            if (!$logoId) {
                continue;
            }

            $altText = trim((string) ($logo['alt'] ?? ''));

            if ($altText === '') {
                $altText = trim((string) get_post_meta($logoId, '_wp_attachment_image_alt', true));
            }

            $parsed[] = [
                'logoId' => $logoId,
                'altText' => $altText,
            ];
        }

        return $parsed;
    }
}
