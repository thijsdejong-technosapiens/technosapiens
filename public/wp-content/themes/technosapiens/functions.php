<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Enqueue;
use TechnoSapiens\Core\Singleton;

//autoload PHP classes
require_once('autoload.php');

//init post-type factory (theme-local)
FactoryPlugin::getInstance();

/**
 * Class Theme
 * @package TechnoSapiens
 */
class Theme extends Singleton {

    /**
     * Theme text domain.
     * @var string
     */
    const TEXT_DOMAIN = 'technosapiens';

    /**
     * Define theme image sizes
     * @var string
     */
    //TODO add real - non block - image sizes as constants below like this:
    //const IMAGE_EPIC_IMAGE = 'epic-image';
    //const IMAGE_EPIC_IMAGE = 'epic-image-x2';

    /**
     * Define navigation locations
     * @var string
     */
    const MENU_PRIMARY = 'menu-primary';
    const MENU_SECONDARY = 'menu-secondary';
    const MENU_FOOTER = 'menu-footer';
    const MENU_MOBILE_TOP = 'menu-mobile-top';
    const MENU_MOBILE_BOTTOM = 'menu-mobile-bottom';

    /**
     * Theme constructor.
     */
    protected function __construct() {

        //define manifest path
        define('MANIFEST_PATH', get_stylesheet_directory() . '/dist/manifest.json');

        //define svg icon sprite path
        $iconPath = Enqueue::getWebpackAssetUrlByKey(MANIFEST_PATH, (self::TEXT_DOMAIN . '-icons-svg'));
        if (!$iconPath) $iconPath = Enqueue::getWebpackAssetUrlByKey(MANIFEST_PATH, (self::TEXT_DOMAIN . '-icons.svg'));
        define('ICON_PATH', $iconPath ? ($iconPath . '#') : '#');

        //init (theme) translations + Techno Sapiens classes
        add_action('after_setup_theme', function () {
            load_theme_textdomain(self::TEXT_DOMAIN, get_stylesheet_directory() . '/languages');
            self::initSettings();
            self::initImageSizes();
            self::initMenuLocations();
            self::initScripts();
            self::initComponents();
            self::initHeaderModule();
            self::initMegaMenuPlugin();
            self::initRestEndpoints();
            self::initPostTypes();
        });

        add_action('acf/init', [self::class, 'initBlocks']);

        //register section ACF JSON load paths before ACF scans them on acf/include_fields
        add_filter('acf/settings/load_json', [self::class, 'registerSectionAcfJsonLoadPaths']);
    }

    /**
     * Register ACF JSON load paths for all section blocks.
     *
     * Section blocks are instantiated on acf/init, which runs after ACF has
     * already scanned its load_json paths on acf/include_fields. Registering the
     * paths here (at theme load) ensures the section field groups are available.
     *
     * @param array $paths
     * @return array
     */
    public static function registerSectionAcfJsonLoadPaths(array $paths = []): array {
        $sectionsDir = get_stylesheet_directory() . '/sections';

        if (!is_dir($sectionsDir)) {
            return $paths;
        }

        $sectionAcfJsonPaths = glob($sectionsDir . '/*/acf-json', GLOB_ONLYDIR) ?: [];

        return array_merge($paths, $sectionAcfJsonPaths);
    }

    /**
     * Init image sizes
     * @return void
     */
    public static function initImageSizes(): void {
        //TODO add real - non block - image sizes below like this:
        //add_image_size(self::IMAGE_EPIC_IMAGE, 400, 400, true);
        //add_image_size(self::IMAGE_EPIC_IMAGE_X2, 800, 800, true);
    }

    /**
     * Init menu locations
     * @return void
     */
    public static function initMenuLocations(): void {
        register_nav_menus([
            self::MENU_PRIMARY => __('Primary menu', self::TEXT_DOMAIN),
            self::MENU_SECONDARY => __('Secondary menu', self::TEXT_DOMAIN),
            self::MENU_FOOTER => __('Footer menu', self::TEXT_DOMAIN),
            self::MENU_MOBILE_TOP => __('Mobile top menu', self::TEXT_DOMAIN),
            self::MENU_MOBILE_BOTTOM => __('Mobile bottom menu', self::TEXT_DOMAIN)
        ]);
    }

    /**
     * Init post types
     * @return void
     */
    public static function initPostTypes(): void {

        //init regular post-types
        PagePostType::getInstance();

        //init Techno Sapiens factory post-types
        if (class_exists('TechnoSapiens\FactoryPlugin\PostTypeSettingsFactory')) {
            StaticBlockPostType::getInstance();
        }
    }

    /**
     * Init scripts
     * @return void
     */
    public static function initScripts(): void {
        FrontendScripts::getInstance();
        AdminScripts::getInstance();
    }

    /**
     * Init settings pages
     * @return void
     */
    public static function initSettings(): void {
        GFMailSettings::getInstance();
        ThemeSettings::getInstance();
        self::initSearchPlugin();
    }

    /**
     * Init search plugin
     * @return void
     */
    public static function initSearchPlugin(): void {
        if (class_exists('acf_pro') && class_exists('TechnoSapiens\SearchPlugin')) {
            SearchPlugin::getInstance();
        }
    }

    /**
     * Init header module
     * @return void
     */
    public static function initHeaderModule(): void {
        if (class_exists('acf_pro') && class_exists('TechnoSapiens\HeaderModulePlugin')) {
            HeaderModulePlugin::getInstance();
        }
    }

    /**
     * Init mega menu plugin
     * @return void
     */
    public static function initMegaMenuPlugin(): void {
        if (
            class_exists('acf_pro') &&
            class_exists('TechnoSapiens\FactoryPlugin\PostTypeSettingsFactory') &&
            class_exists('TechnoSapiens\MegaMenuPlugin')
        ) {
            MegaMenuPlugin::getInstance();
        }
    }

    /**
     * Init components
     * @return void
     */
    public static function initComponents(): void {
        AcfComponent::getInstance();
        GutenbergComponent::getInstance();
        BreadcrumbsComponent::getInstance();
        Page404Component::getInstance();
        RobotsTxtComponent::getInstance();
        StylingComponent::getInstance();
        NoIndexComponent::getInstance();
        PostStatusComponent::getInstance();
        SitemapComponent::getInstance();
        PostPasswordComponent::getInstance();
        OwnerRoleComponent::getInstance();
        OpcacheComponent::getInstance();
        SocialLinkComponent::getInstance();

        //handle Gravity Forms customization
        if (class_exists('\GFForms')) {
            GravityFormsComponent::getInstance();
            GravityFormMailComponent::getInstance();
            GravityFormsTrackingComponent::getInstance();
        }

        //handle Polylang customization
        if (class_exists('\Polylang')) {
            PolylangComponent::getInstance();
        }
    }

    /**
     * Init REST endpoints
     * @return void
     */
    public static function initRestEndpoints(): void {
        GetItemsRestRoute::getInstance();
    }

    /**
     * Init blocks
     * @return void
     */
    public static function initBlocks(): void {

        //init section blocks
        SectionContent::getInstance();
        SectionGrandTitle::getInstance();
        SectionUsps::getInstance();
        SectionCards::getInstance();
    }
}


//init theme
global $themeInstance;
if (!$themeInstance) $themeInstance = Theme::getInstance();

/** No code should pass this line. Ever :) */
