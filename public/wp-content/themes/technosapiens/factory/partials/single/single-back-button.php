<?php

use TechnoSapiens\Core\ButtonComponent;
use TechnoSapiens\Core\PostType;
use TechnoSapiens\FactoryPlugin;

//get post type slug
$postType = PostType::getPostType();
$archiveLink = PostType::getPostTypeArchiveLink($postType);
$postTypeLabelPlural = strtolower(PostType::getPluralLabel($postType)) ?: __("items", FactoryPlugin::TEXT_DOMAIN);

//bail if no post type found
if (!$postType || !$archiveLink || !$postTypeLabelPlural) return false;

?>

<div class="single-back-button container">
    <?php echo ButtonComponent::render(apply_filters('ts_factory_related_posts_button_args', [
        'href' => $archiveLink,
        'text' => sprintf(__("All %1s", FactoryPlugin::TEXT_DOMAIN), $postTypeLabelPlural),
        'blockClass' => 'single-related-posts',
        'type' => 'tertiary',
        'style' => 'filled',
        'size' => 'medium',
        'icon' => 'icon-arrow-left',
        'iconPosition' => 'left',
    ])); ?>
</div>