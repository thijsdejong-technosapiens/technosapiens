<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Enqueue;
use TechnoSapiens\Core\Singleton;

/**
 * Class AdminStyles
 * @package TechnoSapiens
 */
class AdminStyles extends Singleton {
    /**
     * AdminStyles constructor.
     */
    public function __construct() {
        if (is_admin()) {
            add_action('admin_init', [$this, 'setCustomAdminTheme']);
            add_action('admin_head', [$this, 'setAdminBranding']);
            add_filter('get_user_option_admin_color', [$this, 'getAdminThemeKey']);
        }
    }

    /**
     * Create admin color scheme
     */
    public function setCustomAdminTheme() {
        global $_wp_admin_css_colors;

        //enqueue CSS
        $themeStyles = Enqueue::getWebpackAssetUrlByKey(MANIFEST_PATH, 'theme.css');
        if ($themeStyles) {

            //set our admin theme
            wp_admin_css_color(self::getAdminThemeKey(), get_bloginfo('name'), $themeStyles, []);

            //remove other admin themes
            $_wp_admin_css_colors = array_filter($_wp_admin_css_colors, function ($adminColor) {
                return $adminColor->name === get_bloginfo('name');
            });
        }
    }

    /**
     * Set favicon instead of WP icon in admin for branding purposes
     */
    public static function setAdminBranding() {
        $favicon = get_site_icon_url(16);
        if ($favicon) {
            ?>
            <style>
                #wpadminbar > #wp-toolbar > #wp-admin-bar-root-default > #wp-admin-bar-wp-logo .ab-icon {
                    background-image: url(<?php echo $favicon; ?>) !important;
                    margin: 4px;
                    width: 24px;
                    height: 24px;
                    background-size: contain;
                    background-repeat: no-repeat;
                }

                #wpadminbar > #wp-toolbar > #wp-admin-bar-root-default > #wp-admin-bar-wp-logo .ab-icon:before {
                    display: none;
                }
            </style>
            <?php
        }
    }

    /**
     * Force custom admin color scheme for new users
     * @return string
     */
    public static function getAdminThemeKey(): string {
        return Theme::TEXT_DOMAIN . '_admin_theme_key';
    }
}

//init
AdminStyles::getInstance();