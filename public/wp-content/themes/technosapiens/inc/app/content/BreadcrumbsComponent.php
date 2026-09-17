<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Singleton;

/**
 * Class BreadcrumbsComponent
 * @package TechnoSapiens
 */
class BreadcrumbsComponent extends Singleton {
    /**
     * BreadcrumbsComponent component
     */
    protected function __construct() {
        add_filter('wpseo_breadcrumb_separator', [$this, 'filterBreadcrumbSeparator'], 2, 100);
        add_filter('wpseo_breadcrumb_single_link', [$this, 'filterBreadcrumbSingleLink'], 10, 1);
    }

    /**
     * Return breadcrumbs with arrow icon
     * @return string
     */
    public static function filterBreadcrumbSeparator(): string {
        return '<svg class="icon" aria-hidden="true"><use xlink:href="' . ICON_PATH . 'icon-arrow-right"/></svg>';
    }

    /**
     * Filter out spans and replace with list tags
     * @param string $linkOutput
     * @return string
     */
    public static function filterBreadcrumbSingleLink(string $linkOutput): string {
        return preg_replace("/<span\s(.+?)>(.+?)<\/span>/is", "<span $1>$2</span>", $linkOutput);
    }

    /**
     * Check if breadcrumbs are enabled
     * @param bool $isHero
     * @return bool
     */
    public static function breadcrumbsEnabled(bool $isHero = false): bool {
        $enabledChecks = [
            !is_front_page() &&
            !is_search() &&
            class_exists('WPSEO_Options') &&
            \WPSEO_Options::get('breadcrumbs-enable', false) === true
        ];

        if (!$isHero) {
            $enabledChecks[] = get_query_var('has-header-module', false) === false;
        }

        return !in_array(false, $enabledChecks, true);
    }
}

