<?php

use TechnoSapiens\Core\GradientBandsComponent;
use TechnoSapiens\Core\Image;
use TechnoSapiens\Core\WcagComponent;
use TechnoSapiens\SectionBackground;
use TechnoSapiens\SectionBrands;
use TechnoSapiens\SectionMargins;
use TechnoSapiens\SectionReferencePartial;
use TechnoSapiens\SectionVignet;

$sectionBrands = SectionBrands::getInstance();
$sectionTitle = $sectionBrands->getSectionTitle();
$titleHeadingTag = $sectionBrands->getTitleHeadingTag();
$brands = $sectionBrands->getBrands();
$hasContent = $sectionTitle || !empty($brands);

$columnClasses = [
    'col',
    'col--1/2',
    'col--md-1/3',
    'col--lg-1/4',
    'col--xxl-1/6',
    'section-brands__item-wrap',
];

$containerClasses = SectionReferencePartial::getContainerClasses('section-brands');
$marginTop = SectionMargins::getMarginTop();
$marginBottom = SectionMargins::getMarginBottom();
$vignetTop = SectionVignet::getVignetTop();
$vignetBottom = SectionVignet::getVignetBottom();
$backgroundType = SectionBackground::getBackgroundType();
$backgroundBandsAxis = SectionBackground::getBackgroundBandsAxis();
$backgroundBandsAnimDir = SectionBackground::getBackgroundBandsAnimDir();
$backgroundBandsSize = SectionBackground::getBackgroundBandsSize();
?>

<?php if ($hasContent) : ?>
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
            <?php if ($sectionTitle) : ?>
                <<?php echo esc_attr($titleHeadingTag); ?> class="section-brands__title">
                    <?php echo esc_html($sectionTitle); ?>
                </<?php echo esc_attr($titleHeadingTag); ?>>
            <?php endif; ?>

            <?php if (!empty($brands)) : ?>
                <div class="row section-brands__grid">
                    <?php foreach ($brands as $brand) : ?>
                        <div class="<?php echo esc_attr(implode(' ', $columnClasses)); ?>">
                            <?php if (!empty($brand['link'])) : ?>
                                <a class="section-brands__link"
                                   href="<?php echo esc_url($brand['link']['url']); ?>"
                                   target="<?php echo esc_attr($brand['link']['target']); ?>"
                                   <?php if ($brand['link']['rel']) : ?>rel="<?php echo esc_attr($brand['link']['rel']); ?>"<?php endif; ?>>
                                    <span class="section-brands__logo">
                                        <?php echo Image::render([
                                            'blockClass' => 'section-brands',
                                            'sources' => [
                                                [
                                                    'id' => $brand['logoId'],
                                                    'size' => SectionBrands::IMAGE_BRAND_LOGO,
                                                    'size2x' => SectionBrands::IMAGE_BRAND_LOGO_X2,
                                                ],
                                            ],
                                        ]); ?>
                                        <?php echo WcagComponent::getSrLinkOpensInNewTabHtml(); ?>
                                    </span>
                                </a>
                            <?php else : ?>
                                <div class="section-brands__logo">
                                    <?php echo Image::render([
                                        'blockClass' => 'section-brands',
                                        'sources' => [
                                            [
                                                'id' => $brand['logoId'],
                                                'size' => SectionBrands::IMAGE_BRAND_LOGO,
                                                'size2x' => SectionBrands::IMAGE_BRAND_LOGO_X2,
                                            ],
                                        ],
                                    ]); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
