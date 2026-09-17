<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Formatting;
use TechnoSapiens\Core\Partial;
use TechnoSapiens\Core\Singleton;

/**
 * Class GravityFormMailComponent
 * @package TechnoSapiens
 */
class GravityFormMailComponent extends Singleton {
    /**
     * GravityFormMailComponent constructor.
     */
    protected function __construct() {

        //add custom notification fields
        add_filter('gform_notification_settings_fields', [$this, 'filterGravityFormNotification'], 10, 3);

        //filter the HTML template of the e-mail notification
        add_filter('gform_pre_send_email', [$this, 'filterGravityFormMail'], 10, 4);
    }

    /**
     * Add a field in notifications
     * @param array $fields
     * @param array $notification
     * @param array $form
     * @return array
     */
    public static function filterGravityFormNotification(array $fields, array $notification, array $form): array {
        $templateField = [
            [
                'name' => 'email_template',
                'label' => esc_html__('Template', Theme::TEXT_DOMAIN),
                'tooltip' => sprintf(
                    '<button onclick="return false;" onkeypress="return false;" class="gf_tooltip %s %s" aria-label="%s"><i class="gform-icon gform-icon--question-mark" aria-hidden="true"></i></button>',
                    esc_attr('tooltip'),
                    esc_attr('tooltip_email_template'),
                    esc_attr(__("Choose one of the available email templates for this notification.", Theme::TEXT_DOMAIN))
                ),
                'type' => 'select',
                'choices' => [
                    [
                        'value' => 'default',
                        'label' => __("Default template", Theme::TEXT_DOMAIN)
                    ],
                    [
                        'value' => 'admin',
                        'label' => __("Admin template", Theme::TEXT_DOMAIN)
                    ],
                    [
                        'value' => 'customer',
                        'label' => __("Customer template", Theme::TEXT_DOMAIN)
                    ]
                ]
            ],
            [
                'name' => 'email_template_disable_fields',
                'label' => esc_html__('Disable form values', Theme::TEXT_DOMAIN),
                'tooltip' => sprintf(
                    '<button onclick="return false;" onkeypress="return false;" class="gf_tooltip %s %s" aria-label="%s"><i class="gform-icon gform-icon--question-mark" aria-hidden="true"></i></button>',
                    esc_attr('tooltip'),
                    esc_attr('tooltip_email_template_disable_fields'),
                    esc_attr(__("Enable this option to hide the form values in the e-mail template.", Theme::TEXT_DOMAIN))
                ),
                'type' => 'checkbox',
                'choices' => [
                    [
                        'name' => 'disableTemplateAllFields',
                        'label' => esc_html__('Disable fields in template.', Theme::TEXT_DOMAIN)
                    ]
                ],
                'dependency' => [
                    'live' => true,
                    'fields' => [
                        [
                            'field' => 'email_template',
                            'values' => ['admin', 'customer']
                        ]
                    ]
                ]
            ]
        ];

        foreach ($fields[0]['fields'] as $index => $field) {
            if (isset($field['name']) && $field['name'] === 'routing') {
                $fromArray = array_slice($fields[0]['fields'], 0, ($index + 1));
                $toArray = array_slice($fields[0]['fields'], ($index + 1), count($fields[0]['fields']) - 1);
                $fields[0]['fields'] = array_merge($fromArray, $templateField, $toArray);
            }
        }

        return $fields;
    }

