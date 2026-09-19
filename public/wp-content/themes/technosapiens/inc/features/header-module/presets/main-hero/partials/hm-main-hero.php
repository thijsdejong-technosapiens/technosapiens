<?php

use TechnoSapiens\Core\ButtonComponent;
use TechnoSapiens\Core\GradientBandsComponent;

/**
 * @var stdClass $data
 */

?>

    <div class="hm-main-hero">

        <div class="hm-main-hero__background">
            <?php echo GradientBandsComponent::render([
                'size' => 'small',
                'bandSize' => 'small',
                'axis' => 'vertical',
                'staggerMode' => 'center',                
            ]); ?>
        </div>

        <div class="hm-main-hero__meta">
            <div class="hm-main-hero__meta-container container">
                
                <div class="hm-main-hero__meta-top">

                    <?php if (!empty($data->headingText)): ?>
                        <?php echo '<' . $data->headingTag . ' class="hm-main-hero__heading">'; ?>
                        <?php echo $data->headingText; ?>
                        <?php echo '</' . $data->headingTag . '>'; ?>
                    <?php endif; ?>

                    <?php if ($data->subHeading): ?>
                        <span class="hm-main-hero__sub-heading">
                            <?php echo $data->subHeading; ?>
                        </span>
                    <?php endif; ?>

                </div>

                <?php if ($data->content): ?>
                    <div class="hm-main-hero__content">
                        <?php echo $data->content; ?>
                    </div>
                <?php endif; ?>

                <?php if (count($data->buttons) > 0): ?>
                    <div class="hm-main-hero__buttons">
                        <?php foreach ($data->buttons as $button): ?>
                            <?php echo ButtonComponent::render(apply_filters('ts_hm_main_hero_button_args', [
                                'text' => $button->text,
                                'href' => $button->link,
                                'target' => $button->target,
                                'rel' => $button->rel,
                                'blockClass' => 'hm-main-hero',
                                'type' => $button->type,
                                'style' => $button->style,
                                'size' => $data->buttonSize,
                            ])); ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>

        </div>

    </div>
