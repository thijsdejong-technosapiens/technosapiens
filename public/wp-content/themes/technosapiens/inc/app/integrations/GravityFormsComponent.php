<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\ButtonComponent;
use TechnoSapiens\Core\Partial;
use TechnoSapiens\Core\Singleton;

/**
 * Class GravityFormsComponent
 * @package TechnoSapiens
 */
class GravityFormsComponent extends Singleton {
    /**
     * GravityFormsComponent constructor.
     */
    protected function __construct() {
        global $pagenow;

        if (!is_admin() || (is_admin() && $pagenow === 'post.php') || (is_admin() && function_exists('acf_is_ajax') && acf_is_ajax())) {


            //filter gravity form field CSS classes
            add_filter('gform_field_css_class', [$this, 'filterFieldClasses'], 10, 3);

            //remove placeholder attribute from all fields
            add_filter('gform_field_content', function (string $fieldContent) {
                $regex = '/placeholder=\'(.*?)\'/m';
                preg_match_all($regex, $fieldContent, $matches, PREG_SET_ORDER);
                if ($matches && count($matches) > 0) foreach ($matches as $match) if (count($match) > 1) $fieldContent = str_replace($match[0], '', $fieldContent);
                return $fieldContent;
            }, 10, 1);

            //change submit button from input to button
            add_filter('gform_submit_button', [$this, 'filterSubmitButton'], 10, 2);
            add_filter('gform_previous_button', [$this, 'filterPreviousButton'], 10, 2);
            add_filter('gform_next_button', [$this, 'filterNextButton'], 10, 2);

            //disable legacy css
            add_filter('gform_enable_legacy_markup', '__return_false', 10, 2);

            //filter validation message
            add_filter('gform_validation_message', [$this, 'filterValidationMessage'], 10, 2);

            //filter confirmation message
            add_filter('gform_confirmation', [$this, 'filterConfirmationMessage'], 10, 2);
        }

        //customize phone field formats
        add_filter('gform_phone_formats', [$this, 'customizePhoneFormats'], 10, 1);
    }

    /**
     * Filter field classes for material inputs
     * @param string $classes
     * @param \GF_Field $field
     * @param array $form
     * @return string
     */
    public static function filterFieldClasses(string $classes, \GF_Field $field, array $form): string {
        //add field type to css classes
        $classes .= ' type-' . $field->type;

        //add material input CSS classes based on type and default value
        $materialTypes = [
            'text',
            'textarea',
            'phone',
            'email',
            'post_title',
            'post_content',
            'post_tags',
            'post_custom_field',
            'quantity',
            'website'
        ];
        if (in_array($field->type, $materialTypes)) {
            $classes .= ' material-input';
            if (!empty($field->defaultValue)) $classes .= ' material-input__active';
        }

        return $classes;
    }

    /**
     * Filter GF input[type=submit] to button element
     * @param string $buttonInput
     * @param array $form
     * @return string
     */
    public static function filterSubmitButton(string $buttonInput, array $form): string {
        $buttonText = !empty($form['button']['text']) ? $form['button']['text'] : __("Submit", Theme::TEXT_DOMAIN);

        //save attribute string to $button_match[1]
        preg_match("/<input([^\/>]*)(\s\/)*>/", $buttonInput, $buttonMatches);

        if (count($buttonMatches) > 0) {

            //remove value attribute
            $buttonAttributeString = str_replace("value='" . $buttonText . "' ", "", $buttonMatches[1]);

            //add primary class to button
            $buttonAttributeString = str_replace("class='gform_button", "class='gform_button " . ButtonComponent::TS_BUTTON_CLASS . " " . ButtonComponent::TS_BUTTON_CLASS . "--style-filled " . ButtonComponent::TS_BUTTON_CLASS . "--size-large " . ButtonComponent::TS_BUTTON_CLASS . "--color-primary", $buttonAttributeString);
            $buttonAttributeString = str_replace("class='gform-button", "class='gform-button " . ButtonComponent::TS_BUTTON_CLASS . " " . ButtonComponent::TS_BUTTON_CLASS . "--style-filled " . ButtonComponent::TS_BUTTON_CLASS . "--size-large " . ButtonComponent::TS_BUTTON_CLASS . "--color-primary", $buttonAttributeString);

            //create new button HTML
            ob_start(); ?>

            <button <?php echo $buttonAttributeString; ?>>
                <span class="<?php echo ButtonComponent::TS_BUTTON_CLASS; ?>__text">
                    <?php echo $buttonText; ?>
                </span>
            </button>

            <?php return ob_get_clean();
        }

        return $buttonInput;
    }

