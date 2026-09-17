<?php

/**
 * @var array     $items
 * @var \stdClass $googleRatingData
 */

use TechnoSapiens\Core\Partial;
use TechnoSapiens\Core\WcagComponent;
use TechnoSapiens\PolylangComponent;

//set default values
if (!isset($items)) $items = [];
$googleRatingData = $googleRatingData ?? null;

// get all raww languages
$languages = function_exists('pll_the_languages') ? PolylangComponent::getAllLanguages() : [];
?>

<div class="top-navigation">

    <nav class="top-navigation__right" aria-label="Secondary">
        <ul class="top-navigation__items">

            <?php foreach ($items as $item): ?>

                <?php

                //start setting up item CSS classes
                $listItemClasses = ['top-navigation__item'];

                //handle scenario where item is current or current parent
                if ($item->isCurrent) $listItemClasses[] = 'top-navigation__item--current';
                if ($item->isCurrentParent) $listItemClasses[] = 'top-navigation__item--current-parent';

                ?>

                <li class="<?php echo implode(' ', $listItemClasses); ?>">

                    <a class="top-navigation__item-link"
                    href="<?php echo $item->link; ?>"
                    target="<?php echo $item->target; ?>"
                    <?php if ($item->rel): ?>rel="<?php echo $item->rel; ?>"<?php endif; ?>>

                        <span class="top-navigation__item-link-text">
                            <?php echo $item->label; ?>
                            <?php if ($item->target === '_blank'): ?>
                                <?php echo WcagComponent::getSrLinkOpensInNewTabHtml(); ?>
                            <?php endif; ?>
                        </span>

                    </a>

                </li>

            <?php endforeach; ?>


            <?php if (function_exists('pll_the_languages')): ?>
                <?php foreach ($languages as $language): ?> 
                    <li class="top-navigation__item">
                        <a class="top-navigation__item-link top-navigation__item-link--language" href="<?php echo $language['url']; ?>">
                            <span class="top-navigation__item-link-text">
                                <?php echo $language['name']; ?>
                            </span>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="top-navigation__left">
        <?php if ($googleRatingData && $googleRatingData->isValid): ?>
            <?php Partial::render('components/component-google-rating', [
                'rating' => $googleRatingData->rating,
                'url' => $googleRatingData->url,
            ]); ?>
        <?php endif; ?>
    </div>

</div>