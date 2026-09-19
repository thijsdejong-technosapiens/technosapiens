<?php

namespace TechnoSapiens\HeaderModulePlugin;

use TechnoSapiens\Core\Singleton;
use TechnoSapiens\HeaderModulePlugin;
use TechnoSapiens\Page404Component;
use TechnoSapiens\SearchPlugin\SearchSettings;

/**
 * class TemplateComponent
 * package TechnoSapiens\HeaderModulePlugin
 */
class TemplateComponent extends Singleton {

    /**
     * TemplateComponent constructor
     */
    protected function __construct() {
        if (!is_admin()) {
            add_action('template_redirect', function () {

                //set query var for usage in templates
                set_query_var('has-header-module', false);

                //check if hero is enabled in current context
                if (!AcfLocationComponent::getInstance()->currentContextIsEnabled()) {
                    return;
                }

                //determine acf key based on page context
                $acfKey = false;
                if (is_single() || is_page()) {
                    $acfKey = get_the_ID();
                } elseif (is_tax()) {
                    $term = get_queried_object();
                    if (is_a($term, '\WP_Term')) {
                        $acfKey = 'term_' . $term->term_id;
                    }
                } elseif (is_404() && class_exists('\TechnoSapiens\Page404Component')) {
                    $acfKey = Page404Component::get404PageId();
                } elseif (is_archive()) {
                    $queried = get_queried_object();
                    $acfKey = $queried ? $queried->name . '-settings' : false;
                } elseif (is_search() && class_exists('\TechnoSapiens\SearchPlugin\SearchSettings')) {
                    $acfKey = SearchSettings::MENU_SLUG;
                }


                if ($acfKey) {
                    $preset = apply_filters('hm_preset_filter', (get_field('hm_preset', $acfKey) ?: 'main-hero'), $acfKey);
                    $activePresets = HeaderModulePlugin::getInstance()->getActivePresets();

                    //get data and check if data is valid
                    if ($activePresets && array_key_exists($preset, $activePresets)) {
                        $activePresetInstance = $activePresets[$preset];
                        $data = $activePresetInstance->getPresetData($acfKey);
                        $hasValidData = $activePresetInstance->hasValidData($data);

                        if ($hasValidData) {

                            //set query var for usage in templates
                            set_query_var('has-header-module', true);

                            //add useful body classes
                            add_filter('body_class', function (array $classes) use ($preset, $data): array {
                                $classes[] = 'has-header-module';
                                $classes[] = 'header-module--preset-' . $preset;

                                /**
                                 * Make hero body classes filterable                                    *
                                 * @param array $classes
                                 * @param string $preset
                                 * @param \stdClass|false $data
                                 */
                                $classes = apply_filters('ts_hero_body_classes', $classes, $preset, $data);

                                return $classes;
                            }, 1, 100);


                            //add preset partials
                            add_action('ts_after_breadcrumbs', function () use ($data, $activePresetInstance): void {
                                echo $activePresetInstance->getTemplate($data);
                            });

                            //enqueue preset assets
                            add_action('wp_enqueue_scripts', function () use ($data, $activePresetInstance): void {
                                $activePresetInstance->initFrontendAssets($data);
                            });
                        }
                    }
                }
            });
        }
    }
}