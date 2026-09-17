<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\PreviewNotificationComponent;
use TechnoSapiens\Core\Singleton;

/**
 * Class ThemeOptimization
 * @package TechnoSapiens
 */
class ThemeOptimization extends Singleton {
    /**
     * ThemeOptimization constructor.
     */
    protected function __construct() {
        
        //remove default image sizes (like "large")
        add_filter('intermediate_image_sizes', [$this, 'removeDefaultImageSizes'], 100, 1);

        //disable emoji related scripts
        add_action('init', [$this, 'removeWpEmoji']);

        //remove unneeded head meta
        add_action('init', [$this, 'removeHeadMeta']);

        //disable WP heart beat
        add_action('init', [$this, 'disableHeartBeat'], 1);

        //set theme supports
        add_theme_support('post-thumbnails');

        //enable Gutenberg block patterns
        add_theme_support('core-block-patterns');

        //disable default block styles (style variations in editor)
        remove_theme_support('wp-block-styles');

        //add theme support for editor styles
        add_theme_support('editor-styles');

        //remove featured image and comment support for default post types
        add_action('init', function () {
            remove_post_type_support('page', 'thumbnail');
            remove_post_type_support('page', 'comments');
            remove_post_type_support('post', 'comments');
        });

        //remove yoast comments
        add_filter('wpseo_debug_markers', '__return_false');

        //disable unwanted WP 5.5 features
        add_filter('plugins_auto_update_enabled', '__return_false');
        add_filter('themes_auto_update_enabled', '__return_false');

        //disable custom CSS from customizer
        add_action('customize_register', [$this, 'disableCustomizerCss']);

        //remove default WordPress admin footer text
        add_filter('admin_footer_text', function () {
            return sprintf(__("Powered by %1s 🚀", Theme::TEXT_DOMAIN), '<a href="https://technosapiens.com" target="_blank">Techno Sapiens</a>');
        });

        //disable default admin pages
        add_action('admin_menu', [$this, 'disableDefaultAdminPages']);
        
        //add a “Block patterns” CMS page in menu
        add_action('admin_menu', [$this, 'registerBlockPatternsMenu'], 20);

        //disable automatic image lazy loading and decoding
        add_filter('wp_lazy_loading_enabled', '__return_false');
        add_filter('wp_img_tag_add_decoding_attr', '__return_false');

        //prevent WordPress from automatically adding fetchpriority="high" to images which we did not add the attribute to
        add_filter('wp_get_loading_optimization_attributes', function (array $attributes, string $tagName, array $context): array {
            if ($tagName === 'img') {
                $elementFetchPriority = $attributes['fetchpriority'] ?? null;
                $contextFetchPriority = $context['fetchpriority'] ?? null;
                if ($elementFetchPriority && $contextFetchPriority === null) unset($attributes['fetchpriority']);
            }
            return $attributes;
        }, 9999, 3);

        //remove global WP styles
        $this->removeGlobalStyles();

        if (getenv('DEV') === 'false') {

            //remove site status tests which should not be tested within our theme
            add_filter('site_status_tests', [$this, 'filterSiteStatusTests']);

            //remove update core, theme and plugin notices
            add_filter('pre_site_transient_update_core', [$this, 'removeUpdateNotices']);
            add_filter('pre_site_transient_update_themes', [$this, 'removeUpdateNotices']);
            add_filter('pre_site_transient_update_plugins', [$this, 'removeUpdateNotices']);

            //add notice on plugin page for customer
            add_action('admin_notices', [$this, 'addPluginPageNotice']);
        }
    }

    /**
     * Add plugin page notice
     * @return void
     */
    public static function addPluginPageNotice(): void {
        global $pagenow;
        global $current_user;
        if ($pagenow === 'plugins.php' && $current_user) {
            echo "<div style='margin: 1rem 0; max-width: 900px'>";
            echo PreviewNotificationComponent::getNotificationHtml(
                sprintf(__("Hi %1s", Theme::TEXT_DOMAIN), ucfirst($current_user->get('display_name'))),
                sprintf(__("This WordPress website is under version control by %1s. Changes to the website will automatically be deployed. Please do not attempt to add plugins or alter the theme code because your changes will be overwritten during the next deployment.", Theme::TEXT_DOMAIN), '<a href="https://technosapiens.com" target="_blank">Techno Sapiens</a>'),
                'warning'
            );
            echo "</div>";
        }
    }

    /**
     * Remove global styles (like duo tone image SVG code)
     * @return void
     */
    public static function removeGlobalStyles(): void {
        remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
        remove_action('wp_footer', 'wp_enqueue_global_styles', 1);
    }

    /**
     * Remove default image sizes
     * @param array $sizes
     * @return array
     */
    public static function removeDefaultImageSizes(array $sizes = []): array {
        $sizesToRemove = ['thumbnail', 'medium', 'medium_large', 'large', '1536x1536', '2048x2048'];

        foreach ($sizes as $sizeIndex => $size) {
            if (in_array($size, $sizesToRemove)) unset($sizes[$sizeIndex]);
        }

        return $sizes;
    }

    /**
     * Disable emoji's
     * @return void
     */
    public static function removeWpEmoji(): void {
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('admin_print_styles', 'print_emoji_styles');
    }

    /**
     * Disable unneeded meta from head
     * @return void
     */
    public static function removeHeadMeta(): void {
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'wp_resource_hints', 2);
    }

    /**
     * Disable WP heartbeat script
     * @return void
     */
    public static function disableHeartBeat(): void {
        wp_deregister_script('heartbeat');
    }

    /**
     * Disable customizer CSS feature
     * @param $wp_customize
     * @return void
     */
    public static function disableCustomizerCss($wp_customize): void {
        $wp_customize->remove_control('custom_css');
    }

    /**
     * Disable default admin pages (posts / comments)
     * @return void
     */
    public static function disableDefaultAdminPages(): void {
        remove_menu_page('edit.php');
        remove_menu_page('edit-comments.php');
        remove_submenu_page('themes.php', 'site-editor.php');
    }

    /**
     * Add the “Block patterns” submenu that links to the Site Editor.
     *
     * @return void
     */
    public function registerBlockPatternsMenu(): void {
        add_menu_page(
            esc_html__('Patterns', Theme::TEXT_DOMAIN),
            esc_html__('Patterns', Theme::TEXT_DOMAIN),
            'edit_theme_options',
            'site-editor.php?p=%2Fpattern',
            '',
            get_stylesheet_directory_uri() . '/images/admin/icon-ts-patterns.svg',
            50
        );
    }    

    /**
     * Filter site status tests
     * @param array $tests
     * @return array
     */
    public static function filterSiteStatusTests(array $tests): array {
        if (isset($tests['async']['background_updates'])) unset($tests['async']['background_updates']);
        if (isset($tests['direct']['plugin_theme_auto_updates'])) unset($tests['direct']['plugin_theme_auto_updates']);
        if (isset($tests['direct']['debug_enabled'])) unset($tests['direct']['debug_enabled']);
        if (isset($tests['direct']['wordpress_version'])) unset($tests['direct']['wordpress_version']);
        if (isset($tests['direct']['php_extensions'])) unset($tests['direct']['php_extensions']);

        return $tests;
    }

    /**
     * Remove update notice on live site
     * @return \stdClass
     */
    public static function removeUpdateNotices(): \stdClass {
        global $wp_version;
        return (object)['last_checked' => time(), 'version_checked' => $wp_version, 'updates' => []];
    }
}

//init
ThemeOptimization::getInstance();