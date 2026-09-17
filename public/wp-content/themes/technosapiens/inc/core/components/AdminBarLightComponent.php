<?php

namespace TechnoSapiens\Core;

use TechnoSapiens\Core;
use TechnoSapiens\OwnerRoleComponent;
use TechnoSapiens\Page404Component;
use TechnoSapiens\SearchPlugin;
use TechnoSapiens\SearchPlugin\SearchSettings;
use OzdemirBurak\Iris\Color\Hex;

/**
 * Class AdminBarLightComponent
 * Provides an alternative “light” admin bar within the WP frontend.
 */
class AdminBarLightComponent extends Singleton {

    /**
     * Meta key for “use light admin bar” setting field on user level
     */
    public const META_KEY_USE_LIGHT_ADMIN_BAR = 'ts_use_admin_bar_light';

    /**
     * Query parameter used to toggle the light admin bar (?ts-enable-admin-bar-light=true|false).
     */
    public const QUERY_PARAM_ENABLE_LIGHT_ADMIN_BAR = 'ts-enable-admin-bar-light';

    /**
     * Nonce action for the light admin bar toggle.
     */
    public const NONCE_ACTION_TOGGLE_LIGHT_ADMIN_BAR = 'ts_toggle_admin_bar_light';

    /**
     * AdminBarLightComponent constructor.
     */
    public function __construct() {

        //bail if user is not logged in
        if (!is_user_logged_in()) return;

        //bail if current WP user is not allowed to use the light admin bar
        $currentUserId = get_current_user_id();
        if (!$currentUserId || !$this->userIsAllowedToUseAdminBar($currentUserId)) return;

        //admin hooks - register profile field and saving hooks
        if (is_admin()) add_action('admin_init', [$this, 'addUseLightAdminBarSetting']);

        //front-end hooks
        if (!is_admin() && is_admin_bar_showing()) {

            //handle toggle query param
            add_action('template_redirect', [$this, 'handleToggleQueryParam'], 0);

            //check if user had admin bar enabled
            $hasAdminBarLightEnabled = $this->userHasLightAdminBarEnabled($currentUserId);

            //handle default admin bar enabled
            if (!$hasAdminBarLightEnabled) {

                //add toggle link to the default admin bar
                add_action('admin_bar_menu', [$this, 'addLightAdminBarToggle'], 100);

                //enqueue front-end assets
                add_action('wp_enqueue_scripts', [$this, 'initFrontendScriptsAdminBar']);
            }

            //handle light admin bar enabled
            if ($hasAdminBarLightEnabled) {

                //enqueue front-end assets
                add_action('wp_enqueue_scripts', [$this, 'initFrontendScriptsAdminBarLight']);

                //add query var for potential usage in theme
                set_query_var('ts_has_light_admin_bar', true);

                //add a CSS class to the HTML tag
                add_filter('ts_html_classes', static function (array $classes): array {
                    if (!in_array('ts-has-light-admin-bar', $classes, true)) $classes[] = 'ts-has-light-admin-bar';
                    return $classes;
                });

                //disable the default admin bar
                add_filter('show_admin_bar', '__return_false');

                //render the light admin bar at the footer
                add_action('wp_footer', static function (): void {
                    Partial::render('components/admin-bar-light', [], true, WCP_PARTIAL_PATH);
                });
            }
        }
    }

    /**
     * Checks for the ?ts-enable-admin-bar-light=true|false param, updates user meta, and redirects.
     * @return void
     */
    public function handleToggleQueryParam(): void {
        if (isset($_GET[self::QUERY_PARAM_ENABLE_LIGHT_ADMIN_BAR])) {

            //get current user ID
            $userId = get_current_user_id();

            //double-check if user is allowed to use admin bar light
            if (!$userId || !$this->userIsAllowedToUseAdminBar($userId)) return;

            $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
            if (!$nonce || !wp_verify_nonce($nonce, self::NONCE_ACTION_TOGGLE_LIGHT_ADMIN_BAR)) {
                return;
            }

            //determine if we should enable or disable
            $enableParam = $_GET[self::QUERY_PARAM_ENABLE_LIGHT_ADMIN_BAR] ?? '';
            $enableValue = ($enableParam === 'true') ? 1 : 0;

            //update user meta
            update_user_meta($userId, self::META_KEY_USE_LIGHT_ADMIN_BAR, $enableValue);

            //redirect and remove the query param to prevent double toggling
            $redirectUrl = remove_query_arg([
                self::QUERY_PARAM_ENABLE_LIGHT_ADMIN_BAR,
                '_wpnonce',
            ]);
            wp_safe_redirect($redirectUrl);
            exit;
        }
    }

