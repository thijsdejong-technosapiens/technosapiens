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

    /**
     * Get Google Maps API Key
     * @return string
     */
    public static function getGoogleApiKey(): string {
        return get_field('google_maps_api_key', 'option') ?: '';
    }

    /**
     * Get Mapbox Access Token
     * @return string
     */
    public static function getMapboxAccessToken(): string {
        return get_field('mapbox_access_token', 'option') ?: '';
    }

    /**
     * Get Google rating data from theme settings
     * @return \stdClass
     */
    public static function getGoogleRatingData(): \stdClass {
        $googleRatingData = new \stdClass();
        $googleRatingData->rating = (float) (get_field('google_rating', 'option') ?: 0);
        $googleRatingData->url = get_field('google_reviews_url', 'option') ?: '';
        $googleRatingData->isValid = $googleRatingData->rating > 0;

        return $googleRatingData;
    }

    /**
     * Get emergency phone number data
     * @return \stdClass
     */
    public static function getEmergencyPhoneNumberData(): \stdClass {
        $emergencyPhoneNumberData = new \stdClass();
        $emergencyPhoneNumberData->text = '';
        $emergencyPhoneNumberData->url = '';
        $emergencyPhoneNumberData->target = '_self';

        $link = get_field('emergency_phone_number', 'option');
        $emergencyPhoneNumberText = get_field('emergency_phone_number_kopie', 'option');
        
        if ($link) {
            $emergencyPhoneNumberData->text = $emergencyPhoneNumberText ?? $link['title'] ?? '';
            $parsedUrl = Link::parseLink($link['url'] ?? '') ?? '';
            $emergencyPhoneNumberData->url = str_starts_with($parsedUrl, 'tel:')
                ? substr($parsedUrl, 4)
                : $parsedUrl;
            $emergencyPhoneNumberData->target = $link['target'] ?? '_self';
        }

        $emergencyPhoneNumberData->isValid = !empty($emergencyPhoneNumberData->text) && !empty($emergencyPhoneNumberData->url);

        return $emergencyPhoneNumberData;
    }
}