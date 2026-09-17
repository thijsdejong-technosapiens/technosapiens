<?php

/**
 * @var string $preset
 * @var \stdClass $data
 * @var \stdClass $parentItem
 */

use TechnoSapiens\Core\ButtonComponent;
use TechnoSapiens\MegaMenuPlugin;
use TechnoSapiens\MegaMenuPlugin\NavMenuItemResolver;

//default values
if (!isset($preset)) $preset = false;
if (!isset($data)) $data = false;
if (!isset($parentItem)) $parentItem = false;

//bail if no parent item or data is given
if (!$parentItem || !$data || !$preset) return false;

//get mm ID from the item
$mmId = NavMenuItemResolver::getMegaMenuIdForItem($parentItem);

//bail if no mm ID found
if (!$mmId) return false;

//create unique ID for the mega menu trigger
$uniqueMmId = uniqid('trigger-mm-') . '-' . $mmId;

//create unique IDs per render instance for the container and overlay to avoid collisions
$containerId = 'mega-menu-' . $mmId . '-' . uniqid();

?>

<button class="main-navigation__item-sub-toggle main-navigation__sub-toggle"
        id="<?php echo $uniqueMmId; ?>"
        aria-label="<?php esc_attr_e(sprintf('Toggle mega menu "%s"', $parentItem->label), MegaMenuPlugin::TEXT_DOMAIN); ?>"
        aria-haspopup="true"
        aria-expanded="false"
        aria-controls="<?php echo $containerId; ?>">
    <svg class="main-navigation__item-icon" aria-hidden="true">
        <use xlink:href="<?php echo ICON_PATH; ?>chevron-down-clean"/>
    </svg>
</button>

<div class="mega-menu-navigation__close-overlay ts-backdrop"
     id="overlay-<?php echo $containerId; ?>"
     role="presentation"></div>

<div class="mega-menu-navigation mega-menu-navigation--preset-<?php echo esc_attr($preset); ?>"
     id="<?php echo $containerId; ?>"
     data-mm-id="<?php echo $mmId; ?>"
     data-mm-preset="<?php echo esc_attr($preset); ?>"
     aria-label="<?php esc_attr_e(sprintf('Mega menu "%s"', $parentItem->label), MegaMenuPlugin::TEXT_DOMAIN); ?>">

    <div class="mega-menu-navigation__close-wrapper">
        <div class="mega-menu-navigation__close-container container">
            <?php echo ButtonComponent::render([
                'tag' => 'button',
                'class' => 'mega-menu-navigation__close-button',
                'icon' => 'icon-close',
                'iconPosition' => 'left',
                'size' => 'small',
                'style' => 'text',
                'text' => __('Close menu', MegaMenuPlugin::TEXT_DOMAIN)
            ]); ?>
        </div>
    </div>

    <?php echo MegaMenuPlugin::isFullWidthContent() ? '' : '<div class="mega-menu-navigation__container container">'; ?>
