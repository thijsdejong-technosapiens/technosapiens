<?php

use TechnoSapiens\SectionCtaBanner;
use TechnoSapiens\SectionMargins;
use TechnoSapiens\SectionReferencePartial;

$ctaBannerData = SectionCtaBanner::getSectionData();
$title = $ctaBannerData['title'];
$content = $ctaBannerData['content'];
$containerClasses = SectionReferencePartial::getContainerClasses('section-cta-banner');
$marginTop = SectionMargins::getMarginTop();
$marginBottom = SectionMargins::getMarginBottom();

?>

<div class="<?php echo esc_attr(implode(' ', $containerClasses)); ?>"<?php if ($marginTop) : ?> data-mt="<?php echo esc_attr($marginTop); ?>"<?php endif; ?><?php if ($marginBottom) : ?> data-mb="<?php echo esc_attr($marginBottom); ?>"<?php endif; ?>>
    <div class="container">
        <div class="section-cta-banner__inner">
            <h2 class="section-cta-banner__title h3"><?php echo esc_html($title); ?></h2>
            
            <div class="section-cta-banner__content">
                <?php echo $content; ?>
            </div>
        </div>

    </div>
</div>