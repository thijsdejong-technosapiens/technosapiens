<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Singleton;

/**
 * Class RobotsTxtComponent
 * @package TechnoSapiens
 */
class RobotsTxtComponent extends Singleton {

    /**
     * Define allowed directives
     */
    const DIRECTIVE_NOINDEX = 'noindex';
    const DIRECTIVE_ALLOW = 'allow';
    const DIRECTIVE_DISALLOW = 'disallow';
    const DIRECTIVE_HOST = 'host';
    const DIRECTIVE_SITEMAP = 'sitemap';
    const DIRECTIVE_USERAGENT = 'user-agent';
    const DIRECTIVE_CRAWL_DELAY = 'crawl-delay';
    const DIRECTIVE_CLEAN_PARAM = 'clean-param';

    /**
     * RobotsTxtComponent constructor
     */
    protected function __construct() {
        add_filter('robots_txt', [$this, 'filterRobotsTxtOutput'], 999999, 2);
    }

    /**
     * Filter robots.txt file output
     * @param string $output
     * @param bool $public
     * @return string
     */
    public static function filterRobotsTxtOutput(string $output = '', bool $public = true): string {
        //reset robots.txt
        $output = '';

        //add Yoast sitemap URL
        if (!str_contains(strtolower($output), 'sitemap') && class_exists('WPSEO_Options')) {
            $output .= self::getSitemap();
        }

        //add disallows
        $output .= self::getDisallows();

        //add custom robots.txt text from settings
        if ($extraContent = get_field('extra_robotstxt_content', 'option')) {
            $output .= self::getExtraLines($extraContent);
        }

        //add branding
        $output .= self::getBranding();

        //remove invalid / duplicate lines from robots.txt
        return self::validateRobotsTxt($output);
    }


    /**
     * Validate and correct robots txt lines
     * @param string $output
     * @return string
     */
    public static function validateRobotsTxt(string $output = ''): string {
        $lines = explode(PHP_EOL, $output);
        $parsedLines = [];

        foreach ($lines as $line) {

            //maintain comments and line breaks
            if (str_starts_with($line, '#') || empty(trim($line))) {
                $parsedLines[] = $line;
                continue;
            }

            $allowedDirectives = [
                self::DIRECTIVE_NOINDEX,
                self::DIRECTIVE_ALLOW,
                self::DIRECTIVE_DISALLOW,
                self::DIRECTIVE_HOST,
                self::DIRECTIVE_SITEMAP,
                self::DIRECTIVE_USERAGENT,
                self::DIRECTIVE_CRAWL_DELAY,
                self::DIRECTIVE_CLEAN_PARAM,
            ];

            //split line in two parts separated by colon
            $lineParts = explode(':', $line, 2);
            if (count($lineParts) === 2 && in_array(strtolower($lineParts[0]), $allowedDirectives)) {
                $parsedLines[] = $line;
            }
        }

        return self::renderLines($parsedLines);
    }

    /**
     * Get sitemap robots text
     * @return string
     */
    public static function getSitemap(): string {
        $homeUrls = [];

        if (is_multisite()) {
            $sites = get_sites();
            foreach ($sites as $site) $homeUrls[] = get_home_url($site->blog_id);
        } else {
            $homeUrls[] = get_home_url();
        }

        $sitemapLines = ["# Sitemaps",];

        foreach ($homeUrls as $homeUrl) {
            $sitemapLines[] = "Sitemap: $homeUrl/sitemap_index.xml";
        }

        return self::renderLines($sitemapLines);
    }

    /**
     * Get disallowed robots.txt text
     * @return string
     */
    public static function getDisallows(): string {
        $disallowLines = [
            "",
            "# Default disallows",
            "User-Agent: *",
            "Disallow: /wp-admin/"
        ];

        return self::renderLines($disallowLines);
    }

    /**
     * Get extra lines text
     * @param string $extraLines
     * @return string
     */
    public static function getExtraLines(string $extraLines = ''): string {
        $disallowLines = [
            "",
            "# -- Start extra lines --",
            "",
            $extraLines,
            "",
            "# -- End extra lines --",
            "",
        ];

        return self::renderLines($disallowLines);
    }

    /**
     * Get Techno Sapiens branding
     * @return string
     */
    public static function getBranding(): string {
        $brandingLines = [
            "",
            "# ███",
            "# ███▌▄▄▄▄     ,▄▄▄▄,▄▄▄ ╓▄▄,▄▄▄▄╓   ╓▄▄,▄▄▄▄▄   ▄▄▄▄   ╓▄▄▄",
            "# █████████µ ,██████████ ║█████████▄ ▐█████████▄ `███▄ ,███⌐",
            "# ███▌  ███▌ ███▌   ▐███ ║██▌   ▐███ ▐██▌   └███  `███▄███└",
            "# ███µ  ███▌ ║███▄,▄████ ║███▄,▄████ ▐███▄,▄████   `█████─",
            "# ███µ  ███▌  ╙█████████ ║████████▀  ▐████████▀     j███⌐",
            "#              ,╓▄▄       ║██▌        ▐███    å██▌ @████─",
            "#              ║██▌       ╙▀▀▀        ╙▀▀▀    ╙▀▀▀ ╙▀▀└",
            "#              ║███████▄              ▐██▄███ @██▌ ▐███████Γ   ▄█████▄,  ▐██▄████▄",
            "#              ║███▀╙████             ▐████▀▀ ║██▌ ╙▀▀▀███▀  ]███▀▀▀███▌ ╞███▀╙▀███",
            "#              ║██▌  ▐███ ╔▄▄▄    ▄▄▄ ▐███    ║██▌   ╓███'   ╙╙╙└    ╙╙╙ ╞███  ▐███",
            "#              ║██▌  ▐███  █████████▀ ▐███    ║██▌  ▄██████▌             ╞███  ▐███",
            "#              ╙▀▀▀  └▀▀▀   ╙▀███▀▀'  ╙▀▀▀    ╙▀▀▀ ╙▀▀▀▀▀▀▀▀             └███  ▐███",
            "",
        ];

        return self::renderLines($brandingLines);
    }

    /**
     * Render robots.txt lines
     * @param array $lines
     * @return string
     */
    public static function renderLines(array $lines = []): string {
        $linesToRender = [];

        //prevent double line breaks
        foreach ($lines as $lineIndex => $line) {
            if (empty(trim($line)) && isset($lines[$lineIndex - 1]) && empty(trim($lines[$lineIndex - 1]))) continue;
            $linesToRender[] = "$line\n";
        }

        return implode('', $linesToRender);
    }
}
