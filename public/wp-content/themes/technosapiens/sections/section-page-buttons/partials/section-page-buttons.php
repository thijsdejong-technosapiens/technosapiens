<?php

use TechnoSapiens\SectionMargins;
use TechnoSapiens\SectionPageButtons;
use TechnoSapiens\SectionReferencePartial;

$containerClasses = SectionReferencePartial::getContainerClasses('section-page-buttons');
$pageButtons = SectionPageButtons::getInstance()->getPageButtons();
$marginTop = SectionMargins::getMarginTop();
$marginBottom = SectionMargins::getMarginBottom();
?>

<?php if (!empty($pageButtons)) : ?>
    <div class="<?php echo esc_attr(implode(' ', $containerClasses)); ?>"<?php if ($marginTop) : ?> data-mt="<?php echo esc_attr($marginTop); ?>"<?php endif; ?><?php if ($marginBottom) : ?> data-mb="<?php echo esc_attr($marginBottom); ?>"<?php endif; ?>>
        <div class="container">
            <div class="section-page-buttons__row">
                <?php foreach ($pageButtons as $pageButton) : ?>
                    <div class="section-page-buttons__col">
                        <a
                            href="<?php echo esc_url($pageButton['url']); ?>"
                            class="section-page-buttons__button ts-button ts-button--style-filled ts-button--color-primary"
                        >
                            <span class="ts-button__text"><?php echo esc_html($pageButton['label']); ?></span>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