    /**
     * Filter previous page GF input[type=submit] button element
     * @param string $buttonInput
     * @param array $form
     * @return string
     */
    public static function filterPreviousButton(string $buttonInput, array $form): string {
        //save attribute string to $button_match[1]
        preg_match("/<input([^\/>]*)(\s\/)*>/", $buttonInput, $buttonMatches);

        if (count($buttonMatches) > 0) {

            //get value attribute from $buttonMatches[1] and use it as button text
            preg_match("/value='(.*?)'/", $buttonMatches[1], $buttonValueMatches);
            $buttonText = ($buttonValueMatches && count($buttonValueMatches) > 1) ? $buttonValueMatches[1] : __("Previous", Theme::TEXT_DOMAIN);

            //add primary class to button
            $buttonAttributeString = $buttonMatches[1];
            $buttonAttributeString = str_replace("class='gform_previous_button", "class='gform_previous_button gform_button " . ButtonComponent::TS_BUTTON_CLASS . " " . ButtonComponent::TS_BUTTON_CLASS . "--style-outlined " . ButtonComponent::TS_BUTTON_CLASS . "--size-large " . ButtonComponent::TS_BUTTON_CLASS . "--color-primary", $buttonAttributeString);
            $buttonAttributeString = str_replace("class='gform-previous-button", "class='gform-previous-button gform-button " . ButtonComponent::TS_BUTTON_CLASS . " " . ButtonComponent::TS_BUTTON_CLASS . "--style-outlined " . ButtonComponent::TS_BUTTON_CLASS . "--size-large " . ButtonComponent::TS_BUTTON_CLASS . "--color-primary", $buttonAttributeString);

            //create new button HTML
            ob_start(); ?>

            <button <?php echo $buttonAttributeString; ?>>
                <span class="<?php echo ButtonComponent::TS_BUTTON_CLASS; ?>__text">
                    <?php echo $buttonText; ?>
                </span>
            </button>

            <?php return ob_get_clean();
        }

        return $buttonInput;
    }

    /**
     * Filter next page GF input[type=submit] button element
     * @param string $buttonInput
     * @param array $form
     * @return string
     */
    public static function filterNextButton(string $buttonInput, array $form): string {
        //save attribute string to $button_match[1]
        preg_match("/<input([^\/>]*)(\s\/)*>/", $buttonInput, $buttonMatches);

        if (count($buttonMatches) > 0) {

            //get value attribute from $buttonMatches[1] and use it as button text
            preg_match("/value='(.*?)'/", $buttonMatches[1], $buttonValueMatches);
            $buttonText = ($buttonValueMatches && count($buttonValueMatches) > 1) ? $buttonValueMatches[1] : __("Next", Theme::TEXT_DOMAIN);

            //add primary class to button
            $buttonAttributeString = $buttonMatches[1];
            $buttonAttributeString = str_replace("class='gform_next_button", "class='gform_next_button gform_button " . ButtonComponent::TS_BUTTON_CLASS . " " . ButtonComponent::TS_BUTTON_CLASS . "--style-filled " . ButtonComponent::TS_BUTTON_CLASS . "--size-large " . ButtonComponent::TS_BUTTON_CLASS . "--color-primary", $buttonAttributeString);
            $buttonAttributeString = str_replace("class='gform-next-button", "class='gform-next-button gform-button " . ButtonComponent::TS_BUTTON_CLASS . " " . ButtonComponent::TS_BUTTON_CLASS . "--style-filled " . ButtonComponent::TS_BUTTON_CLASS . "--size-large " . ButtonComponent::TS_BUTTON_CLASS . "--color-primary", $buttonAttributeString);

            //create new button HTML
            ob_start(); ?>

            <button <?php echo $buttonAttributeString; ?>>
                <span class="<?php echo ButtonComponent::TS_BUTTON_CLASS; ?>__text">
                    <?php echo $buttonText; ?>
                </span>
            </button>

            <?php return ob_get_clean();
        }

        return $buttonInput;
    }

    /**
     * Filter validation message
     * @param string $message
     * @param array $form
     * @return string
     */
    public static function filterValidationMessage(string $message, array $form): string {

        //strip tags from message or fallback to default message
        $messageText = strip_tags($message) ?: __('There was a problem with your submission. Please check the fields below.', Theme::TEXT_DOMAIN);

        //create new message HTML
        return Partial::render('components/component-alert', [
            'type' => 'error',
            'ariaLive' => 'assertive',
            'text' => $messageText
        ], false);
    }

    /**
     * Filter confirmation message
     * @param array|string $confirmation
     * @param array $form
     * @return array|string
     */
    public static function filterConfirmationMessage(array|string $confirmation, array $form): array|string {

        //bail if confirmation type is redirect
        if (is_array($confirmation)) return $confirmation;

        //strip tags from message or fallback to default message
        $messageText = strip_tags($confirmation) ?: __('Your message has been sent. Thank you for contacting us.', Theme::TEXT_DOMAIN);

        return Partial::render('components/component-alert', [
            'type' => 'success',
            'ariaLive' => 'polite',
            'text' => $messageText
        ], false);
    }

    /**
     * Add phone mask for 10 numbers
     * @param array $phoneFormats
     * @return array
     */
    public static function customizePhoneFormats(array $phoneFormats = []): array {
        return array_merge([
            'ten-numbers' => [
                'label' => '0612345678',
                'mask' => '9999999999',
                'regex' => false,
                'instruction' => false
            ],
            'ten-numbers-formatted' => [
                'label' => '06 12 345 678',
                'mask' => '99 99 999 999',
                'regex' => false,
                'instruction' => false
            ]
        ], $phoneFormats);
    }
}