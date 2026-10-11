<?php

use TechnoSapiens\Core\ButtonComponent;
use TechnoSapiens\Core\GradientBandsComponent;
use TechnoSapiens\SectionBackground;
use TechnoSapiens\SectionContentBanner;
use TechnoSapiens\SectionMargins;
use TechnoSapiens\SectionReferencePartial;
use TechnoSapiens\SectionVignet;

$sectionContentBanner = SectionContentBanner::getInstance();
$sectionTitle = $sectionContentBanner->getSectionTitle();
$titleHeadingTag = $sectionContentBanner->getTitleHeadingTag();
$contentText = $sectionContentBanner->getContentText();
$contentLink = $sectionContentBanner->getContentLink();
$hasContent = $sectionTitle || $contentText || !empty($contentLink);

$containerClasses = SectionReferencePartial::getContainerClasses('section-content-banner');
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

<?php if ($hasContent) : ?>
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

        <div class="container section-content-banner__container">
            <?php if ($sectionTitle) : ?>
                <<?php echo esc_attr($titleHeadingTag); ?> class="section-content-banner__title">
                    <?php echo wp_kses_post($sectionTitle); ?>
                </<?php echo esc_attr($titleHeadingTag); ?>>
            <?php endif; ?>

            <?php if ($contentText || !empty($contentLink)) : ?>
                <div class="section-content-banner__card-wrap">
                    <article class="section-content-banner__card">
                        <div class="section-content-banner__card-body">
                            <?php if ($contentText) : ?>
                                <div class="section-content-banner__card-content">
                                    <?php echo $contentText; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($contentLink)) : ?>
                                <div class="section-content-banner__card-action">
                                    <?php echo ButtonComponent::render([
                                        'text' => $contentLink['text'],
                                        'href' => $contentLink['url'],
                                        'target' => $contentLink['target'],
                                        'rel' => $contentLink['rel'],
                                        'type' => 'tertiary',
                                        'style' => 'filled',
                                        'size' => 'medium',
                                        'blockClass' => 'section-content-banner',
                                        'class' => 'section-content-banner__card-button',
                                    ]); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
