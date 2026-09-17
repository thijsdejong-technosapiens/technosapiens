<?php

namespace TechnoSapiens\Core;

/**
 * Class Image
 *
 * Provides utilities for images, such as retrieving URLs/alt/dimensions from the Media Library,
 * plus a `render()` method to produce <img> or <picture> markup. It can also generate BEM classes
 * if you pass 'blockClass', and optionally add a `decoding` attribute to <img>.
 *
 * @package TechnoSapiens\Core
 */
class Image {

    /**
     * Get URL from a given attachment ID.
     *
     * @param int|string $attachmentId Attachment ID from the Media Library.
     * @param string $size WP registered image size (default "full").
     * @return string|false Returns the absolute URL if found, otherwise false.
     *
     * @example
     * <code>
     * $url = Image::urlFromId(123, 'large');
     * </code>
     */
    public static function urlFromId(int|string $attachmentId, string $size = 'full'): string|false {
        if (!$attachmentId) return false;
        $attachment = wp_get_attachment_image_src($attachmentId, $size);
        return $attachment ? $attachment[0] : false;
    }

    /**
     * Get alt text for a given attachment ID.
     *
     * @param int|string $attachmentId The Media Library attachment ID.
     * @return string Returns an alt string. If none is set, falls back to the post title (sanitized).
     *
     * @example
     * <code>
     * $alt = Image::altFromId(123);
     * </code>
     */
    public static function altFromId(int|string $attachmentId): string {
        if (!$attachmentId) return '';

        //get the alt text from the attachment meta
        $alt = get_post_meta($attachmentId, '_wp_attachment_image_alt', true);

        //if no alt text is set, fall back to the image post title
        if (!$alt) $alt = get_the_title($attachmentId);

        //convert dash/underscore to spaces and uppercase first letter
        if ($alt) $alt = ucfirst(str_replace(['-', '_'], ' ', $alt));

        //escape alt tag for usage in attributes
        $alt = esc_attr($alt);

        return $alt;
    }

    /**
     * Get image dimensions by attachment ID and size.
     *
     * @param int|string $attachmentId Attachment ID.
     * @param string $size WP image size key (default "full").
     * @return \stdClass|false Returns an object with properties ->width and ->height, or false on failure.
     *
     * @example
     * <code>
     * $dims = Image::dimensionsFromId(123, 'thumbnail');
     * if ($dims) {
     *     echo "Width: {$dims->width}, Height: {$dims->height}";
     * }
     * </code>
     */
    public static function dimensionsFromId(int|string $attachmentId, string $size = 'full'): \stdClass|false {
        if (!$attachmentId) return false;
        $attachment = wp_get_attachment_image_src($attachmentId, $size);
        if ($attachment) {
            $dimensions = new \stdClass();
            $dimensions->width = $attachment[1];
            $dimensions->height = $attachment[2];
            return $dimensions;
        }
        return false;
    }

    /**
     * Get image dimensions based on a registered WP image size from global $_wp_additional_image_sizes.
     *
     * @param string $size WP registered image size (default "full").
     * @return \stdClass|false Returns an object with properties ->width and ->height, or false if not found.
     *
     * @example
     * <code>
     * $dims = Image::dimensionsFromImageSize('medium');
     * if ($dims) {
     *     echo "Width: {$dims->width}, Height: {$dims->height}";
     * }
     * </code>
     */
    public static function dimensionsFromImageSize(string $size = 'full'): \stdClass|false {
        global $_wp_additional_image_sizes;
        if ($_wp_additional_image_sizes && isset($_wp_additional_image_sizes[$size])) {
            $dimensions = new \stdClass();
            $dimensions->width = $_wp_additional_image_sizes[$size]['width'] ?? 0;
            $dimensions->height = $_wp_additional_image_sizes[$size]['height'] ?? 0;
            return $dimensions;
        }
        return false;
    }

