<?php

use TechnoSapiens\BreadcrumbsComponent;
use TechnoSapiens\Core\Partial;
use TechnoSapiens\Core\PostType;
use TechnoSapiens\FactoryPlugin\PostTypeFactory;

//get current post type and factory class
$postType = PostType::getPostType();
$factoryClass = PostTypeFactory::getFactoryClassByPostType($postType);

//bail if no post type or factory class found
if (!$postType || !$factoryClass) return false;

?>

<?php get_header(); ?>

    <main id="ts-main" class="single-default__container ts-md-child-margins">

        <?php if (BreadcrumbsComponent::breadcrumbsEnabled()): ?>
            <?php Partial::render('components/component-breadcrumbs'); ?>
        <?php endif; ?>

        <?php do_action('ts_after_breadcrumbs'); ?>

        <?php do_action('ts_default_single_before_gutenberg_content'); ?>

        <div class="single-default__content gutenberg-content-parent">
            <?php the_content(); ?>
        </div>

        <?php do_action('ts_default_single_after_gutenberg_content'); ?>

        <?php if ($factoryClass::featureEnabled('enableSingleBackButton')): ?>
            <?php do_action('ts_default_single_before_back_button'); ?>
            <?php $factoryClass::renderPartial('single/single-back-button'); ?>
            <?php do_action('ts_default_single_after_back_button'); ?>
        <?php endif; ?>

        <?php if ($factoryClass::featureEnabled('enableSinglePrevNextLinks')): ?>
            <?php do_action('ts_default_single_before_next_previous_post'); ?>
            <?php $factoryClass::renderPartial('single/single-next-prev-posts'); ?>
            <?php do_action('ts_default_single_after_next_previous_post'); ?>
        <?php endif; ?>

        <?php if ($factoryClass::featureEnabled('enableSingleRelatedPosts')): ?>
            <?php do_action('ts_default_single_before_related_posts'); ?>
            <?php $factoryClass::renderPartial('single/single-related-posts'); ?>
            <?php do_action('ts_default_single_after_related_posts'); ?>
        <?php endif; ?>

    </main>

<?php get_footer(); ?>