<?php

/**
 * @param array $socialLinks
 * @param bool $isPreview
 */

//set default values
if (!isset($socialLinks)) $socialLinks = [];
if (!isset($isPreview)) $isPreview = false;

//bail if there are no social links configured
if (count($socialLinks) === 0) return false;

?>

<ul class="social-links">
    <?php foreach ($socialLinks as $socialLink) : ?>
        <li class="social-links__item">
            <a href="<?php echo $socialLink->link; ?>"
               target="_blank"
               rel="noopener nofollow"
               title="<?php echo $socialLink->ariaLabel ?: $socialLink->label; ?>"
               aria-label="<?php echo $socialLink->ariaLabel ?: $socialLink->label ?>"
               class="social-links__item-link<?php echo ($isPreview || is_admin()) ? ' ts-preview-link' : ''; ?>">
                <svg class="social-links__item-icon" aria-hidden="true">
                    <use xlink:href='<?php echo ICON_PATH; ?>icon-<?php echo $socialLink->type; ?>'/>
                </svg>
            </a>
        </li>
    <?php endforeach; ?>
</ul>