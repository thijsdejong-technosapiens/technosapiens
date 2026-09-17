<?php

namespace TechnoSapiens\SearchPlugin;

use TechnoSapiens\Core;
use TechnoSapiens\Core\Formatting;
use TechnoSapiens\Core\Link;
use TechnoSapiens\Core\Gutenberg;
use TechnoSapiens\Core\Partial;
use TechnoSapiens\Core\Singleton;
use TechnoSapiens\SearchPlugin;

/**
 * Class SearchComponent
 * @package TechnoSapiens\SearchPlugin
 */
class SearchComponent extends Singleton {

    /**
     * Define default value for searchable post types
     * @var array
     */
    public array $searchablePostTypes = [];

    /**
     * SearchComponent constructor
     */
    protected function __construct() {
        if (!is_admin()) {
            add_action('acf/init', function () {
                if (SearchSettings::getInstance()->isSearchEnabled()) {

                    //change default query parameters for searching
                    add_filter('init', [$this, 'setCustomSearchQueryParams']);
                    add_filter('request', [$this, 'resolveCustomSearchQueryParams']);

                    //add search trigger icon into main nav <ul>
                    add_action('ts_render_search_trigger', [$this, 'getSearchTriggerHtml'], 10, 1);

                    //add search overlay after header nav
                    add_action('ts_render_search_overlay', [$this, 'getSearchOverlayHtml']);

                    //override theme search.php template
                    add_filter('template_include', [$this, 'filterSearchTemplate']);

                    //filter posts per page for search query
                    add_action('pre_get_posts', [$this, 'filterSearchQuery'], 100);

                    //add body class to search page
                    add_filter('body_class', [$this, 'filterBodyClasses'], 1, 100);

                    //replace yoast structured data search url for custom variant
                    add_filter('search_link', [$this, 'filterSearchLink'], 10, 2);
                    add_filter('wpseo_json_ld_search_url', [$this, 'filterYoastSearchUrl']);
                }
            });
        }
    }

    /**
     * Get searchable post types
     * @return array
     */
    public function getSearchablePostTypes(): array {
        if (!$this->searchablePostTypes || count($this->searchablePostTypes) === 0) {
            $postTypesToExclude = ['attachment', 'post'];
            $postTypes = apply_filters('ts_search_post_types', get_post_types(['public' => true, 'exclude_from_search' => false]));

            $this->searchablePostTypes = array_values(array_filter($postTypes, function (string $postType) use ($postTypesToExclude) {
                return !in_array($postType, $postTypesToExclude);
            }));
        }
        return $this->searchablePostTypes;
    }

    /**
     * Add search trigger to navigation
     * @param string|array $args
     * @return string
     */
    public function getSearchTriggerHtml(string|array $args = []): string {
        if (!is_array($args)) $args = [];
        return Partial::render('components/component-search-trigger', $args, true, WSP_PARTIAL_PATH);
    }

    /**
     * Add search field
     * @return string
     */
    public function getSearchOverlayHtml(): string {
        return Partial::render('components/component-search-overlay', [], true, WSP_PARTIAL_PATH);
    }

    /**
     * Checks if search template is called and replaces it with custom template
     * @param string $template
     * @return string
     */
    public function filterSearchTemplate(string $template): string {
        global $wp_query;
        if ($wp_query->is_search) $template = WSP_PLUGIN_PATH . '/templates/search.php';
        return $template;
    }

    /**
     * Filter the WordPress search page query
     * @param \WP_Query $query
     * @return \WP_Query
     */
    public function filterSearchQuery(\WP_Query $query): \WP_Query {
        if (is_search() && !is_admin() && $query->is_main_query()) {
            //set posts per page
            $query->set('posts_per_page', SearchSettings::getInstance()->getPostsPerPage());

            //only return results for correct post types
            $searchType = isset($query->query_vars[self::getSearchTypeParam()]) ? $query->query_vars[self::getSearchTypeParam()] : '';
            if ($searchType && in_array($searchType, $this->getSearchablePostTypes())) $query->set('post_type', [$searchType]);
            else $query->set('post_type', $this->getSearchablePostTypes());

            //get excluded post ID's
            $excludedPostIds = SearchExcludeComponent::getInstance()->getExcludedPostIds();

            //exclude posts from search results
            if (count($excludedPostIds) > 0) {
                $currentPostsNotIn = $query->get('post__not_in') ?? [];
                $query->set('post__not_in', array_merge($currentPostsNotIn, $excludedPostIds));
            }
        }

        return $query;
    }

    /**
     * Filter body classes
     * @param array $classes
     * @return array
     */
    public static function filterBodyClasses(array $classes): array {
        //handle search results page
        if (is_search()) $classes[] = 'search-archive';

        return $classes;
    }

