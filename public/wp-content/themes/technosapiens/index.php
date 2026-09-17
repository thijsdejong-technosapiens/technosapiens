<?php

use TechnoSapiens\Theme;
use TechnoSapiens\BreadcrumbsComponent;
use TechnoSapiens\Core\Partial;

?>

<?php get_header(); ?>

    <main id="ts-main">

        <?php if (BreadcrumbsComponent::breadcrumbsEnabled()): ?>
            <?php Partial::render('components/component-breadcrumbs'); ?>
        <?php endif; ?>

        <?php do_action('ts_after_breadcrumbs'); ?>

        <div class="container">

            <h1><?php _e("Techno Sapiens starter", Theme::TEXT_DOMAIN); ?></h1>

            <p><?php _e("Thank you for using the column based Techno Sapiens starter repository by Techno Sapiens!", Theme::TEXT_DOMAIN); ?></p>

        </div>

    </main>

<?php get_footer(); ?>