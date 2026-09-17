<?php

use TechnoSapiens\Core\Link;
use TechnoSapiens\SearchPlugin;
use TechnoSapiens\SearchPlugin\SearchComponent;

/**
 * @var string $query
 */

if (!isset($query)) $query = get_search_query();

//make search icon href filterable
$searchIconHref = apply_filters('ts_search_icon_href', (WSP_ICON_PATH . 'icon-search'));

?>

<div class="archive-search-bar">

    <div class="container archive-search-bar__container">

        <form role="search"
              method="get"
              action="<?php echo esc_url(Link::getHomePageUrl()) ?>"
              class="archive-search-bar__field-container">

            <label for="search" class="archive-search-bar__field-label ts-sr-only">
                <?php _e('Search for:', SearchPlugin::TEXT_DOMAIN); ?>
            </label>

            <input type="search"
                   id="search"
                   class="archive-search-bar__field-input"
                   name="<?php echo SearchComponent::getSearchQueryParam(); ?>"
                   autocomplete=""
                   placeholder="<?php _e("What are you looking for?", SearchPlugin::TEXT_DOMAIN); ?>"
                   value="<?php echo esc_attr($_GET[SearchComponent::getSearchQueryParam()] ?? ''); ?>"/>

            <button aria-label="<?php _e('Submit your query to search the website', SearchPlugin::TEXT_DOMAIN); ?>"
                    class="archive-search-bar__field-button"
                    type="submit">
                <svg class="archive-search-bar__field-icon">
                    <use xlink:href="<?php echo $searchIconHref; ?>"/>
                </svg>
            </button>

        </form>

    </div>

</div>