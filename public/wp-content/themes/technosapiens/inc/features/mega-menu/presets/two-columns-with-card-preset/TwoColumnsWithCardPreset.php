<?php

namespace TechnoSapiens\MegaMenuPlugin;

use TechnoSapiens\Core\ButtonComponent;
use TechnoSapiens\Core\Enqueue;
use TechnoSapiens\Core\GlobalScripts;
use TechnoSapiens\Core\Link;
use TechnoSapiens\Core\Navigation;
use TechnoSapiens\Core\Partial;
use TechnoSapiens\Core\Singleton;
use TechnoSapiens\ImageHelperPlugin;
use TechnoSapiens\MegaMenuPlugin;

/**
 * Class TwoColumnsWithCardPreset
 * @package TechnoSapiens\MegaMenuPlugin
 */
class TwoColumnsWithCardPreset extends Singleton {

    /**
     * Set a default value for the preset key
     * @var string
     */
    protected string $presetKey = 'two-columns-with-card';

    /**
     * Set default value for preset name
     * @var string
     */
    protected string $presetName = '';

    /**
     * Cache for preset data by ACF key (post ID)
     * @var array<string, \stdClass>
     */
    private array $presetDataCache = [];

    /**
     * Define preset prefix
     * @var string
     */
    const PRESET_FIELD_PREFIX = 'mm_tcwc_';

    /**
     * Define image sizes
     */
    const IMAGE_TCWC_CARD = 'mm-tcwc-card-image';
    const IMAGE_TCWC_VARIABLE = 'mm-tcwc-variable-image';

    /**
     * TwoColumnsWithCardPreset constructor.
     */
    protected function __construct() {

        //define partial path
        define('WMMP_TCWC_PARTIAL_PATH', WMMP_PLUGIN_PATH . '/presets/two-columns-with-card-preset/partials/');

        //define translatable name for preset
        $this->presetName = __("Two columns with card", MegaMenuPlugin::TEXT_DOMAIN);

        //init classes
        $this->initImageSizes();

        //make sure that the mega menu items are rendered in the side navigation
        add_filter('ts_side_navigation_item', [$this, 'addMegaMenuItemsToSideNavigation'], 10, 1);

        //init front-end assets
        add_action('wp_enqueue_scripts', function () {
            $this->initFrontendAssets();
        });

        //apply button types, styles and sizes from ButtonComponent
        add_action('acf/init', function () {
            add_filter('acf/load_field/name=' . self::PRESET_FIELD_PREFIX . 'button_style', [ButtonComponent::getInstance(), 'populateButtonStyleOptions']);
            add_filter('acf/load_field/name=' . self::PRESET_FIELD_PREFIX . 'button_color', [ButtonComponent::getInstance(), 'populateButtonTypeOptions']);
            add_filter('acf/load_field/name=' . self::PRESET_FIELD_PREFIX . 'button_size', [ButtonComponent::getInstance(), 'populateButtonSizeOptions']);
        });

        //add image helper checks after post types/ACF locations are registered
        if (class_exists('TechnoSapiens\ImageHelperPlugin')) {
            add_action('after_setup_theme', function () {
                $this->addImageHelperChecks();
            }, 200);
        }
    }

    /**
     * Init image sizes
     * @return void
     */
    public function initImageSizes(): void {
        add_image_size(self::IMAGE_TCWC_CARD, 600, 300, true);
        add_image_size(self::IMAGE_TCWC_VARIABLE, 600, 0, false);
    }

    /**
     * Replace side navigation item children with mega menu items if available
     * @param \stdClass $item
     * @return \stdClass
     */
    public function addMegaMenuItemsToSideNavigation(\stdClass $item): \stdClass {
        if (NavMenuItemResolver::isMegaMenuItem($item) && $mmId = NavMenuItemResolver::getMegaMenuIdForItem($item)) {

            //bail if the mega menu post is not visible
            if (!MegaMenuUsageResolver::getInstance()->isMegaMenuPostVisible($mmId)) return $item;

            $mmPreset = get_field('mm_preset', $mmId) ?? '';
            if ($mmPreset === $this->getPresetKey()) {
                $data = $this->getPresetData($mmId);
                if ($data->selectedMenuLinks && count($data->selectedMenuLinks) > 0) $item->children = $data->selectedMenuLinks;
            }
        }

        return $item;
    }

