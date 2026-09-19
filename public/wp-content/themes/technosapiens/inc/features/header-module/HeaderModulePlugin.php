<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Singleton;
use TechnoSapiens\HeaderModulePlugin\AcfLocationComponent;
use TechnoSapiens\HeaderModulePlugin\TemplateComponent;

/**
 * Class HeaderModulePlugin
 * @package TechnoSapiens
 */
class HeaderModulePlugin extends Singleton {

    /**
     * Define module version
     */
    const PLUGIN_VERSION = 0.1;

    /**
     * Define text domain for translations
     */
    const TEXT_DOMAIN = 'wordpress-header-module-plugin';

    /**
     * Preset classes enabled for the header module.
     * @var array<int, string>
     */
    const ENABLED_PRESET_CLASSES = [
        'MainHeroPreset',
    ];

    /**
     * Define default value for active presets variable
     * @var array
     */
    public array $activePresets = [];

    /**
     * HeaderModulePlugin constructor
     */
    protected function __construct() {
        if (!defined('WHMP_PLUGIN_PATH')) {
            define('WHMP_PLUGIN_PATH', get_stylesheet_directory() . '/inc/features/header-module');
        }

        if (!defined('WHMP_PLUGIN_URL')) {
            define('WHMP_PLUGIN_URL', get_stylesheet_directory_uri() . '/inc/features/header-module');
        }

        $this->initTranslations();
        $this->initComponents();

        add_action('after_setup_theme', function () {
            $this->initPresets();
        }, 100);
    }

    /**
     * Init module translations
     * @return void
     */
    public function initTranslations(): void {
        load_theme_textdomain(self::TEXT_DOMAIN, WHMP_PLUGIN_PATH . '/languages');
    }

    /**
     * Init components
     * @return void
     */
    public function initComponents(): void {
        AcfLocationComponent::getInstance();
        TemplateComponent::getInstance();
    }

    /**
     * Get enabled preset class names
     * @return array<int, string>
     */
    public static function getEnabledPresetClasses(): array {
        return self::ENABLED_PRESET_CLASSES;
    }

    /**
     * Init presets
     * @return void
     */
    public function initPresets(): void {
        foreach (self::getEnabledPresetClasses() as $presetClass) {
            $fullPresetClass = 'TechnoSapiens\\HeaderModulePlugin\\' . $presetClass;
            if (class_exists($fullPresetClass)) {
                $presetInstance = $fullPresetClass::getInstance();
                if (property_exists($presetInstance, 'presetKey')) {
                    $this->activePresets[$presetInstance->getPresetKey()] = $presetInstance;
                }
            }
        }
    }

    /**
     * Get active presets
     * @return array
     */
    public function getActivePresets(): array {
        return $this->activePresets ?: [];
    }
}