    /**
     * Filter gravity form e-mail template
     * @param array $email
     * @param string $messageFormat
     * @param array $notification
     * @param array $lead
     * @return array
     */
    public static function filterGravityFormMail(array $email, string $messageFormat, array $notification, array $lead): array {
        $template = $notification['email_template'] ?? '';
        $disableAllFields = $notification['disableTemplateAllFields'] ?? '0' === '1';

        if ($template && $template !== 'default') {

            //get form by id
            $form = \GFAPI::get_form($lead['form_id']);

            if ($form) {
                $formValues = [];
                foreach ($form['fields'] as $field) {
                    switch ($field->get_input_type()) {
                        case 'section' :
                        case 'captcha':
                        case 'html':
                        case 'password':
                        case 'page':
                            //ignore captcha, html, password, page field.
                            break;

                        default :
                            //ignore product fields as they will be grouped together at the end of the grid.
                            if (\GFCommon::is_product_field($field->type)) {
                                $has_product_fields = true;
                                break;
                            }

                            $value = \RGFormsModel::get_lead_field_value($lead, $field);

                            if (is_array($field->fields)) {
                                //ensure the top level repeater has the right nesting level so the label is not duplicated.
                                $field->nestingLevel = 0;
                            }

                            $display_value = \GFCommon::get_lead_field_display($field, $value, $lead['currency']);

                            /**
                             * Filters a field value displayed within an entry.
                             *
                             * @param string $display_value The value to be displayed.
                             * @param \GF_Field $field The Field Object.
                             * @param array $lead The Entry Object.
                             * @param array $form The Form Object.
                             * @since 1.5
                             *
                             */
                            $display_value = apply_filters('gform_entry_field_value', $display_value, $field, $lead, $form);

                            if ($display_value) $formValues[$field['label']] = $display_value;
                    }
                }

                if (count($formValues) > 0) {

                    //generate e-mail HTML
                    $emailHtml = Partial::render('email/php/gf-' . $template, [
                        'formTitle' => $form['title'],
                        'formSubject' => $email['subject'] ?: '',
                        'formMessage' => Formatting::toHtml(\GFCommon::replace_variables(str_replace('{all_fields}', '', $notification['message']), $form, $lead, false, true, false)),
                        'formValues' => $formValues,
                        'showAllFields' => !$disableAllFields
                    ], false);

                    //generate message html file for development purposes
                    if (getenv('DEV') === 'true') {
                        if (!file_exists(get_stylesheet_directory() . '/partials/email/html')) mkdir(get_stylesheet_directory() . '/partials/email/html', 0755, true);
                        file_put_contents(get_stylesheet_directory() . '/partials/email/html/gf-' . $template . '.html', $emailHtml);
                    }

                    //update the message HTML
                    $email['message'] = $emailHtml;
                }
            }
        }

        return $email;
    }

    /**
     * Mail template colour/border tokens. Brand hex comes from StylingComponent.
     * @return array<string, string>
     */
    public function getMailVariables(): array {
        $styling = StylingComponent::getInstance();
        $colorPrimary = $styling->getCssVariable('color-primary', '#9A3334');
        $colorSecondary = $styling->getCssVariable('color-secondary', '#6699CD');
        $colorWhite = $styling->getCssVariable('color-white', '#FFFFFF');
        $colorBorder = $styling->getCssVariable('color-border', '#EEEEEE');

        return [
            'gf-mail-body-bg'         => '#f6f5f5',
            'gf-mail-header-bg'       => $colorWhite,
            'gf-mail-main-bg'         => $colorWhite,
            'gf-mail-footer-bg'       => $colorSecondary,
            'gf-mail-footer-bar-bg'   => $colorWhite,
            'gf-mail-label'           => $colorBorder,
            'gf-mail-value'           => $colorWhite,
            'gf-mail-main-text'       => $colorPrimary,
            'gf-mail-label-text'      => $colorPrimary,
            'gf-mail-value-text'      => $colorPrimary,
            'gf-mail-footer-bar-text' => $colorPrimary,
            'gf-mail-border'          => "1px solid {$colorBorder}",
        ];
    }

    /**
     * Get a single mail template CSS variable.
     * @param string $variableKey
     * @param string $defaultValue
     * @return string
     */
    public function getMailVariable(string $variableKey, string $defaultValue): string {
        $variables = $this->getMailVariables();
        return $variables[$variableKey] ?? $defaultValue;
    }
}