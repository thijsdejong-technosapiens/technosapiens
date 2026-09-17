<?php

/**
 * @var array     $mobileMenuTopItems
 * @var array     $mobileMenuBottomItems
 * @var \stdClass $emergencyPhoneNumberData
 * @var \stdClass $googleRatingData
 * @var \stdClass $headerButton
 */

use TechnoSapiens\Theme;
use TechnoSapiens\HeaderUtils;
use TechnoSapiens\Core\ButtonComponent;
use TechnoSapiens\Core\Image;
use TechnoSapiens\Core\Link;
use TechnoSapiens\Core\Partial;
use TechnoSapiens\Core\WcagComponent;

$mobileMenuTopItems = $mobileMenuTopItems ?? [];
$mobileMenuBottomItems = $mobileMenuBottomItems ?? [];
$emergencyPhoneNumberData = $emergencyPhoneNumberData ?? null;
$googleRatingData = $googleRatingData ?? null;
$hasMobileMenuItems = count($mobileMenuTopItems) > 0 || count($mobileMenuBottomItems) > 0;

if (!$hasMobileMenuItems) return false;

// Logo data
$blogName = get_bloginfo('name');
$logoId = get_field('logo_image', 'option') ?: '';
$logoHtml = '';
if ($logoId) $logoHtml = Image::render([
    'class' => 'side-navigation__logo-image',
    'lazy' => null,
    'alt' => sprintf(__("Logo %s", Theme::TEXT_DOMAIN), $blogName),
    'sources' => [['id' => $logoId]]
]);

?>