    /**
     * Get an array of image sizes by merging WordPress intermediate sizes with additional sizes.
     *
     * @return array Returns an array of image size data keyed by size name.
     *
     * @example
     * <code>
     * $sizes = Image::getImageSizes();
     * print_r($sizes);
     * </code>
     */
    public static function getImageSizes(): array {
        global $_wp_additional_image_sizes;
        $imageSizes = [];
        $defaultImageSizes = get_intermediate_image_sizes();

        // Pull the core WP sizes from options
        foreach ($defaultImageSizes as $imageSize) {
            $imageSizes[$imageSize]['width'] = (int)get_option("{$imageSize}_size_w");
            $imageSizes[$imageSize]['height'] = (int)get_option("{$imageSize}_size_h");
            $crop = get_option("{$imageSize}_crop");
            $imageSizes[$imageSize]['crop'] = $crop ? $crop : false;
        }

        // Merge any additional custom sizes
        if ($_wp_additional_image_sizes && count($_wp_additional_image_sizes)) {
            $imageSizes = array_merge($imageSizes, $_wp_additional_image_sizes);
        }

        return $imageSizes;
    }

    /**
     * Renders an image (<img>) or a <picture> element with multiple breakpoints.
     *
     * When only one source is provided, a single <img> is output.
     * When multiple sources are provided, a <picture> element is output with each <source>,
     * and the last item is used as a fallback <img>.
     *
     * Additional parameters:
     *  - 'style': Pass an associative array of CSS properties or a CSS string.
     *  - 'fetchpriority': By default, we auto-append `fetchpriority="high"` when `'lazy' => false`.
     *                     Pass something else (e.g. 'low' or 'auto') to override, or
     *                     pass false (or an empty string) to skip it entirely.
     *
     * @param array $args {
     * @type array[] $sources Each source can include:
     *       - 'id' (int|string): WP attachment ID (optional if 'url' is provided).
     *       - 'url' (string): Direct image URL (optional if using 'id').
     *       - 'size' (string): WP image size for 1x (defaults to "full" if not provided).
     *       - 'size2x' (string): WP image size for 2x.
     *       - 'url2x' (string): Direct URL for 2x image (optional).
     *       - 'media' (string): e.g. "(min-width:768px)" for a <source> tag.
     *       - 'width' (int|string): Manually override width.
     *       - 'height' (int|string): Manually override height.
     *       - 'alt' (string|null): Alt override; if null, falls back to altFromId().
     *       - 'class' (string): Additional classes for <img> or <picture>.
     *       - 'style' (array|string): Inline style (associative array or CSS string).
     *       - 'role' (string): e.g. "presentation".
     *       - 'draggable' (bool|null): Whether the element is draggable.
     *       - 'lazy' (bool|null): If true, adds loading="lazy"; if false, adds loading="eager" (plus fetchpriority).
     *       - 'decoding' (string|null): e.g. "async", "auto", or "sync".
     *
     * @type string $class Extra class for the <picture> or <img>.
     * @type array|string $style Inline style for the container (associative array or CSS string).
     * @type string $role Role attribute for the container.
     * @type bool|null $draggable Draggable default.
     * @type bool|null $lazy Whether to lazy load (true = loading="lazy"; false = loading="eager").
     * @type string $alt Fallback alt text.
     * @type string $blockClass BEM block prefix to generate classes like "blockClass__picture" or "blockClass__image".
     * @type string|null $decoding Decoding attribute (if lazy is true and decoding is null, defaults to "async").
     * @type string|false|null $fetchpriority Optional value to override the default fetchpriority for non‑lazy images. If set to false/empty, no fetchpriority is output. Otherwise, set a string like "low" or "auto" to override the default "high" for non-lazy images.
     * @return string HTML markup (<img> or <picture>).
     *
     * @example
     * <code>
     * echo Image::render([
     *     'blockClass' => 'hero',
     *     'class' => 'extra-class',
     *     'decoding' => 'auto',
     *     'sources' => [
     *         [
     *             'id' => 123,
     *             'size' => 'large',
     *             'alt' => 'Sample image',
     *         ],
     *     ],
     * ]);
     * </code>
     */
    public static function render(array $args = []): string {

        //initialize the image HTML
        $imageHtml = '';

        //set up default image arguments
        $defaults = [
            'sources' => [],
            'class' => '',
            'style' => '',
            'role' => '',
            'draggable' => false,
            'lazy' => true,
            'alt' => null,
            'blockClass' => '',
            'decoding' => null,
            'fetchpriority' => null,
        ];

        //allow filtering the default arguments
        $defaults = apply_filters('ts_image_render_default_args', $defaults);

        //merge default arguments with user-provided ones
        $args = array_merge($defaults, $args);

        //validation - filter out invalid sources (must at least have 'id' or 'url').
        $valid = [];
        foreach ($args['sources'] as $s) {
            if (!empty($s['id']) || !empty($s['url'])) $valid[] = $s;
        }

        //bail if no valid sources given
        if (empty($valid)) return '';

        //if we have exactly one valid source => render an <img> tag
        if (count($valid) === 1) {
            $args['class'] = self::mergeBemClass($args['class'], $args['blockClass'], 'image');
            $imageHtml = self::buildSingleImg($valid[0], $args);
        } else {
            //if we have multiple valid sources => render a <picture> tag
            $args['class'] = self::mergeBemClass($args['class'], $args['blockClass'], 'picture');
            $imageHtml = self::buildPicture($valid, $args);
        }

        //allow filtering the image HTML
        $imageHtml = apply_filters('ts_image_render_html', $imageHtml, $args);

        return $imageHtml;
    }

