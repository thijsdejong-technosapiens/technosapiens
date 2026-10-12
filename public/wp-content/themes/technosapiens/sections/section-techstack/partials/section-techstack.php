<?php

use TechnoSapiens\Core\GradientBandsComponent;
use TechnoSapiens\Core\Image;
use TechnoSapiens\SectionBackground;
use TechnoSapiens\SectionMargins;
use TechnoSapiens\SectionReferencePartial;
use TechnoSapiens\SectionTechstack;
use TechnoSapiens\SectionTitleHeading;
use TechnoSapiens\SectionVignet;

$sectionTechstack = SectionTechstack::getInstance();
$sectionTitle = $sectionTechstack->getSectionTitle();
$titleHeadingTag = SectionTitleHeading::getTag();
$titleHeadingSize = SectionTitleHeading::getSize();
$groups = $sectionTechstack->getGroups();
$hasContent = $sectionTitle || !empty($groups);

$containerClasses = SectionReferencePartial::getContainerClasses('section-techstack');
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

        <div class="container">
            <?php if ($sectionTitle) : ?>
                <<?php echo esc_attr($titleHeadingTag); ?> class="section-techstack__title <?php echo esc_attr($titleHeadingSize); ?>">
                    <?php echo wp_kses_post($sectionTitle); ?>
                </<?php echo esc_attr($titleHeadingTag); ?>>
            <?php endif; ?>

            <?php if (!empty($groups)) : ?>
                <div class="section-techstack__groups">
                    <?php foreach ($groups as $group) : ?>
                        <div class="section-techstack__group">
                            <?php if (!empty($group['title'])) : ?>
                                <h3 class="section-techstack__group-title">
                                    <?php echo esc_html($group['title']); ?>
                                </h3>
                            <?php endif; ?>

                            <?php if (!empty($group['text'])) : ?>
                                <h4 class="section-techstack__group-text h5">
                                    <?php echo esc_html($group['text']); ?>
                                </h4>
                            <?php endif; ?>

                            <div class="section-techstack__grid">
                                <?php foreach ($group['logos'] as $logo) : ?>
                                    <?php
                                    $hasCaption = $logo['altText'] !== '';
                                    ?>
                                    <figure class="section-techstack__item">
                                        <div class="section-techstack__logo">
                                            <?php echo Image::render([
                                                'blockClass' => 'section-techstack',
                                                'alt' => '',
                                                'sources' => [
                                                    [
                                                        'id' => $logo['logoId'],
                                                        'size' => SectionTechstack::IMAGE_TECHSTACK_LOGO,
                                                        'size2x' => SectionTechstack::IMAGE_TECHSTACK_LOGO_X2,
                                                    ],
                                                ],
                                            ]); ?>
                                        </div>
                                        <?php if ($hasCaption) : ?>
                                            <figcaption class="section-techstack__caption">
                                                <?php echo esc_html($logo['altText']); ?>
                                            </figcaption>
                                        <?php endif; ?>
                                    </figure>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
