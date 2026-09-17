<?php

use TechnoSapiens\Core\PostType;
use TechnoSapiens\FactoryPlugin;
use TechnoSapiens\FactoryPlugin\PostTypeFactory;

//get current post type and factory class
$postType = PostType::getPostType();
$factoryClass = PostTypeFactory::getFactoryClassByPostType($postType);
$postTypeLabelSingular = strtolower(PostType::getSingularLabel($postType)) ?: __("item", FactoryPlugin::TEXT_DOMAIN);

//bail if no post type or factory class found
if (!$postType || !$factoryClass) return false;

//get prev next data
$prevNextData = $factoryClass::getPrevNextData(get_the_ID());
if (count($prevNextData) < 2) return false;

?>

<div class="single-next-prev-posts container">

    <?php foreach ($prevNextData as $index => $prevNextDataItem): ?>

        <?php
        $isPrev = $index === 0;
        $isArchive = $prevNextDataItem->date === '';
        $hasImages = property_exists($prevNextDataItem, 'image') && $prevNextDataItem->image && $prevNextDataItem->imageX2;
        $linkClasses = [
            'single-next-prev-posts__link',
            'single-next-prev-posts__link--' . ($hasImages ? 'has' : 'has-no') . '-image',
            'single-next-prev-posts__link--' . ($isPrev ? 'prev' : 'next'),
            'ts-link-snippet',
        ];
        ?>

        <div role="link" class="<?php echo implode(' ', $linkClasses) ?>">

            <?php if ($isPrev): ?>
                <figure class="single-next-prev-posts__image-container">

                    <?php if ($hasImages): ?>
                        <img class="single-next-prev-posts__image"
                             src="<?php echo $prevNextDataItem->image ?>"
                             srcset="<?php echo $prevNextDataItem->image ?> 1x, <?php echo $prevNextDataItem->imageX2 ?> 2x"
                             <?php if ($prevNextDataItem->imageWidth): ?>width="<?php echo $prevNextDataItem->imageWidth; ?>px"<?php endif; ?>
                             <?php if ($prevNextDataItem->imageHeight): ?>height="<?php echo $prevNextDataItem->imageHeight; ?>px"<?php endif; ?>
                             draggable="false"
                             loading="lazy"
                             alt="<?php echo $prevNextDataItem->imageAlt; ?>"/>
                    <?php endif; ?>

                    <svg class="single-next-prev-posts__icon">
                        <use xlink:href="<?php echo ICON_PATH; ?>icon-arrow-left"/>
                    </svg>

                </figure>
            <?php endif; ?>

            <span class="single-next-prev-posts__content">

                 <?php if (!$isArchive): ?>
                     <strong class="single-next-prev-posts__read-more">
                         <?php if ($isPrev): ?>
                             <?php echo sprintf(__("Previous %1s", FactoryPlugin::TEXT_DOMAIN), $postTypeLabelSingular); ?>
                         <?php else: ?>
                             <?php echo sprintf(__("Next %1s", FactoryPlugin::TEXT_DOMAIN), $postTypeLabelSingular); ?>
                         <?php endif; ?>
                     </strong>
                 <?php endif; ?>

                <?php if ($prevNextDataItem->title): ?>
                    <a href="<?php echo $prevNextDataItem->link; ?>"
                       draggable="false"
                       target="_self"
                       class="single-next-prev-posts__title-link"
                       data-role="primary-link">
                        <span class="single-next-prev-posts__title h5">
                            <?php echo $prevNextDataItem->title; ?>
                        </span>
                    </a>
                <?php endif; ?>

                <?php if ($prevNextDataItem->date && $factoryClass::featureEnabled('enableVisiblePostDates')): ?>
                    <time class="single-next-prev-posts__date">
                        <?php echo $prevNextDataItem->date; ?>
                    </time>
                <?php endif; ?>

            </span>

            <?php if (!$isPrev): ?>
                <figure class="single-next-prev-posts__image-container">

                    <?php if ($hasImages): ?>
                        <img class="single-next-prev-posts__image"
                             src="<?php echo $prevNextDataItem->image ?>"
                             srcset="<?php echo $prevNextDataItem->image ?> 1x, <?php echo $prevNextDataItem->imageX2 ?> 2x"
                             <?php if ($prevNextDataItem->imageWidth): ?>width="<?php echo $prevNextDataItem->imageWidth; ?>px"<?php endif; ?>
                             <?php if ($prevNextDataItem->imageHeight): ?>height="<?php echo $prevNextDataItem->imageHeight; ?>px"<?php endif; ?>
                             draggable="false"
                             loading="lazy"
                             alt="<?php echo $prevNextDataItem->imageAlt; ?>"/>
                    <?php endif; ?>

                    <svg class="single-next-prev-posts__icon">
                        <use xlink:href="<?php echo ICON_PATH; ?>icon-arrow-right"/>
                    </svg>

                </figure>
            <?php endif; ?>

        </div>

    <?php endforeach; ?>

</div>