    /**
     * Add image helper checks for this preset
     * @return void
     */
    public function addImageHelperChecks(): void {

        //use mega-menu post type directly since ACF fields are only added to this post type
        $enabledPostTypes = [\TechnoSapiens\MegaMenuPlugin\MegaMenuPostType::TYPE];

        if (count($enabledPostTypes) > 0) {

            //image only (variable height)
            ImageHelperPlugin\ImageCheckComponent::getInstance()->addMetaCheck(
                $this->presetName . ' - ' . __('Image only', MegaMenuPlugin::TEXT_DOMAIN),
                self::IMAGE_TCWC_VARIABLE,
                $enabledPostTypes,
                function (\WP_Post $post): false|int {
                    return get_field(self::PRESET_FIELD_PREFIX . 'image', $post->ID) ?: false;
                },
                function (\WP_Post $post): bool {
                    return get_field('mm_preset', $post->ID) === self::getPresetKey()
                        && get_field(self::PRESET_FIELD_PREFIX . 'content_type', $post->ID) === 'image_only';
                }
            );

            //image with call to action (fixed card size)
            ImageHelperPlugin\ImageCheckComponent::getInstance()->addMetaCheck(
                $this->presetName . ' - ' . __('Card image', MegaMenuPlugin::TEXT_DOMAIN),
                self::IMAGE_TCWC_CARD,
                $enabledPostTypes,
                function (\WP_Post $post): false|int {
                    return get_field(self::PRESET_FIELD_PREFIX . 'image', $post->ID) ?: false;
                },
                function (\WP_Post $post): bool {
                    return get_field('mm_preset', $post->ID) === self::getPresetKey()
                        && get_field(self::PRESET_FIELD_PREFIX . 'content_type', $post->ID) === 'image_with_cta';
                }
            );
        }
    }

    /**
     * Get preset key
     * @return string
     */
    public function getPresetKey(): string {
        return $this->presetKey;
    }

    /**
     * Get preset name
     * @return string
     */
    public function getPresetName(): string {
        return $this->presetName;
    }

    /**
     * Validate if the preset has valid data
     * @param \stdClass $data
     * @return bool
     */
    public function hasValidData(\stdClass $data): bool {
        return count($data->levelOneLinks) > 0;
    }

    /**
     * Set default data for header module preset
     * @return \stdClass
     */
    public function getDefaultPresetData(): \stdClass {
        $defaultData = new \stdClass();

        //handle menu
        $defaultData->selectedMenuId = false;
        $defaultData->selectedMenuLinks = [];
        $defaultData->levelOneLinks = [];
        $defaultData->levelTwoItemsGrouped = new \stdClass();
        $defaultData->hasMenuItems = false;

        //handle type
        $defaultData->contentType = 'none';

        //handle image
        $defaultData->imageId = false;
        $defaultData->imageSize = self::IMAGE_TCWC_CARD;

        //handle card data
        $defaultData->cardTitle = '';
        $defaultData->cardText = '';
        $defaultData->cardLinkUrl = '';
        $defaultData->cardLinkText = '';
        $defaultData->cardLinkTarget = '_self';
        $defaultData->cardLinkRel = '';
        $defaultData->cardButtonStyle = ButtonComponent::getInstance()->getDefaultButtonStyle();
        $defaultData->cardButtonType = ButtonComponent::getInstance()->getDefaultButtonType();
        $defaultData->cardButtonSize = ButtonComponent::getInstance()->getDefaultButtonSize();
        $defaultData->hasValidCard = false;

        return $defaultData;
    }

