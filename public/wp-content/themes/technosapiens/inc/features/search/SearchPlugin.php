<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Formatting;
use TechnoSapiens\Core\Singleton;
use TechnoSapiens\SearchPlugin\FrontendScripts;
use TechnoSapiens\SearchPlugin\PostStatusComponent;
use TechnoSapiens\SearchPlugin\SearchComponent;
use TechnoSapiens\SearchPlugin\SearchExcludeComponent;
use TechnoSapiens\SearchPlugin\SearchSettings;

/**
 * Class SearchPlugin
 * @package TechnoSapiens
 */
class SearchPlugin extends Singleton {

    /**
     * Define module version
     */
    const PLUGIN_VERSION = '1.0.2';

    /**
     * Define text domain for translations
     */
    const TEXT_DOMAIN = 'wordpress-search-plugin';

    /**
     * SearchPlugin constructor
     */
    protected function __construct() {
        if (!defined('WSP_PLUGIN_PATH')) {
            define('WSP_PLUGIN_PATH', get_stylesheet_directory() . '/inc/features/search');
        }

        if (!defined('WSP_PLUGIN_URL')) {
            define('WSP_PLUGIN_URL', get_stylesheet_directory_uri() . '/inc/features/search');
        }

        if (!defined('WSP_PARTIAL_PATH')) {
            define('WSP_PARTIAL_PATH', WSP_PLUGIN_PATH . '/partials/');
        }

        if (!defined('WSP_ICON_PATH')) {
            define('WSP_ICON_PATH', ICON_PATH);
        }

        $this->initTranslations();
        $this->initAcfGroups();
        $this->initScripts();
        $this->initSettings();
        $this->initComponents();
    }

    /**
     * Init module translations
     * @return void
     */
    public function initTranslations(): void {
        add_action('init', function () {
            load_theme_textdomain(self::TEXT_DOMAIN, WSP_PLUGIN_PATH . '/languages');
        });
    }

    /**
     * Init module ACF groups
     * @return void
     */
    public function initAcfGroups(): void {
        $acfJsonPath = WSP_PLUGIN_PATH . '/acf-json';

        add_filter('acf/settings/load_json', function (array $paths = []) use ($acfJsonPath) {
            return array_merge($paths, [$acfJsonPath]);
        });

        add_filter('acf/settings/save_json', function (string $path) use ($acfJsonPath) {
            if ($fieldGroupTitle = ($_POST['post_title'] ?? '')) {
                $addOnGroups = array_map(function ($addOnFile) {
                    return str_replace('.json', '', $addOnFile);
                }, array_diff(scandir($acfJsonPath), ['.', '..']));

                if (count($addOnGroups) > 0 && in_array(Formatting::slugify($fieldGroupTitle), $addOnGroups)) {
                    $path = $acfJsonPath;
                }
            }

            return $path;
        });
    }

    /**
     * Init scripts
     * @return void
     */
    public function initScripts(): void {
        FrontendScripts::getInstance();
    }

    /**
     * Init settings pages
     * @return void
     */
    public function initSettings(): void {
        SearchSettings::getInstance();
    }

    /**
     * Init components
     * @return void
     */
    public function initComponents(): void {
        SearchComponent::getInstance();
        SearchExcludeComponent::getInstance();
        PostStatusComponent::getInstance();
    }
}
