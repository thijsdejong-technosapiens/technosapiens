<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Link;
use TechnoSapiens\Core\Singleton;

class SectionProcessCards extends Singleton {
    use SectionReferenceTrait;

    public const SECTION_ID = 'section_section_process_cards';
    public const SECTION_SLUG = 'section-process-cards';

    private const MAX_CARDS = 4;

    protected function __construct() {
        $supports = [];
        $postTypes = [];

        $this->registerReferenceSection(
            __('Process cards section.', Theme::TEXT_DOMAIN),
            'index-card',
            true,
            $supports,
            $postTypes
        );
    }

    public static function getSectionLabel(): string {
        return __('Process cards', Theme::TEXT_DOMAIN);
    }

    public function getSectionTitle(): string {
        return get_field('section_title') ?? '';
    }

    public function getCards(): array {
        $items = get_field('cards') ?? [];

        if (!is_array($items)) {
            return [];
        }

        $cards = [];

        foreach ($items as $item) {
            $title = $item['card_title'] ?? '';
            $text = $item['card_text'] ?? '';

            if (!$title && !$text) {
                continue;
            }

            $cards[] = [
                'title' => $title,
                'text' => $text,
            ];

            if (count($cards) >= self::MAX_CARDS) {
                break;
            }
        }

        return $cards;
    }

    public function getCallToAction(): array {
        return $this->parseLink(get_field('call_to_action') ?? null);
    }

    private function parseLink(mixed $linkField): array {
        if (!$linkField || !is_array($linkField)) {
            return [];
        }

        $text = $linkField['title'] ?? '';
        $url = Link::parseLink($linkField['url'] ?? '') ?: '';
        $target = $linkField['target'] ?? '_self';
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
