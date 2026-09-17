<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Enqueue;
use TechnoSapiens\Core\Image;
use TechnoSapiens\Core\Link;
use TechnoSapiens\Core\Singleton;

/**
 * Class LoginStyles
 * @package TechnoSapiens
 */
class LoginStyles extends Singleton {

    /**
     * LoginStyles constructor.
     */
    protected function __construct() {
        add_action('login_enqueue_scripts', [$this, 'enqueueLoginStyles']);
        add_filter('login_headerurl', [$this, 'getLoginHeaderUrl']);
        add_filter('login_headertext', [$this, 'getLoginHeaderText']);
        add_filter('login_body_class', [$this, 'addLoginBodyClass']);
        add_action('login_head', [$this, 'renderLoginVariables'], 2, 0);
    }

    /**
     * Enqueue login screen styles.
     * @return void
     */
    public function enqueueLoginStyles(): void {
        $loginCss = Enqueue::getWebpackAssetUrlByKey(MANIFEST_PATH, 'login.css');

        if ($loginCss) {
            wp_enqueue_style(Theme::TEXT_DOMAIN . '_login', $loginCss);
        }
    }

    /**
     * Login logo link URL.
     * @return string
     */
    public function getLoginHeaderUrl(): string {
        return Link::getHomePageUrl();
    }

    /**
     * Login logo link title.
     * @return string
     */
    public function getLoginHeaderText(): string {
        return get_bloginfo('name');
    }

    /**
     * Add a body class when a login background image is set.
     * @param array $classes
     * @return array
     */
    public function addLoginBodyClass(array $classes): array {
        if (ThemeSettings::getLoginBackgroundUrl()) {
            $classes[] = 'login--has-background';
        }

        return $classes;
    }

    /**
     * Output dynamic login CSS variables.
     * @return void
     */
    public function renderLoginVariables(): void {
        $backgroundUrl = ThemeSettings::getLoginBackgroundUrl();
        $logoId = get_field('logo_image_diap', 'option') ?: '';
        $logoUrl = $logoId ? Image::urlFromId($logoId) : '';
        ?>
        <style>
            :root {
                <?php if ($backgroundUrl) : ?>
                --ts-login-bg-url: url('<?php echo esc_url($backgroundUrl); ?>');
                <?php endif; ?>
                <?php if ($logoUrl) : ?>
                --ts-login-logo-url: url('<?php echo esc_url($logoUrl); ?>');
                <?php endif; ?>
            }
        </style>
        <?php
    }
}

LoginStyles::getInstance();
