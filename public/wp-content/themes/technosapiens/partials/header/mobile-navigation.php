<?php

use TechnoSapiens\Core\WcagComponent;
use TechnoSapiens\HeaderUtils;
use TechnoSapiens\Theme;

/**
 * @var array $items
 */

//set default values
if (!isset($items)) $items = [];

if (count($items) === 0) return;

?>

<nav class="mobile-navigation" aria-label="<?php esc_attr_e('Mobile navigation', Theme::TEXT_DOMAIN); ?>">

    <ul class="mobile-navigation__list">

        <?php foreach ($items as $item): ?>

            <li class="<?php echo esc_attr(implode(' ', HeaderUtils::getMainNavigationListItemClassesByItem($item, 'mobile-navigation__item'))); ?>">

                <a class="mobile-navigation__link"
                   href="<?php echo esc_url($item->link); ?>"
                   target="<?php echo esc_attr($item->target); ?>"
                   <?php if ($item->rel): ?>rel="<?php echo esc_attr($item->rel); ?>"<?php endif; ?>
                   <?php if ($item->isCurrent): ?>aria-current="page"<?php endif; ?>>
                    <?php echo esc_html($item->label); ?>
                    <?php if ($item->target === '_blank'): ?>
                        <?php echo WcagComponent::getSrLinkOpensInNewTabHtml(); ?>
                    <?php endif; ?>
                </a>

            </li>

        <?php endforeach; ?>

    </ul>

</nav>
