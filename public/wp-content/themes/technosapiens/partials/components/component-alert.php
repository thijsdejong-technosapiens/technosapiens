<?php

/**
 * @var string $text
 * @var string $type 'informational' | 'success' | 'warning' | 'error'
 * @var string $icon 'informational' | 'success' | 'warning' | 'error'
 * @var string $style 'light' | 'solid'
 * @var string $ariaLive 'polite' | 'assertive'
 */

//default values
if (!isset($text)) $text = '';
if (!isset($type)) $type = 'default';
if (!isset($icon)) $icon = $type;
if (!isset($style)) $style = 'light';
if (!isset($ariaLive)) $ariaLive = 'polite';

//bail if no text is provided
if (!$text) return false;

//set up alert classes
$alertClasses = ['ts-alert'];
if ($type) $alertClasses[] = 'ts-alert--type-' . $type;
if ($style) $alertClasses[] = 'ts-alert--style-' . $style;

?>
<div role="alert" aria-live="<?php echo $ariaLive; ?>" class="<?php echo implode(' ', $alertClasses); ?>">
    <?php if ($icon && in_array($icon, ['informational', 'success', 'warning', 'error'])) : ?>
        <svg class="ts-alert__icon" aria-hidden="true">
            <use xlink:href="<?php echo ICON_PATH; ?>icon-<?php echo $icon; ?>"/>
        </svg>
    <?php endif; ?>
    <span class="ts-alert__text">
        <?php echo $text; ?>
    </span>
</div>