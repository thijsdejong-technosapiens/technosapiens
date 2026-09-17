<?php

use TechnoSapiens\Core\Partial;
use TechnoSapiens\SearchPlugin;
use TechnoSapiens\StaticBlockPostType;
use TechnoSapiens\SearchPlugin\SearchSettings;

?>

<div class="archive-results">

    <?php if (have_posts()) : ?>

        <div class="container">

            <div class="row">

                <?php while (have_posts()) : the_post(); ?>
                    <div class="col col--1 col--md-1/2 snippet-col">
                        <?php Partial::render('snippets/snippet-search', [], true, WSP_PARTIAL_PATH); ?>
                    </div>
                <?php endwhile; ?>

            </div>

        </div>

        <?php wp_reset_postdata(); ?>

    <?php else: ?>

        <?php
        //get no results static block
        $staticBlockHtml = '';
        $staticBlockHtmlBlockId = get_field('no_results_content', SearchSettings::MENU_SLUG) ?: '';
        if ($staticBlockHtmlBlockId) $staticBlockHtml = StaticBlockPostType::getBlockById($staticBlockHtmlBlockId);
        ?>

        <?php if ($staticBlockHtml): ?>
            <div class="container archive-results__no-results-container">
                <?php echo $staticBlockHtml; ?>
            </div>
        <?php else: ?>
            <div class="container archive-results__no-results-container">
                <h2 class="h3"><?php _e("No results found.", SearchPlugin::TEXT_DOMAIN); ?></h2>
                <p><?php _e("There were no results found based on your query. Please refine your query and try again.", SearchPlugin::TEXT_DOMAIN); ?></p>
            </div>
        <?php endif; ?>

    <?php endif; ?>

</div>