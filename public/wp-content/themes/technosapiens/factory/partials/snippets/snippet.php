<?php

use TechnoSapiens\Core\ButtonComponent;
use TechnoSapiens\Core\Image;
use TechnoSapiens\Core\PostType;
use TechnoSapiens\FactoryPlugin;
use TechnoSapiens\FactoryPlugin\PostTypeFactory;

/**
 * @var bool $is_preview
 */

//set default values
if (!isset($postId)) $postId = get_the_ID();
if (!isset($is_preview)) $is_preview = false;

//get current post type and factory class
$postType = PostType::getPostType();
$factoryClass = PostTypeFactory::getFactoryClassByPostType($postType);
$postTypeLabelSingular = strtolower(PostType::getSingularLabel($postType)) ?: __("item", FactoryPlugin::TEXT_DOMAIN);

//bail if no post type or factory class found
if (!$postType || !$factoryClass) return false;

//get parsed snippet data
$data = $factoryClass::getSnippetDataById($postId);

//bail if no data found
if ($data === false) return false;

//render image HTML
$imageHtml = '';
if ($data->imageId) $imageHtml = Image::render([
    'blockClass' => 'snippet-default',
    'lazy' => true,
    'sources' => [
        [
            'id' => $data->imageId,
            'size' => $data->imageSize,
            'size2x' => $data->imageSizeX2,
        ],
        [
            'id' => $data->imageId,
            'size' => $data->imageSize,
        ]
    ]
]);

?>
<article class="snippet-default">

    <?php do_action('ts_default_snippet_before_inner'); ?>

    <div class="snippet-default__inner<?php echo $data->link ? ' ts-link-snippet' : ''; ?>"
         <?php if ($data->link): ?>role="link"<?php endif; ?>>

        <?php do_action('ts_default_snippet_before_meta'); ?>

        <div class="snippet-default__meta">

            <?php do_action('ts_default_snippet_before_meta_top'); ?>

            <div class="snippet-default__meta-top">

                <?php do_action('ts_default_snippet_before_title'); ?>

                <span class="snippet-default__title h5">
                    <?php if ($data->link): ?>
                        <a href="<?php echo $data->link; ?>"
                       <?php echo($is_preview ? 'onclick="return false"' : ''); ?>
                           draggable="false"
                           target="_self"
                           class="snippet-default__link"
                           data-role="primary-link">
                        <?php echo $data->title; ?>
                    </a>
                    <?php else: ?>
                        <?php echo $data->title; ?>
                    <?php endif; ?>
                </span>

                <?php do_action('ts_default_snippet_after_title'); ?>

                <?php do_action('ts_default_snippet_before_date'); ?>

                <?php if ($data->date && $factoryClass::featureEnabled('enableVisiblePostDates')): ?>
                    <time class="snippet-default__date">
                        <?php echo $data->date; ?>
                    </time>
                <?php endif; ?>

                <?php do_action('ts_default_snippet_after_date'); ?>

                <?php do_action('ts_default_snippet_before_excerpt'); ?>

                <?php if ($data->excerpt): ?>
                    <p class="snippet-default__excerpt">
                        <?php echo $data->excerpt; ?>
                    </p>
                <?php endif; ?>

                <?php do_action('ts_default_snippet_before_excerpt'); ?>

                <?php do_action('ts_default_snippet_after_meta'); ?>

            </div>

            <?php do_action('ts_default_snippet_after_meta_top'); ?>

            <?php do_action('ts_default_snippet_before_link'); ?>

            <?php if ($data->link): ?>
                <?php echo ButtonComponent::render(apply_filters('ts_default_snippet_button_args', [
                    'tag' => 'span',
                    'text' => sprintf(__("View %1s", FactoryPlugin::TEXT_DOMAIN), $postTypeLabelSingular),
                    'blockClass' => 'snippet-default',
                    'type' => 'primary',
                    'style' => 'text',
                    'size' => 'medium'
                ])); ?>
            <?php endif; ?>

            <?php do_action('ts_default_snippet_after_link'); ?>

        </div>

        <?php do_action('ts_default_snippet_before_image'); ?>

        <figure class="snippet-default__image-container"
            <?php if ($data->imageSizeDimensions): ?>
                style="aspect-ratio:<?php echo $data->imageSizeDimensions->width; ?>/<?php echo $data->imageSizeDimensions->height; ?>;"
            <?php endif; ?>>
            <?php if ($imageHtml): ?>
                <?php echo $imageHtml; ?>
            <?php else: ?>
                <?php do_action('ts_default_snippet_before_placeholder_image'); ?>
                <div class="snippet-default__placeholder-container">
                    <svg class="snippet-default__placeholder" aria-hidden="true">
                        <use xlink:href="<?php echo ICON_PATH; ?>image-placeholder"/>
                    </svg>
                </div>
                <?php do_action('ts_default_snippet_after_placeholder_image'); ?>
            <?php endif; ?>
        </figure>

        <?php do_action('ts_default_snippet_after_image'); ?>

    </div>

    <?php do_action('ts_default_snippet_after_inner'); ?>

</article>