<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Singleton;

/**
 * Theme-local bootstrap for the post-type factory (formerly mu-plugin).
 */
class FactoryPlugin extends Singleton {

    /**
     * Define plugin version
     */
    const PLUGIN_VERSION = 0.1;

    /**
     * Define text domain for translations
     */
    const TEXT_DOMAIN = 'wordpress-factory-plugin';

    /**
     * FactoryPlugin constructor
     */
    protected function __construct() {

        $factoryPath = get_stylesheet_directory() . '/factory';
        $factoryUrl = get_stylesheet_directory_uri() . '/factory';

        define('WFACP_PLUGIN_PATH', $factoryPath);
        define('WFACP_PLUGIN_URL', $factoryUrl);
        define('WFACP_PARTIAL_PATH', $factoryPath . '/partials/');
        define('WFACP_ICON_PATH', $factoryUrl . '/dist/' . self::TEXT_DOMAIN . '-icons.svg#');
        define('WFACP_MANIFEST_URL', $factoryPath . '/dist/manifest.json');

        $this->initTranslations();
    }

    /**
     * Init factory translations
     * @return void
     */
    public function initTranslations(): void {
        load_theme_textdomain(
            self::TEXT_DOMAIN,
            get_stylesheet_directory() . '/languages/factory'
        );
    }
}
