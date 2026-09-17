<?php

namespace TechnoSapiens\MegaMenuPlugin;

use TechnoSapiens\Core\Singleton;
use TechnoSapiens\MegaMenuPlugin;

/**
 * class PresetComponent
 * @package TechnoSapiens\MegaMenuPlugin
 */
class PresetComponent extends Singleton {

    /**
     * Define default value for active presets variable
     * @var array
     */
    public array $activePresets = [];

    /**
     * Init presets
     */
    public function initPresets(): void {
        foreach (MegaMenuPlugin::getEnabledPresetClasses() as $presetClass) {
            $fullPresetClass = "TechnoSapiens\\MegaMenuPlugin\\" . $presetClass;
            if (class_exists($fullPresetClass)) {
                $presetInstance = $fullPresetClass::getInstance();
                if (property_exists($presetInstance, 'presetKey')) $this->activePresets[$presetInstance->getPresetKey()] = $presetInstance;
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
