<?php

use TechnoSapiens\Core\GradientBandsComponent;
use TechnoSapiens\SectionBackground;
use TechnoSapiens\SectionGrandTitle;
use TechnoSapiens\SectionMargins;
use TechnoSapiens\SectionReferencePartial;
use TechnoSapiens\SectionVignet;

$containerClasses = SectionReferencePartial::getContainerClasses('section-grand-title');
$marginTop = SectionMargins::getMarginTop();
$marginBottom = SectionMargins::getMarginBottom();
$vignetTop = SectionVignet::getVignetTop();
$vignetBottom = SectionVignet::getVignetBottom();
$backgroundType = SectionBackground::getBackgroundType();
$backgroundBandsAxis = SectionBackground::getBackgroundBandsAxis();
$backgroundBandsAnimDir = SectionBackground::getBackgroundBandsAnimDir();
$backgroundBandsSize = SectionBackground::getBackgroundBandsSize();

$titleText = SectionGrandTitle::getTitleText();
$titleTag = SectionGrandTitle::getTitleTag();

?>

<div class="<?php echo esc_attr(implode(' ', $containerClasses)); ?>"<?php if ($marginTop) : ?> data-pt="<?php echo esc_attr($marginTop); ?>"<?php endif; ?><?php if ($marginBottom) : ?> data-pb="<?php echo esc_attr($marginBottom); ?>"<?php endif; ?><?php if ($vignetTop) : ?> data-vignet-top="<?php echo esc_attr($vignetTop); ?>"<?php endif; ?><?php if ($vignetBottom) : ?> data-vignet-bottom="<?php echo esc_attr($vignetBottom); ?>"<?php endif; ?><?php if ($backgroundType) : ?> data-bg="<?php echo esc_attr($backgroundType); ?>"<?php endif; ?>>

    <?php if ($backgroundType === 'bands') : ?>
        <div class="section-background">
            <?php echo GradientBandsComponent::render([
                'bandSize' => $backgroundBandsSize ?: 'medium',
                'axis' => $backgroundBandsAxis ?: 'vertical',
                'staggerMode' => $backgroundBandsAnimDir ?: 'start',
            ]); ?>
        </div>
    <?php endif; ?>

    <div class="container section-grand-title__container">
        <<?php echo esc_attr($titleTag); ?> class="text-center section-grand-title__text"><?php echo esc_html($titleText); ?></<?php echo esc_attr($titleTag); ?>>
    </div>
</div>
