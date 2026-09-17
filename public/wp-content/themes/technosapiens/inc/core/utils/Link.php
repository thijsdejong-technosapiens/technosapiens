<?php

namespace TechnoSapiens\Core;

/**
 * class Link
 * @package TechnoSapiens\Core
 */
class Link {

    /**
     * Get home page URL
     * @return string
     */
    public static function getHomePageUrl(): string {
        return function_exists('pll_home_url') ? pll_home_url() : get_home_url();
    }

    /**
     * Parse link
     * Adds a trailing slash when needed
     * @param string $link
     * @return string
     */
    public static function parseLink(string $link = ''): string {

        //trim the link to remove any unnecessary spaces
        $link = trim($link);

        //if the link is empty, return an empty string
        if (empty($link)) return '';

        //if external link; leave it as is
        if (self::linkIsExternal($link, true)) {
            return $link;
        }

        //if the link is not a valid URL, and does not start with '/', '#' (hash-only), 'tel:' or 'mailto:', return an empty string
        if (!str_starts_with($link, '/') &&
            !str_starts_with($link, '#') &&
            !str_starts_with($link, 'tel:') &&
            !str_starts_with($link, 'mailto:') &&
            !filter_var($link, FILTER_VALIDATE_URL)) {
            return '';
        }

        //handle protocol-relative URLs (e.g., //example.com)
        if (str_starts_with($link, '//')) $link = 'https:' . $link;

        //make link absolute if it starts with a single slash (relative path)
        if (str_starts_with($link, '/') && !str_starts_with($link, '//')) $link = self::getHomePageUrl() . $link;

        //handle special cases: hash, tel, mailto, query parameters, and file extensions
        if (str_contains($link, '#') ||
            str_starts_with($link, 'tel:') ||
            str_starts_with($link, 'mailto:') ||
            str_contains($link, '?') ||
            str_contains(substr($link, -5), '.')) {
            return untrailingslashit($link);
        }

        //handle links to the base domain (remove trailing slash if the URL matches)
        $baseUrlArray = parse_url($link);
        if ($baseUrlArray && isset($baseUrlArray['scheme']) && isset($baseUrlArray['host'])) {
            $baseUrl = $baseUrlArray['scheme'] . '://' . $baseUrlArray['host'];
            if (untrailingslashit($link) === untrailingslashit($baseUrl)) {
                return untrailingslashit($link);
            }
        }

        //if the link is a valid URL, add a trailing slash if it does not have one
        return trailingslashit($link);
    }

    /**
     * Determine if a given link string is external or not
     * @param string $link
     * @param bool $skipParse
     * @return bool
     */
    public static function linkIsExternal(string $link, bool $skipParse = false): bool {

        //parse the link
        if (!$skipParse) $link = self::parseLink($link);

        //handle cases where the link is empty after parsing
        if (empty($link)) return false;

        //parse the URL and check if the host is the same as the home page host
        $parsedLink = parse_url($link);
        if ($parsedLink && isset($parsedLink['scheme']) && in_array($parsedLink['scheme'], ['http', 'https'])) {
            $host = $parsedLink['host'];
            $homeHost = parse_url(self::getHomePageUrl(), PHP_URL_HOST);
            return $host !== $homeHost;
        }

        return false;
    }

    /**
     * Get the rel attribute for a link
     * @param string $link
     * @param string $target
     * @param string $defaultRel
     * @return string
     */
    public static function getLinkRel(string $link, string $target = '_self', string $defaultRel = ''): string {

        //parse the default rel to an array
        $relArray = $defaultRel ? explode(' ', $defaultRel) : [];

        //add "noopener" and "noreferrer" whenever the link opens in a new tab
        if (trim(strtolower($target)) === '_blank') {
            if (!in_array('noopener', $relArray)) $relArray[] = 'noopener';
            if (!in_array('noreferrer', $relArray)) $relArray[] = 'noreferrer';
        }

        //remove empty values and duplicates from array
        $relArray = array_unique(array_filter($relArray));

        return count($relArray) > 0 ? implode(' ', $relArray) : '';
    }

    /**
     * Convert a URL to a human-readable slug for screen readers
     * @param string $url
     * @return string
     */
    public static function humanize(string $url): string {
        if (!$url) return '';

        //hash-only anchors
        if (str_starts_with($url, '#')) {
            $anchor = ltrim($url, '#');
            $anchor = sanitize_title_with_dashes($anchor);
            return ucfirst(str_replace('-', ' ', $anchor));
        }

        //parse the URL
        $parts = wp_parse_url($url);

        //prefer last path segment; fall back to host
        if (!empty($parts['path'])) {
            $filename = rawurldecode(pathinfo($parts['path'], PATHINFO_FILENAME));
            $filename = sanitize_title_with_dashes($filename);
            return ucfirst(str_replace('-', ' ', $filename));
        }

        if (!empty($parts['host'])) {
            return strtolower(preg_replace('/^www\./', '', $parts['host']));
        }

        return '';
    }
}