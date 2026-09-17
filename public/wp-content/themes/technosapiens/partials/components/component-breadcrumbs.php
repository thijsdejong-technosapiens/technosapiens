<?php

use TechnoSapiens\Theme;

?>

<div class="breadcrumbs">
    <nav class="container breadcrumbs__container"
         aria-label="<?php _e("Breadcrumbs", Theme::TEXT_DOMAIN); ?>">
        <ol class="breadcrumbs__list">
            <?php yoast_breadcrumb('<li class="breadcrumbs__item">', '</li>'); ?>
        </ol>
    </nav>
</div>