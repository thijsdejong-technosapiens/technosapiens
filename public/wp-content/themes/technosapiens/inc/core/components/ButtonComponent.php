<?php

namespace TechnoSapiens\Core;

use TechnoSapiens\Core;

/**
 * class ButtonComponent
 * @package TechnoSapiens\Core
 */
class ButtonComponent extends Singleton {

    /**
     * Default button types
     * @var array|\stdClass[]
     */
    protected array $buttonTypes = [];

    /**
     * Default button type
     * @var string
     */
    protected string $defaultButtonType = 'primary';

    /**
     * Default button styles
     * @var array|\stdClass[]
     */
    protected array $buttonStyles = [];

    /**
     * Default button style
     * @var string
     */
    protected string $defaultButtonStyle = 'filled';

    /**
     * Default button sizes
     * @var array|\stdClass[]
     */
    protected array $buttonSizes = [];

    /**
     * Default button size
     * @var string
     */
    protected string $defaultButtonSize = 'large';

    /**
     * Default button icons
     * @var array|\stdClass[]
     */
    protected array $buttonIcons = [];

    /**
     * Default button icon
     * @var string
     */
    protected string $defaultButtonIcon = 'default';

    /**
     * In-request cache for theme sprite icons.
     * @var array|null
     */
    protected static ?array $themeSpriteIconsCache = null;

    /**
     * Button class
     * @var string
     */
    const TS_BUTTON_CLASS = 'ts-button';

    /**
     * ButtonComponent constructor
     */
    protected function __construct() {

        add_action('init', function () {
            //set default button types
            $this->setButtonTypes([
                (object)[
                    'value' => 'primary',
                    'label' => __('Primary', Core::TEXT_DOMAIN),
                ],
                (object)[
                    'value' => 'secondary',
                    'label' => __('Secondary', Core::TEXT_DOMAIN),
                ],
                (object)[
                    'value' => 'tertiary',
                    'label' => __('Tertiary', Core::TEXT_DOMAIN),
                ],
                (object)[
                    'value' => 'danger',
                    'label' => __('Danger', Core::TEXT_DOMAIN),
                ],
            ]);

            //set default button styles
            $this->setButtonStyles([
                (object)[
                    'value' => 'filled',
                    'label' => __('Filled', Core::TEXT_DOMAIN),
                ],
                (object)[
                    'value' => 'outlined',
                    'label' => __('Outlined', Core::TEXT_DOMAIN),
                ],
                (object)[
                    'value' => 'ghost',
                    'label' => __('Ghost', Core::TEXT_DOMAIN),
                ],
                (object)[
                    'value' => 'text',
                    'label' => __('Text', Core::TEXT_DOMAIN),
                ],
                (object)[
                    'value' => 'alternative',
                    'label' => __('Text alternative', Core::TEXT_DOMAIN),
                ],
            ]);

            //set default button sizes
            $this->setButtonSizes([
                (object)[
                    'value' => 'small',
                    'label' => __('Small', Core::TEXT_DOMAIN),
                ],
                (object)[
                    'value' => 'medium',
                    'label' => __('Medium', Core::TEXT_DOMAIN),
                ],
                (object)[
                    'value' => 'large',
                    'label' => __('Large', Core::TEXT_DOMAIN),
                ],
                (object)[
                    'value' => 'xlarge',
                    'label' => __('Extra large', Core::TEXT_DOMAIN),
                ]
            ]);

            //set default button icons
            $this->setButtonIcons([
                (object)[
                    'value' => 'default',
                    'label' => __('Default', Core::TEXT_DOMAIN),
                ]
            ]);
        });
    }

    /**
     * Set default button type
     * @param string $type
     * @return void
     */
    public function setDefaultButtonType(string $type): void {
        $this->defaultButtonType = $type;
    }

    /**
     * Get default button type
     * @return string
     */
    public function getDefaultButtonType(): string {
        return $this->defaultButtonType;
    }

    /**
     * Set button types
     * @param array $types
     * @return void
     */
    public function setButtonTypes(array $types): void {
        $this->buttonTypes = $types;
    }

    /**
     * Get button types
     * @param bool $flat
     * @return array|\stdClass[]
     */
    public function getButtonTypes(bool $flat = false): array {
        if ($flat) {
            return array_map(function ($style) {
                return $style->value;
            }, $this->buttonTypes);
        }

        return $this->buttonTypes;
    }

