<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Singleton;

/**
 * Class PostStatusComponent
 * @package TechnoSapiens
 */
class PostStatusComponent extends Singleton {

    /**
     * PostStatusComponent constructor
     */
    protected function __construct() {
        //display post status
        add_filter('display_post_states', [$this, 'filterPostStates'], 10, 2);
    }

    /**
     * Filter post states
     * @param array $states
     * @param \WP_Post $post
     * @return array
     */
    public static function filterPostStates(array $states, \WP_Post $post): array {
        $postType = $post->post_type;
        $postId = $post->ID;

        //add 404-page ID state
        $page404Id = Page404Component::get404PageId();
        if ($postId === $page404Id) {
            $states[] = __('404 page (theme settings)', Theme::TEXT_DOMAIN);
        }

        //check if entire post type is not indexable from yoast settings
        $postTypeIsNotIndexable = false;
        if (class_exists('\WPSEO_Options')) {
            $postTypeIsNotIndexable = \WPSEO_Options::get('noindex-' . $postType, false);
            if ($postTypeIsNotIndexable) $states[] = __('Not indexable (Yoast settings)', Theme::TEXT_DOMAIN);
        }

        //add not indexable ID state
        if (!$postTypeIsNotIndexable && get_post_meta($postId, '_yoast_wpseo_meta-robots-noindex', true) === '1') {
            $states[] = __('Not indexable (individual)', Theme::TEXT_DOMAIN);
        }

        return $states;
    }
}