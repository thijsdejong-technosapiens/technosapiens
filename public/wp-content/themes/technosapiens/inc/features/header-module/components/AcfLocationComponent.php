<?php

namespace TechnoSapiens\HeaderModulePlugin;

use TechnoSapiens\Core\PostType;
use TechnoSapiens\Core\Singleton;
use TechnoSapiens\Page404Component;
use TechnoSapiens\SearchPlugin\SearchSettings;

/**
 * class AcfLocationComponent
 * package TechnoSapiens\HeaderModulePlugin
 */
class AcfLocationComponent extends Singleton {

    /**
     * Set default value for $config
     * @var array
     */
    protected array $locations = [];

    /**
     * Get ACF locations
     * @return array
     */
    public function getAcfLocations(): array {
        return $this->locations;
    }

    /**
     * Get active header module post types
     * @return array
     */
    public function getActiveHeaderModulePostTypes(): array {
        $acfLocations = AcfLocationComponent::getInstance()->getAcfLocations();
        $enabledPostTypes = [];
        foreach ($acfLocations as $acfLocation) {
            if ($acfLocation[0]['param'] === 'post_type') $enabledPostTypes[] = $acfLocation[0]['value'];
        }

        return array_unique($enabledPostTypes);
    }

    /**
     * Get enabled post‑type archives (via options pages)
     * @return array
     */
    public function getEnabledPostTypesArchives(): array {
        $archives = array_filter($this->locations, fn($loc) => ($loc[0]['param'] ?? '') === 'options_page');
        return array_map(function ($loc) {
            return str_replace('-settings', '', $loc[0]['value'] ?? '');
        }, $archives);
    }

    /**
     * Get enabled taxonomies
     * @return array
     */
    public function getEnabledTaxonomies(): array {
        $tax = array_filter($this->locations, fn($loc) => ($loc[0]['param'] ?? '') === 'taxonomy');
        return array_map(fn($loc) => $loc[0]['value'] ?? '', $tax);
    }

    /**
     * Check if the header module is enabled for the current request context
     * (single, taxonomy, archive, search, 404).
     *
     * @return bool
     */
    public function currentContextIsEnabled(): bool {
        $enabled = false;

        //single post / page
        if (is_single() || is_page()) {
            $postType = PostType::getPostType();
            $enabledPostTypes = $this->getActiveHeaderModulePostTypes();
            $enabled = in_array($postType, $enabledPostTypes, true);
        } elseif (is_tax()) {
            //taxonomy term page
            $term = get_queried_object();
            $taxonomy = $term instanceof \WP_Term ? $term->taxonomy : '';
            $enabledTax = $this->getEnabledTaxonomies();
            $enabled = in_array($taxonomy, $enabledTax, true);
        } elseif (is_404() && class_exists('\TechnoSapiens\Page404Component')) {
            //404 page
            $page404Id = Page404Component::get404PageId();
            $postType = $page404Id ? get_post_type($page404Id) : '';
            $enabledPostTypes = $this->getActiveHeaderModulePostTypes();
            $enabled = in_array($postType, $enabledPostTypes, true);
        } elseif (is_archive()) {
            //post‑type archive
            $postType = PostType::getPostType();
            $enabledArchives = $this->getEnabledPostTypesArchives();
            $enabled = in_array($postType, $enabledArchives, true);
        } elseif (is_search() && class_exists('\TechnoSapiens\SearchPlugin\SearchSettings')) {
            //search results (mapped to the search settings options page)
            $enabledArchives = $this->getEnabledPostTypesArchives();
            $searchKey = str_replace('-settings', '', SearchSettings::MENU_SLUG);
            $enabled = in_array($searchKey, $enabledArchives, true);
        }

        return $enabled;
    }

    /**
     * Add header module to single post
     * @param string $postTypeSlug
     * @return void
     */
    public function addHeaderModuleToPostType(string $postTypeSlug): void {
        $newLocation = [];
        $newLocation['param'] = "post_type";
        $newLocation['operator'] = '==';
        $newLocation['value'] = $postTypeSlug;
        $this->locations[] = [$newLocation];
    }

    /**
     * Add header module to options page
     * @param string $optionsPageKey
     * @return void
     */
    public function addHeaderModuleToOptionsPage(string $optionsPageKey): void {
        $newLocation = [];
        $newLocation['param'] = "options_page";
        $newLocation['operator'] = '==';
        $newLocation['value'] = $optionsPageKey;
        $this->locations[] = [$newLocation];
    }

    /**
     * Add header module to taxonomy term
     * @param string $taxonomySlug
     * @return void
     */
    public function addHeaderModuleToTaxonomy(string $taxonomySlug): void {
        $newLocation = [];
        $newLocation['param'] = "taxonomy";
        $newLocation['operator'] = '==';
        $newLocation['value'] = $taxonomySlug;
        $this->locations[] = [$newLocation];
    }
}