    /**
     * Get parsed button type
     * @param string $buttonType
     * @return string
     */
    public function getParsedButtonType(string $buttonType): string {
        $buttonTypes = self::getButtonTypes(true);
        if (!$buttonType || (count($buttonTypes) > 0 && !in_array($buttonType, $buttonTypes))) return $this->getDefaultButtonType();
        return $buttonType;
    }

    /**
     * @param string $style
     * @return void
     */
    public function setDefaultButtonStyle(string $style): void {
        $this->defaultButtonStyle = $style;
    }

    /**
     * Get default button style
     * @return string
     */
    public function getDefaultButtonStyle(): string {
        return $this->defaultButtonStyle;
    }

    /**
     * Set button styles
     * @param array $styles
     * @return void
     */
    public function setButtonStyles(array $styles): void {
        $this->buttonStyles = $styles;
    }

    /**
     * Get button styles
     * @param bool $flat
     * @return array|\stdClass[]
     */
    public function getButtonStyles(bool $flat = false): array {
        if ($flat) {
            return array_map(function ($style) {
                return $style->value;
            }, $this->buttonStyles);
        }

        return $this->buttonStyles;
    }

    /**
     * Get parsed button style
     * @param string $buttonStyle
     * @return string
     */
    public function getParsedButtonStyle(string $buttonStyle): string {
        $buttonStyles = self::getButtonStyles(true);
        if (!$buttonStyle || (count($buttonStyles) > 0 && !in_array($buttonStyle, $buttonStyles))) return $this->getDefaultButtonStyle();
        return $buttonStyle;
    }

    /**
     * Set default button size
     * @param string $size
     * @return void
     */
    public function setDefaultSize(string $size): void {
        $this->defaultButtonSize = $size;
    }

    /**
     * Get default button size
     * @return string
     */
    public function getDefaultButtonSize(): string {
        return $this->defaultButtonSize;
    }

    /**
     * Set button sizes
     * @param array $sizes
     * @return void
     */
    public function setButtonSizes(array $sizes): void {
        $this->buttonSizes = $sizes;
    }

    /**
     * Get button sizes
     * @return array|\stdClass[]
     */
    public function getButtonSizes(bool $flat = false): array {
        if ($flat) {
            return array_map(function ($style) {
                return $style->value;
            }, $this->buttonSizes);
        }

        return $this->buttonSizes;
    }

    /**
     * Get parsed button size
     * @param string $buttonSize
     * @return string
     */
    public function getParsedButtonSize(string $buttonSize): string {
        $buttonSizes = self::getButtonSizes(true);
        if (!$buttonSize || (count($buttonSizes) > 0 && !in_array($buttonSize, $buttonSizes))) return $this->getDefaultButtonSize();
        return $buttonSize;
    }

    /**
     * Set default button icon
     * @param string $icon
     * @return void
     */
    public function setDefaultIcon(string $icon): void {
        $this->defaultButtonIcon = $icon;
    }

    /**
     * Get default button icon
     * @return string
     */
    public function getDefaultButtonIcon(): string {
        return $this->defaultButtonIcon;
    }

    /**
     * Set button icons
     * @param array $icons
     * @return void
     */
    public function setButtonIcons(array $icons): void {
        //make sure that the "default" option is always there in the icons array
        //if there is at least one other icon given
        if (count($icons) > 0 && !in_array('default', array_map(function ($icon) {
                return $icon->value ?? $icon;
            }, $icons))) {
            array_unshift($icons, (object)[
                'value' => 'default',
                'label' => __('Default', Core::TEXT_DOMAIN),
            ]);
        }

        $this->buttonIcons = $icons;
    }

    /**
     * Get button icons
     * @return array|\stdClass[]
     */
    public function getButtonIcons(bool $flat = false): array {
        if ($flat) {
            return array_map(function ($style) {
                return $style->value;
            }, $this->buttonIcons);
        }

        return $this->buttonIcons;
    }