    /**
     * Get snippet data by post id
     * @param number|boolean $postId
     * @return \stdClass|false
     */
    public static function getSnippetDataById(mixed $postId = false): \stdClass|false {
        if ($postId === false) return false;

        global $post;

        $data = new \stdClass();

        //add post ID
        $data->id = $postId;

        //add post title
        $data->title = html_entity_decode(get_the_title($postId));

        //add slug
        $post = get_post($postId);
        $data->slug = html_entity_decode($post->post_name);

        //get post type
        $postType = get_post_type($postId);

        //add type
        $data->type = $postType;

        //start creating array of labels
        $data->labels = [];

        //get post type label
        $postTypeObject = get_post_type_object(get_post_type());
        $label = ($postTypeObject && isset($postTypeObject->labels->singular_name)) ? $postTypeObject->labels->singular_name : __("Page", SearchPlugin::TEXT_DOMAIN);
        $data->labels[] = $label;

        //add filter to add snippet labels dynamically
        $data->labels = apply_filters('ts_search_snippet_labels', $data->labels, $post);

        //handle excerpt
        $excerptLimit = SearchSettings::getInstance()->getExcerptLimit();
        $data->excerpt = html_entity_decode(wp_trim_words(do_blocks(get_the_excerpt($postId)), $excerptLimit));

        //if no excerpt was set, use a part of the post body as excerpt
        if (empty($data->excerpt)) {
            $content = get_the_content($postId);
            $excludedBlocks = apply_filters('ts_search_excerpt_excluded_blocks', ['acf/block-form']);
            $content = Gutenberg::excludeBlocksFromPostContent($content, $excludedBlocks);
            $data->excerpt = html_entity_decode(wp_trim_words(do_shortcode(do_blocks($content)), $excerptLimit));
        }

        //add link
        $data->link = trailingslashit(get_the_permalink($postId));

        /**
         * Add a filter for changing the search snippet data programmatically from the theme or other modules
         * @param \stdClass $data
         * @param \WP_Post $post
         */
        $data = apply_filters('ts_search_snippet_data', $data, $post);

        return $data;
    }

    /**
     * Get search query param name
     * @return string
     */
    public static function getSearchQueryParam(): string {
        return Formatting::slugify(__("search-query", SearchPlugin::TEXT_DOMAIN));
    }

    /**
     * Get search (post) type param name
     * @return string
     */
    public static function getSearchTypeParam(): string {
        return Formatting::slugify(__("search-type", SearchPlugin::TEXT_DOMAIN));
    }

    /**
     * Set custom search query parameters
     */
    public static function setCustomSearchQueryParams() {
        global $wp;
        $wp->add_query_var(self::getSearchQueryParam());
        $wp->add_query_var(self::getSearchTypeParam());
        $wp->remove_query_var('s');
    }

    /**
     * Resolve custom search query params
     * @param $request
     * @return mixed
     */
    public static function resolveCustomSearchQueryParams($request): mixed {
        if (isset($_REQUEST[self::getSearchQueryParam()])) $request['s'] = $_REQUEST[self::getSearchQueryParam()];
        return $request;
    }

    /**
     * Get result counts per type
     * @param string $query
     * @return array
     */
    public function getResultCountsPerType(string $query = ''): array {
        $resultCounts = ['all' => 0];
        $excludedPostIds = SearchExcludeComponent::getInstance()->getExcludedPostIds();

        foreach ($this->searchablePostTypes as $postType) {
            $args = [
                'post_type' => $postType,
                'post_status' => 'publish',
                'posts_per_page' => 1,
                'fields' => 'ids',
                'no_found_rows' => false,
            ];

            if (count($excludedPostIds) > 0) {
                $args['post__not_in'] = $excludedPostIds;
            }

            if ($query) {
                $args[self::getSearchQueryParam()] = $query;
                $args['s'] = $query;
            }

            $typeQuery = new \WP_Query($args);
            $count = (int)$typeQuery->found_posts;
            if ($count > 0) {
                $resultCounts[$postType] = $count;
            }
            $resultCounts['all'] += $count;
        }

        return $resultCounts;
    }

    /**
     * Get search results by query with a hard cap to avoid unbounded loads.
     * @param string $query
     * @return array
     */
    public function getAllSearchResultsByQuery(string $query = ''): array {
        $maxResults = 100;
        $allResultsQuery = [
            'post_type' => $this->searchablePostTypes,
            'posts_per_page' => $maxResults,
            'post_status' => 'publish',
        ];

        $excludedPostIds = SearchExcludeComponent::getInstance()->getExcludedPostIds();

        if (count($excludedPostIds) > 0) {
            $allResultsQuery['post__not_in'] = $excludedPostIds;
        }

        if ($query) {
            $allResultsQuery[self::getSearchQueryParam()] = $query;
            $allResultsQuery['s'] = $query;
        }

        $requestedPostsQuery = new \WP_Query;
        return $requestedPostsQuery->query($allResultsQuery);
    }

    /**
     * Parse post type to translated label string
     * @param $postType
     * @return string
     */
    public static function postTypeToLabel($postType): string {
        $postTypeObject = get_post_type_object($postType);
        return ($postTypeObject && isset($postTypeObject->labels->name)) ? $postTypeObject->labels->name : __("Page", SearchPlugin::TEXT_DOMAIN);
    }

    /**
     * Filter WP search link
     * @param string $link
     * @param string $search
     * @return string
     */
    public static function filterSearchLink(string $link, string $search = ''): string {
        return trailingslashit(Link::getHomePageUrl()) . '?' . self::getSearchQueryParam() . '=' . $search;
    }

    /**
     * Filter Yoast search url for structured data
     * @return string
     */
    public static function filterYoastSearchUrl(): string {
        return trailingslashit(Link::getHomePageUrl()) . '?' . self::getSearchQueryParam() . '={search_term_string}';
    }
}