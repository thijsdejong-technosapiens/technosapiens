<?php

use TechnoSapiens\Core\GradientBandsComponent;
use TechnoSapiens\SectionBackground;
use TechnoSapiens\SectionFaq;
use TechnoSapiens\SectionMargins;
use TechnoSapiens\SectionReferencePartial;
use TechnoSapiens\SectionTitleHeading;
use TechnoSapiens\SectionVignet;

$sectionFaq = SectionFaq::getInstance();
$sectionTitle = $sectionFaq->getSectionTitle();
$titleHeadingTag = SectionTitleHeading::getTag();
$titleHeadingSize = SectionTitleHeading::getSize();
$isCollapsible = $sectionFaq->isCollapsible();
$faqItems = $sectionFaq->getFaqItems();
$hasContent = $sectionTitle || !empty($faqItems);

$containerClasses = SectionReferencePartial::getContainerClasses('section-faq', $block ?? []);
$anchorId = SectionReferencePartial::getAnchorId($block ?? []);
if ($isCollapsible) {
    $containerClasses[] = 'section-faq--collapsible';
} else {
    $containerClasses[] = 'section-faq--not-collapsible';
}

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
    <div class="<?php echo esc_attr(implode(' ', $containerClasses)); ?>"<?php if ($anchorId) : ?> id="<?php echo esc_attr($anchorId); ?>"<?php endif; ?><?php if ($marginTop) : ?> data-pt="<?php echo esc_attr($marginTop); ?>"<?php endif; ?><?php if ($marginBottom) : ?> data-pb="<?php echo esc_attr($marginBottom); ?>"<?php endif; ?><?php if ($vignetTop) : ?> data-vignet-top="<?php echo esc_attr($vignetTop); ?>"<?php endif; ?><?php if ($vignetBottom) : ?> data-vignet-bottom="<?php echo esc_attr($vignetBottom); ?>"<?php endif; ?><?php if ($backgroundType) : ?> data-bg="<?php echo esc_attr($backgroundType); ?>"<?php endif; ?><?php if ($backgroundColor) : ?> data-bg-color="<?php echo esc_attr($backgroundColor); ?>"<?php endif; ?>>

        <?php if ($backgroundType === 'bands') : ?>
            <div class="section-background">
                <?php echo GradientBandsComponent::render([
                    'bandSize' => $backgroundBandsSize ?: 'medium',
                    'axis' => $backgroundBandsAxis ?: 'vertical',
                    'staggerMode' => $backgroundBandsAnimDir ?: 'start',
                ]); ?>
            </div>
        <?php endif; ?>

        <div class="container container--narrow section-faq__container">
            <?php if ($sectionTitle) : ?>
                <<?php echo esc_attr($titleHeadingTag); ?> class="section-faq__title <?php echo esc_attr($titleHeadingSize); ?>">
                    <?php echo wp_kses_post($sectionTitle); ?>
                </<?php echo esc_attr($titleHeadingTag); ?>>
            <?php endif; ?>

            <?php if (!empty($faqItems)) : ?>
                <div class="section-faq__list">
                    <?php foreach ($faqItems as $index => $faqItem) : ?>
                        <?php
                        $itemId = 'section-faq-item-' . ((int) $index + 1);
                        $questionId = $itemId . '-question';
                        $answerId = $itemId . '-answer';
                        $itemClasses = ['section-faq__item'];
                        if ($isCollapsible) {
                            $itemClasses[] = 'section-faq__item--collapsible';
                        } else {
                            $itemClasses[] = 'section-faq__item--not-collapsible';
                        }
                        ?>
                        <article class="<?php echo esc_attr(implode(' ', $itemClasses)); ?>">
                            <?php if ($isCollapsible) : ?>
                                <button
                                    type="button"
                                    class="section-faq__question h4"
                                    id="<?php echo esc_attr($questionId); ?>"
                                    aria-expanded="false"
                                    aria-controls="<?php echo esc_attr($answerId); ?>"
                                >
                                    <?php echo esc_html($faqItem['question']); ?>
                                </button>
                                <div
                                    class="section-faq__answer"
                                    id="<?php echo esc_attr($answerId); ?>"
                                    role="region"
                                    aria-labelledby="<?php echo esc_attr($questionId); ?>"
                                    hidden
                                >
                                    <div class="section-faq__answer-inner">
                                        <?php echo $faqItem['answerHtml']; ?>
                                    </div>
                                </div>
                            <?php else : ?>
                                <h3 class="section-faq__question h4" id="<?php echo esc_attr($questionId); ?>">
                                    <?php echo esc_html($faqItem['question']); ?>
                                </h3>
                                <div
                                    class="section-faq__answer"
                                    id="<?php echo esc_attr($answerId); ?>"
                                    aria-labelledby="<?php echo esc_attr($questionId); ?>"
                                >
                                    <div class="section-faq__answer-inner">
                                        <?php echo $faqItem['answerHtml']; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($faqItems)) : ?>
        <?php
        $structuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(static function (array $faqItem): array {
                return [
                    '@type' => 'Question',
                    'name' => $faqItem['question'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $faqItem['answerText'],
                    ],
                ];
            }, $faqItems),
        ];
        ?>
        <script type="application/ld+json"><?php echo wp_json_encode($structuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
    <?php endif; ?>
<?php endif; ?>
