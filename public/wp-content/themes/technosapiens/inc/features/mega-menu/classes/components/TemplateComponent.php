<?php

namespace TechnoSapiens\MegaMenuPlugin;

use TechnoSapiens\Core\Log;
use TechnoSapiens\Core\Partial;
use TechnoSapiens\Core\Singleton;

/**
 * class TemplateComponent
 * @package TechnoSapiens\MegaMenuPlugin
 */
class TemplateComponent extends Singleton {

    /**
     * TemplateComponent constructor
     */
    protected function __construct() {
        if (!is_admin()) add_action('ts_render_mega_menu', [$this, 'renderMegaMenu']);
    }

    /**
     * Render Mega Menu wrapper and preset content for a given parent item
     * @param array $args
     * @return void
     */
    public function renderMegaMenu(array $args = []): void {

        //get parent item + bail if no parent item given
        $parentItem = $args['parentItem'] ?? false;
        if (!$parentItem) return;

        //get mega menu ID for item + bail if no mega menu ID found
        $mmId = NavMenuItemResolver::getMegaMenuIdForItem($parentItem) ?? '';
        if (!$mmId) return;

        //only show if publish, or private and user is logged in
        if (!MegaMenuUsageResolver::getInstance()->isMegaMenuPostVisible((int)$mmId)) return;

        //get acf value of the field "mega-menu-preset" based on $mm + bail if no preset found
        $mmPreset = get_field('mm_preset', $mmId) ?? '';

        //get a preset instance based on $mmPreset + bail if no preset instance found
        $activePresets = PresetComponent::getInstance()->getActivePresets();
        $presetInstance = $activePresets[$mmPreset] ?? false;
        if (!$presetInstance || !method_exists($presetInstance, 'render') || !method_exists($presetInstance, 'getPresetData') || !method_exists($presetInstance, 'hasValidData')) {
            Log::log(sprintf('Mega menu preset "%1s" was found but does not have the required methods (getPresetData, hasValidData, or render) to function.', $mmPreset));
            return;
        }

        //render a mega-menu based on preset if it has valid data
        $presetData = $presetInstance->getPresetData($mmId);
        if ($presetInstance->hasValidData($presetData)) {
            Partial::render('mega-menu-opening', ['preset' => $mmPreset, 'data' => $presetData, 'parentItem' => $parentItem], true, WMMP_PLUGIN_PARTIAL_PATH);
            $presetInstance->render($presetData, $parentItem);
            Partial::render('mega-menu-closing', ['preset' => $mmPreset, 'data' => $presetData, 'parentItem' => $parentItem], true, WMMP_PLUGIN_PARTIAL_PATH);
        }
    }
}
