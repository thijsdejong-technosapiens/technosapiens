<?php

/**
 * Recursive footer navigation list.
 *
 * @param array $menuItems
 * @param string $listClass
 * @param int $depth
 */

use TechnoSapiens\Core\WcagComponent;
use TechnoSapiens\Core\Partial;

if (!isset($menuItems) || !is_array($menuItems) || count($menuItems) === 0) {
    return;
}

if (!isset($listClass)) {
    $listClass = 'site-footer__links-menu-list';
}

if (!isset($depth)) {
    $depth = 1;
}

$listClasses = $listClass;
if ($depth > 1) {
    $listClasses .= ' site-footer__links-menu-list--nested';
}
?>

<ul class="<?php echo esc_attr($listClasses); ?>">
    <?php foreach ($menuItems as $menuItem): ?>
        <?php
        $hasChildren = !empty($menuItem->children) && is_array($menuItem->children);
        $itemClasses = trim('site-footer__links-menu-item ' . ($menuItem->linkClass ?? ''));
        if ($hasChildren) {
            $itemClasses .= ' site-footer__links-menu-item--has-children';
        }
        ?>
        <li class="<?php echo esc_attr($itemClasses); ?>">
            <a href="<?php echo $menuItem->link; ?>"
               class="site-footer__links-menu-item-link"
               target="<?php echo $menuItem->target; ?>"
               <?php if ($menuItem->rel): ?>rel="<?php echo $menuItem->rel; ?>"<?php endif; ?>>
                <?php echo $menuItem->label; ?>
                <?php if ($menuItem->target === '_blank'): ?>
                    <?php echo WcagComponent::getSrLinkOpensInNewTabHtml(); ?>
                <?php endif; ?>
            </a>
            <?php if ($hasChildren): ?>
                <?php Partial::render('components/component-footer-menu', [
                    'menuItems' => $menuItem->children,
                    'listClass' => $listClass,
                    'depth' => $depth + 1,
                ]); ?>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ul>
