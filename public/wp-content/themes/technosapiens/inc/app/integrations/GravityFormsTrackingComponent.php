<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Formatting;
use TechnoSapiens\Core\Singleton;

/**
 * Class GravityFormsTrackingComponent
 * @package TechnoSapiens
 */
class GravityFormsTrackingComponent extends Singleton {

    /**
     * Name of the tracking event that is pushed to the dataLayer.
     * @var string
     */
    const TRACKING_EVENT_NAME = 'ts_form_submit_enhanced';

    /**
     * Query parameter name that is used to track the form entry ID on redirect.
     * @var string
     */
    const TRACKING_QUERY_PARAM = 'ts-form-entry-id';

    /**
     * Query parameter name for the HMAC token that authorises entry field exposure.
     * @var string
     */
    const TRACKING_TOKEN_PARAM = 'ts-form-entry-token';

    /**
     * GravityFormsTrackingComponent constructor.
     */
    protected function __construct() {
        global $pagenow;
        if (!is_admin()) {

            //add advanced form submit event tracking using dataLayer for submission with confirmation
            add_filter('gform_confirmation', [$this, 'filterGravityFormConfirmation'], PHP_INT_MAX, 4);

            //add advanced form submit event tracking for submission with redirect
            if (
                isset($_GET[self::TRACKING_QUERY_PARAM], $_GET[self::TRACKING_TOKEN_PARAM])
                && is_numeric($_GET[self::TRACKING_QUERY_PARAM])
            ) {
                add_action('wp_footer', [$this, 'handleGravityFormRedirectTracking']);
            }
        }
    }

    /**
     * Create an HMAC token bound to an entry ID so redirect tracking cannot be guessed.
     * @param int $entryId
     * @return string
     */
    public static function createTrackingToken(int $entryId): string {
        return hash_hmac('sha256', (string)$entryId, wp_salt('auth'));
    }

    /**
     * Verify a tracking token for the given entry ID.
     * @param int $entryId
     * @param string $token
     * @return bool
     */
    public static function verifyTrackingToken(int $entryId, string $token): bool {
        if ($entryId < 1 || $token === '') {
            return false;
        }
        return hash_equals(self::createTrackingToken($entryId), $token);
    }

    /**
     * Add script tag which fires dataLayer event after successful form submission
     * @param string|array $confirmation
     * @param array $form
     * @param array $entry
     * @param bool $ajax
     * @return string|array
     */
    public static function filterGravityFormConfirmation(string|array $confirmation, array $form, array $entry, bool $ajax): string|array {
        //handle redirect confirmation - add the entry to the query parameter string
        if (isset($confirmation['redirect'])) {
            $entryId = (int)rgar($entry, 'id');
            $confirmation['redirect'] = add_query_arg([
                self::TRACKING_QUERY_PARAM => $entryId,
                self::TRACKING_TOKEN_PARAM => self::createTrackingToken($entryId),
            ], $confirmation['redirect']);

            return $confirmation;
        } elseif (is_string($confirmation)) {

            //check if entry is already tracked
            if (rgar($entry, (self::TRACKING_EVENT_NAME . '_tracked'))) return $confirmation;

            //set up basic data layer variables
            $dataLayerObject = new \stdClass();
            $dataLayerObject->event = self::TRACKING_EVENT_NAME;
            $dataLayerObject->timestamp = time();
            $dataLayerObject->formId = (int)rgar($form, 'id');
            $dataLayerObject->formTitle = (string)rgar($form, 'title');
            $dataLayerObject->entryId = (int)rgar($entry, 'id');
            $dataLayerObject->entryUrl = (string)rgar($entry, 'source_url');
            $dataLayerObject->entryIsSpam = rgar($entry, 'status') === 'spam';;

            //define array of field types which should not be tracked
            $doNotTrackTypes = ['section', 'honeypot', 'captcha', 'html', 'password', 'page', 'post_image', 'post_title', 'post_content', 'post_tags', 'post_custom_field', 'singleproduct', 'singleshipping', 'total', 'row_start', 'row_end'];

            //set up entry values
            $dataLayerObject->entryValuesFlat = [];
            $dataLayerObject->entryValues = [];
            foreach ($form['fields'] as $field) {

                //bail if product or post fields
                if (\GFCommon::is_product_field($field->type) || \GFCommon::is_post_field($field)) continue;

                //ensure the top level repeater has the right nesting level so the label is not duplicated.
                if (is_array($field->fields)) $field->nestingLevel = 0;

                if (!in_array($field->get_input_type(), $doNotTrackTypes)) {
                    $entryValueItem = new \stdClass();
                    $entryValueItem->id = (int)rgar($field, 'id') ?: '';
                    $entryValueItem->type = (string)rgar($field, 'type') ?: '';
                    $entryValueItem->label = (string)rgar($field, 'label') ?: $entryValueItem->id;
                    $entryValueItem->labelSlug = Formatting::slugify($entryValueItem->label);
                    $entryValueItem->value = '';

                    //handle value parsing
                    $parsedValue = \RGFormsModel::get_lead_field_value($entry, $field);
                    if (is_serialized($parsedValue)) $parsedValue = unserialize($parsedValue, ['allowed_classes' => false]);
                    if ($entryValueItem->type === 'consent' && is_array($parsedValue)) {
                        $entryValueItem->value = trim(strip_tags($parsedValue[array_key_first($parsedValue)]));
                    } elseif (is_array($parsedValue)) {
                        $values = [];
                        foreach ($parsedValue as $valueItem) {
                            if ($valueItem) $values[] = trim(strip_tags((string)($valueItem)));
                        }
                        $entryValueItem->value = implode(',', $values);
                    } elseif (is_string($parsedValue)) {
                        $entryValueItem->value = trim(strip_tags($parsedValue));
                    }

                    //add flat values as alternative solution for online marketeers
                    $dataLayerObject->entryValuesFlat[$entryValueItem->labelSlug] = $entryValueItem->value;

                    //add entry value as object
                    $dataLayerObject->entryValues[] = $entryValueItem;
                }
            }

            ob_start(); ?>

            <script type="text/javascript">
                (function () {
                    window.dataLayer = window.dataLayer || [];
                    var submitEvent = <?php echo json_encode($dataLayerObject); ?>;
                    var eventExists = window.dataLayer.find((event) => JSON.stringify(event) === JSON.stringify(submitEvent));
                    if (!eventExists) window.dataLayer.push(submitEvent);
                }());
            </script>

            <?php

            $confirmation .= ob_get_clean();

            //set event to tracked to prevent multiple executions
            gform_update_meta($entry['id'], (self::TRACKING_EVENT_NAME . '_tracked'), true);
        }

        return $confirmation;
    }

    /**
     * Add enhanced form tracking event on redirect page
     * @return void
     */
    public static function handleGravityFormRedirectTracking(): void {
        $entryId = (int)$_GET[self::TRACKING_QUERY_PARAM];
        $token = isset($_GET[self::TRACKING_TOKEN_PARAM])
            ? sanitize_text_field(wp_unslash($_GET[self::TRACKING_TOKEN_PARAM]))
            : '';

        if (!self::verifyTrackingToken($entryId, $token)) {
            return;
        }

        $entry = \GFAPI::get_entry($entryId);
        if (!is_wp_error($entry)) {
            $form = \GFAPI::get_form($entry['form_id']);
            if ($form) {
                $confirmation = self::filterGravityFormConfirmation('', $form, $entry, false);
                if ($confirmation) echo $confirmation;
            }
        }
    }
}
