<?php

use TechnoSapiens\Theme;
use TechnoSapiens\Core\Link;
use TechnoSapiens\Core\Image;

$blogName = get_bloginfo('name');
$logoId = get_field('logo_image', 'option') ?: '';
$logoHtml = '';
if ($logoId) $logoHtml = Image::render([
    'class' => 'ts-header__logo-image',
    'lazy' => null,
    'alt' => sprintf(__("Logo %s", Theme::TEXT_DOMAIN), $blogName),
    'sources' => [['id' => $logoId]]
]);

?>

<?php if ($logoHtml): ?>
    <a href="<?php echo Link::getHomePageUrl(); ?>"
    target="_self"
    class="ts-header__logo"
    aria-label="<?php _e("Navigate to the home page", Theme::TEXT_DOMAIN); ?>">
        <?php echo $logoHtml; ?>
    </a>
<?php endif; ?>