<?php

use TechnoSapiens\Core\ButtonComponent;

/**
 * Allows skipping past content by using the tab button
 * @var string $hash
 * @var string $text
 */

//default values
if (!isset($hash)) $hash = '';
if (!isset($text)) $text = '';

//bail if no hash or text is provided
if (!$hash || !$text) return false;

echo ButtonComponent::render([
    'text' => $text,
    'href' => '#' . esc_attr($hash),
    'target' => '_self',
    'class' => 'ts-skip-link',
    'type' => 'primary',
    'style' => 'outlined',
    'size' => 'medium'
]);
