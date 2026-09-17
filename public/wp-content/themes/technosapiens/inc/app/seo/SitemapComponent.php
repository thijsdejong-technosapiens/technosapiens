<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Singleton;

/**
 * Class SitemapComponent
 * @package TechnoSapiens
 */
class SitemapComponent extends Singleton {
    /**
     * SitemapComponent constructor.
     */
    protected function __construct() {
        add_filter('wpseo_sitemap_exclude_post_type', [$this, 'filterExcludeSitemapPostTypes'], 10, 2);
        add_filter('wpseo_sitemap_exclude_taxonomy', [$this, 'filterExcludeSitemapTaxonomies'], 10, 2);
        add_filter('wpseo_sitemap_exclude_author', '__return_empty_array', 10, 1);
    }

    /**
     * Filter sitemap post types
     * @param bool $excluded
     * @param string $postType
     * @return bool
     */
    public static function filterExcludeSitemapPostTypes(bool $excluded, string $postType): bool {
        //exclude post type "post" from sitemap
        if ($postType === 'post') return true;

        return $excluded;
    }

    /**
     * Filter sitemap taxonomies
     * @param bool $excluded
     * @param string $taxonomy
     * @return bool
     */
    public static function filterExcludeSitemapTaxonomies(bool $excluded, string $taxonomy): bool {
        //exclude taxonomy "category" from sitemap
        if ($taxonomy === 'category') return true;

        return $excluded;
    }
}
