<?php

use TechnoSapiens\StaticBlockPostType;
use TechnoSapiens\BreadcrumbsComponent;
use TechnoSapiens\Core\PaginationComponent;
use TechnoSapiens\Core\Partial;
use TechnoSapiens\Core\PostType;
use TechnoSapiens\FactoryPlugin;
use TechnoSapiens\FactoryPlugin\PostTypeFactory;

global $wp_query;

//get current post type and factory class
$postType = PostType::getPostType();
$factoryClass = PostTypeFactory::getFactoryClassByPostType($postType);
$settingsClass = PostTypeFactory::getSettingsClassByPostType($postType);
$postTypeLabelPlural = strtolower(PostType::getPluralLabel($postType)) ?: __("items", FactoryPlugin::TEXT_DOMAIN);

//bail if no post type or factory class found
if (!$postType || !$factoryClass) return false;

//get configured static blocks from settings
$staticBlockTopPost = $settingsClass ? $settingsClass::getStaticBlockTopPost() : null;
$staticBlockBottomPost = $settingsClass ? $settingsClass::getStaticBlockBottomPost() : null;

//get post per page
$postPerPage = $settingsClass ? $settingsClass::getPostsPerPage() : get_option('posts_per_page', -1);

//get total queried posts count
$totalQueriedPosts = (int)$wp_query->found_posts;

//count all posts
$totalPosts = $factoryClass::getTotalCount();

//calculate posts from
$page = (get_query_var('paged')) ? get_query_var('paged') : 1;
$countPostsFrom = (($page - 1) * $postPerPage) + 1;

//calculate posts to
$countPostsTo = $page * $postPerPage;
if ($countPostsTo > $totalQueriedPosts) $countPostsTo = $totalQueriedPosts;

//check if there are more posts
$hasMorePosts = $totalQueriedPosts > $postPerPage;

//handle search
$queriedObject = get_queried_object();

//handle filters enabled
$filters = $factoryClass::getArchiveFilters();
$hasFilters = (count($filters) > 0 && count($filters[0]->options) > 0) && $factoryClass::featureEnabled('enableArchiveTopButtonFilters');

//get snippet column classes
$snippetColumnClasses = $factoryClass::getColumnClasses();

?>

<?php get_header(); ?>

    <main id="ts-main" class="archive-default__content ts-md-child-margins">

        <?php if (BreadcrumbsComponent::breadcrumbsEnabled()): ?>
            <?php Partial::render('components/component-breadcrumbs'); ?>
        <?php endif; ?>

        <?php do_action('ts_after_breadcrumbs'); ?>

        <?php do_action('ts_default_archive_before_static_block_top'); ?>

        <?php if ($staticBlockTopPost): ?>
            <section
                class="gutenberg-content-parent archive-default__static-block archive-default__static-block--top ts-md-child-margin-small">
                <?php echo StaticBlockPostType::getBlockHtml($staticBlockTopPost); ?>
            </section>
        <?php endif; ?>

        <?php do_action('ts_default_archive_after_static_block_top'); ?>

        <?php do_action('ts_default_archive_before_top'); ?>

        <?php if ($hasFilters): ?>
            <div class="archive-default__top-filters container ts-md-child-margin-small"
                 id="<?php echo $postType; ?>-archive-filters">
                <?php $factoryClass::renderPartial('archive/archive-filters-top', ['filters' => $filters, 'totalPosts' => $totalPosts]); ?>
                <?php $factoryClass::renderPartial('archive/archive-filters-mobile', ['filters' => $filters, 'totalPosts' => $totalPosts]); ?>
            </div>
        <?php endif; ?>

        <?php do_action('ts_default_archive_after_top'); ?>

        <?php if (have_posts()) : ?>
            <div class="container archive-default__container">

                <?php do_action('ts_default_archive_before_posts'); ?>

                <div class="row archive-default__items" id="<?php echo $postType; ?>-archive-posts">
                    <?php while (have_posts()): the_post(); ?>
                        <div class="<?php echo implode(' ', $snippetColumnClasses); ?>">
                            <?php $factoryClass::renderPartial('snippets/snippet'); ?>
                        </div>
                    <?php endwhile; ?>
                </div>

                <?php do_action('ts_default_archive_after_posts'); ?>

                <?php do_action('ts_default_archive_before_pagination'); ?>

                <?php if ($hasMorePosts): ?>
                    <?php echo PaginationComponent::getPaginationHtml($postType . '-archive-posts'); ?>
                <?php endif; ?>

                <?php do_action('ts_default_archive_after_pagination'); ?>

                <?php wp_reset_postdata(); ?>

            </div>

        <?php else: ?>
            <div class="archive-default__no-results container">
                <h3 class="archive-default__no-results-title">
                    <?php echo sprintf(
                        __("No %1s found", FactoryPlugin::TEXT_DOMAIN),
                        $postTypeLabelPlural
                    ); ?>
                </h3>
                <p class="archive-default__no-results-description">
                    <?php echo sprintf(
                        __("There were no %1s found based on your request. Please refine your query and try again.", FactoryPlugin::TEXT_DOMAIN),
                        $postTypeLabelPlural
                    ); ?>
                </p>
            </div>
        <?php endif; ?>

        <?php do_action('ts_default_archive_before_static_block_bottom'); ?>

        <?php if ($staticBlockBottomPost): ?>
            <section class="gutenberg-content-parent archive-default__static-block archive-news__static-block--bottom">
                <?php echo StaticBlockPostType::getBlockHtml($staticBlockBottomPost); ?>
            </section>
        <?php endif; ?>

        <?php do_action('ts_default_archive_after_static_block_bottom'); ?>

    </main>

<?php get_footer(); ?>