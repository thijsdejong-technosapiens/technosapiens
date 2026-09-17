<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Singleton;
use TechnoSapiens\MegaMenuPlugin\AcfFieldGroupComponent;
use TechnoSapiens\MegaMenuPlugin\AdminBarLightIntegrationComponent;
use TechnoSapiens\MegaMenuPlugin\AdminColumnComponent;
use TechnoSapiens\MegaMenuPlugin\FrontendScripts;
use TechnoSapiens\MegaMenuPlugin\MegaMenuPostType;
use TechnoSapiens\MegaMenuPlugin\MegaMenuUsageResolver;
use TechnoSapiens\MegaMenuPlugin\NavIntegrationComponent;
use TechnoSapiens\MegaMenuPlugin\PresetComponent;
use TechnoSapiens\MegaMenuPlugin\TemplateComponent;

/**
 * Class MegaMenuPlugin
 * @package TechnoSapiens
 */
class MegaMenuPlugin extends Singleton {

    /**
     * Define module version
     */
    const PLUGIN_VERSION = 0.1;

    /**
     * Define text domain for translations
     */
    const TEXT_DOMAIN = 'wordpress-mega-menu-plugin';

    /**
     * Preset classes enabled for the mega menu.
     * @var array<int, string>
     */
    const ENABLED_PRESET_CLASSES = [
        'TwoColumnsWithCardPreset',
    ];

    /**
     * Whether mega menu content spans the full viewport width.
     */
    const FULL_WIDTH_CONTENT = false;

    /**
     * MegaMenuPlugin constructor
     */
    protected function __construct() {
        if (!defined('WMMP_PLUGIN_PATH')) {
            define('WMMP_PLUGIN_PATH', get_stylesheet_directory() . '/inc/features/mega-menu');
        }

        if (!defined('WMMP_PLUGIN_PARTIAL_PATH')) {
            define('WMMP_PLUGIN_PARTIAL_PATH', WMMP_PLUGIN_PATH . '/partials/');
        }

        if (!defined('WMMP_PLUGIN_URL')) {
            define('WMMP_PLUGIN_URL', get_stylesheet_directory_uri() . '/inc/features/mega-menu');
        }

        $this->initTranslations();
        $this->initComponents();
        $this->initScripts();

        add_action('after_setup_theme', function () {
            $this->initPostTypes();
            PresetComponent::getInstance()->initPresets();
            AcfFieldGroupComponent::getInstance()->addFieldGroup();
        }, 100);
    }

    /**
     * Init components
     * @return void
     */
    public function initComponents(): void {
        NavIntegrationComponent::getInstance();
        TemplateComponent::getInstance();
        AdminColumnComponent::getInstance();
        MegaMenuUsageResolver::getInstance();
        AdminBarLightIntegrationComponent::getInstance();
    }

    /**
     * Get enabled preset class names
     * @return array<int, string>
     */
    public static function getEnabledPresetClasses(): array {
        return self::ENABLED_PRESET_CLASSES;
    }

    /**
     * Check if full-width mega menu content is enabled
     * @return bool
     */
    public static function isFullWidthContent(): bool {
        return self::FULL_WIDTH_CONTENT;
    }

    /**
     * Init module translations
     * @return void
     */
    public function initTranslations(): void {
        add_action('init', function () {
            load_theme_textdomain(self::TEXT_DOMAIN, WMMP_PLUGIN_PATH . '/languages');
        });
    }

    /**
     * Init post types
     * @return void
     */
    public function initPostTypes(): void {
        MegaMenuPostType::getInstance();
    }

    /**
     * Init scripts
     * @return void
     */
    public function initScripts(): void {
        FrontendScripts::getInstance();
    }
}
