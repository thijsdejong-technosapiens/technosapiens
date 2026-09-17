<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Link;
use TechnoSapiens\Core\Singleton;
use TechnoSapiens\Core\WcagComponent;

/**
 * Class SocialLinkComponent
 * @package TechnoSapiens
 */
class SocialLinkComponent extends Singleton {

    /**
     * Array of available social link types based on ACF field choices
     * @var array
     */
    protected array $socialLinkTypesAcf = [];

    /**
     * Array of social links data
     * @var array
     */
    protected array $socialLinks = [];

    /**
     * SocialLinkComponent constructor.
     */
    protected function __construct() {
        add_action('acf/init', function () {

            //get allowed social types from theme settings
            $socialLinksTypesAcf = [];
            $socialLinkOptionsAcf = get_field_object('field_6283a807e28cb') ?: false;
            if ($socialLinkOptionsAcf && isset($socialLinkOptionsAcf['choices'])) $socialLinksTypesAcf = $socialLinkOptionsAcf['choices'] ?: [];
            $this->setSocialLinkTypesAcf($socialLinksTypesAcf);

            //get and parse the social links once for usage in multiple contexts
            if (count($socialLinksTypesAcf) > 0) {
                $socialLinks = [];
                $rawSocialLinks = get_field('social_media_links', 'option') ?: [];
                if ($rawSocialLinks && is_array($rawSocialLinks) && count($rawSocialLinks) > 0) {
                    foreach ($rawSocialLinks as $rawSocialLink) {
                        $socialLinkItem = self::parseSocialLink($rawSocialLink);
                        if (!empty($socialLinkItem->link) && in_array($socialLinkItem->type, array_keys($socialLinksTypesAcf))) {
                            $socialLinks[] = $socialLinkItem;
                        }
                    }
                }
                $this->setSocialLinks($socialLinks);
            }
        });
    }

    /**
     * Set social link types ACF
     * @param array $socialLinkTypesAcf
     * @return void
     */
    public function setSocialLinkTypesAcf(array $socialLinkTypesAcf): void {
        $this->socialLinkTypesAcf = $socialLinkTypesAcf;
    }

    /**
     * Get social link types ACF
     * @return array
     */
    public function getSocialLinkTypesAcf(): array {
        return $this->socialLinkTypesAcf;
    }

    /**
     * Set social links
     * @param array $socialLinks
     * @return void
     */
    public function setSocialLinks(array $socialLinks): void {
        $this->socialLinks = $socialLinks;
    }

    /**
     * Get social links
     * @return array
     */
    public function getSocialLinks(): array {
        return $this->socialLinks;
    }

    /**
     * Get social link types from theme settings
     * @param array $data
     * @return \stdClass
     */
    public static function parseSocialLink(array $data): \stdClass {
        $socialLinkItem = new \stdClass();

        //handle type
        $type = $data['social_type'] ?? '';
        $socialLinkItem->label = isset($type['label']) ? $type['label'] : '';
        $socialLinkItem->type = isset($type['value']) ? $type['value'] : '';
        $socialLinkItem->link = $data['social_link'] ?? '';

        //handle phone link
        if ($socialLinkItem->type === 'phone') {
            $socialLinkItem->link = '';
            $phoneNumber = $data['social_phone'] ?? '';
            if ($phoneNumber) $socialLinkItem->link = 'tel:' . $phoneNumber;
        }

        //handle email link
        if ($socialLinkItem->type === 'email') {
            $socialLinkItem->link = '';
            $emailAddress = $data['social_email'] ?? '';
            if ($emailAddress) $socialLinkItem->link = 'mailto:' . $emailAddress;
        }

        //WCAG 2.1 AA - add accessible label to social media links
        $openInNewTabText = strip_tags(WcagComponent::getSrLinkOpensInNewTabHtml());
        $socialLinkItem->ariaLabel = sprintf(__('Visit our %s page', Theme::TEXT_DOMAIN), $socialLinkItem->label) . $openInNewTabText;
        if (in_array($socialLinkItem->type, ['phone', 'email'])) {
            if ($socialLinkItem->type === 'phone') $socialLinkItem->ariaLabel = __('Contact us by phone', Theme::TEXT_DOMAIN);
            if ($socialLinkItem->type === 'email') $socialLinkItem->ariaLabel = __('Contact us by email', Theme::TEXT_DOMAIN);
        }

        //parse link
        $socialLinkItem->link = Link::parseLink($socialLinkItem->link);

        return $socialLinkItem;
    }
}
