<?php

use TechnoSapiens\Core\GradientBandsComponent;
use TechnoSapiens\Section3dfolio;
use TechnoSapiens\SectionBackground;
use TechnoSapiens\SectionMargins;
use TechnoSapiens\SectionReferencePartial;
use TechnoSapiens\SectionVignet;
use TechnoSapiens\Theme;

$section3dfolio = Section3dfolio::getInstance();
$cases = $section3dfolio->getCases();
$casesJson = wp_json_encode($cases) ?: '[]';
$isEditor = is_admin();

$containerClasses = SectionReferencePartial::getContainerClasses('section-3dfolio');
$marginTop = SectionMargins::getMarginTop();
$marginBottom = SectionMargins::getMarginBottom();
$vignetTop = SectionVignet::getVignetTop();
$vignetBottom = SectionVignet::getVignetBottom();
$backgroundType = SectionBackground::getBackgroundType();
$backgroundBandsAxis = SectionBackground::getBackgroundBandsAxis();
$backgroundBandsAnimDir = SectionBackground::getBackgroundBandsAnimDir();
$backgroundBandsSize = SectionBackground::getBackgroundBandsSize();
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

    <div class="container">
        <div class="section-3dfolio__stage">
            <div class="section-3dfolio__canvas-host" data-section-3dfolio-canvas hidden></div>

            <div class="section-3dfolio__popup-root" data-section-3dfolio-popup hidden></div>

            <div class="section-3dfolio__placeholder" data-section-3dfolio-placeholder>
                <?php if (empty($cases)) : ?>
                    <p class="section-3dfolio__placeholder-text">
                        <?php echo esc_html__('Add up to 6 cases to populate the 3D folio.', Theme::TEXT_DOMAIN); ?>
                    </p>
                <?php else : ?>
                    <ul class="section-3dfolio__placeholder-list">
                        <?php foreach ($cases as $case) : ?>
                            <li class="section-3dfolio__placeholder-item">
                                <?php if ($case['title']) : ?>
                                    <p class="section-3dfolio__placeholder-title"><?php echo esc_html($case['title']); ?></p>
                                <?php endif; ?>
                                <?php if (!$isEditor && $case['body']) : ?>
                                    <div class="section-3dfolio__placeholder-body">
                                        <?php echo $case['body']; ?>
                                    </div>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <script type="application/json" class="section-3dfolio__data" data-section-3dfolio-cases>
            <?php echo $casesJson; ?>
        </script>
    </div>
</div>
