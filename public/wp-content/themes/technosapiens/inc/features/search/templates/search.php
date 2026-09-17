<?php

use TechnoSapiens\BreadcrumbsComponent;
use TechnoSapiens\Core\PaginationComponent;
use TechnoSapiens\Core\Partial;
use TechnoSapiens\SearchPlugin\SearchSettings;

//get search query
$query = get_search_query();

//get posts per load
$postsPerLoad = SearchSettings::getInstance()->getPostsPerPage();

//check total posts / if there are more posts
global $wp_query;
$totalPosts = (int)$wp_query->found_posts;
$hasMorePosts = $totalPosts > $postsPerLoad;

?>

<?php get_header(); ?>

    <main  id="ts-main" class="search-archive__content">

        <?php do_action('ts_after_breadcrumbs'); ?>

        <?php Partial::render('archive/archive-header', ['query' => $query], true, WSP_PARTIAL_PATH); ?>

        <?php Partial::render('archive/archive-search-bar', ['query' => $query], true, WSP_PARTIAL_PATH); ?>

        <div role="presentation" id="search-results"></div>

        <?php Partial::render('archive/archive-filters', ['query' => $query], true, WSP_PARTIAL_PATH); ?>

        <?php Partial::render('archive/archive-results', [], true, WSP_PARTIAL_PATH); ?>

        <?php if ($hasMorePosts): ?>
            <?php echo PaginationComponent::getPaginationHtml('search-results'); ?>
        <?php endif; ?>

    </main>

<?php get_footer(); ?>