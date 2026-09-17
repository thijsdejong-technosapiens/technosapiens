<?php

namespace TechnoSapiens\MegaMenuPlugin;

use TechnoSapiens\Core\Log;
use TechnoSapiens\Core\Navigation;
use TechnoSapiens\Core\Singleton;
use TechnoSapiens\MegaMenuPlugin;
use TechnoSapiens\Theme;

/**
 * class AdminColumnComponent
 * @package TechnoSapiens\MegaMenuPlugin
 */
class AdminColumnComponent extends Singleton {

    /**
     * Define admin column names
     * @type string
     */
    const ADMIN_COLUMN_MM_PRESET = 'mm_preset';
    const ADMIN_COLUMN_MM_ACTIVE = 'mm_is_active';
    const ADMIN_COLUMN_MM_VALID = 'mm_is_valid';
    const ADMIN_COLUMN_MM_RELEVANT_DATA = 'mm_relevant_data';

    /**
     * AdminColumnComponent constructor
     */
    protected function __construct() {
        add_filter('manage_' . MegaMenuPostType::TYPE . '_posts_columns', [$this, 'addAdminColumns']);
        add_action('manage_' . MegaMenuPostType::TYPE . '_posts_custom_column', [$this, 'addAdminColumnContent'], 10, 2);
    }

    /**
     * Add admin column to show visibility in the archive for posts
     * @param array $columns
     * @return array
     */
    public function addAdminColumns(array $columns): array {
        $columns[self::ADMIN_COLUMN_MM_PRESET] = __('Active preset', MegaMenuPlugin::TEXT_DOMAIN);
        $columns[self::ADMIN_COLUMN_MM_ACTIVE] = __('Parent menu item(s)', MegaMenuPlugin::TEXT_DOMAIN);
        $columns[self::ADMIN_COLUMN_MM_VALID] = __('Has valid data', MegaMenuPlugin::TEXT_DOMAIN);
        $columns[self::ADMIN_COLUMN_MM_RELEVANT_DATA] = __('Relevant data', MegaMenuPlugin::TEXT_DOMAIN);
        return $columns;
    }

    /**
     * Add admin column content to show visibility in archive for posts
     * @param string $column
     * @param int $postId
     * @return void
     */
    public function addAdminColumnContent(string $column, int $postId): void {

        //bail if the column is not relevant
        if ($column === self::ADMIN_COLUMN_MM_PRESET || $column === self::ADMIN_COLUMN_MM_ACTIVE || $column === self::ADMIN_COLUMN_MM_VALID || $column === self::ADMIN_COLUMN_MM_RELEVANT_DATA) {

            //get relevant data
            $mmPreset = get_field('mm_preset', $postId);
            $activePresets = PresetComponent::getInstance()->getActivePresets();
            $presetInstance = $activePresets[$mmPreset] ?? false;
            if ($mmPreset && $presetInstance && method_exists($presetInstance, 'render') && method_exists($presetInstance, 'getPresetData') && method_exists($presetInstance, 'hasValidData')) {

                //get preset data
                $presetData = $presetInstance->getPresetData($postId);

                //handle showing column
                if ($column === self::ADMIN_COLUMN_MM_PRESET) {
                    echo $presetInstance->getPresetName();
                }

                //check if preset is connected to a parent menu item in the main navigation and if so; which ones
                if ($column === self::ADMIN_COLUMN_MM_ACTIVE && defined('\TechnoSapiens\Theme::MENU_PRIMARY')) {
                    $matches = [];
                    $mainNavigationItems = Navigation::getLinksByThemeLocation(Theme::MENU_PRIMARY);

                    //resolve the menu term ID for this theme location to build proper admin URLs
                    $menuTermId = 0;
                    $locations = function_exists('get_nav_menu_locations') ? get_nav_menu_locations() : [];
                    if (is_array($locations) && isset($locations[Theme::MENU_PRIMARY])) {
                        $menuTermId = (int)$locations[Theme::MENU_PRIMARY];
                    }

                    //base admin URL for menu editor
                    $baseAdminUrl = admin_url(add_query_arg(['action' => 'edit', 'menu' => $menuTermId ?: null], 'nav-menus.php'));

                    foreach ($mainNavigationItems as $mainNavigationItem) {
                        $itemMmId = NavMenuItemResolver::getMegaMenuIdForItem($mainNavigationItem);
                        if ($itemMmId === $postId) {
                            $label = esc_html($mainNavigationItem->label);
                            if (current_user_can('edit_theme_options')) {
                                $itemUrl = $baseAdminUrl . '#menu-item-' . (int)$mainNavigationItem->id;
                                $matches[] = sprintf('<a href="%s">%s</a>', esc_url($itemUrl), $label);
                            } else {
                                $matches[] = $label;
                            }
                        }
                    }

                    echo count($matches) > 0 ? implode(', ', $matches) : '❌';
                }

                //handle data validation
                if ($column === self::ADMIN_COLUMN_MM_VALID) {
                    echo $presetInstance->hasValidData($presetData) ? '✅' : '❌';
                }

                //handle showing relevant admin columnn data
                if ($column === self::ADMIN_COLUMN_MM_RELEVANT_DATA && method_exists($presetInstance, 'renderAdminColumnRelevantData')) {
                    $presetInstance->renderAdminColumnRelevantData($presetData);
                }

            } else {
                Log::log(sprintf('Mega menu preset "%1s" was found but does not have the required methods (getPresetData, hasValidData, or render) to function.', $mmPreset));
            }
        }
    }
}
