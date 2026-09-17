<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Singleton;

/**
 * Class PolylangComponent
 * @package TechnoSapiens
 */
class PolylangComponent extends Singleton {

    /**
     * PolylangComponent component
     */
    protected function __construct() {

        //filter translatable post types
        add_filter('pll_get_post_types', [$this, 'filterPllPostTypes'], 10, 2);

        //handle front-end
        if (!is_admin()) {

            //tell ACF which (Polylang) language is active for loading correct settings
            add_filter('acf/settings/current_language', function () {
                return !defined('REST_API') ? pll_current_language('locale') : \get_locale();
            });
        }

        //handle CMS
        if (is_admin()) {

            //redirect the content manager to the first language when they visit a "All Languages" settings page
            add_filter('acf/options_page/submitbox_before_major_actions', [$this, 'disableAllLanguagesSettingsPage'], 20, 1);

            //add the name of the current Polylang language after the ACF options page title
            add_filter('acf/get_options_page', [$this, 'filterAcfOptionsPageSettings'], 10, 1);

            //add the flag of the current Polylang language before the ACF options page title
            add_action('admin_head', function () {
                $currentLangCode = pll_current_language('slug');
                $currentLangConfiguration = self::getCurrentLanguageByLocale($currentLangCode);
                if ($currentLangCode && $currentLangConfiguration && $currentLangConfiguration['flag']): ?>
                    <style>
                        .acf-settings-wrap h1 {
                            display: flex;
                            align-items: center;
                            gap: .375em;
                        }

                        .acf-settings-wrap h1:before {
                            content: "";
                            display: inline-block;
                            background-image: url("<?php echo $currentLangConfiguration['flag']; ?>");
                            background-repeat: no-repeat;
                            background-size: contain;
                            background-position: center center;
                            width: 1em;
                            height: 1em;
                        }
                    </style>
                <?php endif;
            });
        }
    }

    /**
     * Add language in the ACF options page title
     * @param array $page
     * @return array
     */
    public static function filterAcfOptionsPageSettings(array $page): array {
        $currentLangCode = pll_current_language('slug');
        $currentLangConfiguration = self::getCurrentLanguageByLocale($currentLangCode);

        if ($currentLangCode && $currentLangConfiguration) {
            $page['page_title'] = $page['page_title'] . ' (' . $currentLangConfiguration['name'] . ')';
        }

        return $page;
    }

    /**
     * Make custom post types translatable in Polylang
     * @param array $postTypes
     * @param bool $isSettings
     * @return array
     */
    public static function filterPllPostTypes(array $postTypes, bool $isSettings): array {
        if (!$isSettings) $postTypes[StaticBlockPostType::TYPE] = StaticBlockPostType::TYPE;
        return $postTypes;
    }

    /**
     * Get current language by locale
     * @param string $locale
     * @return array|bool
     */
    public static function getCurrentLanguageByLocale(string $langCode): array|bool {
        $languages = pll_the_languages(['raw' => 1]);
        foreach ($languages as $languageCode => $language) {
            if ($languageCode === $langCode) {
                return $language;
            }
        }
        return false;
    }

    /**
     * Get all languages
     *
     * @return array
     */
    public static function getAllLanguages(): array {
        return pll_the_languages([
            'raw' => 1,
            'hide_current' => true,
            'display_names_as' => 'slug',
        ]);
    }

    /**
     * Redirect the content manager to the first language when they visit a "All Languages" settings page
     * @param array $page
     * @return array
     */
    public static function disableAllLanguagesSettingsPage(array $page): array {
        if (pll_current_language('name') === false) {
            $pllLanguages = pll_languages_list();
            if (count($pllLanguages) > 0) {
                $firstLanguage = $pllLanguages[0];
                if ($firstLanguage) {

                    //get current cms page path
                    if ($path = strtok($_SERVER['REQUEST_URI'], '?')) {

                        //maintain existing query params but update the lang parameter
                        $queryParams = $_GET ?? [];
                        $queryParams['lang'] = $firstLanguage;

                        //redirect to first Polylang language
                        wp_redirect($path . '?' . http_build_query($queryParams));
                    }
                }
            }
        }

        return $page;
    }
}