<dialog class="side-navigation ts-dialog"
        id="side-navigation"
        aria-labelledby="side-navigation-label"
        data-close-on-overlay-click="true"
        inert>

    <div class="side-navigation__dynamic-wrapper">

        <div class="side-navigation__header">

            <?php if ($googleRatingData && $googleRatingData->isValid): ?>
                <?php Partial::render('components/component-google-rating', [
                    'rating' => $googleRatingData->rating,
                    'url' => $googleRatingData->url,
                ]); ?>
            <?php endif; ?>
            
            <?php if ($logoHtml): ?>
                <a href="<?php echo Link::getHomePageUrl(); ?>"
                class="side-navigation__logo"
                aria-label="<?php _e("Navigate to the home page", Theme::TEXT_DOMAIN); ?>"
                tabindex="-1">
                    
                    <?php echo $logoHtml; ?>
                </a>
            <?php endif; ?>

            <?php Partial::render('header/menu-toggle-button', ['modifier' => 'close', 'autofocus' => true]); ?>

        </div>

        <div class="side-navigation__navs-wrapper">

            <nav class="side-navigation__navigation side-navigation__navigation--root" aria-label="<?php esc_attr_e("Side menu", Theme::TEXT_DOMAIN); ?>">

                <ul class="side-navigation__navigation-wrapper" id="side-navigation-label">

                    <?php if (count($mobileMenuTopItems) > 0): ?>

                        <?php foreach ($mobileMenuTopItems as $item): ?>

                            <?php
                            $item = apply_filters('ts_side_navigation_item', $item);
                            $hasChildren = count($item->children) > 0;
                            ?>

                            <li class="<?php echo implode(' ', HeaderUtils::getSideNavigationListItemClassesByItem($item, 'side-navigation__navigation-item side-navigation__navigation-item--secondary')); ?>">

                                <?php if (!$hasChildren): ?>

                                    <a class="side-navigation__link side-navigation__link--secondary"
                                       href="<?php echo $item->link; ?>"
                                       target="<?php echo $item->target; ?>"
                                       <?php if ($item->rel): ?>rel="<?php echo $item->rel; ?>"<?php endif; ?>>

                                        <span class="side-navigation__link-text">
                                            <?php echo $item->label; ?>
                                            <?php if ($item->target === '_blank'): ?>
                                                <?php echo WcagComponent::getSrLinkOpensInNewTabHtml(); ?>
                                            <?php endif; ?>
                                        </span>

                                    </a>

                                <?php else: ?>

                                    <?php $uniqueId = uniqid(); ?>

                                    <button class="side-navigation__link side-navigation__link--children side-navigation__link--secondary"
                                            aria-label="<?php esc_attr_e(sprintf("Open %s sub menu", $item->label), Theme::TEXT_DOMAIN); ?>"
                                            aria-controls="side-navigation-sec-<?php echo $uniqueId; ?>"
                                            aria-haspopup="true"
                                            aria-expanded="false">

                                        <span class="side-navigation__link-text">
                                            <?php echo $item->label; ?>
                                        </span>

                                        <svg class="side-navigation__link-icon">
                                            <use xlink:href="<?php echo ICON_PATH; ?>icon-arrow-right"/>
                                        </svg>

                                    </button>

                                    <div class="side-navigation__navigation side-navigation__navigation--second"
                                         id="side-navigation-sec-<?php echo $uniqueId; ?>"
                                         inert>

                                        <ul class="side-navigation__navigation-wrapper">

                                            <li class="side-navigation__navigation-item">

                                                <button class="side-navigation__link side-navigation__link--top side-navigation__link--menu-back"
                                                        aria-controls="side-navigation-sec-<?php echo $uniqueId; ?>"
                                                        aria-label="<?php esc_attr_e(sprintf("Close %s sub menu", $item->label), Theme::TEXT_DOMAIN); ?>"
                                                        autofocus>

                                                    <svg class="side-navigation__link-icon">
                                                        <use xlink:href="<?php echo ICON_PATH; ?>icon-arrow-left"/>
                                                    </svg>

                                                    <span class="side-navigation__link-text">
                                                        <?php _e("Back", Theme::TEXT_DOMAIN); ?>
                                                    </span>

                                                </button>

                                            </li>

                                            <?php foreach ($item->children as $itemChild): ?>

                                                <li class="<?php echo implode(' ', HeaderUtils::getSideNavigationListItemClassesByItem($itemChild, 'side-navigation__navigation-item')); ?>">

                                                    <a class="side-navigation__link"
                                                       href="<?php echo $itemChild->link; ?>"
                                                       target="<?php echo $itemChild->target; ?>"
                                                       <?php if ($itemChild->rel): ?>rel="<?php echo $itemChild->rel; ?>"<?php endif; ?>>

                                                        <span class="side-navigation__link-text">
                                                            <?php echo $itemChild->label; ?>
                                                            <?php if ($itemChild->target === '_blank'): ?>
                                                                <?php echo WcagComponent::getSrLinkOpensInNewTabHtml(); ?>
                                                            <?php endif; ?>
                                                        </span>

                                                    </a>

                                                </li>

                                            <?php endforeach; ?>

                                        </ul>

                                    </div>

                                <?php endif; ?>

                            </li>

                        <?php endforeach; ?>

                    <?php endif; ?>

                    <?php if (count($mobileMenuBottomItems) > 0): ?>
                        <li class="side-navigation__navigation-item side-navigation__navigation-item--separator" aria-hidden="true">
                            <div class="side-navigation__separator">
                                <span class="side-navigation__separator-line"></span>

                                <svg class="side-navigation__separator-icon" aria-hidden="true">
                                    <use xlink:href="<?php echo ICON_PATH; ?>icon-baby"/>
                                </svg>

                            </div>
                        </li>

                        <?php foreach ($mobileMenuBottomItems as $item): ?>
                            <?php
                            $item = apply_filters('ts_side_navigation_item', $item);
                            $hasChildren = count($item->children) > 0;
                            ?>

                            <li class="<?php echo implode(' ', HeaderUtils::getSideNavigationListItemClassesByItem($item, 'side-navigation__navigation-item side-navigation__navigation-item--large')); ?>">

                                <?php if (!$hasChildren): ?>

                                    <a class="side-navigation__link"
                                    href="<?php echo $item->link; ?>"
                                    target="<?php echo $item->target; ?>"
                                    <?php if ($item->rel): ?>rel="<?php echo $item->rel; ?>"<?php endif; ?>>

                                        <span class="side-navigation__link-text">
                                            <?php echo $item->label; ?>
                                            <?php if ($item->target === '_blank'): ?>
                                                <?php echo WcagComponent::getSrLinkOpensInNewTabHtml(); ?>
                                            <?php endif; ?>
                                        </span>

                                    </a>

                                <?php else: ?>

                                    <?php $uniqueId = uniqid(); ?>

                                    <button class="side-navigation__link side-navigation__link--children"
                                            aria-label="<?php esc_attr_e(sprintf("Open %s sub menu", $item->label), Theme::TEXT_DOMAIN); ?>"
                                            aria-controls="side-navigation-<?php echo $uniqueId; ?>"
                                            aria-haspopup="true"
                                            aria-expanded="false">

                                        <span class="side-navigation__link-text">
                                            <?php echo $item->label; ?>
                                        </span>

                                        <svg class="side-navigation__link-icon">
                                            <use xlink:href="<?php echo ICON_PATH; ?>icon-arrow-right"/>
                                        </svg>

                                    </button>

                                    <div class="side-navigation__navigation side-navigation__navigation--second"
                                        id="side-navigation-<?php echo $uniqueId; ?>"
                                        inert>

                                        <ul class="side-navigation__navigation-wrapper">

                                            <li class="side-navigation__navigation-item">

                                                <button class="side-navigation__link side-navigation__link--top side-navigation__link--menu-back"
                                                        aria-controls="side-navigation-<?php echo $uniqueId; ?>"
                                                        aria-label="<?php esc_attr_e(sprintf("Close %s sub menu", $item->label), Theme::TEXT_DOMAIN); ?>"
                                                        autofocus>

                                                    <svg class="side-navigation__link-icon">
                                                        <use xlink:href="<?php echo ICON_PATH; ?>icon-arrow-left"/>
                                                    </svg>

                                                    <span class="side-navigation__link-text">
                                                        <?php _e("Back", Theme::TEXT_DOMAIN); ?>
                                                    </span>

                                                </button>

                                            </li>

                                            <?php if ($item->link !== '#'): ?>

                                                <li class="<?php echo implode(' ', HeaderUtils::getSideNavigationListItemClassesByItem($item, 'side-navigation__navigation-item')); ?>">

                                                    <a href="<?php echo $item->link; ?>"
                                                    class="side-navigation__link sub-title-link"
                                                    target="<?php echo $item->target; ?>"
                                                    <?php if ($item->rel): ?>rel="<?php echo $item->rel; ?>"<?php endif; ?>>

                                                        <strong class="side-navigation__sub-title">
                                                            <?php echo $item->label; ?>
                                                            <?php if ($item->target === '_blank'): ?>
                                                                <?php echo WcagComponent::getSrLinkOpensInNewTabHtml(); ?>
                                                            <?php endif; ?>
                                                        </strong>

                                                    </a>

                                                </li>

                                            <?php endif; ?>

                                            <?php foreach ($item->children as $itemChild): ?>

                                                <?php $hasGrandChildren = count($itemChild->children) > 0; ?>

                                                <li class="<?php echo implode(' ', HeaderUtils::getSideNavigationListItemClassesByItem($itemChild, 'side-navigation__navigation-item')); ?>">

                                                    <?php if (!$hasGrandChildren): ?>

                                                        <a class="side-navigation__link"
                                                        href="<?php echo $itemChild->link; ?>"
                                                        target="<?php echo $itemChild->target; ?>"
                                                        <?php if ($itemChild->rel): ?>rel="<?php echo $itemChild->rel; ?>"<?php endif; ?>>

                                                            <span class="side-navigation__link-text">
                                                                <?php echo $itemChild->label; ?>
                                                                <?php if ($itemChild->target === '_blank'): ?>
                                                                    <?php echo WcagComponent::getSrLinkOpensInNewTabHtml(); ?>
                                                                <?php endif; ?>
                                                            </span>

                                                        </a>

                                                    <?php else: ?>

                                                        <?php $uniqueIdGrandchild = uniqid(); ?>

                                                        <button
                                                            class="side-navigation__link side-navigation__link--children"
                                                            aria-haspopup="true"
                                                            aria-controls="side-navigation-<?php echo $uniqueIdGrandchild; ?>"
                                                            aria-expanded="false">

                                                            <span class="side-navigation__link-text">
                                                                <?php echo $itemChild->label; ?>
                                                            </span>

                                                            <svg class="side-navigation__link-icon">
                                                                <use xlink:href="<?php echo ICON_PATH; ?>icon-arrow-right"/>
                                                            </svg>

                                                        </button>

                                                        <div id="side-navigation-<?php echo $uniqueIdGrandchild; ?>"
                                                            class="side-navigation__navigation side-navigation__navigation--third"
                                                            inert>

                                                            <ul class="side-navigation__navigation-wrapper">

                                                                <li class="side-navigation__navigation-item">

                                                                    <button
                                                                        class="side-navigation__link side-navigation__link--top side-navigation__link--menu-back"
                                                                        aria-controls="side-navigation-<?php echo $uniqueIdGrandchild; ?>"
                                                                        aria-label="<?php esc_attr_e(sprintf("Close %s sub menu", $itemChild->label), Theme::TEXT_DOMAIN); ?>"
                                                                        autofocus>

                                                                        <svg class="side-navigation__link-icon">
                                                                            <use xlink:href="<?php echo ICON_PATH; ?>icon-arrow-left"/>
                                                                        </svg>

                                                                        <span class="side-navigation__link-text">
                                                                            <?php _e("Back", Theme::TEXT_DOMAIN); ?>
                                                                        </span>

                                                                    </button>

                                                                </li>

                                                                <?php if ($itemChild->link !== '#'): ?>

                                                                    <li class="<?php echo implode(' ', HeaderUtils::getSideNavigationListItemClassesByItem($item, 'side-navigation__navigation-item')); ?>">

                                                                        <a href="<?php echo $itemChild->link; ?>"
                                                                        class="side-navigation__link sub-title-link"
                                                                        target="<?php echo $itemChild->target; ?>"
                                                                        <?php if ($itemChild->rel): ?>rel="<?php echo $itemChild->rel; ?>"<?php endif; ?>>

                                                                            <strong class="side-navigation__sub-title">
                                                                                <?php echo $itemChild->label; ?>
                                                                                <?php if ($itemChild->target === '_blank'): ?>
                                                                                    <?php echo WcagComponent::getSrLinkOpensInNewTabHtml(); ?>
                                                                                <?php endif; ?>
                                                                            </strong>

                                                                        </a>

                                                                    </li>

                                                                <?php endif; ?>

                                                                <?php foreach ($itemChild->children as $grandChildItem): ?>

                                                                    <li class="<?php echo implode(' ', HeaderUtils::getSideNavigationListItemClassesByItem($grandChildItem, 'side-navigation__navigation-item')); ?>">

                                                                        <a class="side-navigation__link"
                                                                        href="<?php echo $grandChildItem->link; ?>"
                                                                        target="<?php echo $grandChildItem->target; ?>"
                                                                        <?php if ($grandChildItem->rel): ?>rel="<?php echo $grandChildItem->rel; ?>"<?php endif; ?>>

                                                                            <span class="side-navigation__link-text">
                                                                                <?php echo $grandChildItem->label; ?>
                                                                                <?php if ($grandChildItem->target === '_blank'): ?>
                                                                                    <?php echo WcagComponent::getSrLinkOpensInNewTabHtml(); ?>
                                                                                <?php endif; ?>
                                                                            </span>

                                                                        </a>

                                                                    </li>

                                                                <?php endforeach; ?>

                                                            </ul>

                                                        </div>

                                                    <?php endif; ?>

                                                </li>

                                            <?php endforeach; ?>

                                        </ul>

                                    </div>

                                <?php endif; ?>

                            </li>

                        <?php endforeach; ?>

                    <?php endif; ?>                    

                    <?php if ($headerButton && $headerButton->isValid): ?>
                        <li class="side-navigation__navigation-item side-navigation__navigation-item--header-button">
                            <?php echo ButtonComponent::render([
                                'text' => $headerButton->text,
                                'href' => $headerButton->url,
                                'target' => $headerButton->target,
                                'type' => 'secondary',
                                'style' => 'filled',
                                'size' => 'medium',
                                'class' => 'side-navigation__header-button',
                            ]); ?>
                        </li>
                    <?php endif; ?>
                </ul>

            </nav>

        </div>

        <div class="side-navigation__footer">

            <?php if ($emergencyPhoneNumberData && $emergencyPhoneNumberData->isValid): ?>
                <?php echo ButtonComponent::render([
                    'text' => $emergencyPhoneNumberData->text,
                    'href' => 'tel:' . $emergencyPhoneNumberData->url,
                    'target' => $emergencyPhoneNumberData->target,
                    'type' => 'primary',
                    'style' => 'filled',
                    'size' => 'large',
                    'class' => 'side-navigation__emergency-button'
                ]); ?>
            <?php endif; ?>

        </div>

    </div>

</dialog>
