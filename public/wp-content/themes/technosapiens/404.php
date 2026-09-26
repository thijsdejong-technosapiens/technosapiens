<?php

use TechnoSapiens\Page404Component;

//get custom 404 page
$staticBlock404Page = Page404Component::getInstance()->getStaticBlockPost();
$staticBlock404Content = $staticBlock404Page
    ? trim((string)get_post_field('post_content', $staticBlock404Page->ID))
    : '';

?>

<?php get_header(); ?>

<main id="ts-main">

    <?php do_action('ts_after_breadcrumbs'); ?>
    <?php echo apply_filters('the_content', $staticBlock404Content); ?>

</main>

<?php get_footer(); ?>


