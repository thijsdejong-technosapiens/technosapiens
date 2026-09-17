<?php

/**
 * Google rating component with half-star support
 *
 * @var float  $rating      Rating value (e.g. 4.3)
 * @var string $url         Link to Google reviews page (optional)
 */

use TechnoSapiens\Theme;

$rating = isset($rating) ? (float) $rating : 0.0;
$url = $url ?? '';

if ($rating <= 0) return;

// Round rating to nearest 0.5
$roundedRating = round($rating * 2) / 2;
$fullStars = (int) floor($roundedRating);
$hasHalfStar = ($roundedRating - $fullStars) >= 0.5;
$emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);

// Unique ID for half-star clip path
$clipId = 'hsgr-' . uniqid();

$ratingLabel = sprintf(__('Google rating: %.1f out of 5 stars', Theme::TEXT_DOMAIN), $rating);
$ratingScore = sprintf(__('%s on Google', Theme::TEXT_DOMAIN), number_format_i18n($rating, 1));
$linkAriaLabel = sprintf(__('%s, opens in new tab', Theme::TEXT_DOMAIN), $ratingLabel);

?>

<div class="google-rating">

    <?php if ($url): ?>
        <a href="<?php echo esc_url($url); ?>"
           class="google-rating__link"
           target="_blank"
           rel="noopener noreferrer"
           aria-label="<?php echo esc_attr($linkAriaLabel); ?>">
    <?php endif; ?>

    <div class="google-rating__inner">

        <div class="google-rating__stars"
             role="img"
             aria-label="<?php echo esc_attr($ratingLabel); ?>">

            <?php for ($i = 0; $i < $fullStars; $i++): ?>
                <svg class="google-rating__star google-rating__star--full" viewBox="0 0 24 24" aria-hidden="true">
                    <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26" fill="currentColor"/>
                </svg>
            <?php endfor; ?>

            <?php if ($hasHalfStar): ?>
                <svg class="google-rating__star google-rating__star--half" viewBox="0 0 24 24" aria-hidden="true">
                    <defs>
                        <clipPath id="<?php echo esc_attr($clipId); ?>">
                            <rect x="0" y="0" width="12" height="24"/>
                        </clipPath>
                    </defs>
                    <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1"/>
                    <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"
                             fill="currentColor"
                             clip-path="url(#<?php echo esc_attr($clipId); ?>)"/>
                </svg>
            <?php endif; ?>

            <?php for ($i = 0; $i < $emptyStars; $i++): ?>
                <svg class="google-rating__star google-rating__star--empty" viewBox="0 0 24 24" aria-hidden="true">
                    <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1"/>
                </svg>
            <?php endfor; ?>

        </div>

        <div class="google-rating__meta">
            <span class="google-rating__score"><?php echo esc_html($ratingScore); ?></span>
        </div>

    </div>

    <?php if ($url): ?>
        </a>
    <?php endif; ?>

</div>
