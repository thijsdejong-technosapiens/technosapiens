<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Link;
use TechnoSapiens\Core\Singleton;

class SectionStats extends Singleton {
    use SectionReferenceTrait;

    public const SECTION_ID = 'section_section_stats';
    public const SECTION_SLUG = 'section-stats';

    protected function __construct() {
        $supports = [];
        $postTypes = [];

        $this->registerReferenceSection(
            __('Stats section reference section.', Theme::TEXT_DOMAIN),
            'chart-bar',
            false,
            $supports,
            $postTypes
        );
    }

    public function getStatsItems(): array {
        $items = get_field('stats_items') ?? [];

        if (!is_array($items)) {
            return [];
        }

        $statsItems = [];

        foreach ($items as $item) {
            $number = $item['stats_number'] ?? '';
            $title = $item['stats_title'] ?? '';

            if (!$number || !$title) {
                continue;
            }

            $statsItems[] = [
                'iconId' => $this->getStatsIconId($item['stats_icon'] ?? null),
                'number' => $number,
                'title' => $title,
            ];
        }

        return $statsItems;
    }

    public function getCtaLink(): array {
        $ctaLink = get_field('stats_cta_link') ?? [];

        if (!$ctaLink || !is_array($ctaLink)) {
            return [];
        }

        $text = $ctaLink['title'] ?? '';
        $url = Link::parseLink($ctaLink['url'] ?? '') ?: '';
        $target = $ctaLink['target'] ?? '_self';
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

    public static function getSectionLabel(): string {
        return __('Stats section', Theme::TEXT_DOMAIN);
    }

    private function getStatsIconId(mixed $image): int {
        if (is_numeric($image)) {
            return (int) $image;
        }

        if (is_array($image) && !empty($image['ID'])) {
            return (int) $image['ID'];
        }

        return 0;
    }
}
