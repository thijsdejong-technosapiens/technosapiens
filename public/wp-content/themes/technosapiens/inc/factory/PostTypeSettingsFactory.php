<?php

namespace TechnoSapiens\FactoryPlugin;

use TechnoSapiens\Core\Formatting;
use TechnoSapiens\Core\Link;
use TechnoSapiens\Core\PostType;
use TechnoSapiens\Core\Singleton;
use TechnoSapiens\FactoryPlugin;
use TechnoSapiens\GlobalCtaPlugin\AcfLocationComponent as GlobalCtaAcfLocationComponent;
use TechnoSapiens\HeaderModulePlugin\AcfLocationComponent;
use TechnoSapiens\StaticBlockPostType;

/**
 * Class PostTypeSettingsFactory
 * @package TechnoSapiens
 */
abstract class PostTypeSettingsFactory extends Singleton {

    /**
     * Post-type class to configure.
     * @var class-string<PostTypeFactory>
     */
    public const POST_TYPE = '';

    /**
     * PostTypeSettingsFactory constructor.
     */
    protected function __construct() {

        //check if archive is enabled
        $archiveEnabled = static::POST_TYPE::featureEnabled('enableArchive');
        if ($archiveEnabled) {

            //populate other posts global variable
            $this->populateArchiveOtherPosts();

            //handle slug helpers
            $this->addAdminSlugHelpers();

            //integrate hero for archive pages
            if (static::POST_TYPE::featureEnabled('enableArchiveHeroModule') && class_exists('TechnoSapiens\HeaderModulePlugin')) {
                AcfLocationComponent::getInstance()->addHeaderModuleToOptionsPage(self::menuSlug());
            }

            //integrate global CTA for archive pages
            if (static::POST_TYPE::featureEnabled('enableArchiveGlobalCta') && class_exists('TechnoSapiens\GlobalCtaPlugin')) {
                GlobalCtaAcfLocationComponent::getInstance()->addGlobalCtaExcludeSettingsToOptionsPage(self::menuSlug());
            }

            //hide post-type slug field if the Polylang plugin is active
            add_action('acf/init', function () {
                if (function_exists('pll__')) {
                    add_filter('acf/prepare_field/name=' . self::type() . '_archive_slug', function (array $field): array {
                        $field['wrapper']['class'] .= ' hidden';
                        return $field;
                    });
                }
            });
        }

        //register default ACF fields using PHP
        add_action('acf/init', [$this, 'registerAcfFieldGroup']);

        //register the options sub-page
        add_action('init', [$this, 'registerOptionsSubPage'], 20);

        //call the parent constructor
        parent::__construct();
    }

    /**
     * Get the post type slug
     * @return string
     */
    final protected static function type(): string {
        return static::POST_TYPE::TYPE;
    }

    /**
     * Get the settings page slug
     * @return string
     */
    final protected static function menuSlug(): string {
        return self::type() . '-settings';
    }

    /**
     * Create the ACF options sub-page under the custom post-type menu.
     * @return void
     */
    public function registerOptionsSubPage(): void {
        $singularLabel = PostType::getSingularLabel(self::type());
        acf_add_options_sub_page([
            'page_title' => sprintf(__('%s settings', FactoryPlugin::TEXT_DOMAIN), $singularLabel),
            'menu_title' => sprintf(__('%s settings', FactoryPlugin::TEXT_DOMAIN), $singularLabel),
            'menu_slug' => self::menuSlug(),
            'post_id' => self::menuSlug(),
            'parent_slug' => sprintf('edit.php?post_type=%s', self::type()),
        ]);
    }

    /**
     * Ensure static-block assets load on the archive page when required.
     * @return void
     */
    private function populateArchiveOtherPosts(): void {

        //bail if we are in the CMS
        if (is_admin()) return;

        //add the static blocks to the global $otherPosts array
        add_action('wp_enqueue_scripts', static function (): void {
            if (is_archive() && PostType::getPostType() === self::type()) {
                global $otherPosts;
                if (!$otherPosts) $otherPosts = [];
                if ($top = self::getStaticBlockTopPost()) $otherPosts[] = $top;
                if ($bottom = self::getStaticBlockBottomPost()) $otherPosts[] = $bottom;
            }
        }, 1);
    }

