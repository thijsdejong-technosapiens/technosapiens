<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Formatting;
use TechnoSapiens\Core\Singleton;

class SectionFaq extends Singleton {
    use SectionReferenceTrait;

    public const SECTION_ID = 'section_section_faq';
    public const SECTION_SLUG = 'section-faq';

    private const ALLOWED_ANSWER_TAGS = '<a><u><i><em><strong><b><br><ul><ol><li><p>';

    protected function __construct() {
        $supports = [];
        $postTypes = [];

        $this->registerReferenceSection(
            __('FAQ section with optional accordion and FAQ schema.', Theme::TEXT_DOMAIN),
            'editor-help',
            true,
            $supports,
            $postTypes
        );
    }

    public static function getSectionLabel(): string {
        return __('FAQ', Theme::TEXT_DOMAIN);
    }

    public function getSectionTitle(): string {
        return get_field('section_title') ?? '';
    }

    public function isCollapsible(): bool {
        $method = get_field('faq_collapse_method') ?: 'collapsible';

        return $method !== 'not_collapsible';
    }

    public function getFaqItems(): array {
        $items = get_field('faq_items') ?? [];

        if (!is_array($items)) {
            return [];
        }

        $faqItems = [];

        foreach ($items as $item) {
            $question = trim((string) ($item['faq_question'] ?? ''));
            $answer = (string) ($item['faq_answer'] ?? '');

            if (!$question || !$answer) {
                continue;
            }

            $answer = strip_tags($answer, self::ALLOWED_ANSWER_TAGS);
            $answer = preg_replace('/\s*style\s*=\s*("[^"]*"|\'[^\']*\')/i', '', $answer) ?: $answer;
            $answerHtml = Formatting::toHtml($answer);

            if (!$answerHtml) {
                continue;
            }

            $faqItems[] = [
                'question' => $question,
                'answerHtml' => $answerHtml,
                'answerText' => wp_strip_all_tags($answerHtml),
            ];
        }

        return $faqItems;
    }
}
