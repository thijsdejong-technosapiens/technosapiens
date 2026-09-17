<?php

namespace TechnoSapiens\SearchPlugin;

use TechnoSapiens\Core\Singleton;
use TechnoSapiens\SearchPlugin;

/**
 * Class SearchExcludeComponent
 * @package TechnoSapiens\SearchPlugin
 */
class SearchExcludeComponent extends Singleton {

    /**
     * Define default value for excluded post ID's
     * @var array
     */
    public array $excludedPostIds = [];

    /**
     * Define meta key for excluding from search
     */
    const EXCLUDE_META_KEY = 'is_excluded_from_search';

    /**
     * Define settings key for excluded posts
     */
    const EXCLUDE_SETTINGS_KEY = 'excluded_posts_from_search';

    /**
     * SearchExcludeComponent constructor
     */
    protected function __construct() {
        add_action('acf/init', function () {
            if ($searchActive = SearchSettings::getInstance()->isSearchEnabled()) {

                //check if on revision page in WordPress admin
                if (is_admin() && isset($_GET['revision'])) return false;

                add_action('wp_loaded', [$this, 'addExcludeMetaBoxAndSettings']);

                //filter the value of exclude meta box based on settings
                add_filter('acf/load_value/name=' . self::EXCLUDE_META_KEY, [$this, 'filterExcludedMetaValue'], 10, 3);

                //add or remove the excluded post to a global setting
                add_action('acf/save_post', [$this, 'handleAcfSavePost']);
            }
        });
    }

    /**
     * Get array of excluded post ID's from settings
     * @return array
     */
    public function getExcludedPostIds(): array {
        if (!$this->excludedPostIds || count($this->excludedPostIds) === 0) {
            $rawExcludedPostIds = get_field(self::EXCLUDE_SETTINGS_KEY, SearchSettings::MENU_SLUG) ?? [];
            $this->excludedPostIds = is_array($rawExcludedPostIds) ? $rawExcludedPostIds : [];
        }

        return $this->excludedPostIds;
    }

    /**
     * Add exclude meta box to searchable post types
     * @return void
     */
    public static function addExcludeMetaBoxAndSettings(): void {
        //get searchable post types
        $searchablePostTypes = SearchComponent::getInstance()->getSearchablePostTypes();

        //get field group locations based on searchable post types
        $locationsArray = array_map(function (string $postType) {
            return [['param' => 'post_type', 'operator' => '==', 'value' => $postType]];
        }, $searchablePostTypes);

        if ($locationsArray && count($locationsArray) > 0) {

            //add ACF meta box to posts of searchable post types
            acf_add_local_field_group([
                'key' => self::EXCLUDE_META_KEY,
                'title' => __('Search settings', SearchPlugin::TEXT_DOMAIN),
                'fields' => [[
                    'key' => self::EXCLUDE_META_KEY,
                    'name' => self::EXCLUDE_META_KEY,
                    'label' => __('Excluded from search', SearchPlugin::TEXT_DOMAIN),
                    'type' => 'true_false',
                    'instructions' => __('Enable this option to exclude this post from showing on the search results page.', SearchPlugin::TEXT_DOMAIN),
                    'required' => 0,
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => __('Yes', SearchPlugin::TEXT_DOMAIN),
                    'ui_off_text' => __('No', SearchPlugin::TEXT_DOMAIN),
                ]],
                'location' => $locationsArray,
                'menu_order' => 0,
                'position' => 'side',
                'style' => 'default',
                'label_placement' => 'top',
                'instruction_placement' => 'field',
                'hide_on_screen' => '',
                'active' => true,
                'description' => '',
                'show_in_rest' => 0,
                'modified' => time()
            ]);

            //add settings section to search settings
            acf_add_local_field_group([
                'key' => self::EXCLUDE_META_KEY . '_settings_group',
                'title' => __('Exclude settings', SearchPlugin::TEXT_DOMAIN),
                'fields' => [[
                    'key' => self::EXCLUDE_SETTINGS_KEY,
                    'label' => __('Excluded from search', SearchPlugin::TEXT_DOMAIN),
                    'name' => self::EXCLUDE_SETTINGS_KEY,
                    'type' => 'relationship',
                    'instructions' => __('Use the fields above to globally exclude posts from showing on the search results page.', SearchPlugin::TEXT_DOMAIN),
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => [
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ],
                    'post_type' => $searchablePostTypes,
                    'taxonomy' => '',
                    'filters' => ['search', 'post_type'],
                    'elements' => ['featured_image',],
                    'min' => '',
                    'max' => '',
                    'return_format' => 'id'
                ]],
                'location' => [[[
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => SearchSettings::MENU_SLUG
                ]]],
                'menu_order' => 1,
                'position' => 'normal',
                'style' => 'default',
                'label_placement' => 'top',
                'instruction_placement' => 'field',
                'hide_on_screen' => '',
                'active' => true,
                'description' => '',
                'show_in_rest' => 0,
                'modified' => time()
            ]);

        }
    }

    /**
     * Filter excluded from search meta value
     * @param mixed $value
     * @param int|string $postId
     * @param array $field
     * @return bool
     */
    public static function filterExcludedMetaValue(mixed $value, int|string $postId, array $field): bool {
        $excludedPosts = self::getInstance()->getExcludedPostIds();
        return in_array($postId, $excludedPosts) ? '1' : '0';
    }

    /**
     * Handle ACF save post
     * @param mixed $acfKey
     * @return void
     */
    public static function handleAcfSavePost(mixed $acfKey): void {
        //get searchable post types
        $searchablePostTypes = SearchComponent::getInstance()->getSearchablePostTypes();

        //handle post
        $currentPost = get_post($acfKey);
        if (is_a($currentPost, '\WP_Post') && in_array($currentPost->post_type, $searchablePostTypes)) {

            //get excluded posts
            $excludedPostIds = self::getInstance()->getExcludedPostIds();

            //create new array of post ID's for comparison later on
            $newExcludedPostIds = unserialize(serialize($excludedPostIds));

            //check if saved post is excluded
            $postIsExcluded = ($_POST['acf'][self::EXCLUDE_META_KEY] ?? '0') === '1';

            //scenario 1 - Post was excluded - add it to the array
            if ($postIsExcluded) {
                if (!in_array($acfKey, $newExcludedPostIds)) $newExcludedPostIds[] = $acfKey;
            }

            //scenario 2 - Post is not excluded - remove it from the array
            if (!$postIsExcluded) {
                if (in_array($acfKey, $newExcludedPostIds)) {
                    foreach ($newExcludedPostIds as $key => $value) {
                        if ($value === $acfKey) {
                            unset($newExcludedPostIds[$key]);
                            break;
                        }
                    }
                }
            }

            //update the setting if the amount of excluded ID's has changed
            if (count($excludedPostIds) !== count($newExcludedPostIds)) {
                update_field(self::EXCLUDE_SETTINGS_KEY, $newExcludedPostIds, SearchSettings::MENU_SLUG);
            }
        }
    }
}