    /**
     * Get parsed button icon
     * @param string $buttonIcon
     * @return string
     */
    public function getParsedButtonIcon(string $buttonIcon): string {
        $buttonIcons = self::getButtonIcons(true);
        if (!$buttonIcon || (count($buttonIcons) > 0 && !in_array($buttonIcon, $buttonIcons))) return $this->getDefaultButtonIcon();
        return $buttonIcon;
    }

    /**
     * Populate button types
     * @param array $field
     * @return array
     */
    public function populateButtonTypeOptions(array $field): array {
        $buttonTypes = $this->getButtonTypes();
        $defaultButtonTypeKey = $this->getDefaultButtonType();
        if (count($buttonTypes) > 0) $field = $this->populateAcfFieldOptionsAndDefaultValue($field, $buttonTypes, $defaultButtonTypeKey);
        return $field;
    }

    /**
     * Populate button styles
     * @param array $field
     * @return array
     */
    public function populateButtonStyleOptions(array $field): array {
        $buttonStyles = $this->getButtonStyles();
        $defaultButtonStyleKey = $this->getDefaultButtonStyle();
        if (count($buttonStyles) > 0) $field = $this->populateAcfFieldOptionsAndDefaultValue($field, $buttonStyles, $defaultButtonStyleKey);
        return $field;
    }

    /**
     * Populate button sizes
     * @param array $field
     * @return array
     */
    public function populateButtonSizeOptions(array $field): array {
        $buttonSizes = $this->getButtonSizes();
        $defaultButtonSizeKey = $this->getDefaultButtonSize();
        if (count($buttonSizes) > 0) $field = $this->populateAcfFieldOptionsAndDefaultValue($field, $buttonSizes, $defaultButtonSizeKey);
        return $field;
    }

    /**
     * Populate button icons
     * @param array $field
     * @return array
     */
    public function populateButtonIconOptions(array $field): array {
        $buttonIcons = $this->getButtonIcons();
        $defaultButtonIconKey = $this->getDefaultButtonIcon();
        if (count($buttonIcons) > 0) $field = $this->populateAcfFieldOptionsAndDefaultValue($field, $buttonIcons, $defaultButtonIconKey);
        return $field;
    }

    /**
     * Populate ACF field options and default value
     * @param array $field
     * @param array $options
     * @param string $defaultValue
     * @return array
     */
    public function populateAcfFieldOptionsAndDefaultValue(array $field, array $options, string $defaultValue = ''): array {
        if (count($options) > 0) {
            $acfFieldOptions = [];
            $optionValues = [];

            foreach ($options as $option) {
                if (!$option->value || !$option->label) continue;
                $isDefault = !empty($defaultValue) && $defaultValue === $option->value;
                $acfFieldOptions[$option->value] = $option->label . ($isDefault ? ' (' . __("Default", Core::TEXT_DOMAIN) . ')' : '');
                $optionValues[] = $option->value;
            }

            if (count($acfFieldOptions) > 0) {
                $field['choices'] = $acfFieldOptions;
                if ($defaultValue && in_array($defaultValue, $optionValues, true)) {
                    $field['default_value'] = $defaultValue;
                }
            }
        }
        return $field;
    }

    /**
     * Validate if the icon exists in the icon folder.
     * @param string $icon
     * @return bool
     */
    public function isValidIcon(string $icon): bool {
        $themeIcons = array_merge(['default'], self::getAvailableThemeSpriteIcons());
        return in_array($icon, $themeIcons);
    }

    /**
     * Get available theme sprite icons.
     * Cached for the duration of the PHP request using self::$themeSpriteIconsCache.
     * @return array
     */
    public static function getAvailableThemeSpriteIcons(): array {

        //return a cached result if already computed in this request
        if (self::$themeSpriteIconsCache !== null) return self::$themeSpriteIconsCache;

        $icons = [];

        try {
            $themeIconsPath = get_stylesheet_directory() . '/icons/';
            if (is_dir($themeIconsPath)) {
                $files = glob($themeIconsPath . '*.svg') ?: [];
                foreach ($files as $file) {
                    $iconName = basename($file, '.svg');
                    if ($iconName !== '') $icons[] = $iconName;
                }
            }
        } catch (\Throwable $e) {
            Log::log(
                'Error occurred while getting available theme icons in ' .
                'ButtonComponent::getAvailableThemeSpriteIcons(): ' . $e->getMessage()
            );
        }

        //de-duplicate, reindex, and cache
        self::$themeSpriteIconsCache = array_values(array_unique($icons));
        return self::$themeSpriteIconsCache;
    }

