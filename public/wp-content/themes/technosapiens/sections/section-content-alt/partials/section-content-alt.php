<?php

use TechnoSapiens\Core\ButtonComponent;
use TechnoSapiens\Core\GradientBandsComponent;
use TechnoSapiens\SectionBackground;
use TechnoSapiens\SectionContentAlt;
use TechnoSapiens\SectionMargins;
use TechnoSapiens\SectionReferencePartial;
use TechnoSapiens\SectionVignet;

$sectionContentAlt = SectionContentAlt::getInstance();
$sectionTitle = $sectionContentAlt->getSectionTitle();
$titleHeadingTag = $sectionContentAlt->getTitleHeadingTag();
$contentText = $sectionContentAlt->getContentText();
$contentLink = $sectionContentAlt->getContentLink();
$hasContent = $sectionTitle || $contentText || !empty($contentLink);

$containerClasses = SectionReferencePartial::getContainerClasses('section-content-alt');
$marginTop = SectionMargins::getMarginTop();
$marginBottom = SectionMargins::getMarginBottom();
$vignetTop = SectionVignet::getVignetTop();
$vignetBottom = SectionVignet::getVignetBottom();
$backgroundType = SectionBackground::getBackgroundType();
$backgroundColor = SectionBackground::getBackgroundColor();
$backgroundBandsAxis = SectionBackground::getBackgroundBandsAxis();
$backgroundBandsAnimDir = SectionBackground::getBackgroundBandsAnimDir();
$backgroundBandsSize = SectionBackground::getBackgroundBandsSize();
$cardBandsWaitFor = $backgroundType === 'bands' ? '.section-background .gradient-bands' : '';
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

        <div class="container section-content-alt__container">
            <?php if ($sectionTitle) : ?>
                <<?php echo esc_attr($titleHeadingTag); ?> class="section-content-alt__title">
                    <?php echo wp_kses_post($sectionTitle); ?>
                </<?php echo esc_attr($titleHeadingTag); ?>>
            <?php endif; ?>

            <?php if ($contentText || !empty($contentLink)) : ?>
                <div class="section-content-alt__card-wrap">
                    <article class="section-content-alt__card">
                        <span class="section-content-alt__card-line section-content-alt__card-line--pink" aria-hidden="true"></span>
                        <span class="section-content-alt__card-line section-content-alt__card-line--white" aria-hidden="true"></span>

                        <div class="section-content-alt__card-media">
                            <div class="section-content-alt__card-background">
                                <?php echo GradientBandsComponent::render([
                                    'bandSize' => 'small',
                                    'axis' => 'horizontal',
                                    'staggerMode' => 'center',
                                    'delay' => 0,
                                    'waitFor' => $cardBandsWaitFor,
                                    'class' => 'section-content-alt__card-bands',
                                ]); ?>
                            </div>
                        </div>

                        <div class="section-content-alt__card-body">
                            <?php if ($contentText) : ?>
                                <div class="section-content-alt__card-content">
                                    <?php echo $contentText; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($contentLink)) : ?>
                                <div class="section-content-alt__card-action">
                                    <?php echo ButtonComponent::render([
                                        'text' => $contentLink['text'],
                                        'href' => $contentLink['url'],
                                        'target' => $contentLink['target'],
                                        'rel' => $contentLink['rel'],
                                        'type' => 'tertiary',
                                        'style' => 'filled',
                                        'size' => 'medium',
                                        'blockClass' => 'section-content-alt',
                                        'class' => 'section-content-alt__card-button',
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