    /**
     * Add helper filters / actions only visible while editing the options page.
     * @return void
     */
    private function addAdminSlugHelpers(): void {
        if (is_admin() && isset($_GET['page']) && self::menuSlug() === $_GET['page']) {
            add_action('acf/save_post', [$this, 'handleAcfSavePost'], 100);
            add_filter('acf/load_field/name=' . $this->getFieldName('archive_slug'), [$this, 'filterArchiveSlugDescription']);
            add_filter('acf/update_value/name=' . $this->getFieldName('archive_slug'), [$this, 'sanitizeArchiveSlugBeforeSave'], 10, 1);
        }
    }

    /**
     * Build the dynamic field-name used by ACF.
     * @param string $suffix Field identifier (e.g. "archive_slug").
     * @return string
     */
    private function getFieldName(string $suffix): string {
        return self::type() . '_' . $suffix;
    }

    /**
     * Register the field-group for the options page.
     *
     * @return void
     */
    public function registerAcfFieldGroup(): void {

        //ACF not loaded (CLI or early test run).
        if (!function_exists('acf_add_local_field_group')) return;

        //get post type labels
        $singular = PostType::getSingularLabel(self::type());
        $plural = PostType::getPluralLabel(self::type());

        //check if the archive is enabled
        $archiveEnabled = static::POST_TYPE::featureEnabled('enableArchive');

        /* ── 1. fields that *always* belong on the page ───────────── */
        $snippetFields = [
            /* ─ Snippet tab ─ */
            [
                'key' => $this->getFieldName('snippet_settings'),
                'label' => __('Snippet settings', FactoryPlugin::TEXT_DOMAIN),
                'type' => 'tab',
                'placement' => 'top',
            ],
            [
                'key' => $this->getFieldName('excerpt_length'),
                'label' => __('Excerpt length', FactoryPlugin::TEXT_DOMAIN),
                'name' => $this->getFieldName('excerpt_length'),
                'type' => 'range',
                'instructions' => sprintf(
                    __('Number of words shown in the %s snippet.', FactoryPlugin::TEXT_DOMAIN),
                    strtolower($singular)
                ),
                'default_value' => 15,
                'min' => 5,
                'max' => 100,
                'step' => 5,
                'append' => __('words', FactoryPlugin::TEXT_DOMAIN),
            ],
        ];

        /* ── 2. archive-specific fields (added only when enabled) ─── */
        $archiveFields = [];
        if ($archiveEnabled) {
            $archiveFields = [
                /* ─ Archive tab ─ */
                [
                    'key' => $this->getFieldName('archive_page'),
                    'label' => sprintf(__('%s archive page', FactoryPlugin::TEXT_DOMAIN), $singular),
                    'type' => 'tab',
                    'placement' => 'top',
                ],
                [
                    'key' => $this->getFieldName('archive_slug'),
                    'label' => __('Archive slug', FactoryPlugin::TEXT_DOMAIN),
                    'name' => $this->getFieldName('archive_slug'),
                    'type' => 'text',
                    'instructions' => sprintf(
                        __('Slug for the archive/single URLs of this %s. Example: %s', FactoryPlugin::TEXT_DOMAIN),
                        strtolower($singular),
                        '<code>my-epic-' . self::type() . '-archive</code>'
                    ),
                    'wrapper' => ['width' => '100'],
                    'prepend' => '/',
                    'append' => '/',
                ],
                [
                    'key' => $this->getFieldName('items_per_page'),
                    'label' => __('Items per page', FactoryPlugin::TEXT_DOMAIN),
                    'name' => $this->getFieldName('items_per_page'),
                    'type' => 'range',
                    'instructions' => sprintf(
                        __('Number of %s to display per archive page. Use %s to disable pagination.', FactoryPlugin::TEXT_DOMAIN),
                        strtolower($plural),
                        '<code>-1</code>'
                    ),
                    'wrapper' => ['width' => '100'],
                    'default_value' => -1,
                    'min' => -1,
                    'max' => 100,
                    'step' => 1,
                ],
                [
                    'key' => $this->getFieldName('static_block_top'),
                    'label' => __('Static block top', FactoryPlugin::TEXT_DOMAIN),
                    'name' => $this->getFieldName('static_block_top'),
                    'type' => 'post_object',
                    'instructions' => sprintf(
                        __('Optional Gutenberg content displayed <strong>above</strong> the %s archive.', FactoryPlugin::TEXT_DOMAIN),
                        strtolower($singular)
                    ),
                    'wrapper' => ['width' => '50'],
                    'post_type' => [StaticBlockPostType::TYPE],
                    'return_format' => 'object',
                    'ui' => 1,
                    'allow_null' => 1,
                ],
                [
                    'key' => $this->getFieldName('static_block_bottom'),
                    'label' => __('Static block bottom', FactoryPlugin::TEXT_DOMAIN),
                    'name' => $this->getFieldName('static_block_bottom'),
                    'type' => 'post_object',
                    'instructions' => sprintf(
                        __('Optional Gutenberg content displayed <strong>below</strong> the %s archive.', FactoryPlugin::TEXT_DOMAIN),
                        strtolower($singular)
                    ),
                    'wrapper' => ['width' => '50'],
                    'post_type' => [StaticBlockPostType::TYPE],
                    'return_format' => 'object',
                    'ui' => 1,
                    'allow_null' => 1,
                ],
            ];
        }

        acf_add_local_field_group([
            'key' => self::type() . '_settings',
            'title' => sprintf(__('%s settings', FactoryPlugin::TEXT_DOMAIN), $singular),
            'fields' => array_merge($archiveFields, $snippetFields),
            'location' => [
                [
                    [
                        'param' => 'options_page',
                        'operator' => '==',
                        'value' => self::menuSlug(),
                    ],
                ],
            ],
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'active' => true,
            'show_in_rest' => 0,
        ]);
    }

