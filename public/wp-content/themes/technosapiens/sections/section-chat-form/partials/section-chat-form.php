<?php

use TechnoSapiens\Core\GradientBandsComponent;
use TechnoSapiens\Core\Image;
use TechnoSapiens\Core\PreviewNotificationComponent;
use TechnoSapiens\SectionBackground;
use TechnoSapiens\SectionChatForm;
use TechnoSapiens\SectionMargins;
use TechnoSapiens\SectionReferencePartial;
use TechnoSapiens\SectionTitleHeading;
use TechnoSapiens\SectionVignet;
use TechnoSapiens\Theme;

$sectionChatForm = SectionChatForm::getInstance();
$sectionTitle = $sectionChatForm->getSectionTitle();
$titleHeadingTag = SectionTitleHeading::getTag();
$titleHeadingSize = SectionTitleHeading::getSize();
$formId = $sectionChatForm->getValidatedGravityFormId();
$topics = $sectionChatForm->getTopics();
$isEditor = is_admin();
$hasContent = $sectionTitle || $formId || ($isEditor && get_field('gravity_form_id'));

$containerClasses = SectionReferencePartial::getContainerClasses('section-chat-form');
$marginTop = SectionMargins::getMarginTop();
$marginBottom = SectionMargins::getMarginBottom();
$vignetTop = SectionVignet::getVignetTop();
$vignetBottom = SectionVignet::getVignetBottom();
$backgroundType = SectionBackground::getBackgroundType();
$backgroundColor = SectionBackground::getBackgroundColor();
$backgroundBandsAxis = SectionBackground::getBackgroundBandsAxis();
$backgroundBandsAnimDir = SectionBackground::getBackgroundBandsAnimDir();
$backgroundBandsSize = SectionBackground::getBackgroundBandsSize();

$hintId = 'section-chat-form-hint-' . ($formId ?: 'empty');
$micLiveId = 'section-chat-form-mic-' . ($formId ?: 'empty');
?>