    /**
     * Get preset data
     * @param string $acfKey
     * @return \stdClass
     */
    public function getPresetData(string $acfKey): \stdClass {

        //return cached data if available
        if (isset($this->presetDataCache[$acfKey])) {
            return $this->presetDataCache[$acfKey];
        }

        //get default data
        $data = self::getDefaultPresetData();

        //get menu configuration data + bail if no menu found
        $data->selectedMenuId = get_field(self::PRESET_FIELD_PREFIX . 'menu', $acfKey) ?? false;

        //handle menu items processing
        if ($data->selectedMenuId) {
            $data->selectedMenuLinks = Navigation::getLinksByMenuId($data->selectedMenuId);

            if (!empty($data->selectedMenuLinks)) {
                $processedMenu = self::processMenuForMegaMenu($data->selectedMenuLinks, $acfKey);
                $data->levelOneLinks = $processedMenu->levelOneItems;
                $data->levelTwoItemsGrouped = $processedMenu->levelTwoItemsGrouped;
                $data->hasMenuItems = $processedMenu->hasItems;
            }
        }

        //handle content type
        $data->contentType = get_field(self::PRESET_FIELD_PREFIX . 'content_type', $acfKey) ?? 'none';

        //handle image
        $data->imageId = get_field(self::PRESET_FIELD_PREFIX . 'image', $acfKey) ?? false;
        $data->imageSize = $data->contentType === 'image_only' ? self::IMAGE_TCWC_VARIABLE : self::IMAGE_TCWC_CARD;

        //handle card title
        $data->cardTitle = get_field(self::PRESET_FIELD_PREFIX . 'cta_title', $acfKey) ?? '';

        //handle card text
        $data->cardText = get_field(self::PRESET_FIELD_PREFIX . 'cta_text', $acfKey) ?? '';

        //handle card link
        $cardLink = get_field(self::PRESET_FIELD_PREFIX . 'cta_link', $acfKey) ?? false;
        if ($cardLink && is_array($cardLink)) {
            $data->cardLinkText = $cardLink['title'] ?? '';
            $data->cardLinkUrl = Link::parseLink($cardLink['url'] ?? '') ?: '';
            $data->cardLinkTarget = $cardLink['target'] ?? '_self';
            $data->cardLinkRel = $data->cardLinkTarget === '_blank' ? Link::getLinkRel($data->cardLinkUrl, $data->cardLinkTarget) : '';
        }

        //handle card button data
        $data->cardButtonType = ButtonComponent::getInstance()->getParsedButtonType(get_field(self::PRESET_FIELD_PREFIX . 'button_color', $acfKey) ?: ButtonComponent::getInstance()->getDefaultButtonType());
        $data->cardButtonStyle = ButtonComponent::getInstance()->getParsedButtonStyle(get_field(self::PRESET_FIELD_PREFIX . 'button_style', $acfKey) ?: ButtonComponent::getInstance()->getDefaultButtonStyle());
        $data->cardButtonSize = ButtonComponent::getInstance()->getParsedButtonSize(get_field(self::PRESET_FIELD_PREFIX . 'button_size', $acfKey) ?: ButtonComponent::getInstance()->getDefaultButtonSize());

        //validate if we have a valid card configuration
        $data->hasValidCard = ($data->contentType === 'image_with_cta') && !empty($data->cardTitle) && !empty($data->cardLinkUrl);

        //cache and return
        $this->presetDataCache[$acfKey] = $data;
        return $data;
    }

    /**
     * Init frontend assets
     * @return void
     */
    public function initFrontendAssets(): void {

        //only enqueue when this preset is used (with valid data) in the primary navigation
        $isUsed = MegaMenuUsageResolver::getInstance()->isPresetUsedInPrimaryNavigation($this->getPresetKey());
        if (!$isUsed) return;

        //enqueue CSS
        $tcwcCss = Enqueue::getWebpackAssetUrlByKey(MANIFEST_PATH, 'mm-two-columns-with-card.css');
        if ($tcwcCss) wp_enqueue_style(MegaMenuPlugin::TEXT_DOMAIN . '_' . $this->presetKey, $tcwcCss);

        //enqueue JS
        $tcwcJs = Enqueue::getWebpackAssetUrlByKey(MANIFEST_PATH, 'mm-two-columns-with-card-script.js');
        if ($tcwcJs) wp_enqueue_script(MegaMenuPlugin::TEXT_DOMAIN . '_script-' . $this->presetKey, $tcwcJs, [], null, ['in_footer' => true]);

        //enqueue LinkSnippet assets for card functionality
        GlobalScripts::getInstance()->enqueueCoreLinkSnippetAssets();
    }

    /**
     * Register the mega menu render action
     * @param \stdClass $data
     * @param \stdClass $parentItem
     * @return void
     */
    public static function render(\stdClass $data, \stdClass $parentItem): void {
        Partial::render('mm-two-columns-with-card', ['data' => $data, 'parentItem' => $parentItem], true, WMMP_TCWC_PARTIAL_PATH);
    }

