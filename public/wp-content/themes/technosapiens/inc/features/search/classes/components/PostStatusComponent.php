<?php

namespace TechnoSapiens\SearchPlugin;

use TechnoSapiens\Core\Singleton;
use TechnoSapiens\SearchPlugin;

/**
 * Class PostStatusComponent
 * @package TechnoSapiens\SearchPlugin
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

        //add excluded from search state
        $excludedPostIds = SearchExcludeComponent::getInstance()->getExcludedPostIds();
        if (in_array($post->ID, $excludedPostIds)) {
            $states[] = __('Excluded from search', SearchPlugin::TEXT_DOMAIN);
        }

        //add post state for static block when no results in search
        $notFoundStaticBlockPost = SearchSettings::getInstance()->getNoResultsSbId();
        if ($post->ID === $notFoundStaticBlockPost) {
            $states[] = __('No search result content (search settings)', SearchPlugin::TEXT_DOMAIN);
        }

        return $states;
    }
}