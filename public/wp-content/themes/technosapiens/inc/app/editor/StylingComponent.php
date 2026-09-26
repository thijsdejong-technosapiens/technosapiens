<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Singleton;
use OzdemirBurak\Iris\Color\Hex;

/**
 * Class StylingComponent
 * @package TechnoSapiens
 *
 * Token layers (alias-first migration):
 * 1. Primitives — brand colours, type faces, space, radius, elevation
 * 2. Semantic — text / surface / border / action roles used by sections
 * 3. Recipes — button styles live in SCSS (local --ts-btn-*). Feature adapters
 *    (GF mail, admin-bar light) own their tokens and may extend via ts_css_variables.
 */
class StylingComponent extends Singleton {

    /**
     * CSS variables — populated at runtime with hardcoded defaults.
     * @var array|string[]
     */
    public array $cssVariables = [];

    /**
     * StylingComponent constructor
     */
    protected function __construct() {
        $this->initCssVariables();
        add_action('wp_head', [$this, 'renderCssVariables'], 1, 0);
        add_action('customize_controls_print_styles', [$this, 'renderCssVariables'], 1, 0);
        add_action('admin_head', [$this, 'renderCssVariables'], 1, 0);
        add_action('login_head', [$this, 'renderCssVariables'], 1, 0);
        add_action('enqueue_block_editor_assets', [$this, 'renderCssVariablesInEditor'], 1, 0);
        add_action('enqueue_block_assets', [$this, 'renderCssVariablesInEditor'], 1, 0);
    }

    /**
     * Resolve derived tokens (hovers, on-colours) from brand primitives.
     * @return array|string[]
     */
    public function getCssVariables(): array {
        $cssVariables = $this->cssVariables;

        $colorPrimary = (string) $cssVariables['color-primary'];
        $colorSecondary = (string) $cssVariables['color-secondary'];
        $colorTertiary = (string) $cssVariables['color-tertiary'];
        $colorDanger = (string) $cssVariables['color-danger'];
        $colorWhite = (string) $cssVariables['color-white'];

        // Brand hover + on-colour roles (single derivation point)
        $cssVariables['color-primary-hover'] = (string) (new Hex($colorPrimary))->lighten(8);
        $cssVariables['color-secondary-hover'] = (string) (new Hex($colorSecondary))->darken(8);
        $cssVariables['color-tertiary-hover'] = (string) (new Hex($colorTertiary))->darken(8);
        $cssVariables['color-danger-hover'] = (string) (new Hex($colorDanger))->darken(8);
        $cssVariables['color-on-primary'] = $colorWhite;
        $cssVariables['color-on-secondary'] = $colorWhite;
        $cssVariables['color-on-tertiary'] = $colorPrimary;
        $cssVariables['color-on-danger'] = $colorWhite;

        return apply_filters('ts_css_variables', $cssVariables);
    }

