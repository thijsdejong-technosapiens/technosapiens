<?php

namespace TechnoSapiens\HeaderModulePlugin;

use TechnoSapiens\Core\ButtonComponent;
use TechnoSapiens\Core\Formatting;
use TechnoSapiens\Core\Image;
use TechnoSapiens\Core\Link;
use TechnoSapiens\Core\Enqueue;
use TechnoSapiens\Core\Partial;
use TechnoSapiens\Core\Singleton;
use TechnoSapiens\Core\PostType;
use TechnoSapiens\HeaderModulePlugin;

/**
 * Class ImageSimplePreset
 * @package TechnoSapiens\HeaderModulePlugin
 */
class ImageSimplePreset extends Singleton {

    /**
     * Set default value for preset key
     * @var string
     */
    protected string $presetKey = 'image-simple';

    /**
     * Set default value for preset name
     * @var string
     */
    protected string $presetName = '';

    /**
     * Define image sizes
     */
    const IMAGE_HM_SMALL = 'hm-image-simple-small';
    const IMAGE_HM_REGULAR = 'hm-image-simple-regular';
    const IMAGE_HM_LARGE = 'hm-image-simple-large';
    const IMAGE_HM_MOBILE_REGULAR = 'hm-image-simple-mobile-regular';
    const IMAGE_HM_MOBILE_REGULAR_X2 = 'hm-image-simple-mobile-regular-x2';
    const IMAGE_HM_MOBILE_LARGE = 'hm-image-simple-mobile';
    const IMAGE_HM_MOBILE_LARGE_X2 = 'hm-image-simple-mobile-x2';

