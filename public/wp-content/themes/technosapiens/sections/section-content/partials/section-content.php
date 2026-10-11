<?php

use TechnoSapiens\SectionBackground;
use TechnoSapiens\SectionContent;
use TechnoSapiens\SectionMargins;
use TechnoSapiens\SectionReferencePartial;
use TechnoSapiens\SectionTitleHeading;
use TechnoSapiens\SectionVignet;
use TechnoSapiens\Core\GradientBandsComponent;

$sectionContent = SectionContent::getInstance();
$sectionTitle = $sectionContent->getSectionTitle();
$titleHeadingTag = SectionTitleHeading::getTag();
$titleHeadingSize = SectionTitleHeading::getSize();
$contentText = SectionContent::getContentText();

$containerClasses = SectionReferencePartial::getContainerClasses('section-content');
$marginTop = SectionMargins::getMarginTop();
$marginBottom = SectionMargins::getMarginBottom();
$vignetTop = SectionVignet::getVignetTop();
$vignetBottom = SectionVignet::getVignetBottom();
$backgroundType = SectionBackground::getBackgroundType();
$backgroundColor = SectionBackground::getBackgroundColor();
$backgroundBandsAxis = SectionBackground::getBackgroundBandsAxis();
$backgroundBandsAnimDir = SectionBackground::getBackgroundBandsAnimDir();
$backgroundBandsSize = SectionBackground::getBackgroundBandsSize();
?>

<div class="<?php echo esc_attr(implode(' ', $containerClasses)); ?>"<?php if ($marginTop) : ?> data-pt="<?php echo esc_attr($marginTop); ?>"<?php endif; ?><?php if ($marginBottom) : ?> data-pb="<?php echo esc_attr($marginBottom); ?>"<?php endif; ?><?php if ($vignetTop) : ?> data-vignet-top="<?php echo esc_attr($vignetTop); ?>"<?php endif; ?><?php if ($vignetBottom) : ?> data-vignet-bottom="<?php echo esc_attr($vignetBottom); ?>"<?php endif; ?><?php if ($backgroundType) : ?> data-bg="<?php echo esc_attr($backgroundType); ?>"<?php endif; ?><?php if ($backgroundColor) : ?> data-bg-color="<?php echo esc_attr($backgroundColor); ?>"<?php endif; ?>>
    
    <?php if ($backgroundType === 'bands') : ?>
        <div class="section-background">
            <?php echo GradientBandsComponent::render([
                'bandSize' => $backgroundBandsSize ?: 'medium',
                'axis' => $backgroundBandsAxis ?: 'vertical',
                'staggerMode' => $backgroundBandsAnimDir ?: 'start',
            ]); ?>
        </div>
    <?php endif; ?>

    <div class="container container--narrow section-content__container">
        <?php if ($sectionTitle) : ?>
            <<?php echo esc_attr($titleHeadingTag); ?> class="section-content__title <?php echo esc_attr($titleHeadingSize); ?>">
                <?php echo wp_kses_post($sectionTitle); ?>
            </<?php echo esc_attr($titleHeadingTag); ?>>
        <?php endif; ?>

        <div class="section-content__text">
            <?php echo do_shortcode($contentText); ?>
        </div>
    </div>
</div>
