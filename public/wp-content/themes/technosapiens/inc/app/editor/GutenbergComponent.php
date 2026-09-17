<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Singleton;

/**
 * Class GutenbergComponent
 * @package TechnoSapiens
 */
class GutenbergComponent extends Singleton {

    /**
     * Define default core blocks to keep
     * @note use this JS in the admin console to get the list -> console.log(window.wp.blocks.getBlockTypes().map(block => `"${block.name}"`).join(',\n'))
     * @var array
     */
    public static array $coreBlocksToKeep = [
        //"yoast-seo/breadcrumbs",
        //"yoast/how-to-block",
        //"yoast/faq-block",
        //"gravityforms/form",
        //"core/paragraph",
        //"core/image",
        //"core/heading",
        //"core/gallery",
        //"core/list",
        //"core/list-item",
        //"core/quote",
        //"core/shortcode",
        //"core/archives",
        //"core/audio",
        //"core/button",
        //"core/buttons",
        //"core/calendar",
        //"core/categories",
        //"core/code",
        //"core/columns",
        //"core/column",
        //"core/cover",
        //"core/embed",
        //"core/file", <-- Useful for download features
        //"core/group",
        //"core/freeform", <-- Classic WP editor
        //"core/html",
        //"core/media-text",
        //"core/latest-comments",
        //"core/latest-posts",
        "core/missing",
        "core/pattern",
        //"core/more",
        //"core/nextpage",
        //"core/page-list",
        //"core/preformatted",
        //"core/pullquote",
        //"core/rss",
        //"core/search",
        //"core/separator",
        "core/block",
        //"core/social-links",
        //"core/social-link",
        //"core/spacer",
        //"core/table",
        //"core/tag-cloud",
        //"core/text-columns",
        //"core/verse",
        //"core/video", <-- Useful for inline <video> element
        //"core/site-logo",
        //"core/site-tagline",
        //"core/site-title",
        //"core/query",
        //"core/post-template",
        //"core/query-title",
        //"core/query-pagination",
        //"core/query-pagination-next",
        //"core/query-pagination-numbers",
        //"core/query-pagination-previous",
        //"core/post-title",
        //"core/post-content",
        //"core/post-date",
        //"core/post-excerpt",
        //"core/post-featured-image",
        //"core/post-terms",
        //        "core/loginout",
    ];

    /**
     * GutenbergComponent constructor.
     */
    protected function __construct() {

        //set allowed blocks
        add_filter('allowed_block_types_all', [$this, 'setAllowedBlocks'], 2, 100);

        //remove unneeded typography and support options
        add_filter('block_editor_settings_all', [$this, 'disableBlockFeatures']);
    }

    /**
     * Set allowed blocks
     * @param bool|array $blocks
     * @param \WP_Block_Editor_Context $context
     * @return bool|array
     */
    public static function setAllowedBlocks($blocks, \WP_Block_Editor_Context $context) {
        $defaultBlocks = self::$coreBlocksToKeep;
        $acfBlocks = array_keys(acf_get_block_types());
        return array_merge($defaultBlocks, $acfBlocks);
    }

    /**
     * Disable feature globally in core Gutenberg blocks
     * @param array $editorSettings
     * @return array
     */
    public static function disableBlockFeatures(array $editorSettings): array {
        $editorSettings['canLockBlocks'] = false;
        return $editorSettings;
    }
}