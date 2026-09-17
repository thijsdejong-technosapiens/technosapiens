<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Enqueue;
use TechnoSapiens\Core\Singleton;

/**
 * Class FrontendScripts
 * @package TechnoSapiens
 */
class FrontendScripts extends Singleton {

    /**
     * FrontendScripts constructor.
     */
    protected function __construct() {
        if (!is_admin()) {

            //register scripts and styles
            add_action('wp_enqueue_scripts', [$this, 'initFrontendScripts'], 20);

            //add useful variables as window variable (for JS)
            add_action('wp_head', [$this, 'addPathsToHead']);
        }
    }

    /**
     * Init front-end scripts
     */
    public static function initFrontendScripts() {
        //dequeue styles
        $stylesToRemove = ['wp-block-library', 'gravity_forms_theme_reset', 'gravity_forms_theme_foundation', 'gravity_forms_theme_framework', 'gravity_forms_orbital_theme'];
        if (!is_user_logged_in()) $stylesToRemove[] = 'dashicons';
        foreach ($stylesToRemove as $styleToRemove) {
            wp_deregister_style($styleToRemove);
            wp_dequeue_style($styleToRemove);
        }

        //dequeue scripts
        $scriptsToRemove = ['wp-embed', 'wp-polyfill', 'regenerator-runtime'];
        foreach ($scriptsToRemove as $scriptToRemove) {
            wp_deregister_script($scriptToRemove);
            wp_dequeue_script($scriptToRemove);
        }

        //enqueue CSS
        $frontendCss = Enqueue::getWebpackAssetUrlByKey(MANIFEST_PATH, 'frontend.css');
        if ($frontendCss) wp_enqueue_style(Theme::TEXT_DOMAIN . '_styles', $frontendCss, [], null);

        //enqueue JS
        $frontendJs = Enqueue::getWebpackAssetUrlByKey(MANIFEST_PATH, 'frontend.js');
        if ($frontendJs) {
            wp_enqueue_script(Theme::TEXT_DOMAIN . '_scripts', $frontendJs, [], null, true);
        }

        //add live reload script if not in production
        $liveReloadJs = Enqueue::getWebpackAssetUrlByKey(MANIFEST_PATH, 'livereload.js');
        if (getenv('DEV') === 'true' && $liveReloadJs) {
            wp_enqueue_script(Theme::TEXT_DOMAIN . '_livereload', $liveReloadJs, [], null, true);
        }

        //load 404 styling
        if (is_404()) {
            $page404Styling = Enqueue::getWebpackAssetUrlByKey(MANIFEST_PATH, 'page-404.css');
            if ($page404Styling) wp_enqueue_style('ts-page-404', $page404Styling, [], null);
        }

        //load post password page styling
        if (post_password_required()) {
            $postPasswordStyling = Enqueue::getWebpackAssetUrlByKey(MANIFEST_PATH, 'page-post-password.css');
            if ($postPasswordStyling) wp_enqueue_style('ts-post-password', $postPasswordStyling, [], null);
        }

        //load admin bar styling
        if (is_user_logged_in()) {
            $adminBarStyling = Enqueue::getWebpackAssetUrlByKey(MANIFEST_PATH, 'component-admin-bar.css');
            if ($adminBarStyling) wp_enqueue_style('ts-admin-bar', $adminBarStyling, [], null);
        }
    }

    /**
     * Add paths in admin head
     *
     * @hook admin_head
     */
    public function addPathsToHead() {
        ?>
        <script type="application/javascript">
            window.ts = {
                icons: '<?php echo ICON_PATH; ?>'
            };
        </script>
        <?php
    }
}