    /**
     * Render relevant data for admin column
     * @param \stdClass $data
     * @return void
     */
    public static function renderAdminColumnRelevantData(\stdClass $data): void {
        $dataItems = [];

        //handle the selected menu (with hyperlink)
        if ($data->selectedMenuId) {
            $menu = wp_get_nav_menu_object((int)$data->selectedMenuId);
            if ($menu instanceof \WP_Term) {
                $label = $menu->name;
                $editUrl = admin_url(add_query_arg(['action' => 'edit', 'menu' => (int)$menu->term_id], 'nav-menus.php'));
                $value = esc_html($label);
                if (current_user_can('edit_theme_options')) $value = sprintf('<a target="_blank" href="%s">%s</a>', esc_url($editUrl), esc_html($label));
                $dataItems[__('Selected menu', MegaMenuPlugin::TEXT_DOMAIN)] = $value;
            }
        }

        //output the items as a HTML list
        if (count($dataItems) > 0) {
            echo '<ul style="margin: 0">';
            foreach ($dataItems as $dataKey => $dataValue) {
                echo '<li>' . '<strong>' . $dataKey . '</strong>: ' . $dataValue . '</li>';
            }
            echo '</ul>';
        } else {
            echo '-';
        }
    }

    /**
     * Process menu items for mega menu display with all data pre-calculated
     * @param array $menuItems Raw menu items from Navigation
     * @param int $mmId Mega menu post ID (for unique ID generation)
     * @return \stdClass
     */
    private static function processMenuForMegaMenu(array $menuItems, int $mmId): \stdClass {

        //initialize result object
        $result = new \stdClass();
        $result->hasItems = false;
        $result->levelOneItems = [];
        $result->levelTwoItemsGrouped = new \stdClass();
        $result->totalItemCount = 0;

        //bail if no menu items
        if (count($menuItems) === 0) return $result;

        //process level one items and collect level two
        $allLevelTwoItems = [];

        foreach ($menuItems as $levelOneItem) {

            //generate unique ID for this level one item
            $levelOneId = uniqid('mm-l1-') . $mmId . '-' . $levelOneItem->id;

            //augment existing level one item instead of cloning
            $levelOneItem->mmOriginalId = $levelOneItem->id;
            $levelOneItem->id = $levelOneId;
            $levelOneItem->hasChildren = !empty($levelOneItem->children);
            $levelOneItem->childCount = count($levelOneItem->children ?? []);

            //add to level one collection
            $result->levelOneItems[] = $levelOneItem;

            //process children for level two
            if ($levelOneItem->hasChildren) {
                $levelTwoGroup = [];
                foreach ($levelOneItem->children as $levelTwoItem) {

                    //generate unique ID for this level two item
                    $levelTwoId = uniqid('mm-l2-') . $mmId . '-' . $levelTwoItem->id;

                    //augment existing level two item instead of cloning
                    $levelTwoItem->mmOriginalId = $levelTwoItem->id;
                    $levelTwoItem->id = $levelTwoId;
                    $levelTwoItem->parentId = $levelOneId;
                    $levelTwoItem->parentLabel = $levelOneItem->label;

                    //add to the group and primary collection
                    $levelTwoGroup[] = $levelTwoItem;
                    $allLevelTwoItems[] = $levelTwoItem;
                }

                //store grouped items by parent ID as object property
                $result->levelTwoItemsGrouped->$levelOneId = $levelTwoGroup;
            }
        }

        //set result metadata
        $result->hasItems = !empty($result->levelOneItems);
        $result->totalItemCount = count($result->levelOneItems) + count($allLevelTwoItems);

        return $result;
    }