    /**
     * Renders a button (<a class="ts-button">) element.
     * @param array $args
     *
     * An associative array of arguments used to customize the button.
     * @type string $tag The HTML tag to use for the button. Defaults to 'a'. Can also be 'button' or 'span'.
     * @type string $text The text to display inside the button. Defaults to an empty string.
     * @type string $href The URL the button should link to (for 'a' tag). Defaults to an empty string.
     * @type string $target The target attribute for the hyperlink. Defaults to '_self'. Used only for 'a' tag.
     * @type string $rel The 'rel' attribute for the hyperlink. Defaults to an empty string. Used only for 'a' tag.
     * @type string $class Additional CSS classes to add to the button. Defaults to an empty string.
     * @type string $type The button's color/type, controlling its style (e.g., 'primary', 'secondary'). Defaults to the default button type.
     * @type string $style The button's visual style (e.g., 'text', 'alternative'). Defaults to the default button style.
     * @type string $size The button's size (e.g., 'small', 'medium', 'large'). Defaults to the default button size.
     * @type string $icon The icon to display on the button. Defaults to the default button icon. If 'default', a dynamic icon will be set based on the style.
     * @type string $iconPosition The position of the icon on the button (e.g., 'left' or 'right'). Defaults to 'right'.
     * @type string $blockClass A custom block class for additional styling. Defaults to an empty string.
     * @type array $attributes Additional HTML attributes to add to the button element (e.g., data attributes). Defaults to an empty array.
     * @type string $srText Text to be read by screen readers. Useful for accessibility. Defaults to an empty string.
     * @type bool $isPreview Whether the button is being previewed (for example, in a preview mode). Defaults to false.
     *
     * @return string
     */
    public static function render(array $args = []): string {

        //get ButtonComponent instance
        $instance = self::getInstance();

        //set up default method arguments
        $defaults = [
            'tag' => 'a',
            'text' => '',
            'href' => '',
            'target' => '_self',
            'rel' => '',
            'class' => '',
            'type' => $instance->getDefaultButtonType(),
            'style' => $instance->getDefaultButtonStyle(),
            'size' => $instance->getDefaultButtonSize(),
            'icon' => $instance->getDefaultButtonIcon(),
            'iconPosition' => 'right',
            'blockClass' => '',
            'attributes' => [],
            'srText' => '',
            'isPreview' => false,
        ];

        //add PHP filter to change the default args
        $defaults = apply_filters('ts_button_default_args', $defaults);

        //merge defaults with user args
        $args = array_merge($defaults, $args);

        //add PHP filter to change all the args after merging
        $args = apply_filters('ts_button_args', $args);

        //type safety - coerce argument values to expected types
        $stringKeys = ['tag', 'text', 'href', 'target', 'rel', 'class', 'type', 'style', 'size', 'icon', 'iconPosition', 'blockClass', 'srText'];
        foreach ($stringKeys as $key) {
            if (!isset($args[$key]) || (!is_string($args[$key]) && !is_numeric($args[$key]))) $args[$key] = '';
            else $args[$key] = (string)$args[$key];
        }

        //handle attributes
        if (!isset($args['attributes']) || !is_array($args['attributes'])) $args['attributes'] = [];
        $args['isPreview'] = isset($args['isPreview']) && is_bool($args['isPreview']) ? $args['isPreview'] : (bool)$args['isPreview'];

        //validation 1 - check if icon is valid
        if (!$instance->isValidIcon($args['icon'])) {
            Log::log('Validation failed while running ButtonComponent::render() - given icon is not supported: ' . $args['icon']);
            $args['icon'] = 'default';
        }

        //validation 2 - check if given tag is supported
        $supportedTags = ['a', 'button', 'span'];
        if (!in_array($args['tag'], $supportedTags)) {
            Log::log('Validation failed while running ButtonComponent::render() - given tag is not supported: ' . $args['tag']);
            $args['tag'] = 'a';
        }

        //validation 3 - button is a hyperlink; make sure that it has text and href
        if ($args['tag'] === 'a' && (!$args['href'] || !$args['text'])) {
            Log::log('Validation failed while running ButtonComponent::render() - hyperlink has no valid url or text. URL: ' . $args['href'] . ' | Text: ' . $args['text']);
            return '';
        }

        //validation 4 - button is a span or button tag; make sure that it has text
        if (($args['tag'] === 'span' || $args['tag'] === 'button') && !$args['text']) {
            Log::log('Validation failed while running ButtonComponent::render() - button has no text. Text: ' . $args['text']);
            return '';
        }

        //validation 5 - validate icon position
        $allowedIconPositions = ['left', 'right'];
        if (!in_array($args['iconPosition'], $allowedIconPositions, true)) $args['iconPosition'] = 'right';

        //manipulation or args 1 - handle default icons
        if ($args['icon'] === 'default') {
            if ($args['style'] === 'text') {
                //scenario 1 - text style button
                $args['icon'] = apply_filters('ts_button_default_icon_text_style', 'icon-arrow-right', $args);
            } elseif ($args['style'] === 'alternative') {
                //scenario 2 - alternative style button
                $args['icon'] = apply_filters('ts_button_default_icon_alternative_style', 'next', $args);
            } else {
                //scenario 3 - no default icon needed
                $args['icon'] = '';
            }
        }

        //validation 6 - validate visual attributes using parsed values
        $args['type'] = $instance->getParsedButtonType($args['type']);
        $args['style'] = $instance->getParsedButtonStyle($args['style']);
        $args['size'] = $instance->getParsedButtonSize($args['size']);

        return self::buildButton($args);
    }

