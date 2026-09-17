<?php

namespace TechnoSapiens\Core;

/**
 * Class YouTube
 * @package TechnoSapiens\Core
 */
class YouTube {

    /**
     * YouTube short URL regex
     * @var string
     */
    const SHORT_URL_REGEX = '/youtu.be\/([a-zA-Z0-9_-]+)\??/i';

    /**
     * YouTube long URL regex
     * @var string
     */
    const LONG_URL_REGEX = '/youtube.com\/((?:embed)|(?:watch))((?:\?v\=)|(?:\/))([\w\-]+)/i';

    /**
     * YouTube no cookie URL regex
     * @var string
     */
    const NO_COOKIE_URL_REGEX = '/youtube-nocookie.com\/embed\/([a-zA-Z0-9_-]+)/i';

    /**
     * YouTube shorts URL regex
     * @var string
     */
    const SHORTS_URL_REGEX = '/youtube.com\/shorts\/([a-zA-Z0-9_-]+)/i';

    /**
     * @var array Caches previously computed IDs to avoid duplicate regex calls
     */
    private static $cache = [];

    /**
     * Extract YouTube ID from a URL
     * @param string $url
     * @return string
     */
    public static function getYouTubeIdByUrl(string $url): string {

        //validate URL
        if (empty($url)) return '';

        //trim the given URL
        $url = trim($url);

        //check cache
        if (isset(self::$cache[$url])) return self::$cache[$url];

        //set default return value
        $youTubeId = '';

        //scenario 1 - short YouTube URL syntax - https://youtu.be/xxxxxx
        if (preg_match(self::SHORT_URL_REGEX, $url, $matches)) {
            $youTubeId = $matches[count($matches) - 1];
        }

        //scenario 2 - long YouTube URL syntax - https://www.youtube.com/watch?v=xxxxxx or /embed/xxxxxx
        if (!$youTubeId && preg_match(self::LONG_URL_REGEX, $url, $matches)) {
            $youTubeId = $matches[count($matches) - 1];
        }

        //scenario 3 - nocookie URL syntax - https://www.youtube-nocookie.com/embed/xxxxxx
        if (!$youTubeId && preg_match(self::NO_COOKIE_URL_REGEX, $url, $matches)) {
            $youTubeId = $matches[count($matches) - 1];
        }

        //scenario 4 - shorts URL syntax - https://www.youtube.com/shorts/xxxxxx
        if (!$youTubeId && preg_match(self::SHORTS_URL_REGEX, $url, $matches)) {
            $youTubeId = $matches[count($matches) - 1];
        }

        //cache the result before returning
        self::$cache[$url] = $youTubeId;

        return $youTubeId;
    }
}