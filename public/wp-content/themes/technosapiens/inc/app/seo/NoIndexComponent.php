<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Singleton;

/**
 * Class NoIndexComponent
 * @package TechnoSapiens
 */
class NoIndexComponent extends Singleton {
    /**
     * NoIndexComponent constructor.
     */
    protected function __construct() {
        //handle noindex checkbox in read settings
        $indexableSettings = get_option('blog_public', '') === '1';
        $indexableDotEnv = self::isIndexable();
        if ($indexableSettings !== $indexableDotEnv) update_option('blog_public', ($indexableDotEnv ? '1' : '0'));

        //add button in admin bar
        add_action('admin_bar_menu', [$this, 'addNoIndexStatusButton'], 100);
    }

    /**
     * Check if website is indexable based on .env file
     * @return bool
     */
    public static function isIndexable(): bool {
        return getenv('INDEXABLE') === 'true';
    }

    /**
     * Add noindex status button in admin bar
     * @param \WP_Admin_Bar $wpAdminBar
     * @return void
     */
    public static function addNoIndexStatusButton(\WP_Admin_Bar $wpAdminBar): void {
        $isIndexable = self::isIndexable();
        $wpAdminBar->add_node([
            'id' => $isIndexable ? 'index-status' : 'noindex-status',
            'title' => $isIndexable ? '<span class="indexable" aria-hidden="true"></span>' . __("Indexable", Theme::TEXT_DOMAIN) : '<span class="not-indexable" aria-hidden="true"></span>' . __('Not indexable', Theme::TEXT_DOMAIN)
        ]);
    }
}
