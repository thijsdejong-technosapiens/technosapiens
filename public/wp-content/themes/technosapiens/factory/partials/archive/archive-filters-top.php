<?php

use TechnoSapiens\Core\PostType;
use TechnoSapiens\FactoryPlugin;
use TechnoSapiens\FactoryPlugin\PostTypeFactory;

/**
 * @var int $totalPosts
 * @var int $postPerPage
 * @var array $filters
 */

global $wp_query;

//handle default value
if (!isset($filters)) $filters = [];

//bail if no filters
if (count($filters) === 0) return false;

//set default value for total posts
if (!isset($totalPosts)) $totalPosts = 0;

//get current post type and factory class
$postType = PostType::getPostType();
$factoryClass = PostTypeFactory::getFactoryClassByPostType($postType);
$archiveLink = PostType::getPostTypeArchiveLink($postType);
$postTypeLabelPlural = strtolower(PostType::getPluralLabel($postType)) ?: __("items", FactoryPlugin::TEXT_DOMAIN);

//bail if no post type or factory class found
if (!$postType || !$factoryClass || !$archiveLink) return false;

//handle search
$searchParam = $factoryClass::getTranslatedCustomSearchParam();
$query = get_query_var($searchParam) ?: '';

//check if there are results; it not, don't maintain the search query in the URL
$totalQueriedPosts = (int)$wp_query->found_posts;

//check if filter counts are enabled
$filterCountsEnabled = $factoryClass::featureEnabled('enableArchiveFilterCounts');

$allFiltersUrl = esc_url($archiveLink . '#' . $postType . '-archive-filters');

?>

<?php foreach ($filters as $filter): ?>
    <?php $currentFilterValues = isset($_GET[$filter->parameter]) ? is_array($_GET[$filter->parameter]) ? $_GET[$filter->parameter] : [$_GET[$filter->parameter]] ?? [] : []; ?>

    <nav class="archive-filters-top"
         aria-label="<?php echo esc_attr(sprintf(__("Filter by %s", FactoryPlugin::TEXT_DOMAIN), strtolower($filter->heading))); ?>">

        <ul class="archive-filters-top__list">

            <li class="archive-filters-top__item">
                <a href="<?php echo $allFiltersUrl; ?>"
                   aria-label="<?php echo esc_attr(sprintf(__('Show all %1s', FactoryPlugin::TEXT_DOMAIN), $postTypeLabelPlural)); ?>"
                   class="archive-filters-top__button <?php echo empty($currentFilterValues) ? 'archive-filters-top__button--active' : ''; ?>">
                    <span class="archive-filters-top__button-label">
                        <?php echo esc_html(sprintf(__("All %1s", FactoryPlugin::TEXT_DOMAIN), $postTypeLabelPlural)); ?>
                    </span>
                    <?php if ($filterCountsEnabled): ?>
                        <small class="archive-filters-top__button-count">
                            <?php echo (int)$totalPosts; ?>
                        </small>
                    <?php endif; ?>
                </a>
            </li>

            <?php foreach ($filter->options as $option) : ?>
                <?php
                $filterArgs = [
                    $filter->parameter . '[]' => $option->value,
                    'ccAc' => 1,
                ];
                if ($query && $totalQueriedPosts > 0) {
                    $filterArgs[$searchParam] = $query;
                }
                $filterUrl = esc_url(add_query_arg($filterArgs, $archiveLink) . '#' . $postType . '-archive-filters');
                ?>

                <li>
                    <a href="<?php echo $filterUrl; ?>"
                       aria-label="<?php echo esc_attr($option->label); ?>"
                       class="archive-filters-top__button <?php echo in_array($option->value, $currentFilterValues) ? 'archive-filters-top__button--active' : ''; ?>">
                        <span class="archive-filters-top__button-label">
                            <?php echo esc_html($option->label); ?>
                        </span>
                        <?php if ($filterCountsEnabled): ?>
                            <small class="archive-filters-top__button-count">
                                <?php echo (int)$option->count; ?>
                            </small>
                        <?php endif; ?>
                    </a>
                </li>

            <?php endforeach; ?>

        </ul>

    </nav>
<?php endforeach; ?>
