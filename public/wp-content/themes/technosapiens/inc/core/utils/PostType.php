<?php

namespace TechnoSapiens\Core;

use TechnoSapiens\Core;

/**
 * Class PostType
 * @package TechnoSapiens\Core
 */
class PostType {
    /**
     * Get labels for post type or taxonomy
     * @param string $singular
     * @param string $plural
     * @return array
     */
    public static function getLabels(string $singular, string $plural): array {
        $singularLc = strtolower($singular);
        $pluralLc = strtolower($plural);
        
        return [
            //basic menu labels
            'name' => $plural,
            'singular_name' => $singular,
            'menu_name' => $plural,
            'name_admin_bar' => $singular,

            //item‑specific strings (need context)
            'add_new_item' => sprintf(__('Add new %s', Core::TEXT_DOMAIN), $singularLc),
            'edit_item' => sprintf(__('Edit %s', Core::TEXT_DOMAIN), $singularLc),
            'new_item' => sprintf(__('New %s', Core::TEXT_DOMAIN), $singularLc),
            'view_item' => sprintf(__('View %s', Core::TEXT_DOMAIN), $singularLc),
            'view_items' => sprintf(__('View %s', Core::TEXT_DOMAIN), $pluralLc),
            'search_items' => sprintf(__('Search %s', Core::TEXT_DOMAIN), $pluralLc),

            //list / table views
            'filter_items_list' => sprintf(__('Filter %s list', Core::TEXT_DOMAIN), $pluralLc),
            'items_list_navigation' => sprintf(__('%s list navigation', Core::TEXT_DOMAIN), $pluralLc),
            'items_list' => sprintf(__('%s list', Core::TEXT_DOMAIN), $pluralLc),
            'all_items' => sprintf(__('All %s', Core::TEXT_DOMAIN), $pluralLc),

            //empty‑state messages
            'not_found' => sprintf(__('No %s found.', Core::TEXT_DOMAIN), $singularLc),
            'not_found_in_trash' => sprintf(__('No %s found in Trash.', Core::TEXT_DOMAIN), $pluralLc),

            //misc / sidebar / block‑editor
            'parent_item_colon' => sprintf(__('Parent %s:', Core::TEXT_DOMAIN), $singularLc),
            'archives' => sprintf(__('%s archives', Core::TEXT_DOMAIN), $singularLc),
            'attributes' => sprintf(__('%s attributes', Core::TEXT_DOMAIN), $singularLc),
            'insert_into_item' => sprintf(__('Insert into %s', Core::TEXT_DOMAIN), $singularLc),
            'uploaded_to_this_item' => sprintf(__('Uploaded to this %s', Core::TEXT_DOMAIN), $singularLc),

            //editor‑side notices & links
            'item_published' => sprintf(__('%s published.', Core::TEXT_DOMAIN), $singular),
            'item_published_privately' => sprintf(__('%s published privately.', Core::TEXT_DOMAIN), $singular),
            'item_reverted_to_draft' => sprintf(__('%s reverted to draft.', Core::TEXT_DOMAIN), $singular),
            'item_scheduled' => sprintf(__('%s scheduled.', Core::TEXT_DOMAIN), $singular),
            'item_updated' => sprintf(__('%s updated.', Core::TEXT_DOMAIN), $singular),
            'item_link' => sprintf(__('%s link', Core::TEXT_DOMAIN), $singular),
            'item_link_description' => sprintf(__('A link to a %s.', Core::TEXT_DOMAIN), $singularLc),
        ];
    }

    /**
     * More solid way to get current post type
     * @param bool|int $postId
     * @return string|false
     */
    public static function getPostType(bool|int $postId = false): string|false {
        if (!$postId) $postId = get_the_ID();

        //handle scenario with post
        $postType = get_post_type($postId);

        if (!$postType) {

            //handle scenario with archive
            $queriedObject = get_queried_object();
            if ($queriedObject && is_a($queriedObject, '\WP_Post_Type')) $postType = $queriedObject->name;

            //handle scenario with taxonomy term
            if ($queriedObject && is_a($queriedObject, '\WP_Term') && $queriedObject->taxonomy) {
                $taxonomy = get_taxonomy($queriedObject->taxonomy);
                if (is_a($taxonomy, '\WP_Taxonomy') && is_array($taxonomy->object_type) && count($taxonomy->object_type) > 0) {
                    $postType = $taxonomy->object_type[0];
                }
            }
        }

        return $postType;
    }

    /**
     * Return the translated **singular** label of the post-type.
     * @param string $postType
     * @return string
     */
    public static function getSingularLabel(string $postType): string {
        $obj = get_post_type_object($postType);
        if ($obj && isset($obj->labels->singular_name)) return $obj->labels->singular_name;
        return Link::humanize($postType);
    }

    /**
     * Return the translated **plural** label of the post-type.
     * If the post type does not have a plural label, it will return the singular label with an 's' appended.
     * @param string $postType
     * @return string
     */
    public static function getPluralLabel(string $postType): string {
        $obj = get_post_type_object($postType);
        if ($obj && isset($obj->labels->name)) return $obj->labels->name;
        return Link::humanize(self::getSingularLabel($postType) . 's');
    }

    /**
     * Get the post type archive link for a given post type.
     * If the post type does not have an archive, it will return an empty string.
     * @param string $postType
     * @return string
     */
    public static function getPostTypeArchiveLink(string $postType): string {
        $link = get_post_type_archive_link($postType);
        if (!$link) return '';
        return Link::parseLink($link);
    }
}