    /**
     * Populate $cssVariables with hardcoded defaults.
     * Brand hex is defined once as locals, then referenced by buttons / nav / mail.
     * @return void
     */
    private function initCssVariables(): void {
        $colorPrimary = '#662264';
        $colorPrimaryDark = '#4E134E';
        $colorPrimaryDarker = '#250525';
        $colorSecondary = '#E10F7E';
        $colorTertiary = '#FDB500';
        $colorWhite = '#FBFBF3';
        $colorBlack = '#000000';
        $colorGrey = '#222222';
        $colorLight = '#F3F2EB';
        $colorBorder = '#EEEEEE';
        $colorBody = '#FFFFFF';
        $colorAccent = '#00132F';
        $colorNav = '#FFFFFF';
        $colorInputBg = '#EEEEEE';
        $colorInputDefault = '#CCCCCC';
        $colorInputFocus = '#666666';
        $colorInvalid = '#FF0000';
        $colorPasswordMedium = '#f0b849';
        $colorPasswordStrong = '#4db54f';
        $colorNotificationInfo = '#419ecd';
        $colorGradientStop1 = '#5E185E';
        $colorGradientStop3 = '#7F1E69';
        $colorGradientStop4 = '#971A6E';
        $colorGradientStop5 = '#B01774';
        $colorGradientStop6 = '#C81379';

        $fontSans = "'Plus Jakarta Sans', sans-serif";

        $elevation0 = 'none';
        $elevation1 = '0 4px 4px rgba(0,0,0,.1)';
        $elevation2 = '0 8px 8px rgba(0,0,0,.15)';
        $elevation3 = '0 25px 100px 0px rgba(0,0,0,0.15)';
        $elevation4 = '4px 4px 12px 0 rgba(0, 0, 0, 0.20)';

        $radiusXs = '0.5rem';
        $radiusBase = '1rem';
        $radiusSm = '1.9rem';
        $radiusMd = '2.25rem';
        $radiusLg = '2.9rem';
        $radiusXl = '3.125rem';
        $radiusXxl = '4.4rem';
        $radiusPill = '9999px';
        $radiusNone = '0';

        $this->cssVariables = [
            // --- Type: one family; apply weight at the call site ---
            'font-family-sans'  => $fontSans,
            'font-heading'      => $fontSans,
            'font-body'         => $fontSans,
            'font-secondary'    => $fontSans,
            'font-button'       => $fontSans,
            'font-bold'         => $fontSans,
            'font-label'        => $fontSans,
            'font-light'        => $fontSans,
            'font-regular'      => $fontSans,
            'font-semibold'     => $fontSans,

            // --- Sizes ---
            'font-size-default' => '1rem',
            'icon-size-default' => '1rem',

            // Font sizes + line heights (mobile); *-md used at mq-up(md)
            'font-size-extra-large'   => '1.5rem',
            'line-height-extra-large' => '2rem',

            'font-size-large'        => '1.25rem',
            'line-height-large'      => '1.5rem',
            'font-size-large-md'     => '1.25rem',
            'line-height-large-md'   => '1.75rem',

            'font-size-medium'       => '1.125rem',
            'line-height-medium'     => '1.5rem',
            'font-size-medium-md'    => '1.125rem',
            'line-height-medium-md'  => '1.625rem',

            'font-size-base'         => '1rem',
            'line-height-base'       => '1.25rem',
            'line-height-base-md'    => '1.5rem',

            'font-size-small'        => '0.9rem',
            'line-height-small'      => '1.125rem',
            'line-height-small-md'   => '1.375rem',

            'font-size-tiny'         => '0.75rem',
            'line-height-tiny'       => '0.875rem',
            'line-height-tiny-md'    => '1.125rem',

            'font-size-caption'      => '0.75rem',
            'line-height-caption'    => '0.875rem',
            'line-height-caption-md' => '1.125rem',

            'font-size-counter'      => '3rem',
            'line-height-counter'    => '3.5rem',

            'font-size-body-large'   => '1.25rem',
            'line-height-body-large' => '2rem',

            // --- Brand colour primitives ---
            'color-primary'      => $colorPrimary,
            'color-primary-dark' => $colorPrimaryDark,
            'color-primary-darker' => $colorPrimaryDarker,
            'color-secondary'    => $colorSecondary,
            'color-tertiary'     => $colorTertiary,
            'color-danger'       => $colorInvalid,
            'color-white'        => $colorWhite,
            'color-black'        => $colorBlack,
            'color-grey'         => $colorGrey,
            'color-light'        => $colorLight,
            'color-body'         => $colorBody,
            'color-accent'       => $colorAccent,

            // Gradient stops
            'color-gradient-stop-1' => $colorGradientStop1,
            'color-gradient-stop-2' => $colorPrimary,
            'color-gradient-stop-3' => $colorGradientStop3,
            'color-gradient-stop-4' => $colorGradientStop4,
            'color-gradient-stop-5' => $colorGradientStop5,
            'color-gradient-stop-6' => $colorGradientStop6,
            'color-gradient-stop-7' => $colorSecondary,

            // Derived in getCssVariables(): color-*-hover, color-on-*
            'color-primary-hover'   => '',
            'color-secondary-hover' => '',
            'color-tertiary-hover'  => '',
            'color-danger-hover'    => '',
            'color-on-primary'      => '',
            'color-on-secondary'    => '',
            'color-on-tertiary'     => '',
            'color-on-danger'       => '',

            // --- Semantic colour roles (sections should prefer these) ---
            'color-text'          => $colorPrimaryDark,
            'color-text-muted'    => $colorInputDefault,
            'color-text-inverse'  => $colorWhite,
            'color-surface'       => $colorBody,
            'color-surface-muted' => $colorLight,
            'color-border'        => $colorBorder,
            'color-action'        => $colorPrimary,
            'color-action-hover'  => '',
            'color-on-action'     => $colorWhite,

            // Legacy font-colour aliases (same hex as semantic text roles)
            'font-color-primary'   => $colorPrimaryDark,
            'font-color-secondary' => $colorSecondary,
            'font-color-tertiary'  => $colorInputDefault,
            'font-color-white'     => $colorWhite,
            'font-color-nav'       => $colorNav,
            'font-color-nav-hover' => $colorPrimary,

            // Inputs
            'color-input-bg'      => $colorInputBg,
            'color-input-default' => $colorInputDefault,
            'color-input-focus'   => $colorInputFocus,
            'color-input-invalid' => $colorInvalid,

            // Password strength
            'color-password-weak'   => $colorInvalid,
            'color-password-medium' => $colorPasswordMedium,
            'color-password-strong' => $colorPasswordStrong,

            // Font weights (match @font-face 200/400/500/600; bold maps to nearest loaded face)
            'font-weight-light'     => 400,
            'font-weight-semilight' => 400,
            'font-weight-regular'   => 400,
            'font-weight-medium'    => 500,
            'font-weight-semibold'  => 700,
            'font-weight-bold'      => 700,

            // --- Elevation (canonical) + legacy shadow aliases ---
            'elevation-0' => $elevation0,
            'elevation-1' => $elevation1,
            'elevation-2' => $elevation2,
            'elevation-3' => $elevation3,
            'elevation-4' => $elevation4,
            'shadow-sm'       => $elevation1,
            'shadow-sm-hover' => $elevation2,
            'shadow-md'       => $elevation3,
            'shadow-card'     => $elevation4,

            // --- Motion ---
            'duration-fast'           => '0.15s',
            'duration-base'           => '0.3s',
            'easing-standard'         => 'ease-in-out',
            'transition-colors'       => 'background-color 0.3s ease-in-out, color 0.3s ease-in-out, border-color 0.3s ease-in-out',
            'transition'              => 'background-color 0.3s ease-in, color 0.3s ease-in, border-color 0.3s ease-in, opacity 0.3s ease-in, box-shadow 0.3s ease-in, transform 0.3s ease-in',
            'transition-ease-in-out'  => 'background-color 0.3s ease-in-out, color 0.3s ease-in-out, border-color 0.3s ease-in-out, opacity 0.3s ease-in-out, box-shadow 0.3s ease-in-out, transform 0.3s ease-in-out',

            // --- Focus + disabled ---
            'focus-ring'         => "2px solid {$colorPrimaryDark}",
            'focus-ring-inverse' => "2px solid {$colorWhite}",
            'focus-ring-offset'  => '0.125rem',
            'opacity-disabled'   => '0.4',

            // --- Z-index ---
            'z-index-dropdown' => '100',
            'z-index-sticky'   => '200',
            'z-index-modal'    => '1000',
            'z-index-toast'    => '1100',
            'z-index-admin'    => '99999',

            // --- Radius (canonical + legacy names) ---
            'radius-none'          => $radiusNone,
            'radius-xs'            => $radiusXs,
            'radius-base'          => $radiusBase,
            'radius-sm'            => $radiusSm,
            'radius-md'            => $radiusMd,
            'radius-lg'            => $radiusLg,
            'radius-xl'            => $radiusXl,
            'radius-xxl'           => $radiusXxl,
            'radius-pill'          => $radiusPill,
            'border-radius-xs'     => $radiusXs,
            'border-radius-base'   => $radiusBase,
            'border-radius-sm'     => $radiusSm,
            'border-radius-md'     => $radiusMd,
            'border-radius-lg'     => $radiusLg,
            'border-radius-xl'     => $radiusXl,
            'border-radius-xxl'    => $radiusXxl,
            'button-border-radius' => $radiusXl,

            // --- Space scale (0.25rem steps) ---
            'space-1'  => '0.25rem',
            'space-2'  => '0.5rem',
            'space-3'  => '0.75rem',
            'space-4'  => '1rem',
            'space-5'  => '1.25rem',
            'space-6'  => '1.5rem',
            'space-7'  => '1.75rem',
            'space-8'  => '2rem',
            'space-10' => '2.5rem',
            'space-12' => '3rem',
            'space-16' => '4rem',

            // Gradients
            'gradient-primary' => "linear-gradient(180deg, {$colorPrimaryDark} 0%, rgba(62, 31, 42, 0) 100%)",

            // Pagination
            'pagination-size' => '2.5rem',

            // Grid
            'base-width'   => '1410px',
            'narrow-width' => '1188px',
            'gutter-width' => '2rem',

            // Horizontal ruler
            'horizontal-ruler-width' => '1px',
            'horizontal-ruler-color' => $colorBorder,

            // Navigation chrome
            'nav-height-top'        => '2.5rem',
            'nav-height-top-mobile' => '0rem',
            'nav-height'            => '3.75rem',
            'nav-height-mobile'     => '3.75rem',
            'nav-background-color'  => $colorWhite,

            // Row gaps (legacy; prefer space-* for new work)
            'row-gap-small'   => '1rem',
            'row-gap-default' => '2rem',
            'row-gap-medium'  => '3rem',
            'row-gap-large'   => '4rem',
            'row-gap-xlarge'  => '5rem',

            // Feedback (not brand)
            'notification-default'       => $colorGrey,
            'notification-informational' => $colorNotificationInfo,
            'notification-success'       => $colorPasswordStrong,
            'notification-warning'       => $colorPasswordMedium,
            'notification-error'         => $colorInvalid,
            'color-feedback-info'        => $colorNotificationInfo,
            'color-feedback-success'     => $colorPasswordStrong,
            'color-feedback-warning'     => $colorPasswordMedium,
            'color-feedback-error'       => $colorInvalid,

            // Form controls (shared by GF, search, password, selects — not GF-only)
            'form-field-height'           => '3rem',
            'form-field-border-radius'    => '0.25rem',
            'form-field-border'           => '1px solid #c7c9d9',
            'form-field-background-color' => $colorWhite,
        ];
    }

