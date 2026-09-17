<?php

use TechnoSapiens\Core\Link;
use TechnoSapiens\SearchPlugin;
use TechnoSapiens\SearchPlugin\SearchComponent;

//bail if on search page
if (is_search()) return false;

//get search query
$query = get_search_query();

//make search icon hrefs filterable
$searchIconHref = apply_filters('ts_search_icon_href', (WSP_ICON_PATH . 'icon-search'));
$closeIconHref = apply_filters('ts_search_close_icon_href', (WSP_ICON_PATH . 'icon-close'));

?>

    <dialog class="search-overlay ts-dialog"
            id="search-overlay"
            aria-labelledby="search-dialog-label"
            data-close-on-overlay-click="true"
            inert>

        <div class="search-overlay__content">

            <div class="container search-overlay__container">

                <button aria-controls="search-overlay"
                        class="search-overlay__close-trigger"
                        aria-label="<?php _e("Close search overlay", SearchPlugin::TEXT_DOMAIN) ?>">
                    <svg class="search-overlay__close-icon" aria-hidden="true">
                        <use xlink:href="<?php echo $closeIconHref; ?>"/>
                    </svg>
                </button>

                <form role="search"
                      method="get"
                      action="<?php echo esc_url(Link::getHomePageUrl()) ?>"
                      class="search-overlay__form">

                    <label class="search-overlay__heading h4"
                           id="search-dialog-label"
                           for="search-overlay-input">
                        <?php _e("Search", SearchPlugin::TEXT_DOMAIN); ?>
                    </label>

                    <div class="search-overlay__field-container">

                        <input type="search"
                               autofocus
                               class="search-overlay__field-input"
                               name="<?php echo SearchComponent::getSearchQueryParam(); ?>"
                               id="search-overlay-input"
                               autocomplete="search"
                               placeholder="<?php _e("What are you looking for?", SearchPlugin::TEXT_DOMAIN); ?>"
                               value="<?php echo esc_attr($_GET[SearchComponent::getSearchQueryParam()] ?? ''); ?>"/>

                        <button type="submit"
                                aria-label="<?php _e('Submit your query to search the website', SearchPlugin::TEXT_DOMAIN); ?>"
                                class="search-overlay__field-button">
                            <svg class="search-overlay__field-icon" aria-hidden="true">
                                <use xlink:href="<?php echo $searchIconHref; ?>"/>
                            </svg>
                        </button>

                    </div>

                </form>

                <span class="search-overlay__description"
                      aria-live="polite">
                    <?php _e("Press ENTER to search or ESC to close this overlay.", SearchPlugin::TEXT_DOMAIN); ?>
                </span>

            </div>

        </div>

    </dialog>