    /**
     * Build a single <img> tag from one source item.
     *
     * @param array $item Single source item.
     * @param array $global Global arguments from render().
     * @return string <img> HTML or an empty string if there's no workable src.
     * @internal
     */
    protected static function buildSingleImg(array $item, array $global): string {
        $attrs = [];

        //merge BEM blockClass for the item
        if (!empty($global['blockClass'])) $item['class'] = self::mergeBemClass(($item['class'] ?? ''), $global['blockClass'], 'image');

        ///handle the CSS classes
        $class = $item['class'] ?? $global['class'];
        if ($class) $attrs[] = 'class="' . esc_attr($class) . '"';

        //handle inline styles
        $style = $item['style'] ?? $global['style'];
        if ($style) {
            if (is_array($style)) $style = self::normalizeStyle($style);
            $attrs[] = 'style="' . esc_attr($style) . '"';
        }

        //handle role attribute
        $role = $item['role'] ?? $global['role'];
        if ($role) $attrs[] = 'role="' . esc_attr($role) . '"';

        //handle draggable attribute
        $draggable = $item['draggable'] ?? $global['draggable'];
        if (is_bool($draggable)) $attrs[] = 'draggable="' . ($draggable ? 'true' : 'false') . '"';

        //decide if we're lazy loading the image
        $lazy = array_key_exists('lazy', $item) ? $item['lazy'] : $global['lazy'];

        //if we're lazy loading and decoding is not set; default to adding the decoding="async" attribute
        $decoding = $item['decoding'] ?? $global['decoding'] ?? null;
        if ($lazy === true && $decoding === null) $decoding = 'async';
        if (!empty($decoding)) $attrs[] = 'decoding="' . esc_attr($decoding) . '"';

        //apply loading attribute and decide on what to do with fetchpriority attribute
        if ($lazy === true) {
            $attrs[] = 'loading="lazy"';
        } elseif ($lazy === false) {
            $attrs[] = 'loading="eager"';
            if (array_key_exists('fetchpriority', $item) && $item['fetchpriority'] !== null) $fetchPriority = $item['fetchpriority'];
            elseif (array_key_exists('fetchpriority', $global) && $global['fetchpriority'] !== null) $fetchPriority = $global['fetchpriority'];
            else $fetchPriority = 'high';
            if ($fetchPriority !== false && $fetchPriority !== '' && $fetchPriority !== null) $attrs[] = 'fetchpriority="' . esc_attr($fetchPriority) . '"';
        }

        //attempt to resolve the X2 (and maybe X2) source URLs
        $url1x = self::resolveUrl($item, 'size');
        $url2x = self::resolveUrl($item, 'size2x');
        if (!$url1x && $url2x) {
            $url1x = $url2x;
            $url2x = '';
        }

        //no workable URL found. Skip this image
        if (!$url1x) return '';

        //build the srcset attribute
        $attrs[] = 'src="' . esc_url($url1x) . '"';
        if ($url2x) $attrs[] = 'srcset="' . esc_attr("$url1x 1x, $url2x 2x") . '"';

        //handle the alt tag
        $alt = array_key_exists('alt', $item) ? $item['alt'] : ($global['alt'] ?? null);
        if ($alt === null && !empty($item['id'])) $alt = self::altFromId($item['id']);
        $attrs[] = 'alt="' . esc_attr($alt) . '"';

        //handle the width and height attribute
        $width = $item['width'] ?? 0;
        $height = $item['height'] ?? 0;
        if ((!$width || !$height) && !empty($item['id'])) {
            $sz = $item['size'] ?? 'full';
            $dims = self::dimensionsFromId($item['id'], $sz);
            if ($dims) {
                if (!$width) $width = $dims->width;
                if (!$height) $height = $dims->height;
            }
        }
        if ($width) $attrs[] = 'width="' . (int)$width . '"';
        if ($height) $attrs[] = 'height="' . (int)$height . '"';

        //return the fully built <img> tag
        return '<img ' . implode(' ', $attrs) . ' />';
    }

