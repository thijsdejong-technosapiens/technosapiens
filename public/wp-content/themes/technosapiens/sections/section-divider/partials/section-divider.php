<?php

use TechnoSapiens\SectionMargins;
use TechnoSapiens\SectionReferencePartial;

$containerClasses = SectionReferencePartial::getContainerClasses('section-divider');
$marginTop = SectionMargins::getMarginTop();
$marginBottom = SectionMargins::getMarginBottom();
?>

<div class="<?php echo esc_attr(implode(' ', $containerClasses)); ?>"<?php if ($marginTop) : ?> data-mt="<?php echo esc_attr($marginTop); ?>"<?php endif; ?><?php if ($marginBottom) : ?> data-mb="<?php echo esc_attr($marginBottom); ?>"<?php endif; ?>>
    <div class="container container--narrow">

            <div class="section-divider__inner">
                <div class="section-divider__line"></div>

                <div class="section-divider__icon-container">
                    <svg class="section-divider__icon" aria-hidden="true">
                        <use xlink:href="<?php echo ICON_PATH; ?>icon-baby"/>
                    </svg>
                </div>

                <div class="section-divider__line"></div>
            </div>
        </div>
    </div>   
</div>