    /**
     * Adds hooks to display and save the "light admin bar" user setting.
     *
     * @return void
     */
    public function addUseLightAdminBarSetting(): void {

        //render the user profile field
        add_action('personal_options', [$this, 'renderUseLightAdminBarSetting']);

        //save the value on profile update
        add_action('personal_options_update', [$this, 'saveLightAdminBarSetting']);
        add_action('edit_user_profile_update', [$this, 'saveLightAdminBarSetting']);

        //enable by default for new users
        add_action('user_register', [$this, 'setAdminBarFrontDefault'], 10, 1);
    }

    /**
     * Renders the user profile checkbox field for enabling the light admin bar
     * @param \WP_User $user The WP_User object being edited.
     * @return void
     */
    public function renderUseLightAdminBarSetting(\WP_User $user): void {
        $checked = $this->userHasLightAdminBarEnabled($user->ID);
        ?>
        <tr class="user-admin-bar-light-option">
            <th scope="row">
                <label for="<?php echo esc_attr(self::META_KEY_USE_LIGHT_ADMIN_BAR); ?>">
                    <?php esc_html_e("Admin bar light", Core::TEXT_DOMAIN); ?>
                </label>
            </th>
            <td>
                <input name="<?php echo esc_attr(self::META_KEY_USE_LIGHT_ADMIN_BAR); ?>"
                       id="<?php echo esc_attr(self::META_KEY_USE_LIGHT_ADMIN_BAR); ?>"
                       type="checkbox"
                       value="1"
                    <?php checked($checked); ?>/>
                <label for="<?php echo esc_attr(self::META_KEY_USE_LIGHT_ADMIN_BAR); ?>">
                    <?php esc_html_e("Enable admin bar light", Core::TEXT_DOMAIN); ?>
                </label>
                <p class="description">
                    <?php esc_html_e("Enable the option below to use the light version of the admin bar, which is developed by Techno Sapiens. This more simple version of the default admin bar looks more stylish and only features the buttons you actually use.", Core::TEXT_DOMAIN); ?>
                </p>
            </td>
        </tr>
        <?php
    }

    /**
     * Saves the user’s choice to enable or disable the light admin bar.
     * @param int $userId The ID of the user being edited.
     * @return void
     */
    public function saveLightAdminBarSetting(int $userId): void {
        if (!$this->userIsAllowedToUseAdminBar($userId)) return;
        $useLightValue = isset($_POST[self::META_KEY_USE_LIGHT_ADMIN_BAR]) ? (int)sanitize_text_field($_POST[self::META_KEY_USE_LIGHT_ADMIN_BAR]) : 0;
        update_user_meta($userId, self::META_KEY_USE_LIGHT_ADMIN_BAR, $useLightValue);
    }

    /**
     * Enable the light admin bar by default for new users.
     * (Optional logic, remove if you prefer not to default it on.)
     * @param int $userId The ID of the newly created user.
     * @return void
     */
    public function setAdminBarFrontDefault(int $userId): void {
        if (!$this->userIsAllowedToUseAdminBar($userId)) return;
        update_user_meta($userId, self::META_KEY_USE_LIGHT_ADMIN_BAR, 1);
    }

    /**
     * Checks if a user currently has the light admin bar enabled.
     * @param int $userId The user ID.
     * @return bool True if light admin bar is enabled, false otherwise.
     */
    public function userHasLightAdminBarEnabled(int $userId): bool {
        return (bool)get_user_meta($userId, self::META_KEY_USE_LIGHT_ADMIN_BAR, true);
    }

