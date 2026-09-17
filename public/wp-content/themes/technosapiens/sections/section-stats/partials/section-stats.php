<?php

use TechnoSapiens\Core\ButtonComponent;
use TechnoSapiens\Core\Image;
use TechnoSapiens\SectionMargins;
use TechnoSapiens\SectionReferencePartial;
use TechnoSapiens\SectionStats;

$sectionStats = SectionStats::getInstance();
$statsItems = $sectionStats->getStatsItems();
$ctaLink = $sectionStats->getCtaLink();
$hasContent = !empty($statsItems);

$containerClasses = SectionReferencePartial::getContainerClasses('section-stats');
$marginTop = SectionMargins::getMarginTop();
$marginBottom = SectionMargins::getMarginBottom();
?>

<?php if ($hasContent) : ?>
    <div class="<?php echo esc_attr(implode(' ', $containerClasses)); ?>"<?php if ($marginTop) : ?> data-mt="<?php echo esc_attr($marginTop); ?>"<?php endif; ?><?php if ($marginBottom) : ?> data-mb="<?php echo esc_attr($marginBottom); ?>"<?php endif; ?>>
        <div class="container">
            <div class="section-stats__row">
                <div class="section-stats__cols">
                    <?php foreach ($statsItems as $statsItem) : ?>
                        <div class="section-stats__col">
                            <div class="section-stats__tile">
                                <?php if ($statsItem['iconId']) : ?>
                                    <div class="section-stats__icon">
                                        <?php echo Image::render([
                                            'blockClass' => 'section-stats',
                                            'sources' => [
                                                [
                                                    'id' => $statsItem['iconId'],
                                                    'size' => 'full',
                                                ],
                                            ],
                                        ]); ?>
                                    </div>
                                <?php endif; ?>

                                <div class="section-stats__content">
                                    <p class="section-stats__number"><?php echo esc_html($statsItem['number']); ?></p>
                                    <p class="section-stats__title"><?php echo esc_html($statsItem['title']); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($ctaLink) : ?>
                    <?php echo ButtonComponent::render([
                        'text' => $ctaLink['text'],
                        'href' => $ctaLink['url'],
                        'target' => $ctaLink['target'],
                        'rel' => $ctaLink['rel'],
                        'type' => 'secondary',
                        'style' => 'filled',
                        'size' => 'large',
                        'blockClass' => 'section-stats',
                        'class' => 'section-stats__cta',
                    ]); ?>
                <?php endif; ?>
            </div>

            <div class="section-stats__decoration" aria-hidden="true">
                <svg class="section-stats__decoration-icon icon">
                    <use xlink:href="<?php echo ICON_PATH; ?>icon-baby"/>
                </svg>
            </div>            
        </div>
    </div>
<?php endif; ?>
