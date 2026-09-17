<?php

use TechnoSapiens\Core\Formatting;
use TechnoSapiens\SectionMargins;
use TechnoSapiens\SectionReferencePartial;

$openFirstItem = (bool) get_field('open_first_item');
$multiCollapse = (bool) get_field('multi_collapse');
$sectionTitle = (string) get_field('title');

$containerClasses = SectionReferencePartial::getContainerClasses('section-accordion');
$accordionId = 'accordion-' . ($block['id'] ?? uniqid());
$marginTop = SectionMargins::getMarginTop();
$marginBottom = SectionMargins::getMarginBottom();

?>

<?php if (have_rows('items')) : ?>

    <div class="<?php echo esc_attr(implode(' ', $containerClasses)); ?>"<?php if ($marginTop) : ?> data-mt="<?php echo esc_attr($marginTop); ?>"<?php endif; ?><?php if ($marginBottom) : ?> data-mb="<?php echo esc_attr($marginBottom); ?>"<?php endif; ?> data-pt="large" data-pb="large">
        <div class="container container--narrow">
            <?php if ($sectionTitle) : ?>
                <h2 class="section-accordion__section-title"><?php echo esc_html($sectionTitle); ?></h2>
            <?php endif; ?>
            
            <div class="section-accordion__list" id="<?php echo esc_attr($accordionId); ?>" data-multi-collapse="<?php echo $multiCollapse ? 'true' : 'false'; ?>">
                <?php
                $index = 0;
                while (have_rows('items')) :
                    the_row();
                    $itemTitle = get_sub_field('title');
                    $itemContent = get_sub_field('wysiwyg');
                    if (!$itemTitle || !$itemContent) {
                        continue;
                    }
                    $isOpen = $index === 0 && $openFirstItem;
                    $itemId = $accordionId . '-item-' . $index;
                    $contentId = $itemId . '-content';
                    ?>
                    <details class="section-accordion__item" id="<?php echo esc_attr($itemId); ?>"<?php if (!$multiCollapse) : ?> name="<?php echo esc_attr($accordionId); ?>"<?php endif; ?><?php if ($isOpen) : ?> open<?php endif; ?>>
                        <summary class="section-accordion__summary">
                            <span class="section-accordion__title"><?php echo esc_html($itemTitle); ?></span>
                            <span class="section-accordion__toggle" aria-hidden="true"></span>
                        </summary>
                        <div class="section-accordion__content" id="<?php echo esc_attr($contentId); ?>">
                            <?php echo Formatting::toHtml($itemContent); ?>
                        </div>
                    </details>
                    <?php
                    $index++;
                endwhile;
                ?>
            </div>
        </div>
    </div>

<?php endif; ?>
