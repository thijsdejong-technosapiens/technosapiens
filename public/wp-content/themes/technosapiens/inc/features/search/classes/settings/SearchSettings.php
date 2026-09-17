<?php

namespace TechnoSapiens\SearchPlugin;

use TechnoSapiens\Core\Singleton;
use TechnoSapiens\GlobalCtaPlugin\AcfLocationComponent as GlobalCtaAcfLocationComponent;
use TechnoSapiens\SearchPlugin;
use TechnoSapiens\ThemeSettings;

/**
 * Class SearchSettings
 * @package TechnoSapiens\SearchPlugin
 */
class SearchSettings extends Singleton {
    /**
     * Define menu slug
     */
    const MENU_SLUG = 'search-settings';

    /**
     * Set default value for $searchEnabled variable
     * @var bool|string
     */
    public bool $searchEnabled = false;

    /**
     * Set default value for $microCopyEnabled variable
     * @var bool|string
     */
    public bool $microCopyEnabled = false;

    /**
     * Set default value for $searchEnabled variable
     * @var bool|string
     */
    public bool $filterByPostTypeEnabled = false;

    /**
     * Set default value for $postsPerPage variable
     * @var int
     */
    public int $postsPerPage = -1;

    /**
     * Set default value for $excerptLimit variable
     * @var int
     */
    public int $excerptLimit = 10;

    /**
     * SearchSettings constructor.
     */
    protected function __construct() {
        if (function_exists('acf_add_options_sub_page')) {
            acf_add_options_sub_page([
                'page_title' => __("Search settings", SearchPlugin::TEXT_DOMAIN),
                'menu_title' => __("Search settings", SearchPlugin::TEXT_DOMAIN),
                'menu_slug' => self::MENU_SLUG,
                'post_id' => self::MENU_SLUG,
                'parent_slug' => ThemeSettings::MENU_SLUG,
            ]);
        }

        add_action('acf/init', function () {

            //set search enabled
            $this->searchEnabled = get_field('search_enabled', self::MENU_SLUG) ?? false;

            //set filter by post type
            $this->filterByPostTypeEnabled = get_field('filter_by_post_type', self::MENU_SLUG) ?? false;

            //set microcopy enabled
            $this->microCopyEnabled = get_field('search_microcopy_enabled', self::MENU_SLUG) ?? false;

            //set posts per page
            $configuredPostsPerPage = (int)get_field('search_posts_per_page', self::MENU_SLUG) ?: -1;
            $this->postsPerPage = $configuredPostsPerPage === -1 || $configuredPostsPerPage > 0 ? $configuredPostsPerPage : get_option('posts_per_page', -1);

            //set excerpt limit
            $excerptLimit = get_field('search_excerpt_limit', SearchSettings::MENU_SLUG);
            $this->excerptLimit = $excerptLimit ? (int)$excerptLimit : 10;

            //bail if admin
            if (!is_admin()) {
                global $otherPosts;
                if (!$otherPosts) $otherPosts = [];

                //handle static block for search results page
                add_action('wp_enqueue_scripts', function () {
                    if (is_search()) {
                        $noResultsSbId = self::getNoResultsSbId();

                        //only add to global $otherPosts if no results static block is shown
                        global $wp_query;
                        $totalPosts = (int)$wp_query->found_posts ?: 0;
                        if ($noResultsSbId && $totalPosts === 0) {
                            $noResultsSbPost = get_post($noResultsSbId);
                            if (is_a($noResultsSbPost, '\WP_Post')) {
                                global $otherPosts;
                                if (!$otherPosts || !is_array($otherPosts)) $otherPosts = [];
                                $otherPosts[] = $noResultsSbPost;
                            }
                        }
                    }
                }, 1, 1);
            }
        }, 1, 1);
    }

    /**
     * Util to check if search is enabled from settings
     * @return bool
     */
    public function isSearchEnabled(): bool {
        return $this->searchEnabled;
    }

    /**
     * Util to check if search text is enabled from settings
     * @return bool
     */
    public function isMicroCopyEnabled(): bool {
        return $this->microCopyEnabled;
    }

    /**
     * Get posts per page from settings
     * Hard-caps unbounded (-1) and oversized values to avoid loading every match.
     * @return int
     */
    public function getPostsPerPage(): int {
        if ($this->postsPerPage < 1 || $this->postsPerPage > 100) {
            return 100;
        }
        return $this->postsPerPage;
    }

    /**
     * Get excerpt limit from settings
     * @return int
     */
    public function getExcerptLimit(): int {
        return $this->excerptLimit;
    }

    /**
     * Util to check if filter by post type is enabled
     * @return bool
     */
    public function isFilterByPostTypeEnabled(): bool {
        return $this->filterByPostTypeEnabled;
    }


    /**
     * Get no results static block ID
     * @return mixed
     */
    public static function getNoResultsSbId(): mixed {
        return get_field('no_results_content', self::MENU_SLUG) ?: '';
    }
}