    /**
     * Checks if the user is allowed (by role) to use the light admin bar.
     * @param int $userId The user ID.
     * @return bool True if the user’s role is allowed.
     */
    public function userIsAllowedToUseAdminBar(int $userId): bool {
        $currentUser = get_user_by('ID', $userId);
        if (!$currentUser instanceof \WP_User) return false;

        //set up roles which can use the light admin bar
        $allowedRoles = ['administrator'];
        if (class_exists('\TechnoSapiens\OwnerRoleComponent')) {
            $allowedRoles[] = OwnerRoleComponent::ROLE_OWNER;
        }

        //make roles filterable
        $allowedRoles = apply_filters('ts_admin_bar_light_allowed_roles', $allowedRoles);

        foreach ($currentUser->roles as $role) {
            if (in_array($role, $allowedRoles, true)) return true;
        }

        return false;
    }

    /**
     * Adds a toggle link to the default WordPress Admin Bar, *if* the user does not yet have
     * the light admin bar enabled. This link sends the user to ?ts-enable-admin-bar-light=true,
     * which updates the setting and then redirects back without the param.
     * @param \WP_Admin_Bar $wpAdminBar The WP_Admin_Bar object for the current user.
     * @return void
     */
    public function addLightAdminBarToggle(\WP_Admin_Bar $wpAdminBar): void {
        $userId = get_current_user_id();
        if (!$userId) return;

        //build a nonce-protected link to enable the light admin bar
        $toggleUrl = wp_nonce_url(
            add_query_arg(self::QUERY_PARAM_ENABLE_LIGHT_ADMIN_BAR, 'true'),
            self::NONCE_ACTION_TOGGLE_LIGHT_ADMIN_BAR
        );

        //add the toggle link to the admin bar
        $wpAdminBar->add_node([
            'id' => 'toggle-light-admin-bar',
            'title' => '<svg><use xlink:href="' . WCP_ICON_PATH . 'icon-switch-on"/></svg><span>' . __('Use admin bar light', Core::TEXT_DOMAIN) . '</span>',
            'href' => esc_url($toggleUrl),
            'meta' => ['class' => 'toggle-light-admin-bar'],
        ]);
    }


    /**
     * Enqueues admin bar light front-end assets
     * @return void
     */
    public function initFrontendScriptsAdminBar(): void {
        $adminBarStyling = Enqueue::getWebpackAssetUrlByKey(WCP_MANIFEST_PATH, 'component-admin-bar.css');
        if ($adminBarStyling) wp_enqueue_style('ts-core-admin-bar', $adminBarStyling, [], null);
    }

    /**
     * Enqueues admin bar light front-end assets
     * @return void
     */
    public function initFrontendScriptsAdminBarLight(): void {
        $adminBarLightStyling = Enqueue::getWebpackAssetUrlByKey(WCP_MANIFEST_PATH, 'component-admin-bar-light.css');
        if ($adminBarLightStyling) {
            wp_enqueue_style('ts-core-admin-bar-light', $adminBarLightStyling, [], null);
            wp_add_inline_style('ts-core-admin-bar-light', $this->generateCssVariablesString());
        }
        wp_enqueue_style('dashicons');
    }

    /**
     * Feature-owned tokens for the light admin bar. Colours fall back to brand in SCSS
     * unless overridden here.
     * @return array<string, string>
     */
    public function getCssVariables(): array {
        $variables = [
            'admin-bar-light-border-radius' => '.25rem',
        ];

        // Optional overrides — uncomment to customise beyond brand fallbacks in SCSS:
        // $variables['admin-bar-light-color-back'] = '#9A3334';
        // $variables['admin-bar-light-color-front'] = '#ffffff';

        if (!empty($variables['admin-bar-light-color-back'])) {
            $variables['admin-bar-light-color-back-hover'] = (string) (new Hex($variables['admin-bar-light-color-back']))->lighten(8);
        }

        return $variables;
    }

    /**
     * @return string
     */
    private function generateCssVariablesString(): string {
        $cssVariableStrings = [];
        foreach ($this->getCssVariables() as $variableKey => $variableValue) {
            if ($variableValue === '' || $variableValue === null) {
                continue;
            }
            $cssVariableStrings[] = '--ts-' . trim($variableKey) . ': ' . $variableValue . ';';
        }
        if (count($cssVariableStrings) === 0) {
            return '';
        }
        return ':root {' . implode(' ', $cssVariableStrings) . '}';
    }

