<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Image;
use TechnoSapiens\Core\Link;
use TechnoSapiens\Core\Singleton;

/**
 * Class GFMailSettings
 * @package TechnoSapiens
 */
class GFMailSettings extends Singleton {
    /**
     * Define menu slug
     */
    const MENU_SLUG = 'gf-mail-settings';

    /**
     * GFMailSettings constructor.
     */
    protected function __construct() {
        acf_add_options_sub_page([
            'page_title' => __("Mail templates", Theme::TEXT_DOMAIN),
            'menu_title' => __("Mail templates", Theme::TEXT_DOMAIN),
            'menu_slug' => self::MENU_SLUG,
            'post_id' => self::MENU_SLUG,
            'parent_slug' => 'gf_edit_forms'
        ]);
    }

    /**
     * Get social links array
     * @return array
     */
    public static function getGfSocialLinks(): array {
        $socialLinks = [];
        $allowedTypes = [
            'phone',
            'email',
            'social_media'
        ];

        $rawSocialLinks = get_field('gf_social_media_links', 'gf-mail-settings') ?: [];
        if ($rawSocialLinks && is_array($rawSocialLinks) && count($rawSocialLinks)) {
            foreach ($rawSocialLinks as $rawSocialLink) {
                $socialLinkItem = new \stdClass();

                //handle type
                $type = $rawSocialLink['gf_social_type'] ?? '';
                $socialLinkItem->label = isset($type['label']) ? $type['label'] : '';
                $socialLinkItem->type = isset($type['value']) ? $type['value'] : '';
                $socialLinkItem->link = $rawSocialLink['gf_social_link'] ?? '';

                //handle icon
                $imageId = $rawSocialLink['gf_social_image'] ?: '';
                if ($imageId) $socialLinkItem->icon = Image::urlFromId($imageId);

                //handle phone link
                if ($socialLinkItem->type === 'phone') {
                    $socialLinkItem->link = '';
                    $phoneNumber = $rawSocialLink['gf_social_phone'] ?? '';
                    if ($phoneNumber) $socialLinkItem->link = 'tel:' . $phoneNumber;
                }

                //handle email link
                if ($socialLinkItem->type === 'email') {
                    $socialLinkItem->link = '';
                    $emailAddress = $rawSocialLink['gf_social_email'] ?? '';
                    if ($emailAddress) $socialLinkItem->link = 'mailto:' . $emailAddress;
                }

                //parse link
                $socialLinkItem->link = Link::parseLink($socialLinkItem->link);

                //add link to array if valid
                if (!empty($socialLinkItem->link) && !empty($socialLinkItem->type) && in_array($socialLinkItem->type, $allowedTypes)) $socialLinks[] = $socialLinkItem;
            }
        }

        return $socialLinks;
    }
}