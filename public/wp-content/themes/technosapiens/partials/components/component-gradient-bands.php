<?php

/**
 * @var string $size
 * @var string $bandSize
 * @var int[] $stops
 * @var string $axis
 * @var string $staggerMode
 * @var string $class
 * @var string $containerClass
 * @var string $bandClass
 */

if (!isset($size)) $size = 'large';
if (!isset($bandSize)) $bandSize = '';
if (!isset($stops) || !is_array($stops)) $stops = array_merge(range(1, 7), range(6, 1));
if (!isset($axis)) $axis = 'vertical';
if (!isset($staggerMode)) $staggerMode = 'start';
if (!isset($class)) $class = '';
if (!isset($containerClass)) $containerClass = 'gradient-bands';
if (!isset($bandClass)) $bandClass = 'gradient-band';

$containerClasses = [$containerClass];
if ($size) $containerClasses[] = $containerClass . '--' . $size;
if ($axis) $containerClasses[] = $containerClass . '--' . $axis;
if ($class) $containerClasses[] = $class;

?>
<div class="<?php echo esc_attr(implode(' ', $containerClasses)); ?>" data-stagger-mode="<?php echo esc_attr($staggerMode); ?>" aria-hidden="true">
    <?php foreach ($stops as $stop) : ?>
        <?php
        $bandClasses = [
            $bandClass,
            $bandClass . '--stop-' . (int)$stop,
        ];
        if ($bandSize) $bandClasses[] = $bandClass . '--' . $bandSize;
        ?>
        <div class="<?php echo esc_attr(implode(' ', $bandClasses)); ?>"></div>
    <?php endforeach; ?>
</div>
