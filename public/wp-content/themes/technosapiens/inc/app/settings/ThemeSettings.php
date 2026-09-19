<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Image;
use TechnoSapiens\Core\Link;
use TechnoSapiens\Core\Log;
use TechnoSapiens\Core\Singleton;

/**
 * Class ThemeSettings
 * @package TechnoSapiens
 */
class ThemeSettings extends Singleton {
    /**
     * Define menu slug
     */
    const MENU_SLUG = 'theme-settings';

    /**
     * ThemeSettings constructor.
     */
    protected function __construct() {
        if (function_exists('acf_add_options_page')) {

            acf_add_options_page([
                'page_title' => __("Theme settings", Theme::TEXT_DOMAIN),
                'menu_title' => __("Theme settings", Theme::TEXT_DOMAIN),
                'redirect' => false,
                'menu_slug' => self::MENU_SLUG,
            ]);
        }
    }

    /**
     * Get logo link from settings
     * @return string
     */
    public static function getLogo(): string {
        $logoImage = '';
        $logoId = get_field('logo_image', 'option') ?? '';
        if ($logoId) $logoImage = Image::urlFromId($logoId);

        return $logoImage ?? '';
    }

    /**
     * Get login screen background image URL from settings.
     * @return string
     */
    public static function getLoginBackgroundUrl(): string {
        $imageId = get_field('login_background_image', 'option') ?? '';
        if (!$imageId) {
            return '';
        }

        return Image::urlFromId($imageId) ?: '';
    }

    /**
     * Get header button data
     * @return \stdClass
     */
    public static function getHeaderButtonData(): \stdClass {
        //set default value
        $headerButtonData = new \stdClass();
        $headerButtonData->enabled = get_field('enable_header_button', 'option') ?? false;
        $headerButtonData->text = '';
        $headerButtonData->url = '';
        $headerButtonData->target = '_self';

        //get data from settings
        if ($headerButtonData->enabled) {
            $link = get_field('header_button_link', 'option');
            if ($link) {
                $headerButtonData->text = $link['title'] ?? '';
                $headerButtonData->url = Link::parseLink($link['url']) ?? '';
                $headerButtonData->target = $link['target'] ?? '';
            }
        }

        //check if data is valid
        $headerButtonData->isValid = $headerButtonData->enabled && !empty($headerButtonData->text) && !empty($headerButtonData->url);

        return $headerButtonData;
    }
}