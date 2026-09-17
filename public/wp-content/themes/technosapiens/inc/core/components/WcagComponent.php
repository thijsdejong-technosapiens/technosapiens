<?php

namespace TechnoSapiens\Core;

use TechnoSapiens\Core;

/**
 * Class WcagComponent
 * @package TechnoSapiens\Core
 */
class WcagComponent extends Singleton {

    /**
     * WcagComponent constructor.
     */
    protected function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'initWcagAssets'], 9);
        add_action('admin_enqueue_scripts', [$this, 'initWcagAssets'], 9);
        add_action('enqueue_block_assets', [$this, 'initWcagAssetsInEditor'], 9);
    }

    /**
     * Initialize WCAG assets in block editor (including iframes)
     * @return void
     */
    public function initWcagAssetsInEditor(): void {
        if (is_admin()) {
            $this->initWcagAssets();
        }
    }

    /**
     * Initialize WCAG assets
     * @void void
     */
    public function initWcagAssets(): void {
        //enqueue component accessibility
        $componentAccessibilityCss = Enqueue::getWebpackAssetUrlByKey(WCP_MANIFEST_PATH, 'component-accessibility.css');
        if ($componentAccessibilityCss) wp_enqueue_style('component-accessibility', $componentAccessibilityCss);
    }

    /**
     * Get screen reader text for opening in a new tab
     * @return string
     */
    public static function getSrLinkOpensInNewTabHtml(): string {

        //default HTML for screen reader text
        $html = '<span class="ts-sr-only"> (' . __('opens in new tab', Core::TEXT_DOMAIN) . ')</span>';

        /**
         * Filter: ts_core_wcag_sr_opens_in_new_tab_html
         * Modify the HTML output for screen reader text regarding links that open in a new tab.
         *
         * @param string $html The default HTML string.
         * @return string The modified HTML string.
         *
         * @example
         * add_filter('ts_core_wcag_sr_opens_in_new_tab_html', function(string $html): string {
         *     //modify the $html as needed, for example, by adding additional information
         *     $html = '<span class="new-class">New text or icon</span>';
         *     return $html;
         * });
         */
        $html = apply_filters('ts_core_wcag_sr_opens_in_new_tab_html', $html);

        return $html;
    }

    /**
     * Get the aria-label for a given URL
     *
     * @param string $linkUrl
     * @param string $label
     * @param string $linkTarget
     * @param string $linkText
     * @param string $contextualPrefix
     * @return string
     */
    public static function getSrLabel(
        string $linkUrl,
        string $label = '',
        string $linkTarget = '_self',
        string $linkText = '',
        string $contextualPrefix = ''
    ): string {

        //handle empty linkUrl
        if (!$linkUrl) return '';

        //get “opens in new tab” text
        $openInNewTabText = '';
        if ($linkTarget === '_blank') $openInNewTabText = strip_tags(self::getSrLinkOpensInNewTabHtml());

        //scenario 1 – label is provided
        if ($label) return $label . $openInNewTabText;

        //scenario 2 – contextual prefix is provided
        if (!empty($contextualPrefix) && !empty($linkText)) {
            return $contextualPrefix . ' : ' . $linkText . $openInNewTabText;
        }

        //scenario 3 – hyperlink contains a telephone number
        if (str_starts_with($linkUrl, 'tel:')) {
            $phoneNumber = sanitize_text_field(substr($linkUrl, 4));
            if ($phoneNumber) {
                /* translators: %s: phone number */
                return sprintf(__('Call phone number %s', Core::TEXT_DOMAIN), $phoneNumber) . $openInNewTabText;
            }
        }

        //scenario 4 – hyperlink contains an e-mail address
        if (str_starts_with($linkUrl, 'mailto:')) {
            $email = sanitize_email(substr($linkUrl, 7));
            if ($email) {
                /* translators: %s: email address */
                return sprintf(__('Send an email to %s', Core::TEXT_DOMAIN), $email) . $openInNewTabText;
            }
        }

        //scenario 5 – hyperlink is internal and refers to a WP Post
        $siteHost = wp_parse_url(Link::getHomePageUrl(), PHP_URL_HOST);
        $targetHost = wp_parse_url($linkUrl, PHP_URL_HOST);
        if (!$targetHost || $targetHost === $siteHost) {
            $postId = url_to_postid($linkUrl);
            if ($postId && $post = get_post($postId)) {
                if (is_a($post, '\WP_Post')) {
                    /* translators: %s: post title */
                    return sprintf(__('Navigate to %s', Core::TEXT_DOMAIN), $post->post_title) . $openInNewTabText;
                }
            }
        }

        //scenario 6 – inspect linkText for phone / e-mail / URL patterns
        if ($linkText !== '') {

            $cleanText = trim(strip_tags($linkText));

            //6a – phone number in linkText
            if (preg_match('/^\+?\d[\d\-\s]{3,}$/', $cleanText)) {
                $phoneNumber = sanitize_text_field($cleanText);
                /* translators: %s: phone number */
                return sprintf(__('Call phone number %s', Core::TEXT_DOMAIN), $phoneNumber) . $openInNewTabText;
            }

            //6b – e-mail address in linkText
            if (is_email($cleanText)) {
                $email = sanitize_email($cleanText);
                /* translators: %s: email address */
                return sprintf(__('Send an email to %s', Core::TEXT_DOMAIN), $email) . $openInNewTabText;
            }

            //6c – linkText looks like a URL / domain
            if (preg_match('/^(https?:\/\/)?(www\.)?[^\s]+\.[^\s]{2,}$/i', $cleanText)) {

                //add a scheme so wp_parse_url works, then pull the host
                $urlForParsing = str_starts_with($cleanText, 'http') ? $cleanText : 'http://' . $cleanText;
                $host = wp_parse_url($urlForParsing, PHP_URL_HOST);

                if ($host) {
                    //strip leading www.
                    $host = preg_replace('/^www\./i', '', $host);
                    $humanized = $host;
                } else {
                    //fallback to previous behaviour
                    $humanized = Link::humanize($cleanText);
                    if ($humanized === '') $humanized = $cleanText;
                }

                /* translators: %s: destination label */
                return sprintf(__('Navigate to %s', Core::TEXT_DOMAIN), $humanized) . $openInNewTabText;
            }
        }

        //fallback – “Navigate to {URL}”
        $humanizedUrl = Link::humanize($linkUrl);

        //bail if we have nothing to work with
        if (!$humanizedUrl) return '';

        /* translators: %s: destination label */
        return sprintf(__('Navigate to %s', Core::TEXT_DOMAIN), $humanizedUrl) . $openInNewTabText;
    }
}