    /**
     * Build a <picture> element with multiple <source> elements plus a fallback <img>.
     *
     * @param array $sources Validated source items.
     * @param array $global Global arguments from render().
     * @return string <picture> HTML.
     * @internal
     */
    protected static function buildPicture(array $sources, array $global): string {

        //the last item is used as fallback <img>.
        $fallback = array_pop($sources);

        ///handle the BEM CSS class
        $picClass = self::mergeBemClass($global['class'], $global['blockClass'], 'picture');
        $picAttrs = [];
        if ($picClass) $picAttrs[] = 'class="' . esc_attr($picClass) . '"';

        //handle inline styles
        if ($global['style']) {
            $style = $global['style'];
            if (is_array($style)) $style = self::normalizeStyle($style);
            $picAttrs[] = 'style="' . esc_attr($style) . '"';
        }

        //handle role attribute
        if ($global['role']) $picAttrs[] = 'role="' . esc_attr($global['role']) . '"';

        //handle draggable attribute
        if (is_bool($global['draggable'])) $picAttrs[] = 'draggable="' . ($global['draggable'] ? 'true' : 'false') . '"';

        //start building the <picture> tag HTML
        $html = '<picture ' . implode(' ', array_filter($picAttrs)) . '>' . "\n";

        //add the <source> elements
        foreach ($sources as $src) {
            $tag = self::buildSourceTag($src, $global);
            if ($tag) $html .= '  ' . $tag . "\n";
        }

        //add the fallback image tag
        if (!empty($global['blockClass'])) $fallback['class'] = self::mergeBemClass(($fallback['class'] ?? ''), $global['blockClass'], 'image');
        $html .= '  ' . self::buildSingleImg($fallback, $global) . "\n";

        //close the <picture> tag
        $html .= '</picture>';

        //return the fully built <picture> tag
        return $html;
    }

