<?php

namespace TechnoSapiens\HeaderModulePlugin;

use TechnoSapiens\Core\ButtonComponent;
use TechnoSapiens\Core\Formatting;
use TechnoSapiens\Core\Link;
use TechnoSapiens\Core\Enqueue;
use TechnoSapiens\Core\Partial;
use TechnoSapiens\Core\Singleton;
use TechnoSapiens\Core\PostType;
use TechnoSapiens\HeaderModulePlugin;

/**
 * Class MainHeroPreset
 * @package TechnoSapiens\HeaderModulePlugin
 */
class MainHeroPreset extends Singleton {

    /**
     * Set default value for preset key
     * @var string
     */
    protected string $presetKey = 'main-hero';

    /**
     * Set default value for preset name
     * @var string
     */
    protected string $presetName = '';

    /**
     * MainHeroPreset constructor.
     */
    protected function __construct() {

        //Define translatable name for preset
        $this->presetName = __("Main hero", HeaderModulePlugin::TEXT_DOMAIN);

        //apply button types, styles and sizes from ButtonComponent
        add_action('acf/init', function () {
            add_filter('acf/load_field/name=hm_button_type', [ButtonComponent::getInstance(), 'populateButtonTypeOptions']);
            add_filter('acf/load_field/name=hm_button_style', [ButtonComponent::getInstance(), 'populateButtonStyleOptions']);
            add_filter('acf/load_field/name=hm_button_size', [ButtonComponent::getInstance(), 'populateButtonSizeOptions']);
        });
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
     * Set default data for header module preset
     * @return \stdClass
     */
    public function getDefaultPresetData(): \stdClass {
        $defaultData = new \stdClass();

        //content
        $defaultData->headingText = '';
        $defaultData->headingTag = 'h1';
        $defaultData->subHeading = '';
        $defaultData->content = '';
        $defaultData->buttons = [];

        //advanced
        $defaultData->removeBottomSpacing = false;
        $defaultData->removeAutomaticHeading = false;

        return $defaultData;
    }

    /**
     * Get preset data
     * @param string $acfKey
     * @return \stdClass
     */
    public function getPresetData(string $acfKey): \stdClass {
        $data = self::getDefaultPresetData();

        //bail if no ACF key is given
        if (!$acfKey) return $data;

        //handle heading text
        $data->headingText = get_field('hm_heading_text', $acfKey) ?: '';
        preg_match_all('/\{\{(.*?)\}\}/m', $data->headingText, $matches, PREG_SET_ORDER, 0);
        if ($matches && count($matches) > 0) {
            foreach ($matches as $match) $data->headingText = str_replace($match[0], '<span class="hm-main-hero__highlight">' . $match[1] . '</span>', $data->headingText);
        }

        //handle option to remove bottom spacing
        $data->removeAutomaticHeading = get_field('hm_disable_automatic_heading', $acfKey) ?: false;

        //handle automatic heading value
        if (!$data->headingText && !$data->removeAutomaticHeading) {
            $queriedObject = get_queried_object();
            if (is_a($queriedObject, '\WP_Post')) $data->headingText = $queriedObject->post_title;
            elseif (is_a($queriedObject, '\WP_Term')) $data->headingText = $queriedObject->name;
            elseif (is_archive()) {
                $postTypeObject = get_post_type_object(PostType::getPostType());
                $archiveLabel = ($postTypeObject && isset($postTypeObject->labels->name)) ? $postTypeObject->labels->name : '';
                if ($archiveLabel) $data->headingText = $archiveLabel;
            }
        }

        //handle heading tag
        $data->headingTag = get_field('hm_heading_tag', $acfKey) ?: 'h1';

        //handle sub heading
        $data->subHeading = get_field('hm_sub_heading', $acfKey) ?: '';

        //handle content
        $data->content = Formatting::toHtml(get_field('hm_content', $acfKey) ?: '');

        //handle global buttons size
        $data->buttonSize = ButtonComponent::getInstance()->getParsedButtonSize(get_field('hm_button_size', $acfKey) ?: 'large');

        //handle buttons
        $rawButtons = get_field('hm_buttons', $acfKey) ?: [];
        if ($rawButtons && count($rawButtons) > 0) {
            foreach ($rawButtons as $rawButton) {
                $button = new \stdClass();
                $button->text = '';
                $button->link = '';
                $button->target = '_self';
                $button->rel = '';

                //handle button type
                $button->type = ButtonComponent::getInstance()->getParsedButtonType($rawButton['hm_button_type'] ?: 'primary');

                //handle button style
                $button->style = ButtonComponent::getInstance()->getParsedButtonStyle($rawButton['hm_button_style'] ?: 'filled');

                //parse button
                $buttonLink = $rawButton['hm_button_link'] ?: [];
                if ($buttonLink && is_array($buttonLink)) {
                    $button->text = $buttonLink['title'] ?: '';
                    $button->link = Link::parseLink($buttonLink['url']) ?: '';
                    $button->target = $buttonLink['target'] ?: '_self';
                    $button->rel = $button->target === '_blank' ? Link::getLinkRel($button->link, $button->target) : '';
                }

                if ($button->text && $button->link) $data->buttons[] = $button;

            }
        }

        /**
         * Add a filter for changing the header module data programmatically from the theme or other modules
         */
        $data = apply_filters('ts_header_module_filter_data', $data, self::getPresetKey(), $acfKey);

        return $data;
    }


    /**
     * Validate if the preset has valid data
     * @param \stdClass $data
     * @return bool
     */
    public function hasValidData(\stdClass $data): bool {
        return !empty($data->headingText) || !empty($data->subHeading) || !empty($data->content) || count($data->buttons) > 0;
    }

    /**
     * Get preset template
     * @param \stdClass $data
     * @return string
     */
    public function getTemplate(\stdClass $data): string {
        return Partial::render('hm-main-hero', ['data' => $data], false, WHMP_PLUGIN_PATH . '/presets/main-hero/partials/');
    }

    /**
     * Init frontend assets
     * @param \stdClass $data
     * @return void
     */
    public function initFrontendAssets(\stdClass $data): void {
        $presetCss = Enqueue::getWebpackAssetUrlByKey(MANIFEST_PATH, 'hm-main-hero.css');
        if ($presetCss) wp_enqueue_style('hm_' . str_replace('-', '_', $this->presetKey) . '_styles', $presetCss);
    }

    /**
     * Get icon sprite path for this block
     * @return string
     */
    public static function getIconSprite(): string {
        return Enqueue::getWebpackAssetUrlByKey(MANIFEST_PATH, 'main-hero-svg');
    }
}
