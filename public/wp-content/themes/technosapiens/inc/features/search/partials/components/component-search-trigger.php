<?php

/**
 * @param array $args
 */

use TechnoSapiens\SearchPlugin;
use TechnoSapiens\SearchPlugin\searchsettings;

//bail if on search page
if (is_search()) return false;

$triggerElement = 'li';
if (isset($args['element']) && !empty($args['element'])) $triggerElement = $args['element'];

//make search icon href filterable
$searchIconHref = apply_filters('ts_search_icon_href', (WSP_ICON_PATH . 'icon-search'));

//check if microcopy is enabled
$hasMicroCopy = SearchSettings::getInstance()->isMicroCopyEnabled();

?>

<?php echo '<' . $triggerElement . ' class="search-trigger' . ($hasMicroCopy ? ' search-trigger--has-micro-copy' : '') . '">'; ?>
    <button type="button"
            class="search-trigger__open-trigger"
            aria-controls="search-overlay"
            aria-haspopup="dialog"
            aria-label="<?php _e("Open the search overlay", SearchPlugin::TEXT_DOMAIN); ?>">
        <svg class="search-trigger__icon">
            <use xlink:href="<?php echo $searchIconHref ?>"/>
        </svg>
        <?php if (SearchSettings::getInstance()->isMicroCopyEnabled()): ?>
            <span class="search-trigger__microcopy">
                <?php _e("Search", SearchPlugin::TEXT_DOMAIN); ?>
            </span>
        <?php endif; ?>
    </button>
<?php echo '</' . $triggerElement . '>'; ?>