<?php

use TechnoSapiens\Core\Navigation;
use TechnoSapiens\Core\Partial;
use TechnoSapiens\Theme;
use TechnoSapiens\ThemeSettings;
use TechnoSapiens\Core\ButtonComponent;

//get navigation links
$mainItems = Navigation::getLinksByThemeLocation(Theme::MENU_PRIMARY);
$secondaryItems = Navigation::getLinksByThemeLocation(Theme::MENU_SECONDARY);
$mobileMenuTopItems = Navigation::getLinksByThemeLocation(Theme::MENU_MOBILE_TOP);
$mobileMenuBottomItems = Navigation::getLinksByThemeLocation(Theme::MENU_MOBILE_BOTTOM);
$mergedItems = array_merge($mainItems, $secondaryItems);

//get header button data
$headerButton = ThemeSettings::getHeaderButtonData();

//get emergency phone number data
$emergencyPhoneNumberData = ThemeSettings::getEmergencyPhoneNumberData();

//get HTML classes
$htmlClasses = apply_filters('ts_html_classes', ['ts-' . (is_admin_bar_showing() ? 'has-admin-bar' : 'has-no-admin-bar')]);

?>
<!doctype html>
<html <?php language_attributes(); ?><?php echo count($htmlClasses) > 0 ? ' class="' . implode(' ', $htmlClasses) . '"' : ''; ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes, maximum-scale=5"/>

        <title><?php wp_title('|', true, 'right'); ?></title>
        
        <link rel="prefetch" as="font"
              href="<?php echo get_stylesheet_directory_uri() . '/fonts/poppins-v24-latin-500/poppins-v24-latin-200.woff2'; ?>"/>
        <link rel="prefetch" as="font"
              href="<?php echo get_stylesheet_directory_uri() . '/fonts/poppins-v24-latin-regular/poppins-v24-latin-regular.woff2'; ?>"/>
        <link rel="prefetch" as="font"
              href="<?php echo get_stylesheet_directory_uri() . '/fonts/poppins-v24-latin-500/poppins-v24-latin-500.woff2'; ?>"/>
        <link rel="prefetch" as="font"
              href="<?php echo get_stylesheet_directory_uri() . '/fonts/poppins-v24-latin-600/poppins-v24-latin-600.woff2'; ?>"/>
        <?php wp_head(); ?>
    </head>

<body <?php body_class(); ?>>

<?php do_action('body_open'); ?>

    <?php Partial::render('components/component-skip-link', [
        'hash' => 'ts-main',
        'text' => __("Skip to content", Theme::TEXT_DOMAIN)
    ]); ?>

    <header class="ts-header">

        <div class="ts-header__container container">

            <div class="ts-header__emergency-phone-number">
                <?php if ($emergencyPhoneNumberData->isValid): ?>
                    <?php echo ButtonComponent::render([
                        'text' => $emergencyPhoneNumberData->text,
                        'href' => 'tel:' . $emergencyPhoneNumberData->url,
                        'target' => $emergencyPhoneNumberData->target,
                        'type' => 'primary',
                        'style' => 'filled',
                        'size' => 'medium',
                        'class' => 'ts-header__emergency-phone-number-button'
                    ]); ?>
                <?php endif; ?>
            </div>

            <div class="ts-header__inner">
            
                <div class="ts-header__left">
                    <?php Partial::render('header/header-logo'); ?>
                </div>

                <div class="ts-header__right">
                    <?php Partial::render('header/main-navigation', ['items' => $mainItems, 'headerButton' => $headerButton]); ?>
                </div>

            </div>

            <div class="ts-header__top">
                <?php Partial::render('header/top-navigation', [
                    'items' => $secondaryItems,
                    'googleRatingData' => ThemeSettings::getGoogleRatingData(),
                ]); ?>
            </div>

        </div>

        <?php if (count($mergedItems) > 0) Partial::render('header/side-navigation', [
            'mobileMenuTopItems' => $mobileMenuTopItems,
            'mobileMenuBottomItems' => $mobileMenuBottomItems,
            'emergencyPhoneNumberData' => $emergencyPhoneNumberData,
            'googleRatingData' => ThemeSettings::getGoogleRatingData(),
            'headerButton' => $headerButton,
        ]); ?>

        <?php do_action('ts_render_search_overlay'); ?>

    </header>