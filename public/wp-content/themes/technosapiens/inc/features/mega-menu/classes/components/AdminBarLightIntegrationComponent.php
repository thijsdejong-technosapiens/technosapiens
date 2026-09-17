<?php

namespace TechnoSapiens\MegaMenuPlugin;

use TechnoSapiens\Core\Singleton;

/**
 * Class AdminBarLightIntegrationComponent
 * @package TechnoSapiens\MegaMenuPlugin
 * Ensures active mega-menu posts are added to global $otherPosts
 * so Admin Bar Light and other consumers can detect their presence.
 */
class AdminBarLightIntegrationComponent extends Singleton {

    /**
     * AdminBarLightIntegrationComponent constructor
     */
    protected function __construct() {
        if (!is_admin()) add_action('template_redirect', [$this, 'exposeActiveMegaMenusToOtherPosts'], 1);
    }

    /**
     * Add visible and validated connected mega menu posts to global $otherPosts.
     * @return void
     */
    public function exposeActiveMegaMenusToOtherPosts(): void {

        //get all connected mega menu items that are visible and validated
        $connected = MegaMenuUsageResolver::getInstance()->getConnectedMegaMenuItems(true);
        if (count($connected) === 0) return;

        //init global $otherPosts if needed
        global $otherPosts;
        if (!isset($otherPosts) || !is_array($otherPosts)) $otherPosts = [];

        //prevent duplicates if multiple items link to the same menu
        $added = [];
        foreach ($connected as $entry) {

            //bail if no valid mmId
            $mmId = isset($entry['mmId']) ? (int)$entry['mmId'] : 0;
            if ($mmId <= 0 || isset($added[$mmId])) continue;

            //get post and add to $otherPosts
            $post = get_post($mmId);
            if ($post instanceof \WP_Post && $post->post_type === MegaMenuPostType::TYPE) {
                $otherPosts[] = $post;
                $added[$mmId] = true;
            }
        }
    }
}

