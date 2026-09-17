<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Enqueue;
use TechnoSapiens\Core\Singleton;
use TechnoSapiens\Core\Partial;

/**
 * Class PostPasswordComponent
 * @package TechnoSapiens
 */
class PostPasswordComponent extends Singleton {
    /**
     * PostPasswordComponent constructor.
     */
    protected function __construct() {
        add_filter('the_password_form', [$this, 'addCustomStyling'], 2, 100);
        add_action('wp_enqueue_scripts', [$this, 'initFrontendScripts']);
    }

    /**
     * @param string $output
     * @param \WP_Post $post
     * @return string
     */
    public static function addCustomStyling(string $output, \WP_Post $post): string {
        remove_filter('the_content', 'wpautop');
        return Partial::render('components/component-post-password', ['post' => $post], false);
    }

    /**
     * Init front-end scripts
     * @return void
     */
    public static function initFrontendScripts(): void {
        if (post_password_required()) {
            $passwordPageCss = Enqueue::getWebpackAssetUrlByKey(MANIFEST_PATH, 'page-post-password.css');
            if ($passwordPageCss) wp_enqueue_style('page-post-password', $passwordPageCss, [], null);
        }
    }
}