<?php if ($hasContent) : ?>
    <div class="<?php echo esc_attr(implode(' ', $containerClasses)); ?>"<?php if ($marginTop) : ?> data-pt="<?php echo esc_attr($marginTop); ?>"<?php endif; ?><?php if ($marginBottom) : ?> data-pb="<?php echo esc_attr($marginBottom); ?>"<?php endif; ?><?php if ($vignetTop) : ?> data-vignet-top="<?php echo esc_attr($vignetTop); ?>"<?php endif; ?><?php if ($vignetBottom) : ?> data-vignet-bottom="<?php echo esc_attr($vignetBottom); ?>"<?php endif; ?><?php if ($backgroundType) : ?> data-bg="<?php echo esc_attr($backgroundType); ?>"<?php endif; ?><?php if ($backgroundColor) : ?> data-bg-color="<?php echo esc_attr($backgroundColor); ?>"<?php endif; ?>>

        <?php if ($backgroundType === 'bands') : ?>
            <div class="section-background">
                <?php echo GradientBandsComponent::render([
                    'bandSize' => $backgroundBandsSize ?: 'medium',
                    'axis' => $backgroundBandsAxis ?: 'vertical',
                    'staggerMode' => $backgroundBandsAnimDir ?: 'start',
                ]); ?>
            </div>
        <?php endif; ?>

        <div class="container container--narrow section-chat-form__container">
            <?php if ($sectionTitle) : ?>
                <<?php echo esc_attr($titleHeadingTag); ?> class="section-chat-form__title <?php echo esc_attr($titleHeadingSize); ?>">
                    <?php echo wp_kses_post($sectionTitle); ?>
                </<?php echo esc_attr($titleHeadingTag); ?>>
            <?php endif; ?>

            <?php if ($formId) : ?>
                <div class="section-chat-form__logo" aria-hidden="true">
                    <svg class="section-chat-form__logo-image" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" fill-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2" clip-rule="evenodd" viewBox="0 0 512 512">
                        <path class="section-chat-form__logo-rotating-shape" fill="#e10f7e" d="M128.29 477.449.365 256.45l17.88-31.335 60.51-104.872 48.95-85.34 36.177-.27 120.977.133 98.48-.314L511.537 255.92l-17.88 31.335-60.41 104.502-49.42 85.612-36.078-.102-120.706.337z"/>
                        <path fill="#662264" d="M189.679 502.883 9.117 322.32l9.2-34.886L49.753 170.51l25.302-95.073 34.885-9.584 116.925-31.052 95.073-25.685 180.945 180.945-9.2 34.886-31.436 116.54-25.685 95.457-34.886 9.2-116.54 31.436z"/>
                        <path fill="#e10f7e" d="m69.421 270.225 9.488-9.488 38.221 38.222-9.487 9.488zm19.439-18.786 9.488-9.488 38.221 38.222-9.487 9.488zm19.168-19.057 9.488-9.488 38.221 38.222-9.487 9.488zm18.785-19.44 9.487-9.488 38.222 38.222-9.488 9.487z"/>
                        <path fill="#e10f7e" d="m145.871 270.063 9.488-9.487 38.221 38.221-9.487 9.488zm19.439-18.786 9.488-9.487 38.221 38.221-9.487 9.488zm19.058 57.123 9.488-9.488 38.222 38.221-9.488 9.488z"/>
                        <path fill="#e10f7e" d="m203.265 289.613 9.488-9.487 38.222 38.221-9.488 9.488zm-57.285-95.728 9.488-9.488 38.222 38.222-9.488 9.487zm19.168-19.057 9.488-9.488 38.222 38.222-9.488 9.487zm47.92-47.676 9.488-9.487 38.222 38.221-9.488 9.488zm19.168-19.057 9.488-9.487 38.221 38.221-9.487 9.488zm18.784-19.057 9.488-9.487 38.222 38.221-9.488 9.488zm200.768 181.347-9.488 9.488-38.222-38.222 9.488-9.487zm-18.898 19.167-9.487 9.487-38.221-38.221 9.487-9.488zm-19.167 19.278-9.488 9.488-38.221-38.221 9.487-9.488zm-19.168 18.896-9.488 9.488-38.221-38.222 9.487-9.488zm-19.277-57.502-9.488 9.487-38.222-38.221 9.488-9.488zm-19.168 19.437-9.488 9.488-38.222-38.222 9.488-9.487z"/>
                        <path fill="#e10f7e" d="m337.216 232.159-9.488 9.487-38.222-38.221 9.488-9.488zm-19.168 18.895-9.488 9.488-38.222-38.222 9.488-9.487zm57.339 95.567-9.488 9.488-38.221-38.222 9.487-9.487zm-19.439 19.55-9.488 9.488-38.221-38.222 9.487-9.487zm-47.536 47.239-9.488 9.488-38.222-38.222 9.488-9.488zm-18.897 19.55-9.488 9.488-38.221-38.222 9.487-9.488zm-19.168 18.895-9.488 9.488-38.221-38.222 9.487-9.487z"/>
                        <path fill="#fbfbf3" fill-rule="nonzero" d="m103.04 275.168-9.584 9.584-38.336-38.336 9.584-9.584zm-19.168-57.504-9.584 9.584 38.336 38.336 9.584-9.584zm19.168-18.784-9.584 9.584 38.336 38.335 9.584-9.584zm19.168-19.168-9.584 9.584 38.336 38.335 9.584-9.583zm19.168 57.12-9.584 9.584 38.336 38.336 9.584-9.584zm19.168-19.168-9.584 9.584 38.336 38.336L198.88 256zm18.784 57.504-9.584 9.584 38.336 38.336 9.584-9.584zM198.496 256l-9.584 9.584 38.336 38.336 9.584-9.584zm-57.12-95.456-9.584 9.584 38.336 38.336 9.584-9.584zm19.168-19.168-9.584 9.584 38.336 38.336 9.584-9.584zm28.368-28.752-9.584 9.584 38.336 38.336 9.584-9.584zm19.168-19.168-9.584 9.584 38.336 38.336 9.584-9.584zm19.168-18.784-9.584 9.583L256 122.591l9.584-9.584zM408.96 275.168l9.584-9.584-38.336-38.336-9.584 9.584zm-19.168 19.168 9.584-9.584-38.336-38.336-9.584 9.584zm-19.168 19.168 9.584-9.584-38.336-38.336-9.584 9.584zm-19.168 19.168 9.584-9.584-38.336-38.336-9.584 9.584zm-19.168-57.504 9.584-9.584-38.336-38.336-9.584 9.584zm-19.168 19.168 9.584-9.584-38.335-38.336-9.584 9.584zm-18.784-57.504 9.584-9.584-38.336-38.336-9.584 9.584zM275.168 256l9.584-9.584-38.336-38.336-9.584 9.584zm57.12 95.456 9.584-9.584-38.336-38.336-9.584 9.584zm-19.168 19.168 9.584-9.584-38.335-38.336-9.584 9.584zm-28.368 28.752 9.584-9.584L256 351.456l-9.584 9.584zm-19.168 19.168 9.584-9.584-38.336-38.336-9.584 9.584zm-19.168 19.168 9.584-9.584-38.336-38.336-9.584 9.584z"/>
                    </svg>
                </div>

                <div class="section-chat-form__chatbox">
                    <div class="section-chat-form__form">
                        <?php gravity_form($formId, false, false, false, null, true); ?>
                    </div>

                    <button type="button"
                            class="section-chat-form__mic"
                            aria-label="<?php echo esc_attr__('Dictate your message', Theme::TEXT_DOMAIN); ?>"
                            aria-pressed="false"
                            aria-controls="<?php echo esc_attr($micLiveId); ?>"
                            hidden>
                        <svg class="section-chat-form__mic-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                            <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/>
                            <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
                            <line x1="12" y1="19" x2="12" y2="23"/>
                            <line x1="8" y1="23" x2="16" y2="23"/>
                        </svg>
                    </button>
                </div>

                <?php if (!empty($topics)) : ?>
                    <div class="section-chat-form__topics" role="group" aria-label="<?php echo esc_attr__('Topic', Theme::TEXT_DOMAIN); ?>">
                        <?php foreach ($topics as $topic) : ?>
                            <button type="button"
                                    class="section-chat-form__topic section-chat-form__topic--<?php echo esc_attr($topic['color']); ?>"
                                    data-topic-slug="<?php echo esc_attr($topic['slug']); ?>"
                                    data-topic-label="<?php echo esc_attr($topic['label']); ?>"
                                    aria-pressed="false">
                                <?php if ($topic['iconId']) : ?>
                                    <span class="section-chat-form__topic-icon">
                                        <?php echo Image::render([
                                            'blockClass' => 'section-chat-form',
                                            'sources' => [
                                                [
                                                    'id' => $topic['iconId'],
                                                    'size' => 'thumbnail',
                                                    'alt' => '',
                                                ],
                                            ],
                                        ]); ?>
                                    </span>
                                <?php endif; ?>
                                <span class="section-chat-form__topic-label"><?php echo esc_html($topic['label']); ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <p id="<?php echo esc_attr($hintId); ?>" class="section-chat-form__hint" hidden>
                    <?php echo esc_html__('Fill in your message, name, and a valid email address.', Theme::TEXT_DOMAIN); ?>
                </p>

                <div id="<?php echo esc_attr($micLiveId); ?>" class="ts-sr-only" aria-live="polite"></div>
            <?php elseif ($isEditor) : ?>
                <?php echo PreviewNotificationComponent::getNotificationHtml(
                    __('Chat form', Theme::TEXT_DOMAIN),
                    __('Select a form', Theme::TEXT_DOMAIN),
                    'warning'
                ); ?>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
