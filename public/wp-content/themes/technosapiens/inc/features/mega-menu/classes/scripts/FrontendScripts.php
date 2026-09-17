<?php

namespace TechnoSapiens\MegaMenuPlugin;

use TechnoSapiens\Core\Enqueue;
use TechnoSapiens\Core\Singleton;
use TechnoSapiens\MegaMenuPlugin;

/**
 * Class FrontendScripts
 * @package TechnoSapiens\MegaMenuPlugin
 */
class FrontendScripts extends Singleton {

    /**
     * FrontendScripts constructor.
     */
    protected function __construct() {
        if (!is_admin()) add_action('wp_enqueue_scripts', [$this, 'initFrontendScripts']);
    }

    /**
     * Init front-end scripts
     */
    public static function initFrontendScripts() {

        //only enqueue global mega menu assets if at least one (valid) mega menu is connected in the primary navigation
        $hasActiveMegaMenu = MegaMenuUsageResolver::getInstance()->hasAnyMegaMenuInPrimaryNavigation();
        if (!$hasActiveMegaMenu) return;

        //get manifest JSON URL
        $manifestJson = MANIFEST_PATH;

        //enqueue CSS
        $frontendCss = Enqueue::getWebpackAssetUrlByKey($manifestJson, 'mega-menu-frontend.css');
        if ($frontendCss) wp_enqueue_style(MegaMenuPlugin::TEXT_DOMAIN . '_styles', $frontendCss, [], null);

        //enqueue JS
        $frontendJs = Enqueue::getWebpackAssetUrlByKey($manifestJson, 'mega-menu-frontend-script.js');
        if ($frontendJs) wp_enqueue_script(MegaMenuPlugin::TEXT_DOMAIN . '_scripts', $frontendJs, [], null, true);
    }
}
