<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\AcfComponent;
use TechnoSapiens\Core\AdminBarLightComponent;
use TechnoSapiens\Core\Enqueue;
use TechnoSapiens\Core\GlobalScripts;
use TechnoSapiens\Core\PreviewNotificationComponent;
use TechnoSapiens\Core\Singleton;
use TechnoSapiens\Core\WcagComponent;

/**
 * Class Core
 * @package TechnoSapiens
 */
class Core extends Singleton {

    /**
     * Define plugin version
     */
    const PLUGIN_VERSION = 0.6;

    /**
     * Define text domain for translations
     */
    const TEXT_DOMAIN = 'wordpress-core-plugin';

    /**
     * Core constructor
     */
    protected function __construct() {
        $themeDistPath = get_stylesheet_directory() . '/dist/manifest.json';

        if (!defined('WCP_PLUGIN_PATH')) {
            define('WCP_PLUGIN_PATH', get_stylesheet_directory() . '/inc/core');
        }

        if (!defined('WCP_PLUGIN_URL')) {
            define('WCP_PLUGIN_URL', get_stylesheet_directory_uri() . '/inc/core');
        }

        if (!defined('WCP_PARTIAL_PATH')) {
            define('WCP_PARTIAL_PATH', get_stylesheet_directory() . '/inc/core/partials/');
        }

        if (!defined('WCP_MANIFEST_PATH')) {
            define('WCP_MANIFEST_PATH', $themeDistPath);
        }

        if (!defined('WCP_ICON_PATH')) {
            $iconSpritePath = Enqueue::getWebpackAssetUrlByKey($themeDistPath, 'technosapiens-icons-svg');
            if (!$iconSpritePath) {
                $iconSpritePath = Enqueue::getWebpackAssetUrlByKey($themeDistPath, 'technosapiens-icons.svg');
            }
            define('WCP_ICON_PATH', $iconSpritePath ? ($iconSpritePath . '#') : '#');
        }

        self::initScripts();
        self::initComponents();
    }

    /**
     * Init scripts
     * @return void
     */
    public static function initScripts(): void {
        GlobalScripts::getInstance();
    }

    /**
     * Init components
     * @return void
     */
    public static function initComponents(): void {
        PreviewNotificationComponent::getInstance();
        AcfComponent::getInstance();
        WcagComponent::getInstance();

        add_action('after_setup_theme', function () {
            $adminBarLightEnabled = apply_filters('ts_admin_bar_light_feature_enabled', true);
            if ($adminBarLightEnabled) {
                AdminBarLightComponent::getInstance();
            }
        });
    }
}
