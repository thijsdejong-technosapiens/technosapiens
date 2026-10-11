<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Link;
use TechnoSapiens\Core\Singleton;

class SectionContentBanner extends Singleton {
    use SectionReferenceTrait;

    public const SECTION_ID = 'section_section_content_banner';
    public const SECTION_SLUG = 'section-content-banner';

    private const ALLOWED_HEADING_TAGS = ['h2', 'h3', 'h4'];

    protected function __construct() {
        $supports = [];
        $postTypes = [];

        $this->registerReferenceSection(
            __('Content banner section.', Theme::TEXT_DOMAIN),
            'text',
            false,
            $supports,
            $postTypes
        );
    }

    public static function getSectionLabel(): string {
        return __('Content Banner', Theme::TEXT_DOMAIN);
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

    public function getContentText(): string {
        $contentText = get_field('content_text') ?: '';

        return $contentText ? do_shortcode($contentText) : '';
    }

    public function getContentLink(): array {
        return $this->parseContentLink(get_field('content_link') ?? null);
    }

    private function parseContentLink(mixed $contentLink): array {
        if (!$contentLink || !is_array($contentLink)) {
            return [];
        }

        $text = $contentLink['title'] ?? '';
        $url = Link::parseLink($contentLink['url'] ?? '') ?: '';
        $target = $contentLink['target'] ?? '_self';
        $rel = $target === '_blank' ? Link::getLinkRel($url, $target) : '';

        if (!$text || !$url) {
            return [];
        }

        return [
            'text' => $text,
            'url' => $url,
            'target' => $target,
            'rel' => $rel,
        ];
    }
}
