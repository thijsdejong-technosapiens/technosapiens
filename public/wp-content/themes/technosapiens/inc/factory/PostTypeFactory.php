<?php

namespace TechnoSapiens\FactoryPlugin;

use TechnoSapiens\Core\Enqueue;
use TechnoSapiens\Core\Formatting;
use TechnoSapiens\Core\GlobalScripts;
use TechnoSapiens\Core\Image;
use TechnoSapiens\Core\Link;
use TechnoSapiens\Core\Partial;
use TechnoSapiens\Core\PostType;
use TechnoSapiens\Core\Singleton;
use TechnoSapiens\FactoryPlugin;
use TechnoSapiens\HeaderModulePlugin\AcfLocationComponent;
use TechnoSapiens\GlobalCtaPlugin\AcfLocationComponent as GlobalCtaAcfLocationComponent;
use TechnoSapiens\ImageHelperPlugin\ImageCheckComponent;
use ReflectionClass;
use stdClass;
use WP_Post;
use WP_Query;

/**
 * Class PostTypeFactory
 * @package TechnoSapiens
 */
abstract class PostTypeFactory extends Singleton {

    /**
     * Post-type slug.
     * @var string
     */
    public const TYPE = '';

    /**
     * Default archive slug (overridden via settings when configured).
     * @var string
     */
    public const BASE_SLUG = '';

    /**
     * Taxonomy slug for the category taxonomy.
     * @var string
     */
    public const CATEGORY_SLUG = '';

    /**
     * Class name of the settings class (options page, ACF fields, etc.).
     * If empty, no settings page is registered.
     * @var PostTypeSettingsFactory
     */
    protected const SETTINGS_CLASS = '';

    /**
     * Immutable default feature set.
     * @var array<string,bool>
     */
    private const DEFAULT_FEATURES = [
        'enableArchive' => true,
        'enableArchiveHeroModule' => true,
        'enableArchiveTopButtonFilters' => true,
        'enableArchiveFilterCounts' => true,
        'enableArchiveGlobalCta' => true,
        'enableSingle' => true,
        'enableSingleHeroModule' => true,
        'enableSinglePrevNextLinks' => true,
        'enableSingleBackButton' => true,
        'enableSingleRelatedPosts' => true,
        'enableSingleRelatedPostsButton' => false,
        'enableSingleGlobalCta' => true,
        'enableGutenbergEditor' => true,
        'enableVisiblePostDates' => true,
    ];

    /**
     * Per-CPT configuration holders — keyed by concrete class name.
     * @var array<class-string<self>,mixed>
     */
    protected static array $features = [];
    protected static array $customPostTypeLabels = [];
    protected static array $customCategoryLabels = [];
    protected static array $customSupports = [];
    protected static array $customMenuIcon = [];
    protected static array $customCategoryFilterParam = [];
    protected static array $customSearchParam = [];
    protected static array $snippetImageSize = [];
    protected static array $prevNextImageSize = [];
    protected static array $postsPerRow = [];

    /**
     * PostTypeFactory constructor.
     */
    protected function __construct() {

        //init settings
        if (static::SETTINGS_CLASS && class_exists(static::SETTINGS_CLASS)) static::SETTINGS_CLASS::getInstance();

        //register post type, taxonomies and image sizes
        add_action('init', [static::class, 'registerPostType']);
        add_action('init', [static::class, 'initImageSizes']);

        //register the taxonomy if CATEGORY_SLUG is defined
        if (static::CATEGORY_SLUG) {
            add_action('init', [static::class, 'registerTaxonomies']);
        }

        //handle ACF JSON - must be registered before acf/include_fields fires (which happens at init:5)
        add_action('init', [static::class, 'registerAcfJsonPaths'], 1);

        //check if archive and single pages are enabled
        $archiveEnabled = static::featureEnabled('enableArchive');
        $singleEnabled = static::featureEnabled('enableSingle');

        //handle archive customization
        if ($archiveEnabled) {

            //handle post per page, filtering and search on archive
            add_action('pre_get_posts', [static::class, 'filterArchiveQuery']);

            //add filter params for category and search if CATEGORY_SLUG is defined
            add_action('init', [static::class, 'setFilterParams']);

            //handle archive template
            add_filter('archive_template', [static::class, 'filterArchiveTemplate'], 1, 100);

            //make the base slug translatable in Polylang
            if (function_exists('pll_register_string')) {
                pll_register_string(
                    'ts_cpt_' . static::TYPE . '_base',
                    static::BASE_SLUG,
                    __('Post-type factory slugs', FactoryPlugin::TEXT_DOMAIN),
                    false
                );
            }

            //make sure that category taxonomies created by this factory are translatable in Polylang if CATEGORY_SLUG is defined
            if (static::CATEGORY_SLUG) {
                add_filter('pll_get_taxonomies', function (array $taxonomies, bool $isSettings) {
                    if (!$isSettings) $taxonomies[static::CATEGORY_SLUG] = static::CATEGORY_SLUG;
                    return $taxonomies;
                }, 10, 2);
            }
        }

        //handle single customization
        if ($singleEnabled) {

            //handle single template
            add_filter('single_template', [static::class, 'filterSingleTemplate'], 1, 100);

            //add header module support to single pages of this post type
            if (static::featureEnabled('enableSingleHeroModule') && class_exists('TechnoSapiens\HeaderModulePlugin')) {
                AcfLocationComponent::getInstance()->addHeaderModuleToPostType(static::TYPE);
            }

            //add global CTA support to single pages of this post type
            if (static::featureEnabled('enableSingleGlobalCta') && class_exists('TechnoSapiens\GlobalCtaPlugin')) {
                GlobalCtaAcfLocationComponent::getInstance()->addGlobalCtaExcludeSettingsToPostType(static::TYPE);
            }
        }

        //handle either single or archive active
        if ($archiveEnabled || $singleEnabled) {
            add_filter('body_class', [static::class, 'filterBodyClasses'], 1, 100);
            add_action('wp_enqueue_scripts', [static::class, 'enqueueAssets']);
        }

        //handle archive disabled
        if (!$archiveEnabled) {
            add_action('wp', [static::class, 'maybe404Archive'], 0);
            add_filter('wpseo_sitemap_post_type_archive_link', [static::class, 'filterExcludeSitemapArchiveLinks'], 10, 2);
        }

        //handle single disabled
        if (!$singleEnabled) {
            add_action('wp', [static::class, 'maybe404Single'], 0);
            add_filter('wpseo_exclude_from_sitemap_by_post_ids', [static::class, 'filterExcludeSitemapPostsByIds'], 10, 2);
            add_action('do_meta_boxes', [static::class, 'removeDefaultMetaBoxes'], 1, 20);
        }

        //add image helper checks
        if (class_exists('TechnoSapiens\ImageHelperPlugin')) $this->addImageHelperChecks();

        //call the parent constructor
        parent::__construct();
    }