    /**
     * Get preset fields
     * @return array
     */
    public function getPresetFields(): array {
        return [
            [
                "key" => self::PRESET_FIELD_PREFIX . "menu",
                "label" => __("Navigation menu", MegaMenuPlugin::TEXT_DOMAIN),
                "name" => self::PRESET_FIELD_PREFIX . "menu",
                "aria-label" => "",
                "type" => "taxonomy",
                "instructions" => __("Select a navigation menu to get the items from.", MegaMenuPlugin::TEXT_DOMAIN),
                "required" => 1,
                "conditional_logic" => [
                    [
                        [
                            "field" => "mega-menu-preset",
                            "operator" => "==",
                            "value" => $this->getPresetKey()
                        ],
                    ],
                ],
                "wrapper" => [
                    "width" => "25",
                    "class" => "",
                    "id" => ""
                ],
                "taxonomy" => "nav_menu",
                "field_type" => "select",
                "allow_null" => 1,
                "add_term" => 0,
                "save_terms" => 0,
                "load_terms" => 0,
                "return_format" => "id",
                "multiple" => 0,
                "ui" => 1,
            ],
            [
                "key" => self::PRESET_FIELD_PREFIX . "content_type",
                "label" => __("Content type", MegaMenuPlugin::TEXT_DOMAIN),
                "name" => self::PRESET_FIELD_PREFIX . "content_type",
                "aria-label" => "",
                "type" => "radio",
                "instructions" => __("Choose what type of content to display in the third column.", MegaMenuPlugin::TEXT_DOMAIN),
                "required" => 0,
                "conditional_logic" => [
                    [
                        [
                            "field" => "mega-menu-preset",
                            "operator" => "==",
                            "value" => $this->getPresetKey()
                        ],
                    ],
                ],
                "wrapper" => [
                    "width" => "75",
                    "class" => "",
                    "id" => ""
                ],
                "choices" => [
                    "none" => __("No content", MegaMenuPlugin::TEXT_DOMAIN),
                    "image_only" => __("Image only", MegaMenuPlugin::TEXT_DOMAIN),
                    "image_with_cta" => __("Image with call to action", MegaMenuPlugin::TEXT_DOMAIN)
                ],
                "default_value" => "none",
                "layout" => "vertical",
                "return_format" => "value",
                "other_choice" => 0,
                "save_other_choice" => 0,
                "allow_null" => 0
            ],
            [
                "key" => self::PRESET_FIELD_PREFIX . "image",
                "label" => __("Image", MegaMenuPlugin::TEXT_DOMAIN),
                "name" => self::PRESET_FIELD_PREFIX . "image",
                "aria-label" => "",
                "type" => "image",
                "instructions" => __("Select an image to display in the call to action in the third column. The recommended size when using a call to action is <code>600px wide and 300px high</code> and when using only an image is <code>600px wide and a proportional height</code>.", MegaMenuPlugin::TEXT_DOMAIN),
                "required" => 0,
                "conditional_logic" => [
                    [
                        [
                            "field" => self::PRESET_FIELD_PREFIX . "content_type",
                            "operator" => "==",
                            "value" => "image_only"
                        ]
                    ],
                    [
                        [
                            "field" => self::PRESET_FIELD_PREFIX . "content_type",
                            "operator" => "==",
                            "value" => "image_with_cta"
                        ]
                    ]
                ],
                "wrapper" => [
                    "width" => "100",
                    "class" => "",
                    "id" => ""
                ],
                "return_format" => "id",
                "preview_size" => "medium",
                "library" => "all",
                "min_width" => "",
                "min_height" => "",
                "min_size" => "",
                "max_width" => "",
                "max_height" => "",
                "max_size" => "",
                "mime_types" => ""
            ],
            [
                "key" => self::PRESET_FIELD_PREFIX . "cta_title",
                "label" => __("Call to action title", MegaMenuPlugin::TEXT_DOMAIN),
                "name" => self::PRESET_FIELD_PREFIX . "cta_title",
                "aria-label" => "",
                "type" => "text",
                "instructions" => __("Enter a title for the call to action in the third column.", MegaMenuPlugin::TEXT_DOMAIN),
                "required" => 1,
                "conditional_logic" => [
                    [
                        [
                            "field" => self::PRESET_FIELD_PREFIX . "content_type",
                            "operator" => "==",
                            "value" => "image_with_cta"
                        ]
                    ]
                ],
                "wrapper" => [
                    "width" => "100",
                    "class" => "",
                    "id" => ""
                ],
                "default_value" => "",
                "placeholder" => "",
                "prepend" => "",
                "append" => "",
                "maxlength" => "",
                "translations" => "translate"
            ],
            [
                "key" => self::PRESET_FIELD_PREFIX . "cta_text",
                "label" => __("Call to action description", MegaMenuPlugin::TEXT_DOMAIN),
                "name" => self::PRESET_FIELD_PREFIX . "cta_text",
                "aria-label" => "",
                "type" => "textarea",
                "instructions" => __("Enter an (optional) description text for the call to action in the third column.", MegaMenuPlugin::TEXT_DOMAIN),
                "required" => 0,
                "conditional_logic" => [
                    [
                        [
                            "field" => self::PRESET_FIELD_PREFIX . "content_type",
                            "operator" => "==",
                            "value" => "image_with_cta"
                        ]
                    ]
                ],
                "wrapper" => [
                    "width" => "100",
                    "class" => "",
                    "id" => ""
                ],
                "default_value" => "",
                "placeholder" => "",
                "maxlength" => "",
                "rows" => 3,
                "new_lines" => "br",
                "translations" => "translate"
            ],
            [
                "key" => self::PRESET_FIELD_PREFIX . "cta_link",
                "label" => __("Call to action link", MegaMenuPlugin::TEXT_DOMAIN),
                "name" => self::PRESET_FIELD_PREFIX . "cta_link",
                "aria-label" => "",
                "type" => "link",
                "instructions" => __("Set the destination link for the call to action button in the third column.", MegaMenuPlugin::TEXT_DOMAIN),
                "required" => 1,
                "conditional_logic" => [
                    [
                        [
                            "field" => self::PRESET_FIELD_PREFIX . "content_type",
                            "operator" => "==",
                            "value" => "image_with_cta"
                        ]
                    ]
                ],
                "wrapper" => [
                    "width" => "100",
                    "class" => "",
                    "id" => ""
                ],
                "return_format" => "array",
                "translations" => "copy_once"
            ],
            [
                "key" => self::PRESET_FIELD_PREFIX . "button_style",
                "label" => __("Button style", MegaMenuPlugin::TEXT_DOMAIN),
                "name" => self::PRESET_FIELD_PREFIX . "button_style",
                "aria-label" => "",
                "type" => "select",
                "instructions" => __("Choose the visual style for the call to action button in the third column.", MegaMenuPlugin::TEXT_DOMAIN),
                "required" => 1,
                "conditional_logic" => [
                    [
                        [
                            "field" => self::PRESET_FIELD_PREFIX . "content_type",
                            "operator" => "==",
                            "value" => "image_with_cta"
                        ]
                    ]
                ],
                "wrapper" => [
                    "width" => "33",
                    "class" => "",
                    "id" => ""
                ],
                "choices" => [

                ],
                "default_value" => ButtonComponent::getInstance()->getDefaultButtonStyle(),
                "allow_null" => 0,
                "multiple" => 0,
                "ui" => 0,
                "ajax" => 0,
                "return_format" => "value",
                "placeholder" => ""
            ],
            [
                "key" => self::PRESET_FIELD_PREFIX . "button_color",
                "label" => __("Button color", MegaMenuPlugin::TEXT_DOMAIN),
                "name" => self::PRESET_FIELD_PREFIX . "button_color",
                "aria-label" => "",
                "type" => "select",
                "instructions" => __("Select the color scheme for the call to action button in the third column.", MegaMenuPlugin::TEXT_DOMAIN),
                "required" => 1,
                "conditional_logic" => [
                    [
                        [
                            "field" => self::PRESET_FIELD_PREFIX . "content_type",
                            "operator" => "==",
                            "value" => "image_with_cta"
                        ]
                    ]
                ],
                "wrapper" => [
                    "width" => "33",
                    "class" => "",
                    "id" => ""
                ],
                "choices" => [],
                "default_value" => ButtonComponent::getInstance()->getDefaultButtonType(),
                "allow_null" => 0,
                "multiple" => 0,
                "ui" => 0,
                "ajax" => 0,
                "return_format" => "value",
                "placeholder" => ""
            ],
            [
                "key" => self::PRESET_FIELD_PREFIX . "button_size",
                "label" => __("Button size", MegaMenuPlugin::TEXT_DOMAIN),
                "name" => self::PRESET_FIELD_PREFIX . "button_size",
                "aria-label" => "",
                "type" => "select",
                "instructions" => __("Choose the size of the call to action button in the third column.", MegaMenuPlugin::TEXT_DOMAIN),
                "required" => 1,
                "conditional_logic" => [
                    [
                        [
                            "field" => self::PRESET_FIELD_PREFIX . "content_type",
                            "operator" => "==",
                            "value" => "image_with_cta"
                        ]
                    ]
                ],
                "wrapper" => [
                    "width" => "33",
                    "class" => "",
                    "id" => ""
                ],
                "choices" => [],
                "default_value" => ButtonComponent::getInstance()->getDefaultButtonSize(),
                "allow_null" => 0,
                "multiple" => 0,
                "ui" => 0,
                "ajax" => 0,
                "return_format" => "value",
                "placeholder" => ""
            ]
        ];
    }
}
