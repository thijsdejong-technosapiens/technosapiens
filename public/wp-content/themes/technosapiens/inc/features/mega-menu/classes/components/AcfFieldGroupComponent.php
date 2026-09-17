<?php

namespace TechnoSapiens\MegaMenuPlugin;

use TechnoSapiens\Core\Singleton;
use TechnoSapiens\MegaMenuPlugin;

/**
 * class AcfFieldGroupComponent
 * @package TechnoSapiens\MegaMenuPlugin
 */
class AcfFieldGroupComponent extends Singleton {

    /**
     * Name of the ACF group for the mega menu module
     * @var string
     */
    const MM_GROUP_NAME = 'mm-module';

    /**
     * Set default value for fields
     * @var array
     */
    public array $fields = [];

    /**
     * AcfFieldGroupComponent constructor
     */
    protected function __construct() {
        //register ACF field group for nav menu items linking to a Mega Menu post
        add_action('acf/init', [$this, 'registerMegaMenuLinkAcfFieldGroup']);
    }

    /**
     * Get active preset options
     * @return array
     */
    public function getActivePresetOptions(): array {
        $activePresetOptions = [];
        foreach (MegaMenuPlugin::getEnabledPresetClasses() as $presetClass) {
            $fullPresetClass = "TechnoSapiens\\MegaMenuPlugin\\" . $presetClass;
            if (class_exists($fullPresetClass)) {
                $preset = $fullPresetClass::getInstance();
                $activePresetOptions[$preset->getPresetKey()] = $preset->getPresetName();
            }
        }

        return $activePresetOptions;
    }

    /**
     * Get active preset fields
     * @return array
     */
    public function getActivePresetFields(): array {
        $activePresetFields = [];
        foreach (MegaMenuPlugin::getEnabledPresetClasses() as $presetClass) {
            $fullPresetClass = "TechnoSapiens\\MegaMenuPlugin\\" . $presetClass;
            if (class_exists($fullPresetClass)) {
                $preset = $fullPresetClass::getInstance();
                $presetFields = $preset->getPresetFields();
                if (count($presetFields) > 0) $activePresetFields = array_merge($activePresetFields, $presetFields);
            }
        }

        return $activePresetFields;
    }

    /**
     * Add a field group
     * @return void
     */
    public function addFieldGroup(): void {
        if (function_exists('acf_add_local_field_group')) {

            //define location for mega-menu post type
            $locations = [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => \TechnoSapiens\MegaMenuPlugin\MegaMenuPostType::TYPE
                    ]
                ]
            ];

            //get mm preset options
            $presetOptions = $this->getActivePresetOptions();

            //get active mm preset fields
            $presetFields = $this->getActivePresetFields();

            if (count($presetOptions) > 0 && count($presetFields) > 0) {

                //dynamically add preset select field
                $this->fields[] = [
                    'key' => 'mega-menu-preset',
                    'label' => __('Preset', MegaMenuPlugin::TEXT_DOMAIN),
                    'name' => 'mm_preset',
                    'type' => 'select',
                    'instructions' => __('Choose one of the available mega menu presets.', MegaMenuPlugin::TEXT_DOMAIN),
                    'required' => 1,
                    'conditional_logic' => [],
                    'wrapper' => [
                        'width' => '100',
                        'class' => '',
                        'id' => '',
                    ],
                    'choices' => $presetOptions,
                    'default_value' => array_key_first($presetOptions),
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 0,
                    'return_format' => 'value',
                    'ajax' => 0,
                    'placeholder' => ''
                ];

                //add the field group
                acf_add_local_field_group([
                    'key' => self::MM_GROUP_NAME,
                    'title' => __('Configuration', MegaMenuPlugin::TEXT_DOMAIN),
                    'fields' => array_merge($this->fields, $presetFields),
                    "location" => $locations,
                    "menu_order" => 0,
                    "position" => "normal",
                    "style" => "default",
                    "label_placement" => "top",
                    "instruction_placement" => "label",
                    "hide_on_screen" => "",
                    "active" => true,
                    "description" => "",
                    "show_in_rest" => 0,
                    "modified" => time()
                ]);
            }
        }
    }

    /**
     * Register the Mega Menu Link ACF field group for nav menu items
     * @return void
     */
    public function registerMegaMenuLinkAcfFieldGroup(): void {
        if (function_exists('acf_add_local_field_group')) {
            acf_add_local_field_group([
                'key' => 'group_mega_menu_link',
                'title' => __('Mega menu link', MegaMenuPlugin::TEXT_DOMAIN),
                'fields' => [
                    [
                        'key' => 'mm_post_link',
                        'label' => __('Link to mega menu post', MegaMenuPlugin::TEXT_DOMAIN),
                        'name' => 'mm_post_link',
                        'type' => 'post_object',
                        'post_type' => [MegaMenuPostType::TYPE],
                        'return_format' => 'id',
                        'ui' => 1,
                        'allow_null' => 1,
                        'multiple' => 0,
                        'instructions' => __('Select a mega menu post to connect to this menu item. When this menu item is hovered or clicked, the selected mega menu will appear.', MegaMenuPlugin::TEXT_DOMAIN),
                    ],
                ],
                'location' => [
                    [
                        [
                            'param' => 'nav_menu_item',
                            'operator' => '==',
                            'value' => 'location/menu-primary',
                        ],
                    ],
                ],
            ]);
        }
    }
}
