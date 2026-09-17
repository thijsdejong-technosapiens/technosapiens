<?php

use TechnoSapiens\Core;
use TechnoSapiens\Core\AdminBarLightComponent;

//handle primary items
$primaryItems = AdminBarLightComponent::getPrimaryAdminBarLightItems();
$hasPrimaryItems = count($primaryItems) > 0;

//handle secondary items
$secondaryItems = AdminBarLightComponent::getSecondaryAdminBarLightItems();
$hasSecondaryItems = count($secondaryItems) > 0;

//bail if no items
if (!$hasPrimaryItems && !$hasSecondaryItems) return false;

?>

<nav class="admin-bar-light" aria-label="<?php _e("Admin bar light", Core::TEXT_DOMAIN) ?>">

    <ul class="admin-bar-light__list">
        <?php if ($hasPrimaryItems): ?>
            <?php foreach ($primaryItems as $primaryItem): ?>
                <?php $label = !empty($primaryItem->label) ? $primaryItem->label : __('N/A', Core::TEXT_DOMAIN); ?>
                <li class="admin-bar-light__item">
                    <a class="admin-bar-light__link admin-bar-light__link--style-primary"
                       title="<?php echo !empty($primaryItem->title) ? $primaryItem->title : $label; ?>"
                       href="<?php echo !empty($primaryItem->href) ? $primaryItem->href : '#'; ?>"
                       target="<?php echo !empty($primaryItem->target) ? $primaryItem->target : '_self'; ?>">
                        <?php if (!empty($primaryItem->icon_data)): ?>
                            <span class="admin-bar-light__link-icon-container" role="presentation">
                                <?php if ($primaryItem->icon_data['type'] === 'dashicon'): ?>
                                    <span class="admin-bar-light__link-icon admin-bar-light__link-icon--dashicon <?php echo esc_attr($primaryItem->icon_data['value']); ?>"></span>
                                <?php elseif ($primaryItem->icon_data['type'] === 'svg_data'): ?>
                                    <img class="admin-bar-light__link-icon admin-bar-light__link-icon--svg" src="<?php echo esc_attr($primaryItem->icon_data['value']); ?>" alt="" role="presentation">
                                <?php elseif ($primaryItem->icon_data['type'] === 'image_url'): ?>
                                    <img class="admin-bar-light__link-icon admin-bar-light__link-icon--image" src="<?php echo esc_url($primaryItem->icon_data['value']); ?>" alt="" role="presentation">
                                <?php endif; ?>
                            </span>
                        <?php elseif (!empty($primaryItem->icon)): ?>
                            <span class="admin-bar-light__link-icon-container" role="presentation">
                                <svg class="admin-bar-light__link-icon">
                                    <use xlink:href="<?php echo WCP_ICON_PATH . $primaryItem->icon; ?>"/>
                                </svg>
                            </span>
                        <?php endif; ?>
                        <span class="admin-bar-light__link-text">
                            <?php echo $label; ?>
                        </span>
                    </a>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php if ($hasSecondaryItems): ?>
            <li class="admin-bar-light__item">
                <input type="checkbox" id="admin-bar-light-more" class="admin-bar-light__checkbox-more"/>
                <label for="admin-bar-light-more"
                       aria-label="<?php _e("Toggle the secondary action list", Core::TEXT_DOMAIN); ?>"
                       class="admin-bar-light__link admin-bar-light__link--toggle-more admin-bar-light__link--style-primary">
                    <span class="admin-bar-light__link-icon-container" role="presentation">
                        <svg class="admin-bar-light__link-icon admin-bar-light__link-icon--open">
                            <use xlink:href="<?php echo WCP_ICON_PATH ?>icon-dots-vertical"/>
                        </svg>
                        <svg class="admin-bar-light__link-icon admin-bar-light__link-icon--close">
                            <use xlink:href="<?php echo WCP_ICON_PATH ?>icon-close"/>
                        </svg>
                    </span>
                </label>
                <ul class="admin-bar-light__list-more">
                    <?php foreach ($secondaryItems as $secondaryItem): ?>
                        <?php if (!empty($secondaryItem->heading)): ?>
                            <li class="admin-bar-light__item">
                                <strong class="admin-bar-light__heading admin-bar-light__heading--style-secondary">
                                    <?php echo $secondaryItem->heading; ?>
                                </strong>
                            </li>
                        <?php else: ?>
                            <?php $label = !empty($secondaryItem->label) ? $secondaryItem->label : __('N/A', Core::TEXT_DOMAIN); ?>
                            <li class="admin-bar-light__item">
                                <a class="admin-bar-light__link admin-bar-light__link--style-secondary"
                                   title="<?php echo !empty($secondaryItem->title) ? $secondaryItem->title : $label; ?>"
                                   href="<?php echo !empty($secondaryItem->href) ? $secondaryItem->href : '#'; ?>"
                                   target="<?php echo !empty($secondaryItem->target) ? $secondaryItem->target : '_self'; ?>">
                                    <?php if (!empty($secondaryItem->icon_data)): ?>
                                        <span class="admin-bar-light__link-icon-container" role="presentation">
                                            <?php if ($secondaryItem->icon_data['type'] === 'dashicon'): ?>
                                                <span class="admin-bar-light__link-icon admin-bar-light__link-icon--dashicon <?php echo esc_attr($secondaryItem->icon_data['value']); ?>"></span>
                                            <?php elseif ($secondaryItem->icon_data['type'] === 'svg_data'): ?>
                                                <img class="admin-bar-light__link-icon admin-bar-light__link-icon--svg" src="<?php echo esc_attr($secondaryItem->icon_data['value']); ?>" alt="" role="presentation">
                                            <?php elseif ($secondaryItem->icon_data['type'] === 'image_url'): ?>
                                                <img class="admin-bar-light__link-icon admin-bar-light__link-icon--image" src="<?php echo esc_url($secondaryItem->icon_data['value']); ?>" alt="" role="presentation">
                                            <?php endif; ?>
                                        </span>
                                    <?php elseif (!empty($secondaryItem->icon)): ?>
                                        <span class="admin-bar-light__link-icon-container" role="presentation">
                                        <svg class="admin-bar-light__link-icon">
                                            <use xlink:href="<?php echo WCP_ICON_PATH . $secondaryItem->icon; ?>"/>
                                        </svg>
                                    </span>
                                    <?php endif; ?>
                                    <span class="admin-bar-light__link-text">
                                    <?php echo $label; ?>
                                </span>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </li>
        <?php endif; ?>
    </ul>
</nav>
