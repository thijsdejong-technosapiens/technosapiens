<?php

use TechnoSapiens\Core\Link;
use TechnoSapiens\SearchPlugin;
use TechnoSapiens\SearchPlugin\SearchSettings;
use TechnoSapiens\SearchPlugin\SearchComponent;

global $wp_query;

/**
 * @var string $query
 */

if (!isset($query)) $query = get_search_query();

//get result counts per type
$resultCountsPerType = SearchComponent::getInstance()->getResultCountsPerType($query);

//get active type
$activeType = 'search';
$rawActiveType = trim(get_query_var(SearchComponent::getSearchTypeParam()));
if (in_array($rawActiveType, array_merge(SearchComponent::getInstance()->searchablePostTypes, ['search']))) $activeType = $rawActiveType;

//bail if not enabled or no results
if (!SearchSettings::getInstance()->isFilterByPostTypeEnabled() || $resultCountsPerType['all'] === 0) return false;

?>

<div class="archive-filters container">

    <nav class="archive-filters__nav"
         aria-label="<?php _e("Filter by type", SearchPlugin::TEXT_DOMAIN); ?>">

        <div class="archive-filters__mobile">

            <select class="archive-filters__select ts-select"
                    aria-label="<?php _e("Filter by type", SearchPlugin::TEXT_DOMAIN); ?>"
                    id="category-filter">

                <option
                    value="<?php echo Link::getHomePageUrl() . '/?' . SearchComponent::getSearchQueryParam() . '=' . $query ?>#search-results"<?php echo($activeType !== 'search' ? ' selected' : ''); ?>>
                    <?php echo sprintf(__("Show all (%1d)", SearchPlugin::TEXT_DOMAIN), $resultCountsPerType['all']); ?>
                </option>

                <?php foreach (SearchComponent::getInstance()->searchablePostTypes as $type) : ?>
                    <?php if (isset($resultCountsPerType[$type]) && $resultCountsPerType[$type] > 0): ?>
                        <option
                            value="<?php echo Link::getHomePageUrl() . '/?' . SearchComponent::getSearchQueryParam() . '=' . $query . '&' . SearchComponent::getSearchTypeParam() . '=' . $type . '&ccAC=1#search-results'; ?>"<?php echo $activeType === $type ? ' selected' : ''; ?>>
                            <?php echo SearchComponent::postTypeToLabel($type, true) . ' (' . $resultCountsPerType[$type] . ')'; ?>
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>

            </select>

        </div>

        <div class="archive-filters__desktop">

            <ul class="archive-filters__list">

                <li class="archive-filters__item">

                    <a href="<?php echo Link::getHomePageUrl() . '/?' . SearchComponent::getSearchQueryParam() . '=' . $query . '#search-results' ?>"
                       class="archive-filters__button archive-filters__button--all <?php echo($activeType === 'search' ? 'archive-filters__button--active' : ''); ?>"
                       <?php echo($activeType === 'search' ? 'aria-current="page"' : ''); ?>>
                        <span
                            class="archive-filters__button-label"><?php _e('Show all', SearchPlugin::TEXT_DOMAIN); ?></span>
                        <small class="archive-filters__button-count"><?php echo $resultCountsPerType['all']; ?></small>
                    </a>

                </li>

                <?php foreach (SearchComponent::getInstance()->searchablePostTypes as $type) : ?>

                    <?php if (isset($resultCountsPerType[$type]) && $resultCountsPerType[$type] > 0): ?>

                        <li class="archive-filters__item">

                            <a href="<?php echo Link::getHomePageUrl() . '/?' . SearchComponent::getSearchQueryParam() . '=' . $query . '&' . SearchComponent::getSearchTypeParam() . '=' . $type . '&ccAC=1#search-results'; ?>"
                               class="archive-filters__button <?php echo($activeType === $type ? 'archive-filters__button--active' : ''); ?>"
                               <?php echo($activeType === $type ? 'aria-current="page"' : ''); ?>>
                                <span class="archive-filters__button-label">
                                    <?php echo SearchComponent::postTypeToLabel($type); ?>
                                </span>
                                <small class="archive-filters__button-count">
                                    <?php echo $resultCountsPerType[$type]; ?>
                                </small>
                            </a>

                        </li>

                    <?php endif; ?>

                <?php endforeach; ?>

            </ul>

        </div>

    </nav>

</div>