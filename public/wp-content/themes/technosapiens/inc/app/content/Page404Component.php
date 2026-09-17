<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Singleton;

/**
 * Class Page404Component
 * @package TechnoSapiens
 */
class Page404Component extends Singleton {

    /**
     * The static block post (if any) linked to the custom 404 page.
     * @var \WP_Post|false
     */
    private \WP_Post|false $staticBlockPost = false;

    /*
     * Page404Component constructor.
     */
    protected function __construct() {
        add_action('acf/init', function () {

            //retrieve "404_page_id" post from ACF options
            $result = get_field('404_page_id', 'option') ?: false;
            $this->staticBlockPost = (is_a($result, '\WP_Post')) ? $result : false;

            if ($this->staticBlockPost) {

                //store the post in $otherPosts
                add_action('template_redirect', function () {
                    if (!is_admin() && is_404()) {
                        global $otherPosts;
                        if (!$otherPosts) $otherPosts = [];
                        $otherPosts[] = $this->staticBlockPost;
                    }
                });

                //set noindex meta tag for configured 404 page in theme settings
                add_filter('wpseo_robots', function ($robotsString, $context) {
                    if (get_the_ID() === $this->staticBlockPost->ID) return 'noindex, follow';
                    return $robotsString;
                }, 2, 100);

                //exclude configured 404 page in theme settings from sitemap
                add_filter('wpseo_exclude_from_sitemap_by_post_ids', function (array $excludedPostIds) {
                    return array_merge($excludedPostIds, [$this->staticBlockPost->ID]);
                }, 1, 100);
            }
        });
    }

    /**
     * Get the static block post assigned to the main footer
     * @return \WP_Post|false
     */
    public function getStaticBlockPost(): \WP_Post|false {
        return $this->staticBlockPost;
    }

    /**
     * Get 404 page ID from settings
     * @return int|false
     */
    public static function get404PageId(): int|false {
        return self::getInstance()->staticBlockPost->ID ?? false;
    }
}