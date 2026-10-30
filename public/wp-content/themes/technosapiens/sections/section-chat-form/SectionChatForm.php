<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Gutenberg;
use TechnoSapiens\Core\Singleton;

class SectionChatForm extends Singleton {
    use SectionReferenceTrait;

    public const SECTION_ID = 'section_section_chat_form';
    public const SECTION_SLUG = 'section-chat-form';

    private const ALLOWED_TOPIC_COLORS = ['blue', 'green', 'orange', 'cyan', 'purple'];

    protected function __construct() {
        $supports = [];
        $postTypes = [];

        $this->registerReferenceSection(
            __('Chat form section.', Theme::TEXT_DOMAIN),
            'email',
            true,
            $supports,
            $postTypes
        );

        add_filter('acf/load_field/name=gravity_form_id', [$this, 'loadGravityFormChoices']);
        add_filter('ts_admin_bar_light_used_gravity_form_ids', [$this, 'filterUsedGravityFormIds']);
    }

    public static function getSectionLabel(): string {
        return __('Chat form', Theme::TEXT_DOMAIN);
    }

    public function getSectionTitle(): string {
        return get_field('section_title') ?? '';
    }

    /**
     * Validated active Gravity Form ID, or 0 when missing/inactive.
     */
    public function getValidatedGravityFormId(): int {
        $formId = absint(get_field('gravity_form_id') ?? 0);

        if (!$formId || !class_exists('\GFAPI')) {
            return 0;
        }

        $form = \GFAPI::get_form($formId);

        if (!$form || !is_array($form) || empty($form['is_active'])) {
            return 0;
        }

        return $formId;
    }

    /**
     * Topic pills for the chat UI.
     *
     * @return array<int, array{label: string, slug: string, color: string, iconId: int}>
     */
    public function getTopics(): array {
        $items = get_field('topics') ?? [];

        if (!is_array($items)) {
            return [];
        }

        $topics = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $label = trim((string) ($item['label'] ?? ''));
            $slug = sanitize_title((string) ($item['slug'] ?? ''));
            $color = (string) ($item['color'] ?? 'blue');
            $icon = $item['icon'] ?? [];
            $iconId = (!empty($icon['ID'])) ? (int) $icon['ID'] : 0;

            if (!$label || !$slug) {
                continue;
            }

            if (!in_array($color, self::ALLOWED_TOPIC_COLORS, true)) {
                $color = 'blue';
            }

            $topics[] = [
                'label' => $label,
                'slug' => $slug,
                'color' => $color,
                'iconId' => $iconId,
            ];
        }

        return $topics;
    }

    /**
     * Populate the Gravity Form select with active forms only.
     */
    public function loadGravityFormChoices(array $field): array {
        $field['choices'] = [];

        if (!class_exists('\GFAPI')) {
            return $field;
        }

        $forms = \GFAPI::get_forms(true);

        if (!is_array($forms)) {
            return $field;
        }

        foreach ($forms as $form) {
            if (empty($form['id']) || empty($form['title'])) {
                continue;
            }

            $field['choices'][(int) $form['id']] = sprintf(
                '%1$s (ID: %2$d)',
                $form['title'],
                (int) $form['id']
            );
        }

        return $field;
    }

    /**
     * Expose this section's form ID to the light admin bar.
     */
    public function filterUsedGravityFormIds(array $usedGravityFormIds): array {
        $blocks = Gutenberg::getUsedBlocksByName('acf/section-' . self::SECTION_SLUG);

        foreach ($blocks as $block) {
            $gravityFormId = absint($block['attrs']['data']['gravity_form_id'] ?? 0);

            if ($gravityFormId && !in_array($gravityFormId, $usedGravityFormIds, false)) {
                $usedGravityFormIds[] = $gravityFormId;
            }
        }

        return $usedGravityFormIds;
    }
}
