<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Enqueue;
use TechnoSapiens\Core\Partial;

trait SectionReferenceTrait {

    /**
     * Register a reference section with conditional assets and ACF JSON handling.
     *
     * Note: WP 7.1+ requires ACF Block V3; never set `mode` to `edit`; do not rely on
     * `acf/blocks/default_block_version` as the only switch — explicit args survive ACF default changes.
     *
     * @param string $description
     * @param string $icon
     * @param bool $hasScript
     * @param array $supports
     * @param array $postTypes
     * @param string $blockMode ACF block mode: 'edit', 'preview', or 'auto'
     * @return void
     */
    protected function registerReferenceSection(
        string $description,
        string $icon = 'admin-generic',
        bool $hasScript = false,
        array $supports = [],
        array $postTypes = [],
        string $blockMode = 'preview'
    ): void {
        if (!function_exists('acf_register_block_type')) {
            return;
        }

        $defaultSupports = [
            'customClassName' => false,
            'align' => false,
            'mode' => false,
            'reusable' => true,
        ];

        $sectionArgs = [
            'name' => self::SECTION_ID,
            'title' => static::getSectionLabel(),
            'description' => $description,
            'render_callback' => [$this, 'render'],
            'api_version' => 3,
            'acf_block_version' => 3,
            'hide_fields_in_sidebar' => true,
            'supports' => array_merge($defaultSupports, $supports),
            'mode' => $blockMode,
            'attributes' => [
                'mode' => [
                    'type' => 'string',
                    'default' => $blockMode,
                ],
            ],
            'icon' => [
                'src' => $icon,
            ],
        ];

        if (!empty($postTypes)) {
            $sectionArgs['post_types'] = $postTypes;
        }

        acf_register_block_type($sectionArgs);

        SectionReferencePartial::registerSectionIcon(self::SECTION_SLUG, $icon);

        add_action('wp_enqueue_scripts', function () use ($hasScript) {
            global $otherPosts;
            global $post;
            if (!is_array($otherPosts)) {
                $otherPosts = [];
            }
            foreach (array_merge([$post], $otherPosts) as $pagePost) {
                if ($pagePost && has_block('acf/section-' . self::SECTION_SLUG, $pagePost)) {
                    $this->initReferenceSectionFrontendAssets($hasScript);
                    break;
                }
            }
        });

        $this->handleReferenceSectionAcfJson();
    }

    /**
     * Handle ACF JSON saving for reference sections.
     *
     * Loading is handled centrally in Theme::registerSectionAcfJsonLoadPaths()
     * because it must run before ACF scans load_json paths on acf/include_fields.
     *
     * Saves are matched by field group key (not Dutch title slug) and written as
     * `{SECTION_SLUG}.json` so filenames stay English while titles stay Dutch.
     * @return void
     */
    public function handleReferenceSectionAcfJson(): void {
        $acfJsonPath = get_stylesheet_directory() . '/sections/' . self::SECTION_SLUG . '/acf-json';
        $sectionSlug = self::SECTION_SLUG;

        add_filter('acf/settings/save_json', function (string $path) use ($acfJsonPath) {
            if (!is_dir($acfJsonPath)) {
                return $path;
            }

            $fieldGroupKey = $_POST['acf_field_group']['key'] ?? '';
            if (!$fieldGroupKey || !self::sectionAcfJsonContainsGroupKey($acfJsonPath, $fieldGroupKey)) {
                return $path;
            }

            return $acfJsonPath;
        });

        add_filter('acf/json/save_file_name', function (string $fileName, array $acfGroup) use ($acfJsonPath, $sectionSlug): string {
            if (empty($acfGroup['key']) || !is_dir($acfJsonPath)) {
                return $fileName;
            }

            if (!self::sectionAcfJsonContainsGroupKey($acfJsonPath, $acfGroup['key'])) {
                return $fileName;
            }

            return $sectionSlug . '.json';
        }, 20, 2);
    }

    /**
     * Check whether a section acf-json directory already stores the given field group key.
     * @param string $acfJsonPath
     * @param string $fieldGroupKey
     * @return bool
     */
    private static function sectionAcfJsonContainsGroupKey(string $acfJsonPath, string $fieldGroupKey): bool {
        foreach (glob($acfJsonPath . '/*.json') ?: [] as $jsonFile) {
            $json = json_decode((string) file_get_contents($jsonFile), true);
            if (is_array($json) && ($json['key'] ?? '') === $fieldGroupKey) {
                return true;
            }
        }

        return false;
    }

    /**
     * Init frontend assets for reference sections.
     * @param bool $hasScript
     * @return void
     */
    public function initReferenceSectionFrontendAssets(bool $hasScript = false): void {
        $sectionCss = Enqueue::getWebpackAssetUrlByKey(MANIFEST_PATH, self::SECTION_SLUG . '-style.css');
        if ($sectionCss) {
            wp_enqueue_style(self::SECTION_ID . '_styles', $sectionCss);
        }

        if ($hasScript) {
            $sectionJs = Enqueue::getWebpackAssetUrlByKey(MANIFEST_PATH, self::SECTION_SLUG . '-script.js');
            if ($sectionJs) {
                wp_enqueue_script(self::SECTION_ID . '_scripts', $sectionJs, [Theme::TEXT_DOMAIN . '_scripts'], null, true);
            }
        }
    }

    /**
     * Render section template with PHP.
     * @param array $block
     * @param string $content
     * @param bool $is_preview
     * @param int|string $post_id
     * @return string
     */
    public function render(array $block, string $content = '', bool $is_preview = false, int|string $post_id = 0): string {
        $isExample = !empty($block['data']['is_example']);

        if ($isExample || $is_preview) {
            $bar = SectionReferencePartial::renderEditorBar(self::SECTION_SLUG, static::getSectionLabel());
            echo $bar;

            return $bar;
        }

        return Partial::render(
            self::SECTION_SLUG,
            ['block' => $block, 'content' => $content, 'is_preview' => $is_preview, 'post_id' => $post_id],
            true,
            get_stylesheet_directory() . '/sections/' . self::SECTION_SLUG . '/partials/'
        );
    }
}
