<?php

use TechnoSapiens\Core\ButtonComponent;
use TechnoSapiens\Core\GradientBandsComponent;
use TechnoSapiens\SectionBackground;
use TechnoSapiens\SectionCards;
use TechnoSapiens\SectionMargins;
use TechnoSapiens\SectionReferencePartial;
use TechnoSapiens\SectionVignet;

$sectionCards = SectionCards::getInstance();
$sectionTitle = $sectionCards->getSectionTitle();
$titleHeadingTag = $sectionCards->getTitleHeadingTag();
$cardsPerRow = $sectionCards->getCardsPerRow();
$cards = $sectionCards->getCards();
$hasContent = $sectionTitle || !empty($cards);

$columnClasses = ['col', 'col--1', 'section-cards__card-wrap'];
if ($cardsPerRow > 1) {
    $columnClasses[] = 'col--md-1/2';
}
if ($cardsPerRow > 2) {
    $columnClasses[] = 'col--xl-1/' . $cardsPerRow;
}

$containerClasses = SectionReferencePartial::getContainerClasses('section-cards');
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
                <<?php echo esc_attr($titleHeadingTag); ?> class="section-cards__title">
                    <?php echo esc_html($sectionTitle); ?>
                </<?php echo esc_attr($titleHeadingTag); ?>>
            <?php endif; ?>

            <?php if (!empty($cards)) : ?>
                <div class="row section-cards__grid">
                    <?php foreach ($cards as $card) : ?>
                        <div class="<?php echo esc_attr(implode(' ', $columnClasses)); ?>">
                            <article class="section-cards__card">
                                <span class="section-cards__card-line section-cards__card-line--pink" aria-hidden="true"></span>    
                                <span class="section-cards__card-line section-cards__card-line--white" aria-hidden="true"></span>

                                <div class="section-cards__card-media">
                                    <div class="section-cards__card-background">
                                        <?php echo GradientBandsComponent::render([
                                            'bandSize' => 'small',
                                            'axis' => 'horizontal',
                                            'staggerMode' => 'center',
                                            'class' => 'section-cards__card-bands',
                                        ]); ?>
                                    </div>
                                </div>

                                <div class="section-cards__card-body">
                                    <?php if ($card['title']) : ?>
                                        <p class="section-cards__card-title"><?php echo esc_html($card['title']); ?></p>
                                    <?php endif; ?>

                                    <?php if ($card['content']) : ?>
                                        <div class="section-cards__card-content">
                                            <?php echo $card['content']; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($card['link'])) : ?>
                                        <div class="section-cards__card-action">
                                            <?php echo ButtonComponent::render([
                                                'text' => $card['link']['text'],
                                                'href' => $card['link']['url'],
                                                'target' => $card['link']['target'],
                                                'rel' => $card['link']['rel'],
                                                'type' => 'tertiary',
                                                'style' => 'filled',
                                                'size' => 'medium',
                                                'blockClass' => 'section-cards',
                                                'class' => 'section-cards__card-button',
                                            ]); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
