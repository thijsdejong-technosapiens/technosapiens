<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Formatting;
use TechnoSapiens\Core\Singleton;

/**
 * Class AcfComponent
 * @package TechnoSapiens
 */
class AcfComponent extends Singleton {
    /**
     * AcfComponent constructor.
     */
    protected function __construct() {
        //hide ACF menu item when DEV environment variable is disabled
        add_filter('acf/settings/show_admin', function () {
            return getenv('DEV') === 'true';
        });

        //filter available taxonomies in ACF taxonomy selector
        add_filter('acf/get_taxonomies', [$this, 'filterAcfTaxonomies'], 1, 100);

        //automatically slugify the value of custom_archive_slug field
        add_filter('acf/update_value/name=custom_archive_slug', [$this, 'handleBeforeUpdateCustomArchiveSlugValue'], 10, 3);

        //flush permalinks when custom_archive_slug field is saved
        add_action('acf/save_post', [$this, 'handleAcfSavePost'], 100);

        //flush permalinks when needed
        add_action('init', function () {
            if (get_option('should_flush_permalinks') === "yes") {
                flush_rewrite_rules();
                update_option('should_flush_permalinks', "no");
            }
        });

        //register your Google API key
        add_filter('acf/fields/google_map/api', [$this, 'handleAcfApiKey'], 100);
    }

    /**
     * Handle ACF save
     * @param (int|string) $postId
     */
    public static function handleAcfSavePost(int|string $postId): void {
        $fields = get_fields($postId);
        if (array_key_exists('custom_archive_slug', $fields)) update_option('should_flush_permalinks', "yes");
    }

    /**
     * Handle ACF value update
     * @param string $value
     * @return string
     */
    public static function handleBeforeUpdateCustomArchiveSlugValue(string $value): string {
        return Formatting::slugify($value);
    }

    /**
     * Filter available ACF taxonomies in admin
     * @param array $taxonomies
     * @param array $args
     * @return array
     */
    public static function filterAcfTaxonomies(array $taxonomies): array {
        if (!in_array('nav_menu', $taxonomies)) $taxonomies[] = 'nav_menu';
        return $taxonomies;
    }

    /**
     * Handle ACF Google maps API key
     * @param array $api
     * @return array
     */
    public static function handleAcfApiKey(array $api): array {
        return $api;
    }
}