    /**
     * Returns an array of primary items for the light admin bar.
     * @return array
     */
    public static function getPrimaryAdminBarLightItems(): array {
        $items = [];

        //always add link to dashboard
        $items[] = (object)[
            'href' => admin_url(),
            'label' => __('Dashboard', Core::TEXT_DOMAIN),
            'icon' => 'icon-wordpress'
        ];

        //if we are on a single post/page/CPT, add an "Edit post" link
        if (is_singular()) {
            $postId = get_the_ID();
            if ($postId) {
                $editUrl = get_edit_post_link($postId);
                if ($editUrl) {
                    $postTypeObject = get_post_type_object(get_post_type($postId));
                    $singularName = $postTypeObject && !empty($postTypeObject->labels->singular_name) ? strtolower($postTypeObject->labels->singular_name) : __('item', Core::TEXT_DOMAIN);
                    $iconData = $postTypeObject ? self::getPostTypeIcon($postTypeObject) : ['type' => 'dashicon', 'value' => 'dashicons-edit'];
                    $items[] = (object)[
                        'href' => $editUrl,
                        'label' => sprintf(__('Edit %s', Core::TEXT_DOMAIN), $singularName),
                        'icon_data' => $iconData,
                    ];
                }
            }
        }

        //handle 404 page
        if (is_404() && class_exists('\TechnoSapiens\Page404Component')) {
            if ($page404Id = Page404Component::get404PageId()) {
                if ($editUrl = get_edit_post_link($page404Id)) {
                    $postTypeObject = get_post_type_object(get_post_type($page404Id));
                    $iconData = $postTypeObject ? self::getPostTypeIcon($postTypeObject) : ['type' => 'dashicon', 'value' => 'dashicons-edit'];
                    $items[] = (object)[
                        'href' => $editUrl,
                        'label' => __('Edit 404 page', Core::TEXT_DOMAIN),
                        'icon_data' => $iconData,
                    ];
                }
            }
        }

        //if we are on a term archive, add an "Edit Term" link
        if (is_category() || is_tag() || is_tax()) {
            $term = get_queried_object();
            if ($term instanceof \WP_Term) {
                $editTermUrl = admin_url('term.php?taxonomy=' . $term->taxonomy . '&tag_ID=' . $term->term_id);
                $tax = get_taxonomy($term->taxonomy);
                $taxName = $tax && !empty($tax->labels->singular_name) ? $tax->labels->singular_name : __('item', Core::TEXT_DOMAIN);
                $items[] = (object)[
                    'href' => $editTermUrl,
                    'label' => sprintf(__('Edit %s', Core::TEXT_DOMAIN), strtolower($taxName)),
                    'icon' => 'icon-pen',
                ];
            }
        }

        //handle post type archive
        if (is_post_type_archive()) {

            //get post type
            $postType = get_query_var('post_type') ?? '';

            //add link to the post type overview in WP Admin
            if (!empty($postType) && !is_array($postType)) {
                $postTypeObject = get_post_type_object($postType);
                if ($postTypeObject && !empty($postTypeObject->labels->name)) {
                    $editUrl = admin_url('edit.php?post_type=' . $postType);
                    $items[] = (object)[
                        'href' => $editUrl,
                        'label' => $postTypeObject->labels->name,
                        'icon' => 'icon-list',
                    ];
                }
            }

            //add link to the post type settings in WP Admin
            if (!empty($postType) && !is_array($postType) && function_exists('\acf_get_options_pages')) {

                //find the ACF options page for the post type
                $acfOptionPages = acf_get_options_pages();
                $match = false;
                foreach ($acfOptionPages as $acfOptionPageKey => $acfOptionPage) {
                    if ($acfOptionPageKey === $postType . '-settings') {
                        $match = $acfOptionPage;
                        break;
                    }
                }

                if ($match && isset($match['menu_slug'], $match['page_title'], $match['parent_slug'])) {
                    $items[] = (object)[
                        'href' => admin_url($match['parent_slug'] . '&page=' . $match['menu_slug']),
                        'label' => $match['page_title'],
                        'icon' => 'icon-settings',
                    ];
                }
            }
        }

        //handle search results page
        if (is_search() && class_exists('\TechnoSapiens\SearchPlugin')) {
            $items[] = (object)[
                'href' => admin_url('admin.php?page=' . SearchSettings::MENU_SLUG),
                'label' => __("Search settings", SearchPlugin::TEXT_DOMAIN),
                'icon' => 'icon-settings',
            ];
        }

        return apply_filters('ts_admin_bar_light_primary_items', $items);
    }