    /**
     * Return the fully-qualified class name of the concrete CPT.
     * @return string
     */
    private static function getChildClassKey(): string {
        return static::class;
    }

    /**
     * Get the array of features for the child post type.
     * @return array
     */
    private static function getFeatures(): array {
        return static::$features[self::getChildClassKey()] ?? self::DEFAULT_FEATURES;
    }

    /**
     * Get the post type labels for the child post type.
     * @return array
     */
    private static function getPostTypeLabels(): array {
        return static::$customPostTypeLabels[self::getChildClassKey()] ?? PostType::getLabels(
            Link::humanize(static::TYPE),
            Link::humanize(static::TYPE . 's')
        );
    }

    /**
     * Get the category labels for the child post type.
     * @return array
     */
    private static function getCategoryLabels(): array {
        return static::$customCategoryLabels[self::getChildClassKey()] ?? PostType::getLabels(
            sprintf(__('%s category', FactoryPlugin::TEXT_DOMAIN), Link::humanize(static::TYPE)),
            sprintf(__('%s categories', FactoryPlugin::TEXT_DOMAIN), Link::humanize(static::TYPE . 's'))
        );
    }

    /**
     * Get the post type supports for the child post type.
     * @return array
     */
    private static function getSupports(): array {
        return static::$customSupports[self::getChildClassKey()] ?? ['title', 'excerpt', 'editor', 'thumbnail', 'revisions'];
    }

    /**
     * Get the menu icon for the child post type.
     * @return string
     */
    private static function getMenuIcon(): string {
        return static::$customMenuIcon[self::getChildClassKey()] ?? 'dashicons-admin-post';
    }

    /**
     * Get the custom translated category filter param for the child post type.
     * @return string
     */
    private static function getCustomCategoryFilterParam(): string {
        if (!static::CATEGORY_SLUG) return '';
        return static::$customCategoryFilterParam[self::getChildClassKey()] ?? static::CATEGORY_SLUG . '-filter';
    }

    /**
     * Get the custom translated category search param for the child post type.
     * @return string
     */
    private static function getCustomSearchParam(): string {
        return static::$customSearchParam[self::getChildClassKey()] ?? static::TYPE . '-search-query';
    }

    /**
     * Get the snippet image sizes for the child post type.
     * @return int[]
     */
    private static function getSnippetSize(): array {
        return static::$snippetImageSize[self::getChildClassKey()] ?? [340, 200];
    }

    /**
     * Get the next/prev image sizes for the child post type.
     * @return int[]
     */
    private static function getPrevNextSize(): array {
        return static::$prevNextImageSize[self::getChildClassKey()] ?? [180, 180];
    }

    /**
     * Get the posts per row for the child post type.
     * @return int
     */
    private static function getPostsPerRow(): int {
        return static::$postsPerRow[self::getChildClassKey()] ?? 4;
    }

    /**
     * Get the path to the theme templates directory for this post type.
     * @return string
     */
    protected static function getThemePartialsPath(): string {
        return get_stylesheet_directory() . '/post-types/' . static::TYPE . '/partials/';
    }

    /**
     * Core override resolver used by both templates *and* partials.
     *
     * @param string $themePath Absolute path inside the theme.
     * @param string $pluginPath Fallback path inside the plugin.
     * @param string $cacheKey Unique key (slug + type) for static cache.
     */
    private static function resolveOverride(string $themePath, string $pluginPath, string $cacheKey): string {
        static $memo = [];
        if (!isset($memo[$cacheKey])) $memo[$cacheKey] = is_readable($themePath) ? $themePath : $pluginPath;
        return $memo[$cacheKey];
    }

    /**
     * Given a template slug (`archive-default` / `single-default`) return
     * the absolute path to the file to load and remember whether we fell
     * back to the factory copy.
     *
     * @param string $slug
     * @return string
     */
    protected static function locatePartialPath(string $slug): string {
        return self::resolveOverride(
            self::getThemePartialsPath() . $slug . '.php',
            WFACP_PARTIAL_PATH . "{$slug}.php",
            static::TYPE . '-template-' . $slug
        );
    }

    /**
     * Has the factory (default) version been used for this template?
     * @param string $slug
     * @param bool $factoryOnly
     * @return bool
     */
    protected static function usingFactoryTemplate(string $slug, bool $factoryOnly = false): bool {

        //only check if not in theme
        if ($factoryOnly) return !is_readable(self::getThemePartialsPath() . $slug . '.php');

        $template = self::locatePartialPath($slug);
        return str_starts_with($template, WFACP_PARTIAL_PATH);
    }