    /**
     * Build a <source> tag from a single source item.
     *
     * @param array $item Single source item.
     * @return string <source> or an empty string if no URL found.
     * @internal
     */
    protected static function buildSourceTag(array $item): string {

        //get the media of the given item
        $media = $item['media'] ?? '';

        //attempt to resolve the X2 (and maybe X2) source URLs
        $url1x = self::resolveUrl($item, 'size');
        $url2x = self::resolveUrl($item, 'size2x');
        if (!$url1x && $url2x) {
            $url1x = $url2x;
            $url2x = '';
        }

        //no workable URL found. Skip this image
        if (!$url1x) return '';

        //build the srcset attribute
        $srcset = $url1x;
        if ($url2x) $srcset .= ' 1x, ' . $url2x . ' 2x';

        //handle the width and height attribute
        $w = $item['width'] ?? 0;
        $h = $item['height'] ?? 0;
        if ((!$w || !$h) && !empty($item['id'])) {
            $sz = $item['size'] ?? 'full';
            $dims = self::dimensionsFromId($item['id'], $sz);
            if ($dims) {
                if (!$w) $w = $dims->width;
                if (!$h) $h = $dims->height;
            }
        }

        //build <source> attributes
        $attrs = [];
        if ($media) $attrs[] = 'media="' . esc_attr($media) . '"';
        $attrs[] = 'srcset="' . esc_attr($srcset) . '"';
        if ($w) $attrs[] = 'width="' . (int)$w . '"';
        if ($h) $attrs[] = 'height="' . (int)$h . '"';

        //return the fully built <source> tag
        return '<source ' . implode(' ', $attrs) . ' />';
    }

    /**
     * Resolve the 1x or 2x URL from an attachment or direct URL.
     *
     * For 1x images: if an ID is provided and no size is defined, defaults to "full".
     * For 2x images: if a direct URL is provided via "url2x", that is returned immediately.
     *
     * @param array $item Single source item.
     * @param string $sizeKey Either "size" for 1x or "size2x" for 2x.
     * @return string|null The resolved URL or null if not found.
     * @internal
     */
    protected static function resolveUrl(array $item, string $sizeKey): ?string {

        //if we have an ID but no explicit size, default to 'full'
        if ($sizeKey === 'size' && !empty($item['id']) && empty($item[$sizeKey])) $item[$sizeKey] = 'full';

        //if user gave a direct 2x URL, just return that
        if ($sizeKey === 'size2x' && !empty($item['url2x'])) return $item['url2x'];

        //if we have an ID and a known size, get from WP attachment
        if (!empty($item['id']) && !empty($item[$sizeKey])) {
            $url = self::urlFromId($item['id'], $item[$sizeKey]);
            if ($url) return $url;
        }

        //if we have a direct 'url' for 1x, use that
        if (!empty($item['url']) && $sizeKey === 'size') return $item['url'];

        //if we failed to resolve a URL, return null
        return null;
    }

    /**
     * Merge a BEM-style class into an existing class string.
     *
     * @param string $existing The existing class string.
     * @param string $blockClass The BEM block prefix (e.g. "my-block").
     * @param string $suffix The element name (e.g. "image" or "picture").
     * @return string The merged class string without duplicates.
     *
     * @internal
     * @example
     * <code>
     * $merged = Image::mergeBemClass("extra-class", "hero", "image");
     * // Returns "extra-class hero__image"
     * </code>
     */
    protected static function mergeBemClass(string $existing, string $blockClass, string $suffix): string {
        if (!$blockClass) return $existing;
        $bem = $blockClass . '__' . $suffix;
        $all = trim($existing . ' ' . $bem);
        $unique = array_unique(array_filter(explode(' ', $all)));
        return implode(' ', $unique);
    }

    /**
     * Normalize style data.
     *
     * If the provided style is an associative array, converts it into an inline CSS string.
     *
     * @param array|string $style Associative array of CSS or a CSS string.
     * @return string Normalized inline CSS.
     *
     * @internal
     * @example
     * <code>
     * $css = Image::normalizeStyle(["color" => "red", "font-size" => "14px"]);
     * // Returns "color: red; font-size: 14px;"
     * </code>
     */
    protected static function normalizeStyle(array|string $style): string {
        if (is_array($style)) {
            $css = '';
            foreach ($style as $key => $value) $css .= $key . ': ' . $value . '; ';
            return trim($css);
        }
        return (string)$style;
    }
}