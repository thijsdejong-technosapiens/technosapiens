<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Partial;
use TechnoSapiens\Core\Singleton;

class SectionStaticSection extends Singleton {
    use SectionReferenceTrait;

    public const SECTION_ID = 'section_section_static_section';
    public const SECTION_SLUG = 'section-static-section';
    public const BLOCK_NAME = 'acf/section-section-static-section';
    public const FIELD_STATIC_BLOCK = 'static_block';
    public const SECTION_SETTINGS_GROUP_KEY = 'group_6156aaf3cead0';

    private static int $embedDepth = 0;

    protected function __construct() {
        $supports = [
            'customClassName' => false,
            'anchor' => false,
        ];

        $this->registerReferenceSection(
            __('Embed a managed static block without extra section chrome.', Theme::TEXT_DOMAIN),
            'editor-insertmore',
            false,
            $supports
        );

        add_action('wp_enqueue_scripts', [self::class, 'registerEmbeddedStaticBlocksForAssets'], 1);
        add_filter('allowed_block_types_all', [self::class, 'excludeFromStaticBlockEditor'], 20, 2);
        add_filter('acf/location/rule_match/block', [self::class, 'hideSectionSettingsForThisBlock'], 10, 4);
        add_filter('acf/fields/post_object/query/name=' . self::FIELD_STATIC_BLOCK, [self::class, 'filterStaticBlockQuery'], 10, 1);
    }

    public static function getSectionLabel(): string {
        return __('Static section', Theme::TEXT_DOMAIN);
    }

    public static function getStaticBlockId(): int {
        $staticBlockId = get_field(self::FIELD_STATIC_BLOCK);

        return is_numeric($staticBlockId) ? (int) $staticBlockId : 0;
    }

    /**
     * @param array $block
     * @param string $content
     * @param bool $is_preview
     * @param int|string $post_id
     * @return string
     */
    public function render(array $block, string $content = '', bool $is_preview = false, int|string $post_id = 0): string {
        $isExample = !empty($block['data']['is_example']);

        if ($isExample || $is_preview) {
            $bar = SectionReferencePartial::renderEditorBar(self::SECTION_SLUG, static::getSectionLabel());
            echo $bar;
            echo self::renderEditorPlaceholder($block);

            return $bar;
        }

        return Partial::render(
            self::SECTION_SLUG,
            ['block' => $block, 'content' => $content, 'is_preview' => $is_preview, 'post_id' => $post_id],
            true,
            get_stylesheet_directory() . '/sections/' . self::SECTION_SLUG . '/partials/'
        );
    }

    /**
     * Render selected static block HTML, or empty when nested / invalid.
     * @param int $staticBlockId
     * @return string
     */
    public static function renderSelectedStaticBlock(int $staticBlockId): string {
        if (self::$embedDepth > 0 || $staticBlockId <= 0) {
            return '';
        }

        $post = get_post($staticBlockId);
        if (
            !($post instanceof \WP_Post)
            || $post->post_type !== StaticBlockPostType::TYPE
            || $post->post_status !== 'publish'
        ) {
            return '';
        }

        self::$embedDepth++;
        $html = StaticBlockPostType::getBlockById($staticBlockId);
        self::$embedDepth--;

        return $html;
    }

    /**
     * Push referenced static blocks into $otherPosts before section asset enqueue.
     * @return void
     */
    public static function registerEmbeddedStaticBlocksForAssets(): void {
        if (is_admin()) {
            return;
        }

        global $post;
        if (!($post instanceof \WP_Post)) {
            return;
        }

        foreach (self::collectStaticBlockIdsFromPost($post) as $staticBlockId) {
            $staticBlockPost = get_post($staticBlockId);
            if (
                $staticBlockPost instanceof \WP_Post
                && $staticBlockPost->post_type === StaticBlockPostType::TYPE
                && $staticBlockPost->post_status === 'publish'
            ) {
                StaticBlockPostType::addPostToOtherPosts($staticBlockPost);
            }
        }
    }

