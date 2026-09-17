<?php

if (is_blog_installed()) {

    //load third party MU plugins
    require WPMU_PLUGIN_DIR . '/advanced-custom-fields-pro/acf.php';
    require WPMU_PLUGIN_DIR . '/intuitive-custom-post-order/intuitive-custom-post-order.php';

    //load mu-plugins with other plugin dependencies
    add_action('plugins_loaded', function () {
        if (class_exists('GFForms')) {
            require WPMU_PLUGIN_DIR . '/acf-gravityforms-add-on/acf-gravityforms-add-on.php';
        }

        //add WP plugin that makes Polylang settings page translatable
        if (class_exists('Polylang')) {
            require WPMU_PLUGIN_DIR . '/acf-options-for-polylang/bea-acf-options-for-polylang.php';
        }
    });
}
