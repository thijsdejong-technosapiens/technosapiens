<?php

namespace TechnoSapiens\SearchPlugin;

use TechnoSapiens\Core\Enqueue;
use TechnoSapiens\Core\GlobalScripts;
use TechnoSapiens\Core\Singleton;
use TechnoSapiens\SearchPlugin;

/**
 * Class FrontendScripts
 * @package TechnoSapiens\SearchPlugin
 */
class FrontendScripts extends Singleton {

    /**
     * FrontendScripts constructor.
     */
    protected function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'initFrontendScripts']);
    }

    /**
     * Init front-end scripts
     */
    public static function initFrontendScripts() {
        if (SearchSettings::getInstance()->isSearchEnabled()) {

            //enqueue search overlay CSS
            if (!is_search()) {
                $overlayCss = Enqueue::getWebpackAssetUrlByKey(MANIFEST_PATH, 'search-trigger-overlay.css');
                if ($overlayCss) wp_enqueue_style(SearchPlugin::TEXT_DOMAIN . '_overlay_styles', $overlayCss, [], null);
            }

            //enqueue search result CSS
            if (is_search()) {

                //add snippet css
                $resultsCss = Enqueue::getWebpackAssetUrlByKey(MANIFEST_PATH, 'search-archive.css');
                if ($resultsCss) wp_enqueue_style(SearchPlugin::TEXT_DOMAIN . '_results_styles', $resultsCss, [], null);

                //add snippet JS
                GlobalScripts::getInstance()->enqueueCoreLinkSnippetAssets();

                //add filter JS
                $filterJs = Enqueue::getWebpackAssetUrlByKey(MANIFEST_PATH, 'search-filter-script.js');
                if ($filterJs) wp_enqueue_script(SearchPlugin::TEXT_DOMAIN . '_filter_select', $filterJs, [], null, true);
            }
        }
    }
}