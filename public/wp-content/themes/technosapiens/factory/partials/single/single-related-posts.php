<?php

use TechnoSapiens\Core\ButtonComponent;
use TechnoSapiens\Core\PostType;
use TechnoSapiens\FactoryPlugin;
use TechnoSapiens\FactoryPlugin\PostTypeFactory;

global $post;

//get current post type and factory class
$postType = PostType::getPostType();
$factoryClass = PostTypeFactory::getFactoryClassByPostType($postType);

//bail if no post type or factory class found
if (!$postType || !$factoryClass) return false;

//get related posts
$relatedPosts = $factoryClass::getRelatedPosts(get_the_ID());

//bail if no related posts
if (count($relatedPosts) === 0) return false;

//get snippet column classes
$snippetColumnClasses = $factoryClass::getColumnClasses();

//get post type archive link
$archiveLink = PostType::getPostTypeArchiveLink($postType);
$enableArchiveButton = $factoryClass::featureEnabled('enableSingleRelatedPostsButton') && $archiveLink;

//get post type labels
$postTypeLabelPlural = strtolower(PostType::getPluralLabel($postType)) ?: __("items", FactoryPlugin::TEXT_DOMAIN);

?>

<section class="single-related-posts" aria-labelledby="single-related-posts__heading">

    <div class="single-related-posts__container container">

        <div class="single-related-posts__header">

            <span class="single-related-posts__heading h4" id="single-related-posts__heading">
                <?php echo sprintf(__("Related %s", FactoryPlugin::TEXT_DOMAIN), $postTypeLabelPlural); ?>
            </span>

            <?php if ($enableArchiveButton): ?>
                <?php echo ButtonComponent::render(apply_filters('ts_factory_related_posts_button_args', [
                    'href' => $archiveLink,
                    'text' => sprintf(__("All %1s", FactoryPlugin::TEXT_DOMAIN), $postTypeLabelPlural),
                    'blockClass' => 'single-related-posts',
                    'type' => 'primary',
                    'style' => 'outlined',
                    'size' => 'medium'
                ])); ?>
            <?php endif; ?>

        </div>

        <div class="row single-related-posts__items">
            <?php foreach ($relatedPosts as $index => $post) : setup_postdata($post); ?>
                <div class="<?php echo implode(' ', $snippetColumnClasses); ?>">
                    <?php $factoryClass::renderPartial('snippets/snippet') ?>
                </div>
            <?php endforeach; ?>
            <?php wp_reset_postdata(); ?>
        </div>

    </div>

</section>