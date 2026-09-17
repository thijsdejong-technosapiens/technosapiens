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

//handle default values
if (!isset($filters)) $filters = [];
if (!isset($totalPosts)) $totalPosts = 0;

//get current post type and factory class
$postType = PostType::getPostType();
$factoryClass = PostTypeFactory::getFactoryClassByPostType($postType);
$archiveLink = PostType::getPostTypeArchiveLink($postType);
$postTypeLabelPlural = strtolower(PostType::getPluralLabel($postType)) ?: __("items", FactoryPlugin::TEXT_DOMAIN);

//bail if no post type or factory class found
if (!$postType || !$factoryClass || !$archiveLink) return false;

//bail if no filters
if (count($filters) === 0) return false;

//handle search
$searchParam = $factoryClass::getTranslatedCustomSearchParam();
$query = get_query_var($searchParam) ?: '';

//check if there are results; it not, don't maintain the search query in the URL
$totalQueriedPosts = (int)$wp_query->found_posts;

//check if filter counts are enabled
$filterCountsEnabled = $factoryClass::featureEnabled('enableArchiveFilterCounts');

$allFiltersUrl = esc_url($archiveLink . '#' . $postType . '-archive-filters');

?>

<div class="archive-filters-mobile">

    <?php foreach ($filters as $filter): ?>

        <?php $currentFilterValues = isset($_GET[$filter->parameter]) ? is_array($_GET[$filter->parameter]) ? $_GET[$filter->parameter] : [$_GET[$filter->parameter]] ?? [] : []; ?>

        <select class="archive-filters-mobile__select ts-select ts-filter-select"
                aria-label="<?php echo esc_attr(sprintf(__("Filter by %s", FactoryPlugin::TEXT_DOMAIN), strtolower($filter->heading))); ?>">

            <option value="<?php echo $allFiltersUrl; ?>">
                <?php echo esc_html(sprintf(__('All %1s', FactoryPlugin::TEXT_DOMAIN), $postTypeLabelPlural)); ?>
                <?php echo $filterCountsEnabled ? '(' . (int)$totalPosts . ')' : ''; ?>
            </option>

            <?php foreach ($filter->options as $option): ?>
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
            <option
                value="<?php echo $filterUrl; ?>" <?php echo in_array($option->value, $currentFilterValues) ? 'selected' : ''; ?>>
                <?php echo esc_html($option->label . ($filterCountsEnabled ? ' (' . $option->count . ')' : '')); ?>
                <?php if (isset($option->children) && is_array($option->children) && count($option->children) > 0) : ?>
                    <optgroup label="<?php echo esc_attr($option->label); ?>">
                        <?php foreach ($option->children as $child): ?>
                            <?php
                            $childFilterUrl = esc_url(add_query_arg([
                                $filter->parameter . '[]' => $child->value,
                                'ccAc' => 1,
                            ], $archiveLink) . '#' . $postType . '-archive-filters');
                            ?>
                            <option
                                value="<?php echo $childFilterUrl; ?>"<?php echo in_array($child->value, $currentFilterValues) ? 'selected' : ''; ?>>
                                <?php echo esc_html($child->label . ($filterCountsEnabled ? ' (' . $child->count . ')' : '')); ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endif; ?>
                </option>
            <?php endforeach; ?>

        </select>

    <?php endforeach; ?>

</div>
