<?php

use TechnoSapiens\Core\Image;
use TechnoSapiens\SectionMargins;
use TechnoSapiens\SectionReferencePartial;
use TechnoSapiens\SectionTitleHeading;
use TechnoSapiens\SectionUsps;

$sectionUsps = SectionUsps::getInstance();
$uspsTitle = $sectionUsps->getUspsTitle();
$titleHeadingTag = SectionTitleHeading::getTag();
$titleHeadingSize = SectionTitleHeading::getSize();
$uspsImageId = $sectionUsps->getUspsImageId();
$uspsItems = $sectionUsps->getUspsItems();
$hasContent = $uspsTitle || $uspsImageId || !empty($uspsItems);

$containerClasses = SectionReferencePartial::getContainerClasses('section-usps');
$marginTop = SectionMargins::getMarginTop();
$marginBottom = SectionMargins::getMarginBottom();
?>

<?php if ($hasContent) : ?>
    <div class="<?php echo esc_attr(implode(' ', $containerClasses)); ?>"<?php if ($marginTop) : ?> data-mt="<?php echo esc_attr($marginTop); ?>"<?php endif; ?><?php if ($marginBottom) : ?> data-mb="<?php echo esc_attr($marginBottom); ?>"<?php endif; ?>>
        <div class="container">
            <div class="section-usps__row">

                <?php if ($uspsImageId) : ?>
                    <div class="section-usps__col section-usps__col--image">
                        <figure class="section-usps__figure">
                            <?php echo Image::render([
                                'blockClass' => 'section-usps',
                                'sources' => [
                                    [
                                        'id' => $uspsImageId,
                                        'size' => 'full',
                                    ],
                                ],
                            ]); ?>
                        </figure>
                    </div>
                <?php endif; ?>

                <?php if ($uspsTitle || !empty($uspsItems)) : ?>
                    <div class="section-usps__col section-usps__col--content">
                        <?php if ($uspsTitle) : ?>
                            <<?php echo esc_attr($titleHeadingTag); ?> class="section-usps__title <?php echo esc_attr($titleHeadingSize); ?>"><?php echo wp_kses_post($uspsTitle); ?></<?php echo esc_attr($titleHeadingTag); ?>>
                        <?php endif; ?>

                        <?php if (!empty($uspsItems)) : ?>
                            <ul class="section-usps__list">
                                <?php foreach ($uspsItems as $uspsItem) : ?>
                                    <li class="section-usps__item">
                                        <svg class="section-usps__icon icon" aria-hidden="true">
                                            <use xlink:href="<?php echo ICON_PATH; ?>icon-checkbox"/>
                                        </svg>
                                        <span class="section-usps__text"><?php echo esc_html($uspsItem['text']); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
<?php endif; ?>
