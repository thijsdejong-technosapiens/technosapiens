<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Singleton;

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
     * CSS variables, including Figma semantic aliases set in initCssVariables.
     * @return array|string[]
     */
    public function getCssVariables(): array {
        return apply_filters('ts_css_variables', $this->cssVariables);
    }

    /**
     * Populate $cssVariables with hardcoded defaults.
     * Brand hex is defined once as locals, then referenced by buttons / nav / mail.
     * @return void
     */
    private function initCssVariables(): void {
        // Figma Primitives collection (❖ Foundations). Hex matches the variable values.
        $colorPlum950 = '#250525';
        $colorPlum800 = '#4E134E';
        $colorPlum750 = '#580F58';
        $colorPlum700 = '#5E185E';
        $colorPlum650 = '#631F63';
        $colorPlum600 = '#662264';
        $colorPlum500 = '#7F1E69';
        $colorPlum400 = '#971A6E';
        $colorPlum300 = '#B01774';
        $colorPink600 = '#C81379';
        $colorPink500 = '#E10F7E';
        $colorYellow500 = '#FDB500';
        $colorYellow600 = '#E0A200';
        $colorYellow700 = '#C48D00';
        $colorCream50 = '#FBFBF3';
        $colorGreen600 = '#1E7A46';
        $colorRed700 = '#B3261E';
        $colorRed800 = '#8F1D1D';
        $colorBlue600 = '#1F5FAD';

        // Legacy locals that are not in the Figma primitive ramp (still referenced by used aliases)
        $colorPrimary = $colorPlum600;
        $colorPrimaryDark = $colorPlum800;
        $colorWhite = $colorCream50;
        $colorBlack = '#000000';
        $colorLight = '#F3F2EB';
        $colorBorder = '#EEEEEE';
        $colorBody = '#FFFFFF';
        $colorAccent = '#00132F';
        $colorNav = '#FFFFFF';
        $colorPasswordStrong = '#4db54f';

        $fontSans = "'Plus Jakarta Sans', sans-serif";

        $elevation1 = '0 4px 4px rgba(0,0,0,.1)';
        $elevation2 = '0 8px 8px rgba(0,0,0,.15)';
        $elevation3 = '0 25px 100px 0px rgba(0,0,0,0.15)';

        $this->cssVariables = [
            // --- Type: one family; apply weight at the call site ---
            'font-family-sans' => $fontSans,

            // Figma Responsive collection. Mobile is the default; *-md is Desktop (768px).
            'font-size-display'      => '3rem',
            'font-size-display-md'   => '5rem',
            'line-height-display'    => '3.5rem',
            'line-height-display-md' => '6rem',
            'font-size-h1'           => '2.25rem',
            'font-size-h1-md'        => '3.25rem',
            'line-height-h1'         => '2.75rem',
            'line-height-h1-md'      => '4rem',
            'font-size-h2'           => '1.75rem',
            'font-size-h2-md'        => '2.25rem',
            'line-height-h2'         => '2rem',
            'line-height-h2-md'      => '2.75rem',
            'font-size-h3'           => '1.5rem',
            'font-size-h3-md'        => '1.75rem',
            'line-height-h3'         => '1.75rem',
            'line-height-h3-md'      => '2rem',
            'font-size-h4'           => '1.25rem',
            'font-size-h4-md'        => '1.5rem',
            'line-height-h4'         => '1.5rem',
            'line-height-h4-md'      => '1.75rem',
            'font-size-body-base'      => '1rem',
            'font-size-body-base-md'   => '1.125rem',
            'line-height-body-base'    => '1.5rem',
            'line-height-body-base-md' => '1.75rem',
            'font-size-body-small'      => '1rem',
            'line-height-body-small'    => '1.5rem',

            // --- Figma color primitives ---
            'color-plum-950'  => $colorPlum950,
            'color-plum-800'  => $colorPlum800,
            'color-plum-750'  => $colorPlum750,
            'color-plum-700'  => $colorPlum700,
            'color-plum-650'  => $colorPlum650,
            'color-plum-600'  => $colorPlum600,
            'color-plum-500'  => $colorPlum500,
            'color-plum-400'  => $colorPlum400,
            'color-plum-300'  => $colorPlum300,
            'color-pink-600'  => $colorPink600,
            'color-pink-500'  => $colorPink500,
            'color-yellow-500' => $colorYellow500,
            'color-yellow-600' => $colorYellow600,
            'color-yellow-700' => $colorYellow700,
            'color-cream-50'  => $colorCream50,
            'color-green-600' => $colorGreen600,
            'color-red-700'   => $colorRed700,
            'color-red-800'   => $colorRed800,
            'color-blue-600'  => $colorBlue600,

            // Brand aliases still consumed by SCSS/PHP (same hex as the primitives above)
            'color-primary'        => $colorPlum600,
            'color-primary-dark'   => $colorPlum800,
            'color-primary-darker' => $colorPlum950,
            'color-secondary'      => $colorPink500,
            'color-tertiary'       => $colorYellow500,
            'color-white'          => $colorCream50,
            'color-black'          => $colorBlack,
            'color-light'          => $colorLight,
            'color-body'           => $colorBody,
            'color-accent'         => $colorAccent,

            // Gradient stops (same ramp as plum/pink primitives)
            'color-gradient-stop-1' => $colorPlum700,
            'color-gradient-stop-2' => $colorPlum600,
            'color-gradient-stop-3' => $colorPlum500,
            'color-gradient-stop-4' => $colorPlum400,
            'color-gradient-stop-5' => $colorPlum300,
            'color-gradient-stop-6' => $colorPink600,
            'color-gradient-stop-7' => $colorPink500,

            // Brand hover / on-colour still consumed outside button recipes
            'color-primary-hover'   => $colorPlum750,
            'color-on-primary'      => $colorCream50,

            // Figma Semantic collection. text/default and surface/page are the dark-page roles.
            // color-text and color-surface stay the light-page roles sections already use.
            'color-brand-primary'    => $colorPlum600,
            'color-brand-secondary'  => $colorPink500,
            'color-brand-tertiary'   => $colorYellow500,
            'color-text-default'     => $colorCream50,
            'color-text-accent'      => $colorPink500,
            'color-text-on-primary'  => $colorCream50,
            'color-text-on-tertiary' => $colorPlum950,
            'color-text-on-feedback' => $colorCream50,
            'color-text-placeholder' => $colorPlum500,
            'color-surface-page'     => $colorPlum950,
            'color-surface-panel'    => $colorPlum800,
            'color-surface-panel-alt' => $colorPlum700,
            'color-surface-card'     => $colorCream50,
            'color-border-accent'    => $colorPink600,
            'color-border-strong'    => $colorPink500,
            'color-border-field'     => $colorPlum500,
            'color-border-field-hover' => $colorPlum400,
            'color-action-primary'         => $colorPlum600,
            'color-action-primary-hover'   => $colorPlum750,
            'color-action-primary-pressed' => $colorPlum800,
            'color-action-secondary'         => $colorPink500,
            'color-action-secondary-hover'   => $colorPink600,
            'color-action-secondary-pressed' => $colorPlum400,
            'color-action-tertiary'         => $colorYellow500,
            'color-action-tertiary-hover'   => $colorYellow600,
            'color-action-tertiary-pressed' => $colorYellow700,
            'color-action-danger'         => $colorRed700,
            'color-action-danger-hover'   => $colorRed800,
            'color-focus-ring' => $colorCream50,

            // Light-page roles already consumed by sections
            'color-text'          => $colorPrimaryDark,
            'color-text-inverse'  => $colorCream50,
            'color-surface'       => $colorBody,
            'color-border'        => $colorBorder,

            // Legacy font-colour aliases (same hex as semantic text roles)
            'font-color-primary'   => $colorPrimaryDark,
            'font-color-white'     => $colorWhite,
            'font-color-nav'       => $colorNav,
            'font-color-nav-hover' => $colorPrimary,

            // Password strength
            'color-password-weak'   => $colorRed700,
            'color-password-strong' => $colorPasswordStrong,

            // Font weights (match @font-face 200/400/500/600; bold maps to nearest loaded face)
            'font-weight-light'     => 400,
            'font-weight-regular'   => 400,
            'font-weight-medium'    => 500,
            'font-weight-semibold'  => 700,
            'font-weight-bold'      => 700,

            // --- Elevation ---
            'elevation-1' => $elevation1,
            'elevation-2' => $elevation2,
            'elevation-3' => $elevation3,

            // --- Motion ---
            'duration-fast'   => '0.15s',
            'duration-base'   => '0.25s',
            'duration-slow'   => '0.4s',
            'easing-standard' => 'cubic-bezier(0.2, 0, 0, 1)',

            // --- Disabled ---
            'opacity-disabled' => '0.4',

            // --- Z-index ---
            'z-index-base'     => '0',
            'z-index-dropdown' => '100',
            'z-index-sticky'   => '200',
            'z-index-overlay'  => '300',
            // modal/toast stay above the Figma scale so WP admin chrome and dialogs keep stacking
            'z-index-modal'    => '1000',
            'z-index-toast'    => '1100',
            'z-index-admin'    => '99999',

            // Figma radius/* in rem (8 / 16 / 24 / 30 / 46.4 / 50).
            'radius-foundation-xs'   => '0.5rem',
            'radius-foundation-sm'   => '1rem',
            'radius-foundation-md'   => '1.5rem',
            'radius-foundation-lg'   => '1.875rem',
            'radius-foundation-xl'   => '2.9rem',
            'radius-foundation-pill' => '3.125rem',

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
            'space-20' => '5rem',
            // Figma space/* is named by pixels. The step scale above already covers 4–64px under space-1…space-16.
            'space-96' => '6rem',

            // Grid layout widths (no Figma equivalent)
            'base-width'   => '1410px',
            'narrow-width' => '1188px',

            // Navigation chrome (feature layout; no Figma equivalent)
            'nav-height-top'        => '2.5rem',
            'nav-height-top-mobile' => '0rem',
            'nav-height'            => '3.75rem',
            'nav-height-mobile'     => '3.75rem',

            // Feedback
            'color-feedback-info'    => $colorBlue600,
            'color-feedback-success' => $colorGreen600,
            'color-feedback-warning' => $colorYellow500,
            'color-feedback-error'   => $colorRed700,

            // Figma size/* and stroke/*
            'size-button-sm'    => '2.5rem',
            'size-button-md'    => '3rem',
            'size-button-lg'    => '3.5rem',
            'size-hit-area'     => '2.75rem',
            'size-field-height' => '3rem',
            'stroke-thin'       => '2px',
            'stroke-medium'     => '4px',
            'stroke-thick'      => '8px',
            'container-padding'    => '1rem',
            'container-padding-md' => '2rem',
            'section-spacing'      => '3rem',
            'section-spacing-md'   => '6rem',
            'grid-gutter'          => '1rem',
            'grid-gutter-md'       => '1.5rem',
            'grid-columns'         => '4',
            'grid-columns-md'      => '12',
        ];
    }

    /**
     * Generate CSS variables string
     * @return string
     */
    private function generateCssVariablesString(): string {
        $variables = $this->getCssVariables();

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
        $value = $variables[$variableKey] ?? $defaultValue;
        return $value === '' ? $defaultValue : (string) $value;
    }
}
