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
$logoId = get_field('logo_image_diap', 'option') ?: '';
$logoHtml = '';
if ($logoId) $logoHtml = Image::render([
    'class' => 'site-footer__logo-image',
    'lazy' => null,
    'alt' => sprintf(__("Logo %s", Theme::TEXT_DOMAIN), $blogName),
    'sources' => [['id' => $logoId]]
]);
?>

<footer id="ts-footer" class="site-footer" aria-label="Main Footer">
    <div class="container">
        <div class="row site-footer__row">
            <div class="col col--1 col--xl-1/5">
                <div class="col col--1 col--xl-1/4 site-footer__col site-footer__col--logo">
                    <a href="<?php echo Link::getHomePageUrl(); ?>"
                    target="_self"
                    class="site-footer__logo"
                    aria-label="<?php _e("Navigate to the home page", Theme::TEXT_DOMAIN); ?>">
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
                            <div class="site-footer__contact-data">
                                <?php echo $contactData; ?>
                            </div>
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
                            <nav class="site-footer__links-menu" aria-label="<?php _e('Footer menu', Theme::TEXT_DOMAIN); ?>">
                                <ul class="site-footer__links-menu-list">
                                    <?php foreach ($footerMenuItems as $footerMenuItem): ?>
                                        <li class="site-footer__links-menu-item <?php echo $footerMenuItem->linkClass; ?>">
                                            <a href="<?php echo $footerMenuItem->link; ?>"
                                            class="site-footer__links-menu-item-link"
                                            target="<?php echo $footerMenuItem->target; ?>"
                                            
                                            <?php if ($footerMenuItem->rel): ?>rel="<?php echo $footerMenuItem->rel; ?>"<?php endif; ?>>
                                                <?php echo $footerMenuItem->label; ?>
                                                <?php if ($footerMenuItem->target === '_blank'): ?>
                                                    <?php echo WcagComponent::getSrLinkOpensInNewTabHtml(); ?>
                                                <?php endif; ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>

                    <?php if ($hasFooterSecondaryMenuItems): ?>
                        <div class="col col--1 col--xl-1/4 order-3 order-md-2 site-footer__col site-footer__col--links-secondary">
                            <nav class="site-footer__links-menu site-footer__links-menu--secondary" aria-label="<?php _e('Secondary footer menu', Theme::TEXT_DOMAIN); ?>">
                                <ul class="site-footer__links-menu-list">
                                    <?php foreach ($footerSecondaryMenuItems as $footerSecondaryMenuItem): ?>
                                        <li class="site-footer__links-menu-item <?php echo $footerSecondaryMenuItem->linkClass; ?>">
                                            <a href="<?php echo $footerSecondaryMenuItem->link; ?>"
                                            class="site-footer__links-menu-item-link"
                                            target="<?php echo $footerSecondaryMenuItem->target; ?>"
                                            
                                            <?php if ($footerSecondaryMenuItem->rel): ?>rel="<?php echo $footerSecondaryMenuItem->rel; ?>"<?php endif; ?>>
                                                <?php echo $footerSecondaryMenuItem->label; ?>
                                                <?php if ($footerSecondaryMenuItem->target === '_blank'): ?>
                                                    <?php echo WcagComponent::getSrLinkOpensInNewTabHtml(); ?>
                                                <?php endif; ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>