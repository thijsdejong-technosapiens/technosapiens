<?php

use TechnoSapiens\Theme;
use TechnoSapiens\Core\Link;
use TechnoSapiens\Core\Image;
use TechnoSapiens\SocialLinkComponent;
use TechnoSapiens\Core\WcagComponent;
use TechnoSapiens\Core\Partial;
use TechnoSapiens\Core\Navigation;

$contactData = get_field('contact_data', 'option');
$socialLinks = SocialLinkComponent::getInstance()->getSocialLinks();
$hasSocialLinks = count($socialLinks) > 0;

$footerMenuId = Theme::MENU_FOOTER;
$footerMenuItems = Navigation::getLinksByThemeLocation($footerMenuId);
$hasFooterMenuItems = count($footerMenuItems);

$footerSecondaryMenuId = Theme::MENU_FOOTER_SECONDARY;
$footerSecondaryMenuItems = Navigation::getLinksByThemeLocation($footerSecondaryMenuId);
$hasFooterSecondaryMenuItems = count($footerSecondaryMenuItems);

$blogName = get_bloginfo('name');
$copyrightYear = gmdate('Y');
$logoId = get_field('logo_image_diap', 'option') ?: '';
$logoHtml = '';
if ($logoId) $logoHtml = Image::render([
    'class' => 'site-footer__logo-image',
    'lazy' => null,
    'alt' => sprintf(__("Logo %s", Theme::TEXT_DOMAIN), $blogName),
    'sources' => [['id' => $logoId]]
]);
?>

<footer id="ts-footer" class="site-footer" aria-label="<?php esc_attr_e('Site footer', Theme::TEXT_DOMAIN); ?>">
    <div class="container">
        <div class="row site-footer__row">
            <div class="col col--1 col--xl-1/5">
                <div class="col col--1 col--xl-1/4 site-footer__col site-footer__col--logo">
                    <a href="<?php echo Link::getHomePageUrl(); ?>"
                    target="_self"
                    class="site-footer__logo"
                    aria-label="<?php echo esc_attr(sprintf(__('%s — go to homepage', Theme::TEXT_DOMAIN), $blogName)); ?>">
                        <?php if ($logoHtml): ?>
                            <?php echo $logoHtml; ?>
                        <?php elseif ($blogName): ?>
                            <strong class="site-footer__logo-placeholder h3">
                                <?php echo $blogName; ?>
                            </strong>
                        <?php endif; ?>
                    </a>
                </div>
            </div>

            <div class="col col--1 col--xl-4/5">
                <div class="row site-footer__row">
                    <?php if ($contactData): ?>
                        <div class="col col--1 col--xl-1/4 site-footer__col site-footer__col--contact-data">
                            <section class="site-footer__contact-data" aria-labelledby="footer-contact-heading">
                                <h2 id="footer-contact-heading" class="site-footer__heading h6">
                                    <?php _e('Contact', Theme::TEXT_DOMAIN); ?>
                                </h2>
                                <address class="site-footer__address">
                                    <?php echo $contactData; ?>
                                </address>
                            </section>
                        </div>
                    <?php endif; ?>
                    
                    <div class="col col--1 col--xl-1/4 order-1 order-md-3 site-footer__col site-footer__col--ctas">
                        <div class="site-footer__ctas">
                            
                            <?php if ($hasSocialLinks): ?>
                                <?php Partial::render('components/component-social-links', ['socialLinks' => $socialLinks]); ?>
                            <?php endif; ?>

                        </div>
                    </div>

                    <?php if ($hasFooterMenuItems): ?>
                        <div class="col col--1 col--xl-1/4 order-2 order-md-1 site-footer__col site-footer__col--links">
                            <nav class="site-footer__links-menu" aria-labelledby="footer-nav-heading">
                                <h2 id="footer-nav-heading" class="site-footer__heading h6">
                                    <?php _e('Navigation', Theme::TEXT_DOMAIN); ?>
                                </h2>
                                <?php Partial::render('components/component-footer-menu', [
                                    'menuItems' => $footerMenuItems,
                                    'listClass' => 'site-footer__links-menu-list',
                                ]); ?>
                            </nav>
                        </div>
                    <?php endif; ?>

                    <?php if ($hasFooterSecondaryMenuItems): ?>
                        <div class="col col--1 col--xl-1/4 order-3 order-md-2 site-footer__col site-footer__col--links-secondary">
                            <nav class="site-footer__links-menu site-footer__links-menu--secondary" aria-labelledby="footer-legal-heading">
                                <h2 id="footer-legal-heading" class="site-footer__heading h6">
                                    <?php _e('Legal', Theme::TEXT_DOMAIN); ?>
                                </h2>
                                <?php Partial::render('components/component-footer-menu', [
                                    'menuItems' => $footerSecondaryMenuItems,
                                    'listClass' => 'site-footer__links-menu-list',
                                ]); ?>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($blogName): ?>
            <p class="site-footer__copyright">
                &copy; <?php echo esc_html($copyrightYear); ?>
                <a href="<?php echo Link::getHomePageUrl(); ?>" class="site-footer__copyright-link"><?php echo esc_html($blogName); ?></a>
                <?php
                $tagline = get_bloginfo('description');
                if ($tagline):
                    echo ' — ' . esc_html($tagline);
                endif;
                ?>
            </p>
        <?php endif; ?>
    </div>
</footer>

<?php wp_footer(); ?>
