<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Formatting;
use TechnoSapiens\Core\PostType;
use TechnoSapiens\FactoryPlugin\PostTypeFactory;

/**
 * Class StaticBlockPostType
 * @package TechnoSapiens
 */
final class StaticBlockPostType extends PostTypeFactory {

    /**
     * Define post type slug
     */
    public const TYPE = 'static-block';

    /**
     * StaticBlockPostType constructor
     */
    protected function __construct() {

        //set up post type labels
        self::setPostTypeLabels(PostType::getLabels(
            __('Static block', Theme::TEXT_DOMAIN),
            __('Static blocks', Theme::TEXT_DOMAIN)
        ));

        //set up post type supports
        self::setSupports(['title', 'editor', 'revisions']);

        //set up post type icon
        self::setMenuIcon('dashicons-editor-insertmore');

        //set up default post-type features
        self::setFeatures([
            'enableArchive' => false,
            'enableSingle' => false,
            'enableGutenbergEditor' => true,  //since the Gutenberg editor is needed 🙂
        ]);

        //call the parent constructor
        parent::__construct();
    }

    /**
     * Get static block by id
     * @param $postId
     * @return string
     */
    public static function getBlockById($postId): string {
        if (!$postId || !is_numeric($postId)) return '';

        $blockHtml = '';
        $post = get_post($postId);
        if ($post instanceof \WP_Post && $post->post_type === self::TYPE) {
            self::addPostToOtherPosts($post);
            $blockHtml = self::getBlockHtml($post);
        }

        return $blockHtml;
    }

    /**
     * Register a static block post so section assets can be detected via has_block().
     *
     * @param \WP_Post $post
     * @return void
     */
    public static function addPostToOtherPosts(\WP_Post $post): void {
        if (is_admin()) {
            return;
        }

        global $otherPosts;

        if (!is_array($otherPosts)) {
            $otherPosts = [];
        }

        foreach ($otherPosts as $existingPost) {
            if ($existingPost instanceof \WP_Post && (int) $existingPost->ID === (int) $post->ID) {
                return;
            }
        }

        $otherPosts[] = $post;
    }

    /**
     * Get formatted html of static block post
     *
     * @param \WP_Post $post
     * @return string
     */
    public static function getBlockHtml(\WP_Post $post): string {
        $content = $post->post_content;
        return Formatting::toHtmlWithoutP($content);
    }
}
