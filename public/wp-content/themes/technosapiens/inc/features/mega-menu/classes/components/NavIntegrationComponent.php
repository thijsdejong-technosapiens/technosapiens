<?php

namespace TechnoSapiens\MegaMenuPlugin;

use TechnoSapiens\Core\Singleton;

/**
 * class NavIntegrationComponent
 * @package TechnoSapiens\MegaMenuPlugin
 */
class NavIntegrationComponent extends Singleton {

    /**
     * NavIntegrationComponent constructor
     */
    protected function __construct() {
        add_filter('ts_main_navigation_item_classes', [$this, 'filterMainNavigationItemClasses'], 10, 3);
    }

    /**
     * Filter main navigation item classes to add mega menu classes if active
     * @param array $itemClasses
     * @param \stdClass $item
     * @return array
     */
    public function filterMainNavigationItemClasses(array $itemClasses, \stdClass $item): array {

        //bail if not a mega menu item
        if (!NavMenuItemResolver::isMegaMenuItem($item)) return $itemClasses;

        //get mega menu ID for item
        $mmId = NavMenuItemResolver::getMegaMenuIdForItem($item);

        //bail if mega menu ID is not set or the linked post is not visible
        if (!$mmId || !MegaMenuUsageResolver::getInstance()->isMegaMenuPostVisible((int)$mmId)) return $itemClasses;

        //add CSS classes to parent menu item
        $itemClasses[] = 'main-navigation__item--has-mega-menu';
        $itemClasses[] = 'main-navigation__item--has-sub';

        return $itemClasses;
    }
}