    /**
     * Returns an array of secondary items for the light admin bar.
     * @return array
     */
    public static function getSecondaryAdminBarLightItems(): array {
        $items = [];

        //add edit links for global $otherPosts
        global $otherPosts;
        if (is_array($otherPosts) && count($otherPosts) > 0) {
            $idsInAdminBar = [];

            //group posts by post type and sort by menu order
            $postsByType = self::groupPostsByTypeAndSort($otherPosts);

            foreach ($postsByType as $postType => $posts) {

                //get post type object for label and icon
                $postTypeObj = get_post_type_object($postType);
                if (!$postTypeObj) continue;

                //add heading for this post type
                $items[] = (object)['heading' => sprintf(__("Active %s", Core::TEXT_DOMAIN), strtolower($postTypeObj->labels->name))];

                foreach ($posts as $post) {
                    if (is_a($post, '\WP_Post') && !in_array($post->ID, $idsInAdminBar, true)) {

                        //set up button label
                        $label = sprintf(__('Edit "%1s"', Core::TEXT_DOMAIN), $post->post_title);
                        if (is_user_logged_in() && current_user_can('administrator')) $label .= ' (ID: ' . $post->ID . ')';

                        //get post type icon
                        $iconData = self::getPostTypeIcon($postTypeObj);

                        //add item
                        $items[] = (object)[
                            'href' => admin_url('post.php?post=' . $post->ID . '&action=edit'),
                            'label' => $label,
                            'icon_data' => $iconData
                        ];

                        $idsInAdminBar[] = $post->ID;
                    }
                }
            }
        }

        //add edit links of used gravity forms within current page
        if (class_exists('\GFAPI')) {
            $usedGravityFormIds = [];

            //get all blocks used in the current and other posts
            $formBlocks = Gutenberg::getUsedBlocksByName('acf/block-form');
            foreach ($formBlocks as $formBlock) {
                $gravityFormId = $formBlock['attrs']['data']['gravity_form_id'] ?? '';
                if ($gravityFormId && !in_array($gravityFormId, $usedGravityFormIds, true)) $usedGravityFormIds[] = $gravityFormId;
            }

            //make gravity form IDs filterable
            $usedGravityFormIds = apply_filters('ts_admin_bar_light_used_gravity_form_ids', $usedGravityFormIds);

            //add edit links for the used gravity forms
            if (count($usedGravityFormIds) > 0) {

                //add heading
                $items[] = (object)['heading' => __("Active forms", Core::TEXT_DOMAIN)];

                foreach ($usedGravityFormIds as $gfFormId) {
                    $form = \GFAPI::get_form($gfFormId);
                    if ($form && is_array($form)) {
                        $formTitle = !empty($form['title']) ? $form['title'] : __('Form', Core::TEXT_DOMAIN);

                        //set up button label
                        $label = sprintf(__('Edit "%1s"', Core::TEXT_DOMAIN), $formTitle);
                        if (is_user_logged_in() && current_user_can('administrator')) $label .= ' (ID: ' . $gfFormId . ')';

                        //add item
                        $items[] = (object)[
                            'href' => admin_url('admin.php?page=gf_edit_forms&id=' . $gfFormId),
                            'label' => $label,
                            'icon' => 'icon-gform'
                        ];
                    }
                }
            }
        }

        //add heading
        $items[] = (object)['heading' => __("Actions", Core::TEXT_DOMAIN)];

        //add toggle link to switch back to default admin bar
        $items[] = (object)[
            'href' => wp_nonce_url(
                add_query_arg(self::QUERY_PARAM_ENABLE_LIGHT_ADMIN_BAR, 'false'),
                self::NONCE_ACTION_TOGGLE_LIGHT_ADMIN_BAR
            ),
            'label' => __('Use default admin bar', Core::TEXT_DOMAIN),
            'icon' => 'icon-switch-off',
        ];

        //add log out button (uses nonce-protected URL) and include user name/login
        $currentUser = wp_get_current_user();
        $logoutLabel = __('Log out', Core::TEXT_DOMAIN);
        $logoutTitle = $logoutLabel;
        if ($currentUser instanceof \WP_User) {
            $displayNameRaw = trim($currentUser->display_name);
            $login = $currentUser->user_login;
            $firstNameRaw = trim((string) get_user_meta($currentUser->ID, 'first_name', true));
            $safeDisplay = wp_strip_all_tags($displayNameRaw);
            $safeFirst = wp_strip_all_tags($firstNameRaw);
            $nameOrLogin = $safeDisplay !== '' ? $safeDisplay : $login;
            $shortName = $safeFirst !== '' ? $safeFirst : $nameOrLogin;
            $logoutLabel = sprintf(__('Log out (%s)', Core::TEXT_DOMAIN), $shortName);
            $logoutTitle = sprintf(__('Log out %s', Core::TEXT_DOMAIN), $nameOrLogin);
        }
        $items[] = (object)[
            'href' => wp_logout_url(home_url('/')),
            'label' => $logoutLabel,
            'title' => $logoutTitle,
            'icon' => 'icon-logout',
        ];

        return apply_filters('ts_admin_bar_light_secondary_items', $items);
    }

