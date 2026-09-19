<?php

use TechnoSapiens\Core\ButtonComponent;
use TechnoSapiens\Core\Partial;
use TechnoSapiens\Core\WcagComponent;
use TechnoSapiens\HeaderUtils;
use TechnoSapiens\MegaMenuPlugin\NavMenuItemResolver;
use TechnoSapiens\Theme;

/**
 * @var array $items
 * @var stdClass $headerButton
 */

//set default values
if (!isset($items)) $items = [];

?>

<nav class="main-navigation" aria-label="<?php _e("Main navigation", Theme::TEXT_DOMAIN); ?>">

    <ul class="main-navigation__items main-navigation__list">

        <?php foreach ($items as $item): ?>

            <?php

            //get base classes from HeaderUtils
            $itemClasses = HeaderUtils::getMainNavigationListItemClassesByItem($item, 'main-navigation__item');

            //allow filtering of item classes for mega menu from add-ons
            $itemClasses = apply_filters('ts_main_navigation_item_classes', $itemClasses, $item);

            //check if item has a mega menu
            $hasMegaMenu = class_exists('TechnoSapiens\MegaMenuPlugin\NavMenuItemResolver') && NavMenuItemResolver::isMegaMenuItem($item);

            //check if item has children
            $hasChildren = count($item->children) > 0;

            ?>

            <li class="<?php echo implode(' ', $itemClasses); ?>">

                <a class="main-navigation__item-link"
                   href="<?php echo $item->link; ?>"
                   target="<?php echo $item->target; ?>"
                   <?php if ($item->rel): ?>rel="<?php echo $item->rel; ?>"<?php endif; ?>
                   <?php if ($hasChildren && !$hasMegaMenu): ?>
                   aria-haspopup="true"
                   aria-expanded="false"
                   aria-controls="main-navigation__sub-menu-<?php echo $item->id; ?>"
                   <?php endif; ?>>

                    <span class="main-navigation__item-text">
                        <?php echo $item->label; ?>
                        <?php if ($item->target === '_blank'): ?>
                            <?php echo WcagComponent::getSrLinkOpensInNewTabHtml(); ?>
                        <?php endif; ?>
                    </span>

                </a>

                <?php if ($hasMegaMenu): ?>
                    <?php do_action('ts_render_mega_menu', ['parentItem' => $item]); ?>
                <?php elseif ($hasChildren): ?>
                    <button class="main-navigation__item-sub-toggle main-navigation__sub-toggle"
                            aria-label="<?php esc_attr_e(sprintf('Toggle %s sub menu', $item->label), Theme::TEXT_DOMAIN); ?>"
                            aria-haspopup="true"
                            aria-expanded="false"
                            aria-controls="main-navigation__sub-menu-<?php echo $item->id; ?>">
                        <svg class="main-navigation__item-icon">
                            <use xlink:href="<?php echo ICON_PATH; ?>chevron-down-clean"/>
                        </svg>
                    </button>

                    <ul class="main-navigation__sub-menu main-navigation__list"
                        id="main-navigation__sub-menu-<?php echo $item->id; ?>">

                        <?php foreach ($item->children as $itemChild): ?>

                            <?php $hasGrandChildren = count($itemChild->children) > 0; ?>

                            <li class="<?php echo implode(' ', HeaderUtils::getMainNavigationListItemClassesByItem($itemChild, 'main-navigation__sub-menu-item')); ?>">

                                <a class="main-navigation__sub-menu-item-link"
                                   href="<?php echo $itemChild->link; ?>"
                                   target="<?php echo $itemChild->target; ?>"
                                   <?php if ($itemChild->rel): ?>rel="<?php echo $itemChild->rel; ?>"<?php endif; ?>>

                                    <span class="main-navigation__sub-menu-item-text">
                                        <?php echo $itemChild->label; ?>
                                        <?php if ($itemChild->target === '_blank'): ?>
                                            <?php echo WcagComponent::getSrLinkOpensInNewTabHtml(); ?>
                                        <?php endif; ?>
                                    </span>

                                </a>

                                <?php if ($hasGrandChildren): ?>

                                    <button
                                        class="main-navigation__sub-menu-item-sub-toggle main-navigation__sub-toggle"
                                        aria-label="<?php esc_attr_e(sprintf('Toggle %s sub menu', $itemChild->label), Theme::TEXT_DOMAIN); ?>"
                                        aria-expanded="false"
                                        aria-haspopup="true"
                                        aria-controls="main-navigation__second-sub-menu-<?php echo $itemChild->id; ?>">
                                        <svg class="main-navigation__item-icon main-navigation__item-icon--rotated">
                                            <use xlink:href="<?php echo ICON_PATH; ?>chevron-down-clean"/>
                                        </svg>
                                    </button>

                                    <ul class="main-navigation__second-sub-menu main-navigation__list"
                                        id="main-navigation__second-sub-menu-<?php echo $itemChild->id; ?>">

                                        <?php foreach ($itemChild->children as $grandChildItem): ?>

                                            <li class="<?php echo implode(' ', HeaderUtils::getMainNavigationListItemClassesByItem($grandChildItem, 'main-navigation__second-sub-menu-item')); ?>">

                                                <a class="main-navigation__second-sub-menu-item-link"
                                                   href="<?php echo $grandChildItem->link; ?>"
                                                   target="<?php echo $grandChildItem->target; ?>"
                                                   <?php if ($grandChildItem->rel): ?>rel="<?php echo $grandChildItem->rel; ?>"<?php endif; ?>>

                                                    <span class="main-navigation__second-sub-menu-item-text">
                                                        <?php echo $grandChildItem->label; ?>
                                                        <?php if ($grandChildItem->target === '_blank'): ?>
                                                            <?php echo WcagComponent::getSrLinkOpensInNewTabHtml(); ?>
                                                        <?php endif; ?>
                                                    </span>

                                                </a>

                                            </li>

                                        <?php endforeach; ?>

                                    </ul>

                                <?php endif; ?>

                            </li>

                        <?php endforeach; ?>

                    </ul>

                <?php endif; ?>

            </li>

        <?php endforeach; ?>

        <?php if ($headerButton->isValid): ?>

            <li class="main-navigation__item main-navigation__item--header-button">

                <?php echo ButtonComponent::render([
                    'text' => $headerButton->text,
                    'href' => $headerButton->url,
                    'target' => $headerButton->target,
                    'type' => 'tertiary',
                    'style' => 'filled',
                    'size' => 'medium'
                ]); ?>

            </li>

            <?php endif; ?>
    </ul>

    <?php do_action('ts_render_search_trigger', ['element' => 'div']); ?>

    <?php Partial::render('header/menu-toggle-button', ['modifier' => 'open']); ?>
</nav>