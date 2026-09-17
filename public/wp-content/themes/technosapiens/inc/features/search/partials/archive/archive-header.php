<?php

use TechnoSapiens\SearchPlugin;

/**
 * @var string $query
 */

if (!isset($query)) $query = get_search_query();

global $wp_query;

//get search query and total posts
$totalPosts = (int)$wp_query->found_posts;

?>

<div class="archive-header">
    <div class="container">
        <h1 class="archive-header__heading h2">
            <?php if ($query): ?>
                <?php echo sprintf(__("Results for “%1s”", SearchPlugin::TEXT_DOMAIN), $query); ?>
            <?php else: ?>
                <?php _e("All results", SearchPlugin::TEXT_DOMAIN); ?>
            <?php endif; ?>
        </h1>
        <p class="archive-header__description">
            <?php if ($totalPosts === 1): ?>
                <?php echo sprintf(__("%1d result found", SearchPlugin::TEXT_DOMAIN), $totalPosts); ?>
            <?php else: ?>
                <?php echo sprintf(__("%1d results found", SearchPlugin::TEXT_DOMAIN), $totalPosts); ?>
            <?php endif; ?>
        </p>
    </div>
</div>