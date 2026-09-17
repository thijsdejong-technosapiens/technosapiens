<?php

use TechnoSapiens\Core\Formatting;
use TechnoSapiens\Core\Image;
use TechnoSapiens\SectionMargins;
use TechnoSapiens\SectionReferencePartial;

$contentType = get_field('content_type') ?? '';
$contentText = get_field('content_text') ?? '';
$contentTextBg = get_field('content_text_bg') ?? 'none';
$roundedCornersPosition = get_field('rounded_corners_position') ?? 'all';
$contentImage = get_field('content_image') ?? [];
$contentImageId = !empty($contentImage['ID']) ? (int) $contentImage['ID'] : 0;

$textClasses = ['section-content__text'];
if ($contentType === 'text' && $contentTextBg === 'white') {
    $textClasses[] = 'section-content__text--bg-white';

    if ($roundedCornersPosition === 'top') {
        $textClasses[] = 'section-content__text--rounded-top';
    } elseif ($roundedCornersPosition === 'bottom') {
        $textClasses[] = 'section-content__text--rounded-bottom';
    }
}

$hasContent = ($contentType === 'text' && $contentText) || ($contentType === 'image' && $contentImageId);

$containerClasses = SectionReferencePartial::getContainerClasses('section-content');
$marginTop = SectionMargins::getMarginTop();
$marginBottom = SectionMargins::getMarginBottom();
?>

<?php if ($hasContent) : ?>
    <div class="<?php echo esc_attr(implode(' ', $containerClasses)); ?>"<?php if ($marginTop) : ?> data-mt="<?php echo esc_attr($marginTop); ?>"<?php endif; ?><?php if ($marginBottom) : ?> data-mb="<?php echo esc_attr($marginBottom); ?>"<?php endif; ?>>
        <div class="container container--narrow section-content__container<?php echo ($contentType === 'image' ? ' section-content__container--has-image' : ''); ?>">

            <?php if ($contentType === 'text') : ?>

                <div class="<?php echo esc_attr(implode(' ', $textClasses)); ?>">
                    <?php echo Formatting::toHtml($contentText); ?>
                </div>

            <?php elseif ($contentType === 'image' && $contentImageId) : ?>

                <figure class="section-content__figure">
                    <?php echo Image::render([
                        'blockClass' => 'section-content',
                        'sources' => [
                            [
                                'id' => $contentImageId,
                                'size' => 'full',
                            ],
                        ],
                    ]); ?>
                </figure>

            <?php endif; ?>

        </div>
    </div>
<?php endif; ?>