    /**
     * Generate CSS variables string
     * @return string
     */
    private function generateCssVariablesString(): string {
        $variables = $this->getCssVariables();

        // Semantic action-hover aliases the derived brand hover after Iris runs
        $variables['color-action-hover'] = $variables['color-primary-hover'];

        $cssVariableStrings = [];
        foreach ($variables as $variableKey => $variableValue) {
            if ($variableValue === '' || $variableValue === null) {
                continue;
            }
            $cssVariableStrings[] = '--ts-' . trim($variableKey) . ': ' . $variableValue . ';';
        }

        return ':root {' . implode(' ', $cssVariableStrings) . '}';
    }

    /**
     * Render CSS variables partial
     * @return void
     */
    public function renderCssVariables(): void {
        echo '<style id="ts-css-variables">' . $this->generateCssVariablesString() . '</style>';
    }

    /**
     * Render CSS variables inline for block editor iframe contexts
     * @return void
     */
    public function renderCssVariablesInEditor(): void {
        $cssVariablesContent = $this->generateCssVariablesString();
        if (wp_style_is(Theme::TEXT_DOMAIN . '_gutenberg_styles', 'registered')) {
            wp_add_inline_style(Theme::TEXT_DOMAIN . '_gutenberg_styles', $cssVariablesContent);
        } else {
            wp_add_inline_style('wp-block-library', $cssVariablesContent);
        }
    }

    /**
     * Get CSS variable by key (includes derived hover / on-colour values).
     * @param string $variableKey
     * @param string $defaultValue
     * @return string
     */
    public function getCssVariable(string $variableKey, string $defaultValue): string {
        $variables = $this->getCssVariables();
        if ($variableKey === 'color-action-hover') {
            return (string) ($variables['color-primary-hover'] ?? $defaultValue);
        }
        $value = $variables[$variableKey] ?? $defaultValue;
        return $value === '' ? $defaultValue : (string) $value;
    }
}
