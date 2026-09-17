<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Enqueue;
use TechnoSapiens\Core\Singleton;

/**
 * Class AdminScripts
 * @package TechnoSapiens
 */
class AdminScripts extends Singleton {

    /**
     * AdminScripts constructor.
     */
    protected function __construct() {
        if (is_admin()) {
            add_action('admin_enqueue_scripts', [$this, 'initAdminScripts']);
            add_action('enqueue_block_editor_assets', [$this, 'initAdminScripts']);
            add_action('enqueue_block_assets', [$this, 'initAdminScripts']);
        }
    }

    /**
     * Dequeue styles
     * @return void
     */
    public static function dequeueDefaultGfStyles(): void {
        $stylesToRemove = ['gform_theme','gform_theme_components', 'gravity_forms_theme_framework'];
        foreach ($stylesToRemove as $styleToRemove) {
            wp_deregister_style($styleToRemove);
            wp_dequeue_style($styleToRemove);
        }
    }

    /**
     * Init admin scripts
     */
    public static function initAdminScripts() {

        //dequeue default GF styles
        self::dequeueDefaultGfStyles();

        //enqueue CSS
        $adminCss = Enqueue::getWebpackAssetUrlByKey(MANIFEST_PATH, 'admin.css');
        if ($adminCss) wp_enqueue_style(Theme::TEXT_DOMAIN . '_styles', $adminCss);

        //enqueue Gutenberg CSS
        $gutenbergCss = Enqueue::getWebpackAssetUrlByKey(MANIFEST_PATH, 'gutenberg.css');
        if ($gutenbergCss) wp_enqueue_style(Theme::TEXT_DOMAIN . '_gutenberg_styles', $gutenbergCss);

        //enqueue JS
        $adminJs = Enqueue::getWebpackAssetUrlByKey(MANIFEST_PATH, 'admin.js');
        if ($adminJs) wp_enqueue_script(Theme::TEXT_DOMAIN . '_scripts', $adminJs, ['lodash', 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-block-editor', 'wp-data', 'wp-compose'], false, true);
    }
}