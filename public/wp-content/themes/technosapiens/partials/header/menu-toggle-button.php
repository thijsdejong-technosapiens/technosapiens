<?php

use TechnoSapiens\Theme;

/**
 * @var string $modifier 'open' | 'close'
 * @var bool $autofocus
 */

$modifier = $modifier ?? 'open';
$autofocus = $autofocus ?? false;

?>

<button type="button"
        class="menu-toggle-button menu-toggle-button--<?php echo esc_attr($modifier); ?>"
        aria-controls="side-navigation"
        <?php if ($modifier === 'open'): ?>aria-haspopup="dialog"<?php endif; ?>
        aria-expanded="false"
        aria-label="<?php esc_attr_e('Open side menu', Theme::TEXT_DOMAIN); ?>"
        data-label-open="<?php esc_attr_e('Open side menu', Theme::TEXT_DOMAIN); ?>"
        data-label-close="<?php esc_attr_e('Close side menu', Theme::TEXT_DOMAIN); ?>"
        <?php if ($autofocus): ?>autofocus<?php endif; ?>>

    <span class="menu-toggle-button__bars" aria-hidden="true">
        <span class="menu-toggle-button__bar"></span>
        <span class="menu-toggle-button__bar"></span>
        <span class="menu-toggle-button__bar"></span>
    </span>

</button>