    /**
     * Render a partial (theme-override aware) but *always* through
     * TechnoSapiens\Core\Partial::render().
     *
     * @param string $slug Relative path under post-type/partials (no “.php”)
     * @param array $vars Variables to make available in the view
     * @param bool $output If true, capture and return the HTML
     * @return string
     */
    public static function renderPartial(string $slug, array $vars = [], bool $output = true): string {
        $themeFile = self::getThemePartialsPath() . $slug . '.php';
        $baseDir = is_readable($themeFile) ? self::getThemePartialsPath() : WFACP_PARTIAL_PATH;
        return Partial::render($slug, $vars, $output, $baseDir);
    }

    /**
     * Filter archive page from sitemap
     * @param string $archiveUrl
     * @param string $postType
     * @return string
     */
    public static function filterExcludeSitemapArchiveLinks(string $archiveUrl, string $postType): string {
        if ($postType === static::TYPE) return '';
        return $archiveUrl;
    }

    /**
     * Filter sitemap post types
     * @return array
     */
    public static function filterExcludeSitemapPostsByIds(): array {
        return get_posts([
            'post_type' => static::TYPE,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ]);
    }

    /**
     * Force 404 when singles are disabled.
     * @return void
     */
    public static function maybe404Single(): void {
        if (is_singular(static::TYPE)) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            nocache_headers();
        }
    }

    /**
     * Force 404 when the archive is disabled.
     * @return void
     */
    public static function maybe404Archive(): void {
        if (is_post_type_archive(static::TYPE)) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            nocache_headers();
        }
    }

    /**
     * Remove default meta-boxes
     * @return void
     */
    public static function removeDefaultMetaBoxes(): void {
        remove_meta_box('metronet_tag_manager', static::TYPE, 'normal');
        remove_meta_box('slugdiv', static::TYPE, 'normal');
        remove_meta_box('msls', static::TYPE, 'side');
        remove_meta_box('sep_metabox_id', static::TYPE, 'side');
        remove_meta_box('wpseo_meta', static::TYPE, 'normal');
    }

    /**
     * Get factory class by post type slug
     * @param string $postType
     * @return string|PostTypeFactory|null
     */
    public static function getFactoryClassByPostType(string $postType): string|PostTypeFactory|null {
        $obj = get_post_type_object($postType);
        return $obj->ts_factory_class ?? null;
    }

    /**
     * Get setting class by post type
     * @param string $postType
     * @return string|PostTypeSettingsFactory|null
     */
    public static function getSettingsClassByPostType(string $postType): string|PostTypeSettingsFactory|null {
        $obj = get_post_type_object($postType);
        return $obj->ts_settings_class ?? null;
    }

    /**
     * Public helper so templates can ask the post-type for the flag.
     * @param string $featureKey
     * @return bool
     */
    public static function featureEnabled(string $featureKey): bool {
        $all = self::getFeatures();
        return $all[$featureKey] ?? false;
    }

    /**
     * Public helper to set features for the child post type.
     * @param array $newFeatures
     */
    public static function setFeatures(array $newFeatures): void {
        static::$features[self::getChildClassKey()] = array_replace(self::DEFAULT_FEATURES, $newFeatures);
    }

    /**
     * Set the post-type labels for the child post type.
     * @param array $labels
     * @return void
     */
    public static function setPostTypeLabels(array $labels): void {
        static::$customPostTypeLabels[self::getChildClassKey()] = $labels;
    }

    /**
     * Set the category taxonomy labels for the child post type.
     * @param array $labels
     * @return void
     */
    public static function setCategoryLabels(array $labels): void {
        static::$customCategoryLabels[self::getChildClassKey()] = $labels;
    }

    /**
     * Set the post type supports array for the child post type.
     * @param array $supports
     * @return void
     */
    public static function setSupports(array $supports): void {
        static::$customSupports[self::getChildClassKey()] = $supports;
    }

    /**
     * Set the post type menu icon for the child post type.
     * @param string $icon
     * @return void
     */
    public static function setMenuIcon(string $icon): void {
        static::$customMenuIcon[self::getChildClassKey()] = $icon;
    }

    /**
     * Set the translated filter parameter for the category taxonomy for the child post type.
     * @param string $param
     * @return void
     */
    public static function setCategoryFilterParam(string $param): void {
        static::$customCategoryFilterParam[self::getChildClassKey()] = $param;
    }

    /**
     * Set the translated search parameter
     * @param string $param
     * @return void
     */
    public static function setCustomSearchParam(string $param): void {
        static::$customSearchParam[self::getChildClassKey()] = $param;
    }

    /**
     * Set the snippet image size for the child post type.
     * @param array $size
     * @return void
     */
    public static function setSnippetImageSize(array $size): void {
        static::$snippetImageSize[self::getChildClassKey()] = $size;
    }

    /**
     * Set the prev/next image size for the child post type.
     * @param array $size
     * @return void
     */
    public static function setPrevNextImageSize(array $size): void {
        static::$prevNextImageSize[self::getChildClassKey()] = $size;
    }

    /**
     * Set the number of posts per row for the child post type.
     * @param int $postsPerRow
     * @return void
     */
    public static function setPostsPerRow(int $postsPerRow): void {
        static::$postsPerRow[self::getChildClassKey()] = $postsPerRow;
    }

    /**
     * Register the custom post-type.
     * @return void
     */
    public static function registerPostType(): void {

        //get post type slug
        $slug = static::getArchiveSlug();

        //set up post type labels
        $labels = self::getPostTypeLabels();

        //set up post type supports
        $supports = self::getSupports();

        //set up post type menu icon
        $menuIcon = self::getMenuIcon();

        //check if we need to enable the Gutenberg editor
        $enableGutenbergEditor = static::featureEnabled('enableGutenbergEditor') ?? true;

        //check if archive and single pages are enabled
        $archiveEnabled = static::featureEnabled('enableArchive');
        $singleEnabled = static::featureEnabled('enableSingle');
        $isPublic = $archiveEnabled || $singleEnabled;

        $args = [
            'public' => $isPublic,
            'show_ui' => true,
            'labels' => $labels,
            'supports' => $supports,
            'has_archive' => $archiveEnabled ? $slug : false,
            'rewrite' => ['slug' => $slug, 'with_front' => true],
            'menu_icon' => $menuIcon,
            'show_in_rest' => $enableGutenbergEditor,
            'exclude_from_search' => !$singleEnabled,
        ];

        //make args filterable
        $args = apply_filters('ts_post_type_' . static::TYPE . '_args', $args);

        //bind our factory and settings class to the post type object
        $args['ts_factory_class'] = static::class;
        $args['ts_settings_class'] = static::SETTINGS_CLASS ?: null;

        //register post-type
        register_post_type(static::TYPE, $args);
    }

    /**
     * Register the taxonomy attached to this post type.
     * @return void
     */
    public static function registerTaxonomies(): void {

        //set up taxonomy labels
        $labels = self::getCategoryLabels();

        $args = [
            'public' => false,
            'labels' => $labels,
            'hierarchical' => true,
            'rewrite' => ['slug' => false],
            'show_ui' => true,
            'show_admin_columns' => true,
            'query_var' => static::CATEGORY_SLUG,
            'show_in_rest' => true,
        ];

        //make args filterable
        $args = apply_filters('ts_taxonomy_' . static::CATEGORY_SLUG . '_args', $args);

        //register taxonomy
        register_taxonomy(static::CATEGORY_SLUG, static::TYPE, $args);
    }

    /**
     * Get the prev/next image size.
     * @param bool $isRetina
     * @return string
     */
    public static function getSnippetImageSize(bool $isRetina = false): string {
        return "snippet-" . static::TYPE . ($isRetina ? "-x2" : "");
    }

    /**
     * Get the prev/next image size.
     * @param bool $isRetina
     * @return string
     */
    public static function getPrevNextImageSize(bool $isRetina = false): string {
        return "image-next-prev-" . static::TYPE . ($isRetina ? "-x2" : "");
    }

    /**
     * Register image sizes.
     * @return void
     */
    public static function initImageSizes(): void {
        $snippetImageSizes = apply_filters("ts_" . static::TYPE . "_snippet_image_size", self::getSnippetSize());
        add_image_size(self::getSnippetImageSize(), $snippetImageSizes[0], $snippetImageSizes[1], true);
        add_image_size(self::getSnippetImageSize(true), $snippetImageSizes[0] * 2, $snippetImageSizes[1] * 2, true);

        //handle prev/next image sizes
        if (static::featureEnabled('enableSinglePrevNextLinks')) {
            $prevNextLinkImageSizes = apply_filters("ts_" . static::TYPE . "_prev_next_image_size", self::getPrevNextSize());
            add_image_size(self::getPrevNextImageSize(), $prevNextLinkImageSizes[0], $prevNextLinkImageSizes[1], true);
            add_image_size(self::getPrevNextImageSize(true), $prevNextLinkImageSizes[0] * 2, $prevNextLinkImageSizes[1] * 2, true);
        }
    }

    /**
     * Add featured-image checks (Image Helper plugin).
     * @return void
     */
    private function addImageHelperChecks(): void {
        $supports = self::getSupports();
        if (!in_array('thumbnail', $supports, true)) return;

        //add featured image check for the snippet to the image helper
        ImageCheckComponent::getInstance()->addPostCheck(
            __('Featured image', FactoryPlugin::TEXT_DOMAIN),
            static::TYPE,
            self::getSnippetImageSize(true),
            static fn(WP_Post $post) => get_post_thumbnail_id($post)
        );
    }

    /**
     * Use custom archive template.
     * @param string $template selected template path
     * @return string
     */
    public static function filterArchiveTemplate(string $template): string {
        if (is_post_type_archive(static::TYPE)) {
            return self::locatePartialPath('templates/archive');
        }

        return $template;
    }

    /**
     * Use custom single template.
     * @param string $template selected template path
     * @return string
     */
    public static function filterSingleTemplate(string $template): string {
        if (is_singular(static::TYPE)) {
            return self::locatePartialPath('templates/single');
        }

        return $template;
    }

    /**
     * Modify the main archive query: pagination, filters and search.
     * @param WP_Query $query
     * @return WP_Query
     */
    public static function filterArchiveQuery(WP_Query $query): WP_Query {

        //bail if not the main query or not the archive for this post type
        if (is_admin() || !is_post_type_archive(static::TYPE) || !$query->is_main_query()) return $query;

        //set posts per page
        if (static::SETTINGS_CLASS && method_exists(static::SETTINGS_CLASS, 'getPostsPerPage')) {
            $query->set('posts_per_page', static::SETTINGS_CLASS::getPostsPerPage());
        }

        //handle taxonomy filters
        if (static::CATEGORY_SLUG) {
            $param = static::getTranslatedCustomCategoryFilterParam();
            $taxQuery = $query->get('tax_query') ?: [];
            if ($terms = $query->get($param)) {
                $taxQuery[] = ['taxonomy' => static::CATEGORY_SLUG, 'field' => 'slug', 'terms' => (array)$terms];
                $query->set('tax_query', $taxQuery);
            }
        }

        //handle search
        $searchParam = static::getTranslatedCustomSearchParam();
        if ($searchParam && $searchQuery = $query->get($searchParam)) $query->set('s', $searchQuery);

        return $query;
    }

    /**
     * Enqueue CSS and JS depending on context.
     * @return void
     */
    public static function enqueueAssets(): void {

        //check if we are in the archive or single context
        $isArchive = is_archive() && PostType::getPostType() === static::TYPE;
        $isSingle = is_single() && PostType::getPostType() === static::TYPE;

        //get snippet CSS
        $snippetDefaultCss = Enqueue::getWebpackAssetUrlByKey(WFACP_MANIFEST_URL, 'snippet-default.css');
        $usingDefaultSnippet = self::usingFactoryTemplate('snippets/snippet', true);

        //handle assets for both archive and single context
        if (($isArchive || $isSingle) && $snippetDefaultCss && $usingDefaultSnippet) {
            wp_enqueue_style('ts_snippet_default', $snippetDefaultCss, [], null);
            GlobalScripts::getInstance()->enqueueCoreLinkSnippetAssets();
        }

        //handle archive assets
        if ($isArchive) {

            //handle archive page CSS
            $archiveCss = Enqueue::getWebpackAssetUrlByKey(WFACP_MANIFEST_URL, 'archive-default.css');
            $usingDefaultArchive = self::usingFactoryTemplate('templates/archive', true);
            if ($archiveCss && $usingDefaultArchive) {
                wp_enqueue_style('ts_archive_page_default', $archiveCss, [], null);
            }

            //handle archive top filters
            $usingDefaultArchiveTopFilters = self::usingFactoryTemplate('archive/archive-filters-top', true) && static::CATEGORY_SLUG;
            if (static::featureEnabled('enableArchiveTopButtonFilters') && $usingDefaultArchiveTopFilters) {
                if ($archiveFiltersTopCss = Enqueue::getWebpackAssetUrlByKey(WFACP_MANIFEST_URL, 'archive-filters-top.css')) {
                    wp_enqueue_style('ts_archive_top_filters_css', $archiveFiltersTopCss, [], null);
                }
                if ($archiveFiltersMobileCss = Enqueue::getWebpackAssetUrlByKey(WFACP_MANIFEST_URL, 'archive-filters-mobile.css')) {
                    wp_enqueue_style('ts_archive_mobile_filters_css', $archiveFiltersMobileCss, [], null);
                }
                if ($archiveFilterSelectJs = Enqueue::getWebpackAssetUrlByKey(WFACP_MANIFEST_URL, 'ts-filter-select.js')) {
                    wp_enqueue_script('ts_filter_select_js', $archiveFilterSelectJs, [], null, true);
                }
            }
        }

        //handle single assets
        if ($isSingle) {

            //handle single page CSS
            $singleCss = Enqueue::getWebpackAssetUrlByKey(WFACP_MANIFEST_URL, 'single-default.css');
            $usingDefaultSingle = self::usingFactoryTemplate('templates/single', true);
            if ($singleCss && $usingDefaultSingle) {
                wp_enqueue_style('ts_single_page_default', $singleCss, [], null);
            }

            //handle single back button CSS
            $usingDefaultSingleBackButton = self::usingFactoryTemplate('single/single-back-button', true);
            if (static::featureEnabled('enableSingleBackButton') && $usingDefaultSingleBackButton) {
                if ($singleBackButtonCss = Enqueue::getWebpackAssetUrlByKey(WFACP_MANIFEST_URL, 'single-back-button.css')) {
                    wp_enqueue_style('ts_single_back_button_css', $singleBackButtonCss, [], null);
                }
            }

            //handle single next/prev posts
            $usingDefaultSinglePrevNext = self::usingFactoryTemplate('single/single-next-prev-posts', true);
            if (static::featureEnabled('enableSinglePrevNextLinks') && $usingDefaultSinglePrevNext) {
                if ($singlePrevNextCss = Enqueue::getWebpackAssetUrlByKey(WFACP_MANIFEST_URL, 'single-next-prev-posts.css')) {
                    wp_enqueue_style('ts_single_next_prev_posts', $singlePrevNextCss, [], null);
                }
            }

            //handle single related posts
            $usingDefaultSingleRelatedPosts = self::usingFactoryTemplate('single/single-related-posts', true);
            if (static::featureEnabled('enableSingleRelatedPosts') && $usingDefaultSingleRelatedPosts) {
                if ($singleRelatedPostCss = Enqueue::getWebpackAssetUrlByKey(WFACP_MANIFEST_URL, 'single-related-posts.css')) {
                    wp_enqueue_style('ts_single_related_posts', $singleRelatedPostCss, [], null);
                }
            }
        }

        //enqueue other assets from child instances
        static::enqueueAssetsAdditional();
    }

    /**
     * Enqueue additional assets for child classes.
     * This method can be overridden by child classes to enqueue additional assets.
     * @return void
     */
    protected static function enqueueAssetsAdditional(): void {
        //silence is golden
    }

    /**
     * Get the number of columns per row (filterable).
     * @param int $default default value
     * @return int
     */
    public static function getColumnsPerRow(int $default = 4): int {
        $postsPerRow = static::getPostsPerRow();
        return (int)apply_filters('ts_' . static::TYPE . '_columns_per_row', $postsPerRow);
    }

    /**
     * Generate column class names for snippets.
     * @param int $perRow number of columns
     * @return array<int,string>
     */
    public static function getColumnClasses(int $perRow = 4): array {
        $perRow = self::getColumnsPerRow($perRow);
        $classes = ['col', 'col--1'];
        if ($perRow > 1) $classes[] = 'col--md-1/2';
        if ($perRow > 2) $classes[] = 'col--xl-1/' . $perRow;
        return $classes;
    }

    /**
     * Get snippet data for a given post ID.
     * @param int|false $postId post id
     * @return stdClass|false
     */
    public static function getSnippetDataById(mixed $postId = false): stdClass|false {
        if (!$postId) return false;

        //validate post
        $post = get_post($postId);
        if (!$post instanceof WP_Post || $post->post_type !== static::TYPE) return false;

        //start creating data
        $data = new stdClass();
        $data->id = $postId;

        //handle images
        $data->imageId = get_post_thumbnail_id($postId);
        $data->imageSize = self::getSnippetImageSize();
        $data->imageSizeX2 = self::getSnippetImageSize(true);
        $data->imageSizeDimensions = Image::dimensionsFromImageSize($data->imageSize);

        //handle date
        $data->date = date_i18n(get_option('date_format', 'j F Y'), strtotime(get_the_date('Y-m-d', $postId)));

        //handle title
        $data->title = html_entity_decode($post->post_title);

        //handle excerpt
        $limit = static::SETTINGS_CLASS && method_exists(static::SETTINGS_CLASS, 'getExcerptLimit') ? static::SETTINGS_CLASS::getExcerptLimit() : 15;
        $data->excerpt = html_entity_decode(wp_trim_words(do_blocks($post->post_excerpt), $limit));
        if (empty($data->excerpt)) $data->excerpt = html_entity_decode(wp_trim_words(do_shortcode($post->post_content), $limit));

        //handle link
        $data->link = static::featureEnabled('enableSingle') ? get_the_permalink($postId) : '';

        return apply_filters("ts_" . static::TYPE . "_snippet_data", $data, $postId);
    }

    /**
     * Get prev / next post data
     *
     * Returns an array with two objects (prev + next).
     * – When the archive is **enabled** and one side is missing, an “archive” tile
     *   is shown.
     * – When the archive is **disabled**, we fill any gap with the most‐recent
     *   other post (skipping the current and already-used ones).
     *
     * @param mixed $postId
     * @return array<int,stdClass>
     */
    public static function getPrevNextData(mixed $postId = false): array {
        $data = [];

        //bail if no post ID given
        if (!$postId) return $data;

        //check if the archive is enabled (we need this later)
        $archiveEnabled = static::featureEnabled('enableArchive');

        //get image sizes
        $imageSize = self::getPrevNextImageSize();
        $imageSizeX2 = self::getPrevNextImageSize(true);

        //handle next post
        $nextPostItem = false;
        if ($nextPost = get_next_post()) {
            $nextPostItem = self::createPrevNextDataItem($nextPost, $imageSize, $imageSizeX2);
            $data[] = $nextPostItem;
        }

        //handle previous post
        $prevPostItem = false;
        if ($prevPost = get_previous_post()) {
            $prevPostItem = self::createPrevNextDataItem($prevPost, $imageSize, $imageSizeX2);
            $data[] = $prevPostItem;
        }

        //don't show if no posts
        if (!$prevPostItem && !$nextPostItem) return $data;

        //handle archive active
        if ($archiveEnabled) {
            if (!$prevPostItem || !$nextPostItem) {
                $archiveItem = new stdClass();
                $postType = PostType::getPostType($postId);
                $archiveItem->link = PostType::getPostTypeArchiveLink($postType);
                $label = PostType::getSingularLabel($postType);
                $archiveItem->title = sprintf(__("Back to %1s archive", FactoryPlugin::TEXT_DOMAIN), strtolower($label));
                $archiveItem->date = '';
                if (!$prevPostItem) $data[] = $archiveItem;
                if (!$nextPostItem) array_unshift($data, $archiveItem);
            }
        }

        //handle archive disabled
        if (!$archiveEnabled) {

            //archive disabled → fill gap with a fallback post
            if (count($data) < 2) {

                //collect IDs we must skip (current + already added items)
                $excludeIds = [$postId];
                foreach ($data as $item) {
                    $excludeIds[] = url_to_postid($item->link);
                }

                //get the most recent other post
                $fallbackIds = get_posts([
                    'post_type' => static::TYPE,
                    'post_status' => 'publish',
                    'posts_per_page' => 1,
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'fields' => 'ids',
                    'post__not_in' => $excludeIds,
                ]);

                if ($fallbackIds) {
                    $fallbackPost = get_post($fallbackIds[0]);
                    if ($fallbackPost instanceof WP_Post) {
                        $fallbackItem = self::createPrevNextDataItem($fallbackPost, $imageSize, $imageSizeX2);
                        if (!$prevPostItem) array_unshift($data, $fallbackItem);
                        else $data[] = $fallbackItem;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Build a standard prev/next data-item object from a given post.
     *
     * Re-uses the exact image/alt/dimensions logic that was duplicated in the
     * original method.
     *
     * @param WP_Post $post
     * @param string $imageSize
     * @param string $imageSizeX2
     * @return stdClass
     */
    protected static function createPrevNextDataItem(WP_Post $post, string $imageSize, string $imageSizeX2): stdClass {
        $item = new stdClass();
        $item->link = get_the_permalink($post->ID);
        $item->title = $post->post_title;
        $item->date = date_i18n(get_option('date_format', 'j F Y'), strtotime($post->post_date));

        //handle images
        if (has_post_thumbnail($post->ID)) {

            //id & alt
            $imageId = get_post_thumbnail_id($post->ID);
            $item->imageAlt = Image::altFromId($imageId);

            //regular image
            $item->image = Image::urlFromId($imageId, $imageSize);
            $dims = Image::dimensionsFromId($imageId, $imageSize);
            $item->imageWidth = $dims ? $dims->width : 0;
            $item->imageHeight = $dims ? $dims->height : 0;

            //retina image
            $item->imageX2 = Image::urlFromId($imageId, $imageSizeX2);
            $dims = Image::dimensionsFromId($imageId, $imageSizeX2);
            $item->imageWidthX2 = $dims ? $dims->width : 0;
            $item->imageHeightX2 = $dims ? $dims->height : 0;
        }

        return $item;
    }

    /**
     * Get related post IDs for a given post ID with optional amount
     * @param int $postId
     * @param int $amount
     * @return array
     */
    public static function getRelatedPostsIdsByCategory(int $postId, int $amount = -1): array {
        if (!static::CATEGORY_SLUG) return [];

        //start building array of related post IDs
        $relatedPostIds = [];
        $taxQuery = ['relation' => 'OR'];

        //first, try to get posts by primary category
        if (function_exists('\yoast_get_primary_term_id')) {
            $primaryTermId = yoast_get_primary_term_id(static::CATEGORY_SLUG, $postId);
            if ($primaryTermId) {
                $taxQuery[] = ['taxonomy' => static::CATEGORY_SLUG, 'field' => 'term_id', 'terms' => [$primaryTermId]];
            } else {
                //if no primary category is set, get first category
                $terms = wp_get_post_terms($postId, static::CATEGORY_SLUG, ['fields' => 'ids', 'amount' => 1]);
                if ($terms && count($terms) > 0) {
                    $taxQuery[] = ['taxonomy' => static::CATEGORY_SLUG, 'field' => 'term_id', 'terms' => $terms];
                }
            }
        }

        //get posts by term ID when at least one taxonomy query is added
        if (count($taxQuery) > 1) {
            $args = [
                'post_type' => static::TYPE,
                'post_status' => 'publish',
                'posts_per_page' => $amount,
                'numberposts' => $amount,
                'post__not_in' => [$postId], //exclude current post
                'orderby' => 'date', //order by date descending
                'order' => 'DESC',
                'fields' => 'ids',
                'tax_query' => $taxQuery
            ];

            //add filter to allow for custom related posts
            $args = apply_filters('ts_' . static::TYPE . '_related_posts_category_args', $args, $postId, $amount);

            $relatedPostIds = get_posts($args);
        }

        return $relatedPostIds;
    }

    /**
     * Get most recent post IDs with optional amount and exclude ID parameter
     * @param int $postId
     * @param int $amount
     * @param array $excludeIds
     * @return array
     */
    public static function getMostRecentPostIds(int $postId, int $amount = -1, array $excludeIds = []): array {

        $args = [
            'post_type' => static::TYPE,
            'post_status' => 'publish',
            'posts_per_page' => $amount,
            'numberposts' => $amount,
            'post__not_in' => $excludeIds,
            'orderby' => 'date',
            'order' => 'DESC', //order by date descending
            'fields' => 'ids'
        ];

        //add filter to allow for custom related posts
        $args = apply_filters('ts_' . static::TYPE . '_related_posts_recent_args', $args, $postId, $amount, $excludeIds);

        return get_posts($args);
    }

    /**
     * Get array of related posts based on category / most recent
     * @param int $postId
     * @return array
     */
    public static function getRelatedPosts(int $postId): array {

        //determine how many related posts to fetch based on columns per row
        $relatedPostAmount = self::getColumnsPerRow();

        //fetch related posts based on the categories of the current post
        $categoryRelatedPostIds = self::getRelatedPostsIdsByCategory($postId, $relatedPostAmount);
        $categoryRelatedPostsCount = count($categoryRelatedPostIds);

        //initialize the array of exclusion IDs with the current post ID
        $excludeIds = [$postId];

        //if there are category-related posts, add them to exclusion IDs
        if ($categoryRelatedPostsCount > 0) {
            $excludeIds = array_merge($excludeIds, $categoryRelatedPostIds);
        }

        //if the desired amount isn't met, fetch recent posts
        if ($categoryRelatedPostsCount < $relatedPostAmount) {
            $amountToFetch = $relatedPostAmount - $categoryRelatedPostsCount;
            $recentPostIds = self::getMostRecentPostIds($postId, $amountToFetch, $excludeIds);
            $categoryRelatedPostIds = array_merge($categoryRelatedPostIds, $recentPostIds);
            $categoryRelatedPostsCount = count($categoryRelatedPostIds);
        }

        //if no related posts are found, return an empty array
        if ($categoryRelatedPostsCount === 0) return [];

        //final query to fetch related posts
        $args = [
            'post_type' => static::TYPE,
            'post_status' => 'publish',
            'numberposts' => $categoryRelatedPostsCount,
            'posts_per_page' => $categoryRelatedPostsCount,
            'post__in' => $categoryRelatedPostIds,
            'orderby' => 'post__in',
            'order' => 'ASC',
        ];

        // Add filter to allow for custom related posts
        $args = apply_filters('ts_' . static::TYPE . '_related_posts_args', $args, $postId);

        return get_posts($args);
    }

    /**
     * Get array of archive filters
     * @param bool $withChildren
     * @return array
     */
    public static function getArchiveFilters(bool $withChildren = true): array {
        if (!static::CATEGORY_SLUG) return [];

        $filters = [];

        //get search query
        $searchParam = self::getTranslatedCustomSearchParam();
        $query = get_query_var($searchParam) ?: '';
        $taxonomy = static::CATEGORY_SLUG;
        $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => true, 'parent' => 0]);

        if ($terms && count($terms) > 0) {
            $filter = new stdClass();
            $taxonomyLabels = get_taxonomy_labels(get_taxonomy($taxonomy));
            $filter->heading = $taxonomyLabels->singular_name;
            $filter->parameter = self::getTranslatedCustomCategoryFilterParam();
            $filter->options = [];

            foreach ($terms as $term) {
                $filterOption = new stdClass();
                $filterOption->label = $term->name;
                $filterOption->value = $term->slug;
                $filterOption->parent = $term->parent;
                $filterOption->count = $query ? self::countResultsByCategoryId($taxonomy, $term->term_id) : $term->count;
                $filterOption->children = [];

                //get the child terms
                if ($withChildren) {
                    $children = get_terms(['taxonomy' => $taxonomy, 'parent' => $term->term_id, 'orderby' => 'slug', 'hide_empty' => true]);
                    foreach ($children as $child) {
                        $filterOptionChild = new stdClass();
                        $filterOptionChild->label = $child->name;
                        $filterOptionChild->value = $child->slug;
                        $filterOptionChild->parent = $child->parent;
                        $filterOptionChild->count = $query ? self::countResultsByCategoryId($taxonomy, $child->term_id) : $child->count;
                        $filterOption->children[] = $filterOptionChild;
                    }
                }

                $filter->options[] = $filterOption;
            }

            $filters[] = $filter;
        }

        //remove the filter options that have a count of 0
        foreach ($filters as $filter) {
            foreach ($filter->options as $optionKey => $option) {
                if ($option->count === 0) unset($filter->options[$optionKey]);
            }
        }

        return $filters;
    }

    /**
     * Count results (posts) by category ID
     * @param string $taxonomy
     * @param int $categoryId
     * @return int
     */
    public static function countResultsByCategoryId(string $taxonomy, int $categoryId): int {
        if (!static::CATEGORY_SLUG) return 0;

        $postArgs = [
            'post_type' => static::TYPE,
            'numberposts' => -1,
            'post_per_page' => -1,
            'post_status' => 'publish',
            'fields' => 'ids',
            "tax_query" => [
                [
                    "taxonomy" => $taxonomy,
                    "field" => "term_id",
                    "terms" => [$categoryId]
                ]
            ]
        ];

        //check if there are posts found in current query
        global $wp_query;
        if ($wp_query->found_posts > 0) {
            $searchQuery = get_query_var(self::getTranslatedCustomSearchParam()) ?: '';
            if ($searchQuery) $postArgs['s'] = $searchQuery;
        }

        return count(get_posts($postArgs));
    }

    /**
     * Get the total count of posts for this post type.
     * @return int
     */
    public static function getTotalCount(): int {
        return (int)wp_count_posts(static::TYPE)->publish;
    }

    /**
     * Register ACF JSON load/save paths for this post-type.
     * @return void
     */
    public static function registerAcfJsonPaths(): void {

        //get the directory name for ACF JSON files
        $dir = dirname((new ReflectionClass(static::class))->getFileName()) . '/acf-json';
        if (!is_dir($dir)) return;

        //handle load json
        add_filter('acf/settings/load_json', static function (array $paths) use ($dir): array {
            $paths[] = $dir;
            return $paths;
        });

        //handle save json
        add_filter('acf/settings/save_json', static function (string $path) use ($dir): string {
            if (isset($_POST['post_title'])) {
                $slug = Formatting::slugify($_POST['post_title']);
                if (file_exists($dir . '/' . $slug . '.json')) return $dir;
            }
            return $path;
        });
    }

    /**
     * Add BEM-style body classes for archive and single context.
     * @param array<int,string> $classes existing classes
     * @return array<int,string>
     */
    public static function filterBodyClasses(array $classes): array {

        //handle archive body class
        if (is_archive() && PostType::getPostType() === static::TYPE) {
            if (self::usingFactoryTemplate('archive', true)) $classes[] = 'archive-default';
            $classes[] = 'archive-' . static::TYPE;
        }

        //handle single body class
        if (is_single() && PostType::getPostType() === static::TYPE) {
            if (self::usingFactoryTemplate('single', true)) $classes[] = 'single-default';
            $classes[] = 'single-' . static::TYPE;
        }

        return $classes;
    }

    /**
     * Get the archive slug for this post type.
     * @return string
     */
    public static function getArchiveSlug(): string {

        //set default base slug
        $archiveSlug = static::BASE_SLUG;

        //get from settings class if available
        if ((static::SETTINGS_CLASS && method_exists(static::SETTINGS_CLASS, 'getRewriteSlug'))) {
            $archiveSlug = static::SETTINGS_CLASS::getRewriteSlug();
        }

        return $archiveSlug;
    }

    /**
     * Add search query variable for filtering
     * @return void
     */
    public static function setFilterParams(): void {
        global $wp;
        if (static::CATEGORY_SLUG) $wp->add_query_var(static::getTranslatedCustomCategoryFilterParam());
        $wp->add_query_var(static::getTranslatedCustomSearchParam());
    }

    /**
     * Get the translated filter parameter for the category taxonomy.
     * @return string
     */
    public static function getTranslatedCustomCategoryFilterParam(): string {
        if (!static::CATEGORY_SLUG) return '';
        return Formatting::slugify(self::getCustomCategoryFilterParam());
    }

    /**
     * Get the translated search parameter.
     * @return string
     */
    public static function getTranslatedCustomSearchParam(): string {
        return Formatting::slugify(self::getCustomSearchParam());
    }
}