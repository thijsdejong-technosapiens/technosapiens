<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Formatting;
use TechnoSapiens\Core\Link;
use TechnoSapiens\Core\Singleton;

class SectionCards extends Singleton {
    use SectionReferenceTrait;

    public const SECTION_ID = 'section_section_cards';
    public const SECTION_SLUG = 'section-cards';

    private const ALLOWED_HEADING_TAGS = ['h2', 'h3', 'h4'];
    private const ALLOWED_CARDS_PER_ROW = [1, 2, 3, 4];
    private const MAX_CARDS = 4;

    protected function __construct() {
        $supports = [];
        $postTypes = [];

        $this->registerReferenceSection(
            __('Cards section.', Theme::TEXT_DOMAIN),
            'grid-view',
            true,
            $supports,
            $postTypes
        );
    }

    public static function getSectionLabel(): string {
        return __('Cards', Theme::TEXT_DOMAIN);
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

    public function getCardsPerRow(): int {
        $cardsPerRow = (int) (get_field('cards_per_row') ?: 3);

        if (!in_array($cardsPerRow, self::ALLOWED_CARDS_PER_ROW, true)) {
            return 3;
        }

        return $cardsPerRow;
    }

    public function getCards(): array {
        $items = get_field('cards') ?? [];

        if (!is_array($items)) {
            return [];
        }

        $cards = [];

        foreach ($items as $item) {
            $title = $item['card_title'] ?? '';
            $content = $item['card_content'] ?? '';
            $link = $this->parseCardLink($item['card_link'] ?? null);

            if (!$title && !$content && !$link) {
                continue;
            }

            $cards[] = [
                'title' => $title,
                'content' => $content ? Formatting::toHtml($content) : '',
                'link' => $link,
            ];

            if (count($cards) >= self::MAX_CARDS) {
                break;
            }
        }

        return $cards;
    }

    private function parseCardLink(mixed $cardLink): array {
        if (!$cardLink || !is_array($cardLink)) {
            return [];
        }

        $text = $cardLink['title'] ?? '';
        $url = Link::parseLink($cardLink['url'] ?? '') ?: '';
        $target = $cardLink['target'] ?? '_self';
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
