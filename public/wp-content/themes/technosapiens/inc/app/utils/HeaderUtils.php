<?php

namespace TechnoSapiens;

/**
 * Class HeaderComponent
 * @package TechnoSapiens
 */
class HeaderUtils {

    /**
     * Get CSS classes for list item
     * @param \stdClass $item
     * @param string $baseCssClass
     * @return array
     */
    public static function getMainNavigationListItemClassesByItem(\stdClass $item, string $baseCssClass): array {

        //start setting up item CSS classes
        $listItemClasses = [$baseCssClass];

        //handle scenario where item has children
        if (count($item->children) > 0) {
            $listItemClasses[] = $baseCssClass . '--has-sub';
        }

        //handle scenario where item is current or current parent
        if ($item->isCurrent) $listItemClasses[] = $baseCssClass . '--current';
        if ($item->isCurrentParent) $listItemClasses[] = $baseCssClass . '--current-parent';

        return $listItemClasses;
    }

    /**
     * Get CSS classes for list item
     * @param \stdClass $item
     * @param string $baseCssClass
     * @return array
     */
    public static function getSideNavigationListItemClassesByItem(\stdClass $item, string $baseCssClass): array {

        //start setting up item CSS classes
        $listItemClasses = [$baseCssClass];

        //check if hash link
        $isHashLink = $item->link === '#';

        //handle scenario where item is current or current parent
        if (!$isHashLink && $item->isCurrent) $listItemClasses[] = $baseCssClass . '--current';
        if (!$isHashLink && $item->isCurrentParent) $listItemClasses[] = $baseCssClass . '--current-parent';

        return $listItemClasses;
    }
}