    /**
     * ImageSimplePreset constructor.
     */
    protected function __construct() {

        //Define translatable name for preset
        $this->presetName = __("Full width image banner", HeaderModulePlugin::TEXT_DOMAIN);

        //make preset image sizes filterable
        $imageSizeSmall = apply_filters('ts_header_module_image_simple_small_size', [1728, 496]);
        $imageSizeRegular = apply_filters('ts_header_module_image_simple_regular_size', [1728, 496]);
        $imageSizeLarge = apply_filters('ts_header_module_image_simple_large_size', [1728, 784]);
        $imageSizeMobileRegular = apply_filters('ts_header_module_image_simple_mobile_regular_size', [900, 528]);
        $imageSizeMobileLarge = apply_filters('ts_header_module_image_simple_mobile_large_size', [900, 900]);
        $imageSizeMobileRegularX2 = [$imageSizeMobileRegular[0] * 2, $imageSizeMobileRegular[1] * 2];
        $imageSizeMobileLargeX2 = [$imageSizeMobileLarge[0] * 2, $imageSizeMobileLarge[1] * 2];

        //header module sizes
        add_image_size(self::IMAGE_HM_SMALL, $imageSizeSmall[0], $imageSizeSmall[1], true);
        add_image_size(self::IMAGE_HM_REGULAR, $imageSizeRegular[0], $imageSizeRegular[1], true);
        add_image_size(self::IMAGE_HM_LARGE, $imageSizeLarge[0], $imageSizeLarge[1], true);
        add_image_size(self::IMAGE_HM_MOBILE_REGULAR, $imageSizeMobileRegular[0], $imageSizeMobileRegular[1], true);
        add_image_size(self::IMAGE_HM_MOBILE_REGULAR_X2, $imageSizeMobileRegularX2[0], $imageSizeMobileRegularX2[1], true);
        add_image_size(self::IMAGE_HM_MOBILE_LARGE, $imageSizeMobileLarge[0], $imageSizeMobileLarge[1], true);
        add_image_size(self::IMAGE_HM_MOBILE_LARGE_X2, $imageSizeMobileLargeX2[0], $imageSizeMobileLargeX2[1], true);

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
        $defaultData->size = 'small';

        //handle media type
        $defaultData->mediaType = 'image'; // 'image' | 'video'

        //media - images
        $defaultData->imageDesktop = false;
        $defaultData->imageDesktopWidth = 0;
        $defaultData->imageDesktopHeight = 0;
        $defaultData->imageMobile = false;
        $defaultData->imageMobileWidth = 0;
        $defaultData->imageMobileHeight = 0;
        $defaultData->imageMobileX2 = false;
        $defaultData->imageMobileX2Width = 0;
        $defaultData->imageMobileX2Height = 0;

        //media - video
        $defaultData->videoUrlDesktop = '';
        $defaultData->videoUrlMobile = '';

        //media - misc
        $defaultData->imageFocusPoint = 'center center';

        //content
        $defaultData->headingText = '';
        $defaultData->headingTag = 'h1';
        $defaultData->subHeading = '';
        $defaultData->content = '';
        $defaultData->buttons = [];

        //slot
        $defaultData->slotType = 'none'; // 'none' | 'breadcrumbs' | 'static-section'
        $defaultData->slotStaticSection = '';

        //advanced
        $defaultData->removeBottomSpacing = false;
        $defaultData->removeAutomaticHeading = false;

        //image sizes
        $defaultData->imageHeightRegular = Image::dimensionsFromImageSize(self::IMAGE_HM_REGULAR)->height;
        $defaultData->imageHeightLarge = Image::dimensionsFromImageSize(self::IMAGE_HM_LARGE)->height;
        $defaultData->imageHeightMobileRegular = Image::dimensionsFromImageSize(self::IMAGE_HM_MOBILE_REGULAR)->height;
        $defaultData->imageHeightMobileLarge = Image::dimensionsFromImageSize(self::IMAGE_HM_MOBILE_LARGE)->height;

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

        //handle media type
        $mediaType = get_field('hm_media_type', $acfKey) ?: 'image'; // 'image' | 'video'
        if (in_array($mediaType, ['image', 'video'])) $data->mediaType = $mediaType;

        $pageImageDesktopId = get_field('hm_image_desktop', $acfKey);
        $pageImageMobileId = get_field('hm_image_mobile', $acfKey);
        $usesFallbackImages = $mediaType === 'image'
            && (!$pageImageDesktopId || !is_numeric($pageImageDesktopId))
            && (!$pageImageMobileId || !is_numeric($pageImageMobileId));

        //handle size
        $size = $usesFallbackImages ? 'regular' : (get_field('hm_size', $acfKey) ?: 'small');
        if (in_array($size, ['small', 'regular', 'large'])) $data->size = $size;

        //handle media type "image"
        if ($mediaType === 'image') {

            //handle image desktop
            $imageDesktopId = $pageImageDesktopId;
            if (!$imageDesktopId || !is_numeric($imageDesktopId)) {
                $imageDesktopId = get_field('hm_fallback_image_desktop', 'option') ?: get_field('hm_image_desktop', 'option');
            }
            if ($imageDesktopId && is_numeric($imageDesktopId)) {
                $sizeMapping = ['small' => self::IMAGE_HM_SMALL, 'regular' => self::IMAGE_HM_REGULAR, 'large' => self::IMAGE_HM_LARGE];
                $data->imageAlt = Image::altFromId($imageDesktopId);
                $data->imageDesktop = Image::urlFromId($imageDesktopId, $sizeMapping[$size]);
                $imageDimensions = Image::dimensionsFromId($imageDesktopId, $sizeMapping[$size]);
                if ($imageDimensions) {
                    $data->imageDesktopWidth = $imageDimensions->width;
                    $data->imageDesktopHeight = $imageDimensions->height;
                }
            }

            //handle image mobile
            $imageMobileId = $pageImageMobileId;
            if (!$imageMobileId || !is_numeric($imageMobileId)) {
                $imageMobileId = get_field('hm_fallback_image_mobile', 'option') ?: get_field('hm_image_mobile', 'option');
            }
            if ($imageMobileId && is_numeric($imageMobileId)) {
                $mobileSizeMapping = ['small' => self::IMAGE_HM_MOBILE_REGULAR, 'regular' => self::IMAGE_HM_MOBILE_REGULAR, 'large' => self::IMAGE_HM_MOBILE_LARGE];
                $mobileSizeMappingX2 = ['small' => self::IMAGE_HM_MOBILE_REGULAR_X2, 'regular' => self::IMAGE_HM_MOBILE_REGULAR_X2, 'large' => self::IMAGE_HM_MOBILE_LARGE_X2];

                $data->imageMobile = Image::urlFromId($imageMobileId, $mobileSizeMapping[$size]);
                $imageDimensions = Image::dimensionsFromId($imageMobileId, $mobileSizeMapping[$size]);
                if ($imageDimensions) {
                    $data->imageMobileWidth = $imageDimensions->width;
                    $data->imageMobileHeight = $imageDimensions->height;
                }

                $data->imageMobileX2 = Image::urlFromId($imageMobileId, $mobileSizeMappingX2[$size]);
                $imageDimensionsX2 = Image::dimensionsFromId($imageMobileId, $mobileSizeMappingX2[$size]);
                if ($imageDimensionsX2) {
                    $data->imageMobileX2Width = $imageDimensionsX2->width;
                    $data->imageMobileX2Height = $imageDimensionsX2->height;
                }
            }
        }

        //handle media type "video"
        if ($mediaType === 'video') {

            //handle video urls
            $data->videoUrlDesktop = get_field('hm_video_url_desktop', $acfKey) ?: '';
            $data->videoUrlMobile = get_field('hm_video_url_mobile', $acfKey) ?: '';
        }

        //get image focus point
        $data->imageFocusPoint = get_field('hm_focus_point', $acfKey) ?? 'center center'; // 'left top' | 'left center' | 'left bottom' | 'center top' | 'center center' | 'center bottom' | 'right top' | 'right|centered'

        //handle heading text
        if ($usesFallbackImages) {
            $data->headingText = '';
            $data->removeAutomaticHeading = false;
        } else {
            $data->headingText = get_field('hm_heading_text', $acfKey) ?: '';
            preg_match_all('/\{\{(.*?)\}\}/m', $data->headingText, $matches, PREG_SET_ORDER, 0);
            if ($matches && count($matches) > 0) {
                foreach ($matches as $match) $data->headingText = str_replace($match[0], '<span class="hm-image-simple__highlight">' . $match[1] . '</span>', $data->headingText);
            }

            //handle option to remove bottom spacing
            $data->removeAutomaticHeading = get_field('hm_disable_automatic_heading', $acfKey) ?: false;
        }

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
        if ($usesFallbackImages) {
            $data->subHeading = '';
        } else {
            $data->subHeading = get_field('hm_sub_heading', $acfKey) ?: '';
        }

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

        //handle slot content type
        if ($usesFallbackImages) {
            $data->slotType = 'breadcrumbs';
        } else {
            $slotType = get_field('hm_slot_content', $acfKey) ?: 'none';
            if (in_array($slotType, ['none', 'breadcrumbs', 'static-section'])) $data->slotType = $slotType;
        }

        //handle static section slot
        if (!$usesFallbackImages && $data->slotType === 'static-section') {
            $staticSection = get_field('hm_slot_static_section', $acfKey);
            if ($staticSection instanceof \WP_Post && class_exists('\TechnoSapiens\StaticBlockPostType')) {
                $data->slotStaticSection = \TechnoSapiens\StaticBlockPostType::getBlockById($staticSection->ID);
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
        $hasValidImage = $data->mediaType === 'image' && ($data->imageDesktop && $data->imageMobile);
        $hasValidVideo = $data->mediaType === 'video' && ($data->videoUrlDesktop && $data->videoUrlMobile);
        return $hasValidImage || $hasValidVideo;
    }

    /**
     * Get preset template
     * @param \stdClass $data
     * @return string
     */
    public function getTemplate(\stdClass $data): string {
        return Partial::render('hm-image-simple', ['data' => $data], false, WHMP_PLUGIN_PATH . '/presets/image-simple/partials/');
    }

    /**
     * Init frontend assets
     * @param \stdClass $data
     * @return void
     */
    public function initFrontendAssets(\stdClass $data): void {
        $presetCss = Enqueue::getWebpackAssetUrlByKey(MANIFEST_PATH, 'hm-image-simple.css');
        if ($presetCss) wp_enqueue_style('hm_' . str_replace('-', '_', $this->presetKey) . '_styles', $presetCss);
    }

    /**
     * Get icon sprite path for this block
     * @return string
     */
    public static function getIconSprite(): string {
        return Enqueue::getWebpackAssetUrlByKey(MANIFEST_PATH, 'image-simple-svg');
    }
}
