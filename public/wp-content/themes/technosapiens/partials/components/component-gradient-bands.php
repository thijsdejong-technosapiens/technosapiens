<?php

/**
 * @var string $bandSize
 * @var int[] $stops
 * @var string $axis
 * @var string $staggerMode
 * @var float $delay
 * @var string $waitFor
 * @var string $class
 * @var string $containerClass
 * @var string $bandClass
 */

if (!isset($bandSize)) $bandSize = '';
if (!isset($stops) || !is_array($stops)) $stops = array_merge(range(1, 7), range(6, 1));
if (!isset($axis)) $axis = 'vertical';
if (!isset($staggerMode)) $staggerMode = 'start';
if (!isset($delay)) $delay = 0;
if (!isset($waitFor)) $waitFor = '';
if (!isset($class)) $class = '';
if (!isset($containerClass)) $containerClass = 'gradient-bands';
if (!isset($bandClass)) $bandClass = 'gradient-band';

$containerClasses = [$containerClass];
if ($axis) $containerClasses[] = $containerClass . '--' . $axis;
if ($class) $containerClasses[] = $class;

$delayValue = is_numeric($delay) ? (float)$delay : 0.0;
$waitForValue = is_string($waitFor) ? trim($waitFor) : '';

?>
<div class="<?php echo esc_attr(implode(' ', $containerClasses)); ?>" data-stagger-mode="<?php echo esc_attr($staggerMode); ?>"<?php if ($delayValue > 0) : ?> data-delay="<?php echo esc_attr((string)$delayValue); ?>"<?php endif; ?><?php if ($waitForValue !== '') : ?> data-wait-for="<?php echo esc_attr($waitForValue); ?>"<?php endif; ?> aria-hidden="true">
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