    /**
     * Sanitize the archive slug before it is saved by ACF.
     * @param mixed $value Raw form value.
     * @return string
     */
    public static function sanitizeArchiveSlugBeforeSave(mixed $value): string {
        return is_string($value) ? Formatting::archiveSlugify($value) : (string)$value;
    }

    /**
     * When the archive slug changes, flag WordPress to flush permalinks.
     * @param string|int $postId Options-page post-ID passed by ACF.
     * @return void
     */
    public static function handleAcfSavePost(string|int $postId): void {
        $fields = get_fields($postId);
        if (isset($fields[self::type() . '_archive_slug'])) update_option('should_flush_permalinks', 'yes');
    }

    /**
     * Append the current archive URL below the slug field in the admin.
     * @param array<string,mixed> $field Original field array.
     * @return array<string,mixed>
     */
    public function filterArchiveSlugDescription(array $field): array {
        $slug = self::getRewriteSlug();
        $archivePage = Link::parseLink(Link::getHomePageUrl() . '/' . $slug);
        $field['instructions'] .= sprintf(
            '<p class="admin-archive-url"><span class="dashicons dashicons-admin-links admin-archive-url__icon"></span> <span class="admin-archive-url__text">%s</span></p>',
            wp_kses_post(
                sprintf(
                /* translators: %s = URL */
                    __('Current archive URL: %s', FactoryPlugin::TEXT_DOMAIN),
                    '<a target="_blank" href="' . esc_url($archivePage) . '">' . esc_html($archivePage) . '</a>'
                )
            )
        );
        return $field;
    }

    /**
     * Get the rewrite slug for this post type.
     * @return string
     */
    public static function getRewriteSlug(): string {

        //set default value
        $base = static::POST_TYPE::BASE_SLUG;

        //try to get from Polylang translation
        if (function_exists('pll__')) {
            $translated = pll__('ts_cpt_' . self::type() . '_base');
            if ($translated !== 'ts_cpt_' . self::type() . '_base') {
                return Formatting::archiveSlugify($translated);
            }
        }

        //try to get from option
        $optionKey = self::menuSlug() . '_' . self::type() . '_archive_slug';
        $raw = get_option($optionKey);
        if (is_string($raw) && $raw !== '') {
            return Formatting::archiveSlugify($raw);
        }

        //fallback to base slug
        return $base;
    }

    /**
     * Return the posts-per-page value stored in settings.
     * @return int
     */
    public static function getPostsPerPage(): int {
        $configuredPostsPerPage = (int)get_field(self::type() . '_items_per_page', self::menuSlug()) ?: -1;
        return $configuredPostsPerPage === -1 || $configuredPostsPerPage > 0 ? $configuredPostsPerPage : get_option('posts_per_page', -1);
    }

    /**
     * Return the excerpt length setting.
     * @return int
     */
    public static function getExcerptLimit(): int {
        $limit = get_field(self::type() . '_excerpt_length', self::menuSlug());
        return (int)($limit ?: 15);
    }

    /**
     * Get the static block to show **above** the archive (if any).
     * @return \WP_Post|false
     */
    public static function getStaticBlockTopPost(): \WP_Post|false {
        return get_field(self::type() . '_static_block_top', self::menuSlug()) ?: false;
    }

    /**
     * Get the static block to show **below** the archive (if any).
     * @return \WP_Post|false
     */
    public static function getStaticBlockBottomPost(): \WP_Post|false {
        return get_field(self::type() . '_static_block_bottom', self::menuSlug()) ?: false;
    }
}