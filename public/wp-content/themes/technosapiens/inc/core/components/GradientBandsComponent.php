<?php

namespace TechnoSapiens\Core;

/**
 * class GradientBandsComponent
 * @package TechnoSapiens\Core
 */
class GradientBandsComponent {

    const CONTAINER_CLASS = 'gradient-bands';
    const BAND_CLASS = 'gradient-band';

    const SIZES = ['xlarge', 'large', 'medium', 'small']; // add sizes in rem: xlarge: 8.25rem, large: 3.75rem, medium: 2.4rem, small: 2.1rem
    const AXES = ['vertical', 'horizontal'];
    const STAGGER_MODES = ['start', 'end', 'center'];
    const STOP_COUNT = 7;

    /**
     * Default stop sequence: 1→7 then 6→1.
     * @return int[]
     */
    public static function getDefaultStops(): array {
        return array_merge(
            range(1, self::STOP_COUNT),
            range(self::STOP_COUNT - 1, 1)
        );
    }

    /**
     * Renders gradient bands markup.
     * @param array $args
     * @type string $bandSize Optional size modifier applied to every band (xlarge|large|medium|small).
     * @type array $stops Stop numbers to render (1–7). Defaults to 1→7→1.
     * @type string $axis Layout axis (vertical|horizontal). Defaults to 'vertical'.
     * @type string $staggerMode Stagger order (start|end|center). Defaults to 'start'.
     * @type string $class Extra CSS classes for the container.
     * @return string
     */
    public static function render(array $args = []): string {
        $defaults = [
            'bandSize' => '',
            'stops' => self::getDefaultStops(),
            'axis' => 'vertical',
            'staggerMode' => 'start',
            'class' => '',
        ];

        $defaults = apply_filters('ts_gradient_bands_default_args', $defaults);
        $args = apply_filters('ts_gradient_bands_args', array_merge($defaults, $args));

        $bandSize = is_string($args['bandSize']) ? $args['bandSize'] : '';
        if ($bandSize && !in_array($bandSize, self::SIZES, true)) $bandSize = '';

        $stops = is_array($args['stops']) ? $args['stops'] : self::getDefaultStops();
        $stops = array_values(array_filter($stops, static function ($stop) {
            return is_numeric($stop) && (int)$stop >= 1 && (int)$stop <= self::STOP_COUNT;
        }));
        $stops = array_map('intval', $stops);
        if (count($stops) === 0) $stops = self::getDefaultStops();

        $axis = is_string($args['axis']) ? $args['axis'] : 'vertical';
        if (!in_array($axis, self::AXES, true)) $axis = 'vertical';

        $staggerMode = is_string($args['staggerMode']) ? $args['staggerMode'] : 'start';
        if (!in_array($staggerMode, self::STAGGER_MODES, true)) $staggerMode = 'start';

        $extraClass = is_string($args['class']) ? $args['class'] : '';

        return Partial::render('components/component-gradient-bands', [
            'bandSize' => $bandSize,
            'stops' => $stops,
            'axis' => $axis,
            'staggerMode' => $staggerMode,
            'class' => $extraClass,
            'containerClass' => self::CONTAINER_CLASS,
            'bandClass' => self::BAND_CLASS,
        ], false);
    }
}
