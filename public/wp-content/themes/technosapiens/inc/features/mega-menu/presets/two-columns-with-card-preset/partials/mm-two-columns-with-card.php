<?php

/**
 * @var \stdClass $data
 * @var \stdClass $parentItem
 */

use TechnoSapiens\Core\ButtonComponent;
use TechnoSapiens\Core\Image;
use TechnoSapiens\Core\WcagComponent;
use TechnoSapiens\MegaMenuPlugin;

//default values
if (!isset($data)) $data = false;
if (!isset($parentItem)) $parentItem = false;

//bail if no parent item or data is given
if (!$parentItem || !$data) return false;

//get the main navigation label and URL
$mainNavLabel = $parentItem->label ?? '';
$mainNavUrl = $parentItem->link ?? '';

//allow filtering of the level 2 trigger icon href
$triggerIconHref = apply_filters('ts_tcwc_level2_trigger_icon_href', ICON_PATH . 'icon-arrow-right');

?>
<div class="tcwc-preset__row row">

    <nav class="tcwc-preset__col tcwc-preset__col--first col col--1/3 col--xl-1/3"
         aria-label="<?php echo esc_attr($mainNavLabel ? sprintf(__('Browse %s', MegaMenuPlugin::TEXT_DOMAIN), $mainNavLabel) : __('Primary menu', MegaMenuPlugin::TEXT_DOMAIN)); ?>">

        <div class="tcwc-preset__column tcwc-preset__column--level-one tcwc-preset__column--scrollable">

            <div class="tcwc-preset__column-wrapper">

                <?php if ($mainNavLabel): ?>
                    <div class="tcwc-preset__parent-title tcwc-preset__parent-title--main" data-mm-main-parent="true">
                        <?php if ($mainNavUrl && $mainNavUrl !== '#'): ?>
                            <a href="<?php echo esc_url($mainNavUrl); ?>"
                               class="tcwc-preset__parent-title-link tcwc-preset__parent-title-link--has-hover tcwc-preset__link">
                                <span class="tcwc-preset__parent-title-text h5">
                                    <?php echo esc_html($mainNavLabel); ?>
                                </span>
                            </a>
                        <?php else: ?>
                            <span class="tcwc-preset__parent-title-text tcwc-preset__link h5">
                                <?php echo esc_html($mainNavLabel); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <ul class="tcwc-preset__menu tcwc-preset__menu--first">

                    <?php foreach ($data->levelOneLinks as $levelOneLink): ?>

                        <?php

                        //build list item classes using item state
                        $levelOneItemClasses = ['tcwc-preset__item', 'tcwc-preset__item--active'];
                        if ($levelOneLink->isCurrent) $levelOneItemClasses[] = 'tcwc-preset__item--current';
                        if ($levelOneLink->isCurrentParent) $levelOneItemClasses[] = 'tcwc-preset__item--current-parent';

                        ?>

                        <li class="<?php echo implode(' ', $levelOneItemClasses); ?>"
                            data-mm-item="<?php echo $levelOneLink->id; ?>"
                            <?php if ($levelOneLink->hasChildren): ?>data-mm-has-submenu="true"<?php endif; ?>>

                            <a href="<?php echo $levelOneLink->link; ?>"
                               class="tcwc-preset__link<?php if (!empty($levelOneLink->isCurrent)): ?> tcwc-preset__link--current<?php endif; ?>"
                               <?php if ($levelOneLink->target !== '_self'): ?>target="<?php echo $levelOneLink->target; ?>" <?php endif; ?>
                               <?php if (!empty($levelOneLink->rel)): ?>rel="<?php echo $levelOneLink->rel; ?>" <?php endif; ?>
                               <?php if (!empty($levelOneLink->isCurrent)): ?>aria-current="page" <?php endif; ?>>

                                    <span class="tcwc-preset__link-text">
                                        <?php echo $levelOneLink->label; ?>
                                        <?php if ($levelOneLink->target === '_blank'): ?>
                                            <?php echo WcagComponent::getSrLinkOpensInNewTabHtml(); ?>
                                        <?php endif; ?>
                                    </span>

                            </a>

                            <?php if ($levelOneLink->hasChildren): ?>
                                <button type="button"
                                        class="tcwc-preset__trigger"
                                        data-mm-trigger="<?php echo $levelOneLink->id; ?>"
                                        aria-label="<?php echo esc_attr(sprintf(__('Show submenu for %s', MegaMenuPlugin::TEXT_DOMAIN), $levelOneLink->label)); ?>"
                                        aria-haspopup="menu"
                                        aria-expanded="false"
                                        aria-controls="mm-submenu-<?php echo $levelOneLink->id; ?>">
                                    <svg class="tcwc-preset__trigger-icon" aria-hidden="true">
                                        <use xlink:href="<?php echo $triggerIconHref; ?>"/>
                                    </svg>
                                </button>
                            <?php endif; ?>

                        </li>

                    <?php endforeach; ?>

                </ul>

            </div>
        </div>

    </nav>

    <nav class="tcwc-preset__col tcwc-preset__col--second col col--1/3 col--xl-1/3"
         aria-label="<?php echo esc_attr__('Submenu', MegaMenuPlugin::TEXT_DOMAIN); ?>">

        <div class="tcwc-preset__column tcwc-preset__column--scrollable">

            <div class="tcwc-preset__column-wrapper">

                <?php foreach ($data->levelOneLinks as $parentLevelOneLink): ?>

                    <?php if ($parentLevelOneLink->hasChildren): ?>

                        <div class="tcwc-preset__parent-title"
                             id="mm-parent-title-<?php echo $parentLevelOneLink->id; ?>"
                             data-mm-parent="<?php echo $parentLevelOneLink->id; ?>">

                            <?php if ($parentLevelOneLink->link && $parentLevelOneLink->link !== '#'): ?>
                                <a href="<?php echo esc_url($parentLevelOneLink->link); ?>"
                                   class="tcwc-preset__parent-title-link tcwc-preset__link<?php if (!empty($parentLevelOneLink->isCurrent)): ?> tcwc-preset__link--current<?php endif; ?>"
                                   <?php if ($parentLevelOneLink->target !== '_self'): ?>target="<?php echo $parentLevelOneLink->target; ?>" <?php endif; ?>
                                   <?php if (!empty($parentLevelOneLink->rel)): ?>rel="<?php echo $parentLevelOneLink->rel; ?>" <?php endif; ?>
                                   <?php if (!empty($parentLevelOneLink->isCurrent)): ?>aria-current="page" <?php endif; ?>>
                                    <span class="tcwc-preset__parent-title-text h5">
                                        <?php echo esc_html($parentLevelOneLink->label); ?>
                                        <?php if ($parentLevelOneLink->target === '_blank'): ?>
                                            <?php echo WcagComponent::getSrLinkOpensInNewTabHtml(); ?>
                                        <?php endif; ?>
                                    </span>
                                </a>
                            <?php else: ?>
                                <span class="tcwc-preset__parent-title-text tcwc-preset__link h5">
                                    <?php echo esc_html($parentLevelOneLink->label); ?>
                                </span>
                            <?php endif; ?>

                        </div>

                    <?php endif; ?>

                <?php endforeach; ?>

                <?php foreach ($data->levelTwoItemsGrouped as $levelTwoParentId => $levelTwoItems): ?>

                    <ul class="tcwc-preset__submenu"
                        data-mm-submenu="<?php echo $levelTwoParentId; ?>"
                        id="mm-submenu-<?php echo $levelTwoParentId; ?>"
                        aria-labelledby="mm-parent-title-<?php echo $levelTwoParentId; ?>">

                        <?php foreach ($levelTwoItems as $levelTwoItem): ?>

                            <?php

                            //build list item classes for level 2 items using item state
                            $levelTwoItemClasses = ['tcwc-preset__item'];
                            if ($levelTwoItem->isCurrent) $levelTwoItemClasses[] = 'tcwc-preset__item--current';
                            if ($levelTwoItem->isCurrentParent) $levelTwoItemClasses[] = 'tcwc-preset__item--current-parent';

                            ?>

                            <li class="<?php echo implode(' ', $levelTwoItemClasses); ?>">

                                <a href="<?php echo $levelTwoItem->link; ?>"
                                   class="tcwc-preset__link<?php if (!empty($levelTwoItem->isCurrent)): ?> tcwc-preset__link--current<?php endif; ?>"
                                   <?php if ($levelTwoItem->target !== '_self'): ?>target="<?php echo $levelTwoItem->target; ?>" <?php endif; ?>
                                   <?php if (!empty($levelTwoItem->rel)): ?>rel="<?php echo $levelTwoItem->rel; ?>" <?php endif; ?>
                                   <?php if (!empty($levelTwoItem->isCurrent)): ?>aria-current="page" <?php endif; ?>>

                                    <span class="tcwc-preset__link-text">
                                        <?php echo $levelTwoItem->label; ?>
                                        <?php if ($levelTwoItem->target === '_blank'): ?>
                                            <?php echo WcagComponent::getSrLinkOpensInNewTabHtml(); ?>
                                        <?php endif; ?>
                                    </span>

                                </a>

                            </li>

                        <?php endforeach; ?>

                    </ul>

                <?php endforeach; ?>

            </div>
        </div>

    </nav>

    <div class="tcwc-preset__col tcwc-preset__col--third col col--1/3 col--xl-1/3">

        <div class="tcwc-preset__column tcwc-preset__column--scrollable">

            <div class="tcwc-preset__column-wrapper">

                <?php if ($data->contentType === 'image_only'): ?>
                    <?php if ($data->imageId && $data->imageSize): ?>
                        <?php echo Image::render([
                            'blockClass' => 'tcwc-preset',
                            'sources' => [['id' => $data->imageId, 'size' => $data->imageSize]]
                        ]); ?>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($data->contentType === 'image_with_cta' && $data->hasValidCard): ?>

                    <div class="tcwc-preset__cta-wrapper ts-link-snippet" role="link">

                        <?php $imageHtml = $data->imageId && $data->imageSize ? Image::render([
                            'class' => 'tcwc-preset__cta-image',
                            'sources' => [['id' => $data->imageId, 'size' => $data->imageSize]]
                        ]) : ''; ?>

                        <figure class="tcwc-preset__cta-image-container">
                            <?php if ($imageHtml): ?>
                                <?php echo $imageHtml; ?>
                            <?php else: ?>
                                <svg class="tcwc-preset__cta-image-placeholder" aria-hidden="true">
                                    <use xlink:href="<?php echo ICON_PATH; ?>image-placeholder"/>
                                </svg>
                            <?php endif; ?>
                        </figure>

                        <div class="tcwc-preset__cta-meta">

                            <div class="tcwc-preset__cta-meta-top">
                                <?php if ($data->cardTitle): ?>
                                    <span class="tcwc-preset__cta-title">
                                        <a href="<?php echo esc_url($data->cardLinkUrl); ?>"
                                           class="tcwc-preset__cta-title-link h5"
                                           <?php if ($data->cardLinkTarget): ?>target="<?php echo esc_attr($data->cardLinkTarget); ?>"<?php endif; ?>
                                           <?php if ($data->cardLinkRel): ?>rel="<?php echo esc_attr($data->cardLinkRel); ?>"<?php endif; ?>
                                           data-role="primary-link"
                                           draggable="false">
                                            <?php echo esc_html($data->cardTitle); ?>
                                            <?php if ($data->cardLinkTarget === '_blank'): ?>
                                                <?php echo WcagComponent::getSrLinkOpensInNewTabHtml(); ?>
                                            <?php endif; ?>
                                        </a>
                                    </span>
                                <?php endif; ?>
                                <?php if ($data->cardText): ?>
                                    <span class="tcwc-preset__cta-text">
                                        <?php echo esc_html($data->cardText); ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <?php if ($data->cardLinkUrl && $data->cardLinkText): ?>
                                <?php echo ButtonComponent::render([
                                    'tag' => 'span',
                                    'text' => $data->cardLinkText,
                                    'class' => 'tcwc-preset__cta-button',
                                    'type' => $data->cardButtonType,
                                    'style' => $data->cardButtonStyle,
                                    'size' => $data->cardButtonSize
                                ]); ?>
                            <?php endif; ?>

                        </div>

                    </div>

                <?php endif; ?>

            </div>

        </div>

    </div>

</div>