    /**
     * Builds a button element based on the given parameters.
     * @param array $args
     * @return string
     */
    protected static function buildButton(array $args): string {
        //set up default button CSS classes
        //"ts-button--bc" class stands for button component, so we can see which buttons are created by this component
        $buttonClasses = [self::TS_BUTTON_CLASS, self::TS_BUTTON_CLASS . '--bc'];

        //handle button style CSS modifier
        if ($args['style']) $buttonClasses[] = self::TS_BUTTON_CLASS . '--style-' . esc_attr($args['style']);

        //handle button size CSS modifier
        if ($args['size']) $buttonClasses[] = self::TS_BUTTON_CLASS . '--size-' . esc_attr($args['size']);

        //handle button color CSS modifier
        if ($args['type']) $buttonClasses[] = self::TS_BUTTON_CLASS . '--color-' . esc_attr($args['type']);

        //handle icon position CSS modifier
        $buttonIcon = $args['icon'] ?: '';
        if ($buttonIcon) {
            $buttonIconModifier = self::TS_BUTTON_CLASS . '--icon-' . $buttonIcon;
            $buttonIconModifier = str_replace('icon-icon', 'icon', $buttonIconModifier);
            $buttonClasses[] = esc_attr($buttonIconModifier);
            $buttonClasses[] = self::TS_BUTTON_CLASS . '--icon-position-' . esc_attr($args['iconPosition']);
        }

        //add class and block classes if they are given
        if ($args['class']) $buttonClasses[] = esc_attr($args['class']);
        if ($args['blockClass']) $buttonClasses[] = esc_attr($args['blockClass'] . '__button');

        //prepare button attributes
        $buttonAttributes = is_array($args['attributes']) ? $args['attributes'] : [];

        //remove reserved/duplicated attributes; we will set these ourselves
        foreach (['class', 'href', 'target', 'rel', 'onclick'] as $reservedAttr) {
            if (isset($buttonAttributes[$reservedAttr])) unset($buttonAttributes[$reservedAttr]);
        }

        //handle hyperlink attributes
        if ($args['tag'] === 'a') {

            //handle hyperlink href
            $buttonAttributes['href'] = esc_url(Link::parseLink($args['href']) ?? '');

            //handle hyperlink target
            if ($args['target']) $buttonAttributes['target'] = esc_attr($args['target']);

            //handle hyperlink rel (auto-append noopener/noreferrer for new tabs)
            $rel = (string)$args['rel'];
            $opensInNewTab = !empty($args['target']) && $args['target'] === '_blank';
            $relParts = preg_split('/\s+/', $rel, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            if ($opensInNewTab) $relParts = array_unique(array_merge($relParts, ['noopener', 'noreferrer']));
            if (count($relParts) > 0) $buttonAttributes['rel'] = implode(' ', $relParts);
        }

        //handle default type attribute for <button>
        if ($args['tag'] === 'button' && empty($buttonAttributes['type'])) {
            $buttonAttributes['type'] = 'button';
        }

        //handle Gutenberg preview + CMS - prevent click on buttons in preview mode or any admin context
        if ($args['isPreview'] === true || is_admin()) {
            $buttonClasses[] = 'ts-preview-link';
        }

        //handle screen reader text
        $srText = $args['srText'];
        $srTextIsDefault = false;
        $opensInNewTab = !empty($args['target']) && $args['target'] === '_blank';
        if (!$srText && $opensInNewTab) {
            $srText = WcagComponent::getSrLinkOpensInNewTabHtml();
            $srTextIsDefault = true;
        }

        //make the button attributes ready to implode
        $buttonAttributesFlat = [];
        $allowedAttrName = '/^[a-zA-Z_:][a-zA-Z0-9:._-]*$/';
        foreach ($buttonAttributes as $key => $value) {

            //handle boolean attributes and skip invalid names
            if (is_string($key) && preg_match($allowedAttrName, $key)) {
                if (is_bool($value)) {
                    if ($value) $buttonAttributesFlat[] = $key;
                    continue;
                }
                if ($value === null || $value === '') continue;
                $buttonAttributesFlat[] = $key . '="' . esc_attr((string)$value) . '"';
            } else {
                if (is_string($value) && $value !== '') $buttonAttributesFlat[] = esc_attr($value);
            }
        }

        //start building button HTML
        $buttonOpenHtml = '<' . esc_attr($args['tag']) . ' ' . implode(' ', $buttonAttributesFlat) . ' class="' . implode(' ', $buttonClasses) . '">';

        //start building button text HTML
        $buttonText = is_scalar($args['text']) ? (string)$args['text'] : '';
        $buttonText = wp_kses_post($buttonText);
        $buttonText .= ($srText && $opensInNewTab && $srTextIsDefault === true) ? $srText : ($srText ? '<span class="ts-sr-only">' . esc_html($srText) . '</span>' : '');
        $buttonTextClasses = [self::TS_BUTTON_CLASS . '__text'];
        if ($args['blockClass']) $buttonTextClasses[] = $args['blockClass'] . '__button-text';
        $buttonTextHtml = '<span class="' . implode(' ', $buttonTextClasses) . '">' . $buttonText . '</span>';

        //start building button icon HTML
        $buttonIconHtml = '';
        if ($args['icon'] && $args['icon'] !== 'default') {

            //handle figure element
            $buttonIconFigureClasses = [self::TS_BUTTON_CLASS . '__icon-container'];
            if ($args['blockClass']) $buttonIconFigureClasses[] = $args['blockClass'] . '__button-icon-container';
            $buttonIconFigureOpenHtml = '<figure class="' . implode(' ', $buttonIconFigureClasses) . '">';

            //handle svg element
            $buttonIconSvgClasses = [self::TS_BUTTON_CLASS . '__icon'];
            if ($args['blockClass']) $buttonIconSvgClasses[] = $args['blockClass'] . '__button-icon';
            $buttonIconSvgOpen = '<svg class="' . implode(' ', $buttonIconSvgClasses) . '" aria-hidden="true">';
            $buttonIconSvgUse = '<use xlink:href="' . ICON_PATH . $args['icon'] . '"/>';

            $buttonIconSvgClose = '</svg>';
            $buttonIconFigureCloseHtml = '</figure>';

            //combine all parts to create the final button icon HTML
            $buttonIconHtml = $buttonIconFigureOpenHtml . $buttonIconSvgOpen . $buttonIconSvgUse . $buttonIconSvgClose . $buttonIconFigureCloseHtml;
        }

        //build closing HTML tag
        $buttonCloseHtml = '</' . esc_attr($args['tag']) . '>';

        //combine all parts to create the final button HTML
        $html = $buttonOpenHtml . $buttonTextHtml . $buttonIconHtml . $buttonCloseHtml;

        //add PHP filter to change the HTML output
        $html = apply_filters('ts_button_html_output', $html, $args);

        return $html;
    }
}