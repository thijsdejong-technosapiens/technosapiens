<?php

namespace TechnoSapiens\MegaMenuPlugin;

use TechnoSapiens\Core\Singleton;

/**
 * class NavMenuItemResolver
 * @package TechnoSapiens\MegaMenuPlugin
 * Small helper for resolving Mega Menu metadata for nav items
 */
class NavMenuItemResolver extends Singleton {

    /**
     * Get mega menu ID by nav item object
     * @param \stdClass $item
     * @return int|false
     */
    public static function getMegaMenuIdForItem(\stdClass $item): int|false {
        return get_field('mm_post_link', $item->id) ?: false;
    }

    /**
     * Check if the item is a mega menu item
     * @param \stdClass $item
     * @return bool
     */
    public static function isMegaMenuItem(\stdClass $item): bool {
        $mmId = self::getMegaMenuIdForItem($item);
        return (bool)$mmId;
    }
}

