<?php

namespace TechnoSapiens\MegaMenuPlugin;

use TechnoSapiens\Core\Navigation;
use TechnoSapiens\Core\Singleton;
use TechnoSapiens\Theme;

/**
 * Class MegaMenuUsageResolver
 * @package TechnoSapiens\MegaMenuPlugin
 * Helper for determining if/which mega menus are connected and valid
 */
class MegaMenuUsageResolver extends Singleton {

    /**
     * Cache for usage checks
     * @var array<string, mixed>
     */
    private array $cache = [];

    /**
     * Get all primary navigation items
     * @return array
     */
    public function getPrimaryNavigationItems(): array {
        if (!class_exists('TechnoSapiens\\Core\\Navigation') || !defined('\\TechnoSapiens\\Theme::MENU_PRIMARY')) return [];
        return Navigation::getLinksByThemeLocation(Theme::MENU_PRIMARY);
    }

    /**
     * Get connected mega menu items in primary navigation
     * @param bool $requireValidData When true, only return items whose preset has valid data
     * @return array<int, array{item: \stdClass, mmId: int, presetKey: string}>
     */
    public function getConnectedMegaMenuItems(bool $requireValidData = true): array {

        //return form cache if available
        $cacheKey = 'connected:' . ($requireValidData ? 'valid' : 'all');
        if (isset($this->cache[$cacheKey])) return $this->cache[$cacheKey];

        //get primary navigation items
        $results = [];
        $items = $this->getPrimaryNavigationItems();
        if (count($items) === 0) return $this->cache[$cacheKey] = $results;

        //get active presets
        $activePresets = PresetComponent::getInstance()->getActivePresets();

        foreach ($items as $item) {

            //bail if not an object
            if (!is_object($item)) continue;

            //get mega menu ID for item + bail if no mega menu connected
            $mmId = NavMenuItemResolver::getMegaMenuIdForItem($item);
            if (!$mmId) continue;

            //respect visibility: only published or private when a user is logged in
            if (!$this->isMegaMenuPostVisible($mmId)) continue;

            //get preset key for a mega menu and bail if none set
            $presetKey = get_field('mm_preset', $mmId) ?: '';
            if (!$presetKey) continue;

            //validate preset data if required
            if ($requireValidData) {

                //get preset instance
                $presetInstance = $activePresets[$presetKey] ?? false;
                if (!$presetInstance || !method_exists($presetInstance, 'getPresetData') || !method_exists($presetInstance, 'hasValidData')) continue;

                //validate preset data
                $data = $presetInstance->getPresetData($mmId);
                if (!$presetInstance->hasValidData($data)) continue;
            }

            //add to results
            $results[] = ['item' => $item, 'mmId' => $mmId, 'presetKey' => (string)$presetKey];
        }

        return $this->cache[$cacheKey] = $results;
    }

    /**
     * Check if there is any (valid) mega menu in primary navigation
     * @param bool $requireValidData
     * @return bool
     */
    public function hasAnyMegaMenuInPrimaryNavigation(bool $requireValidData = true): bool {
        $connected = $this->getConnectedMegaMenuItems($requireValidData);
        return count($connected) > 0;
    }

    /**
     * Check if a specific preset is used in primary navigation
     * @param string $presetKey
     * @param bool $requireValidData
     * @return bool
     */
    public function isPresetUsedInPrimaryNavigation(string $presetKey, bool $requireValidData = true): bool {
        if (!$presetKey) return false;

        //return form cache if available
        $cacheKey = 'preset:' . $presetKey . ':' . ($requireValidData ? 'valid' : 'all');
        if (isset($this->cache[$cacheKey])) return (bool)$this->cache[$cacheKey];

        //check if preset is used in primary navigation
        foreach ($this->getConnectedMegaMenuItems($requireValidData) as $connected) {
            if ($connected['presetKey'] === $presetKey) return $this->cache[$cacheKey] = true;
        }

        return $this->cache[$cacheKey] = false;
    }

    /**
     * Check if a mega menu post is visible on the frontend
     * - publish: visible to everyone
     * - private: visible to logged-in users
     * @param int $mmId
     * @return bool
     */
    public function isMegaMenuPostVisible(int $mmId): bool {
        if ($mmId <= 0) return false;
        $status = get_post_status($mmId);
        if ($status === 'publish') return true;
        if ($status === 'private') return is_user_logged_in();
        return false;
    }
}
