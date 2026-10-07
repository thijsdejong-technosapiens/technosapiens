<?php

use TechnoSapiens\Core\ButtonComponent;
use TechnoSapiens\Core\GradientBandsComponent;
use TechnoSapiens\SectionBackground;
use TechnoSapiens\SectionMargins;
use TechnoSapiens\SectionProcessCards;
use TechnoSapiens\SectionReferencePartial;
use TechnoSapiens\SectionVignet;

$sectionProcessCards = SectionProcessCards::getInstance();
$sectionTitle = $sectionProcessCards->getSectionTitle();
$titleHeadingTag = $sectionProcessCards->getTitleHeadingTag();
$cards = $sectionProcessCards->getCards();
$callToAction = $sectionProcessCards->getCallToAction();
$hasContent = $sectionTitle || !empty($cards) || !empty($callToAction);

$containerClasses = SectionReferencePartial::getContainerClasses('section-process-cards');
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
            <div class="section-process-cards__wrap">

                <?php if ($sectionTitle) : ?>
                    <<?php echo esc_attr($titleHeadingTag); ?> class="section-process-cards__title">
                        <?php echo esc_html($sectionTitle); ?>
                    </<?php echo esc_attr($titleHeadingTag); ?>>
                <?php endif; ?>


                <div class="section-process-cards__gradient-wrap"></div>

                <?php if (!empty($cards)) : ?>
                    <div class="row section-process-cards__grid">
                        <?php foreach ($cards as $card) : ?>
                            <div class="col col--1 col--md-1/2 col--xl-1/4 section-process-cards__card-wrap">
                                <article class="section-process-cards__card">
                                    <?php if ($card['title']) : ?>
                                        <p class="section-process-cards__card-title h4"><?php echo esc_html($card['title']); ?></p>
                                    <?php endif; ?>

                                    <?php if ($card['text']) : ?>
                                        <div class="section-process-cards__card-text">
                                            <?php echo wp_kses_post($card['text']); ?>
                                        </div>
                                    <?php endif; ?>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($callToAction)) : ?>
                    <div class="section-process-cards__action">
                        <?php echo ButtonComponent::render([
                            'text' => $callToAction['text'],
                            'href' => $callToAction['url'],
                            'target' => $callToAction['target'],
                            'rel' => $callToAction['rel'],
                            'type' => 'tertiary',
                            'style' => 'filled',
                            'size' => 'large',
                            'blockClass' => 'section-process-cards',
                            'class' => 'section-process-cards__button',
                        ]); ?>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
<?php endif; ?>