    /**
     * Prevent inserting this shell inside static-block compositions.
     * @param bool|array $blocks
     * @param \WP_Block_Editor_Context $context
     * @return bool|array
     */
    public static function excludeFromStaticBlockEditor($blocks, \WP_Block_Editor_Context $context) {
        if (!is_array($blocks)) {
            return $blocks;
        }

        $editorPost = $context->post ?? null;
        if (!($editorPost instanceof \WP_Post) || $editorPost->post_type !== StaticBlockPostType::TYPE) {
            return $blocks;
        }

        return array_values(array_filter(
            $blocks,
            static fn($blockName): bool => $blockName !== self::BLOCK_NAME
        ));
    }

    /**
     * Keep shared section settings out of this chrome-less shell.
     * @param bool $match
     * @param array $rule
     * @param array $screen
     * @param array $fieldGroup
     * @return bool
     */
    public static function hideSectionSettingsForThisBlock(bool $match, array $rule, array $screen, array $fieldGroup): bool {
        if (($fieldGroup['key'] ?? '') !== self::SECTION_SETTINGS_GROUP_KEY) {
            return $match;
        }

        if (($rule['param'] ?? '') !== 'block' || ($rule['value'] ?? '') !== 'all') {
            return $match;
        }

        $blockName = (string) ($screen['block'] ?? '');
        if ($blockName === self::BLOCK_NAME) {
            return false;
        }

        return $match;
    }

    /**
     * @param array $args
     * @return array
     */
    public static function filterStaticBlockQuery(array $args): array {
        $args['post_status'] = 'publish';
        $args['post_type'] = StaticBlockPostType::TYPE;

        return $args;
    }

    /**
     * @param \WP_Post $post
     * @return int[]
     */
    private static function collectStaticBlockIdsFromPost(\WP_Post $post): array {
        return array_values(array_unique(self::collectStaticBlockIdsFromBlocks(parse_blocks((string) $post->post_content))));
    }

    /**
     * @param array $blocks
     * @return int[]
     */
    private static function collectStaticBlockIdsFromBlocks(array $blocks): array {
        $ids = [];

        foreach ($blocks as $block) {
            if (($block['blockName'] ?? '') === self::BLOCK_NAME) {
                $staticBlockId = $block['attrs']['data'][self::FIELD_STATIC_BLOCK] ?? 0;
                if (is_numeric($staticBlockId) && (int) $staticBlockId > 0) {
                    $ids[] = (int) $staticBlockId;
                }
            }

            if (!empty($block['innerBlocks']) && is_array($block['innerBlocks'])) {
                $ids = array_merge($ids, self::collectStaticBlockIdsFromBlocks($block['innerBlocks']));
            }
        }

        return $ids;
    }

    /**
     * @param array $block
     * @return string
     */
    private static function renderEditorPlaceholder(array $block): string {
        $staticBlockId = get_field(self::FIELD_STATIC_BLOCK);
        if (!$staticBlockId) {
            $staticBlockId = $block['data'][self::FIELD_STATIC_BLOCK] ?? 0;
        }

        $staticBlockId = is_numeric($staticBlockId) ? (int) $staticBlockId : 0;
        $message = __('Select a published static block.', Theme::TEXT_DOMAIN);

        if ($staticBlockId > 0) {
            $staticBlockPost = get_post($staticBlockId);
            if (
                $staticBlockPost instanceof \WP_Post
                && $staticBlockPost->post_type === StaticBlockPostType::TYPE
                && $staticBlockPost->post_status === 'publish'
            ) {
                $message = sprintf(
                    /* translators: %s: static block title */
                    __('Embedding static block: %s', Theme::TEXT_DOMAIN),
                    get_the_title($staticBlockPost)
                );
            } else {
                $message = __('Selected static block is unavailable.', Theme::TEXT_DOMAIN);
            }
        }

        return '<p class="section-static-section__editor-placeholder">' . esc_html($message) . '</p>';
    }
}
