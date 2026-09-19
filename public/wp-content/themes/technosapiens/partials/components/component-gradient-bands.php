<?php

/**
 * @var string $size
 * @var string $bandSize
 * @var int[] $stops
 * @var string $class
 * @var string $containerClass
 * @var string $bandClass
 */

if (!isset($size)) $size = 'large';
if (!isset($bandSize)) $bandSize = '';
if (!isset($stops) || !is_array($stops)) $stops = array_merge(range(1, 7), range(6, 1));
if (!isset($class)) $class = '';
if (!isset($containerClass)) $containerClass = 'gradient-bands';
if (!isset($bandClass)) $bandClass = 'gradient-band';

$containerClasses = [$containerClass];
if ($size) $containerClasses[] = $containerClass . '--' . $size;
if ($class) $containerClasses[] = $class;

?>
<div class="<?php echo esc_attr(implode(' ', $containerClasses)); ?>" aria-hidden="true">
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
