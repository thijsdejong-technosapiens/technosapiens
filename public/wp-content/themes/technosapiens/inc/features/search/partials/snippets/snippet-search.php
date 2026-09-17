<?php


/**
 * @var int|false $id
 */

use TechnoSapiens\Core\ButtonComponent;
use TechnoSapiens\SearchPlugin;
use TechnoSapiens\SearchPlugin\SearchComponent;

//initial values
$postId = isset($id) ? $id : get_the_ID();

//bail if no ID
if (empty($postId) || !is_numeric($postId)) return false;

//get parsed snippet data
$data = SearchComponent::getSnippetDataById($postId);

//bail if no data
if ($data === false) return false;

?>

<article class="snippet-search">

    <div class="snippet-search__inner ts-link-snippet" role="link">

        <div class="snippet-search__inner-top">

            <span class="snippet-search__title h4">
                <a href="<?php echo $data->link; ?>"
                   target="_self"
                   class="snippet-search__link"
                   data-role="primary-link">
                    <?php echo(!empty($data->title) ? $data->title : $data->slug); ?>
                </a>
            </span>

            <?php if (count($data->labels) > 0): ?>

                <ul class="snippet-search__labels">

                    <?php foreach ($data->labels as $index => $label): ?>

                        <li class="snippet-search__label">
                            <?php echo $label; ?>
                        </li>

                        <?php if (($index + 1) !== count($data->labels)): ?>
                            <li class="snippet-search__label-separator">|</li>
                        <?php endif; ?>

                    <?php endforeach; ?>

                </ul>

            <?php endif; ?>

            <?php if ($data->excerpt): ?>
                <p class="snippet-search__excerpt">
                    <?php echo $data->excerpt; ?>
                </p>
            <?php endif; ?>

        </div>

        <?php echo ButtonComponent::render(apply_filters('ts_snippet_search_button_args', [
            'tag' => 'span',
            'text' => __("Read more", SearchPlugin::TEXT_DOMAIN),
            'blockClass' => 'snippet-search',
            'type' => 'primary',
            'style' => 'text',
            'size' => 'medium'
        ])); ?>

    </div>

</article>