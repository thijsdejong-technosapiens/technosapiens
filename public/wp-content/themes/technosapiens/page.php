<?php

use TechnoSapiens\BreadcrumbsComponent;
use TechnoSapiens\Core\Partial;

?>

<?php get_header(); ?>

    <main id="ts-main">

        <?php if (BreadcrumbsComponent::breadcrumbsEnabled()): ?>
            <?php Partial::render('components/component-breadcrumbs'); ?>
        <?php endif; ?>

        <?php do_action('ts_after_breadcrumbs'); ?>

        <?php the_content(); ?>
    </main>

<?php get_footer(); ?>