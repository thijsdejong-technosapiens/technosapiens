<?php

namespace TechnoSapiens;

class SectionReferencePartial {

    /**
     * @var array<string, string>
     */
    private static array $sectionIcons = [];

    /**
     * Register the Dashicon slug for a reference section.
     * @param string $sectionSlug
     * @param string $icon
     * @return void
     */
    public static function registerSectionIcon(string $sectionSlug, string $icon): void {
        $icon = preg_replace('/^dashicons-/', '', $icon) ?? $icon;
        $icon = preg_replace('/[^a-z0-9-]/', '', strtolower($icon)) ?? '';

        if ($icon) {
            self::$sectionIcons[$sectionSlug] = $icon;
        }
    }

    /**
     * Get the Dashicon slug for a reference section.
     * @param string $sectionSlug
     * @return string
     */
    public static function getSectionIcon(string $sectionSlug): string {
        return self::$sectionIcons[$sectionSlug] ?? 'admin-generic';
    }

    /**
     * Get the editor preview image URL for a reference section.
     * @param string $sectionSlug
     * @return string
     */
    public static function getPreviewImageUrl(string $sectionSlug): string {
        return get_stylesheet_directory_uri() . '/sections/' . $sectionSlug . '/preview/' . $sectionSlug . '.jpg';
    }

    /**
     * Check if a reference section has an editor preview image.
     * @param string $sectionSlug
     * @return bool
     */
    public static function hasPreviewImage(string $sectionSlug): bool {
        return file_exists(get_stylesheet_directory() . '/sections/' . $sectionSlug . '/preview/' . $sectionSlug . '.jpg');
    }

    /**
     * Get all reference section slugs that have an editor preview image.
     * @return array<int, string>
     */
    public static function getSectionSlugsWithPreviewImages(): array {
        static $sectionSlugs = null;

        if ($sectionSlugs !== null) {
            return $sectionSlugs;
        }

        $sectionSlugs = [];
        $sectionsDirectory = get_stylesheet_directory() . '/sections';

        if (!is_dir($sectionsDirectory)) {
            return $sectionSlugs;
        }

        foreach (scandir($sectionsDirectory) as $sectionDirectory) {
            if ($sectionDirectory === '.' || $sectionDirectory === '..') {
                continue;
            }

            if (self::hasPreviewImage($sectionDirectory)) {
                $sectionSlugs[] = $sectionDirectory;
            }
        }

        sort($sectionSlugs);

        return $sectionSlugs;
    }

    /**
     * Build compact editor bar HTML for block inserter and canvas preview.
     * Thumbnail is optional; title and icon always render.
     * @param string $sectionSlug
     * @param string $label
     * @return string
     */
    public static function renderEditorBar(string $sectionSlug, string $label): string {
        $icon = esc_attr(self::getSectionIcon($sectionSlug));
        $thumb = '';

        if (self::hasPreviewImage($sectionSlug)) {
            $thumb = '<img class="section-reference-editor-bar__thumb" src="'
                . esc_url(self::getPreviewImageUrl($sectionSlug))
                . '" alt="">';
        }

        return '<div class="section-reference-editor-bar">'
            . '<span class="section-reference-editor-bar__title">'
            . '<span class="section-reference-editor-bar__icon dashicons dashicons-' . $icon . '" aria-hidden="true"></span>'
            . '<span class="section-reference-editor-bar__label">' . esc_html($label) . '</span>'
            . '</span>'
            . $thumb
            . '</div>';
    }

    /**
     * Build container classes for a reference section.
     * @param string $sectionClass
     * @return array
     */
    public static function getContainerClasses(string $sectionClass): array {
        $classes = ['section', $sectionClass];
        $background = get_field('bg') ?? '';

        if ($background) {
            $classes[] = $sectionClass . '--bg-' . $background;
        }

        if (self::hasDarkBackground($background)) {
            $classes[] = 'ts-has-dark-background';
        }

        return $classes;
    }

    /**
     * Check if background choice requires dark foreground styling.
     * @param string $background
     * @return bool
     */
    public static function hasDarkBackground(string $background = ''): bool {
        return in_array($background, ['black', 'darkgrey'], true);
    }
}
