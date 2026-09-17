<?php

use TechnoSapiens\Core\ButtonComponent;
use TechnoSapiens\Core\Image;
use TechnoSapiens\Core\Partial;

/**
 * @var stdClass $data
 */

$hasSlot = $data->slotType !== 'none' && ($data->slotType !== 'static-section' || $data->slotStaticSection);

$containerClasses = [
    'hm-image-simple',
    'hm-image-simple--size-' . $data->size
];

if ($hasSlot) $containerClasses[] = 'hm-image-simple--has-slot';

$figureClasses = [
    'hm-image-simple__image-container'
];

?>

    <div class="<?php echo implode(' ', $containerClasses); ?>">

        <style>
            .hm-image-simple {
                --ts-hero-height-regular: <?php echo $data->imageHeightRegular; ?>px;
                --ts-hero-height-large: <?php echo $data->imageHeightLarge; ?>px;
                --ts-hero-height-mobile: <?php echo $data->imageHeightMobileRegular; ?>px;
                --ts-hero-height-mobile-large: <?php echo $data->imageHeightMobileLarge; ?>px;
            }
        </style>

        <figure class="<?php echo implode(' ', $figureClasses); ?>">

            <?php if ($data->mediaType === 'video'): ?>
                <video class="hm-image-simple__video"
                       draggable="false"
                       style="object-position: <?php echo $data->focusPoint; ?>"
                       autoplay
                       muted
                       loop
                       playsinline>
                    <source src="<?php echo $data->videoUrlDesktop; ?>" type="video/mp4"
                            media="all and (min-width: 481px)">
                    <source src="<?php echo $data->videoUrlMobile; ?>" type="video/mp4"
                            media="all and (max-width: 480px)">
                </video>
            <?php elseif ($data->mediaType === 'image'): ?>
                <?php echo Image::render([
                    'blockClass' => 'hm-image-simple',
                    'lazy' => false,
                    'sources' => [
                        [
                            'media' => '(min-width: 481px)',
                            'url' => $data->imageDesktop,
                            'width' => $data->imageDesktopWidth,
                            'height' => $data->imageDesktopHeight,
                        ],
                        [
                            'media' => '(min-width: 0px)',
                            'url' => $data->imageMobile,
                            'url2x' => $data->imageMobileX2,
                            'width' => $data->imageMobileWidth,
                            'height' => $data->imageMobileHeight,
                        ],
                        [
                            'url' => $data->imageDesktop,
                            'alt' => $data->imageAlt,
                            'width' => $data->imageDesktopWidth,
                            'height' => $data->imageDesktopHeight,
                            'style' => ['object-position' => $data->imageFocusPoint]
                        ],
                    ],
                ]); ?>
            <?php endif; ?>

        </figure>

        <div class="hm-image-simple__meta">
            <div class="hm-image-simple__meta-container container">
                
                <div class="hm-image-simple__meta-top">

                    <?php if (!empty($data->headingText)): ?>
                        <?php echo '<' . $data->headingTag . ' class="hm-image-simple__heading">'; ?>
                        <?php echo $data->headingText; ?>
                        <?php echo '</' . $data->headingTag . '>'; ?>
                    <?php endif; ?>

                    <?php if ($data->subHeading): ?>
                        <span class="hm-image-simple__sub-heading">
                            <?php echo $data->subHeading; ?>
                        </span>
                    <?php endif; ?>

                </div>

                <?php if ($data->content): ?>
                    <div class="hm-image-simple__content">
                        <?php echo $data->content; ?>
                    </div>
                <?php endif; ?>

                <?php if (count($data->buttons) > 0): ?>
                    <div class="hm-image-simple__buttons">
                        <?php foreach ($data->buttons as $button): ?>
                            <?php echo ButtonComponent::render(apply_filters('ts_hm_image_simple_button_args', [
                                'text' => $button->text,
                                'href' => $button->link,
                                'target' => $button->target,
                                'rel' => $button->rel,
                                'blockClass' => 'hm-image-simple',
                                'type' => $button->type,
                                'style' => $button->style,
                                'size' => $data->buttonSize,
                            ])); ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>

        </div>

    </div>

    <?php if ($hasSlot): ?>
        <div class="hm-image-simple__slot hm-image-simple__slot--<?php echo $data->slotType; ?>">
            <div class="container">    
                <div class="hm-image-simple__slot-inner hm-image-simple__slot-inner--<?php echo $data->slotType; ?>">
                    <?php if ($data->slotType === 'breadcrumbs'): ?>
                        <?php echo Partial::render('components/component-breadcrumbs', [], false); ?>
                    <?php elseif ($data->slotType === 'static-section'): ?>
                        <?php echo $data->slotStaticSection; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>