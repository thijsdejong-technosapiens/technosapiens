<?php

use TechnoSapiens\SectionBackground;
use TechnoSapiens\SectionContent;
use TechnoSapiens\SectionMargins;
use TechnoSapiens\SectionReferencePartial;
use TechnoSapiens\SectionVignet;
use TechnoSapiens\Core\GradientBandsComponent;

$containerClasses = SectionReferencePartial::getContainerClasses('section-content');
$marginTop = SectionMargins::getMarginTop();
$marginBottom = SectionMargins::getMarginBottom();
$vignetTop = SectionVignet::getVignetTop();
$vignetBottom = SectionVignet::getVignetBottom();
$backgroundType = SectionBackground::getBackgroundType();
$backgroundBandsAxis = SectionBackground::getBackgroundBandsAxis();
$backgroundBandsAnimDir = SectionBackground::getBackgroundBandsAnimDir();

$contentText = SectionContent::getContentText();
?>

<div class="<?php echo esc_attr(implode(' ', $containerClasses)); ?>"<?php if ($marginTop) : ?> data-pt="<?php echo esc_attr($marginTop); ?>"<?php endif; ?><?php if ($marginBottom) : ?> data-pb="<?php echo esc_attr($marginBottom); ?>"<?php endif; ?><?php if ($vignetTop) : ?> data-vignet-top="<?php echo esc_attr($vignetTop); ?>"<?php endif; ?><?php if ($vignetBottom) : ?> data-vignet-bottom="<?php echo esc_attr($vignetBottom); ?>"<?php endif; ?><?php if ($backgroundType) : ?> data-bg="<?php echo esc_attr($backgroundType); ?>"<?php endif; ?>>
    
    <?php if ($backgroundType === 'bands') : ?>
        <div class="section-background">
            <?php echo GradientBandsComponent::render([
                'bandSize' => 'xlarge',
                'axis' => $backgroundBandsAxis ?: 'vertical',
                'staggerMode' => $backgroundBandsAnimDir ?: 'start',
            ]); ?>
        </div>
    <?php endif; ?>

    <div class="container container--narrow section-content__container">
        <?php echo do_shortcode($contentText); ?>
    </div>
</div>