    /**
     * Groups posts by post type and sorts them by menu order.
     * @param array $posts Array of WP_Post objects.
     * @return array Associative array with post types as keys and sorted post arrays as values.
     */
    private static function groupPostsByTypeAndSort(array $posts): array {
        $postsByType = [];

        //group posts by post type
        foreach ($posts as $post) {
            if (is_a($post, '\WP_Post')) {
                $postType = $post->post_type;
                if (!isset($postsByType[$postType])) $postsByType[$postType] = [];
                $postsByType[$postType][] = $post;
            }
        }

        //get all registered post types with their menu positions
        $postTypeObjects = get_post_types(['public' => true], 'objects');
        $postTypeOrder = [];
        foreach ($postTypeObjects as $postType => $postTypeObj) {

            //default menu position if not set
            $menuPosition = $postTypeObj->menu_position ?? 25;
            
            //built-in post types get special positions
            if ($postType === 'post') $menuPosition = 5;
            elseif ($postType === 'page') $menuPosition = 20;

            $postTypeOrder[$postType] = $menuPosition;
        }

        //sort post types by menu order
        uksort($postsByType, function ($a, $b) use ($postTypeOrder) {
            $posA = $postTypeOrder[$a] ?? 999;
            $posB = $postTypeOrder[$b] ?? 999;
            return $posA <=> $posB;
        });

        return $postsByType;
    }

    /**
     * Gets the appropriate icon data for a post type.
     * @param \WP_Post_Type $postTypeObj The post type object.
     * @return array Icon data with type and value.
     */
    private static function getPostTypeIcon(\WP_Post_Type $postTypeObj): array {
        $menuIcon = $postTypeObj->menu_icon;

        //handle different icon formats -> defaults to post dashicon
        if (empty($menuIcon) || $menuIcon === 'none') {
            return [
                'type' => 'dashicon',
                'value' => 'dashicons-admin-post'
            ];
        }

        //handle dashicons
        if (is_string($menuIcon) && str_starts_with($menuIcon, 'dashicons-')) {
            return [
                'type' => 'dashicon',
                'value' => $menuIcon
            ];
        }

        //handle SVG data URIs
        if (is_string($menuIcon) && str_starts_with($menuIcon, 'data:image/svg+xml')) {
            return [
                'type' => 'svg_data',
                'value' => $menuIcon
            ];
        }

        //handle custom image URLs
        if (is_string($menuIcon) && (str_starts_with($menuIcon, 'http') || str_starts_with($menuIcon, '/'))) {
            return [
                'type' => 'image_url',
                'value' => $menuIcon
            ];
        }

        //fallback to default dashicon
        return [
            'type' => 'dashicon',
            'value' => 'dashicons-admin-post'
        ];
    }
}
