<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Singleton;
use WP_Query;
use WP_REST_Request;

class GetItemsRestRoute extends Singleton {

    /**
     * Hard cap for posts_per_page to prevent unbounded queries.
     */
    const MAX_POSTS_PER_PAGE = 50;

    protected function __construct() {
        add_action('rest_api_init', [$this, 'registerRoute']);
    }

    public function registerRoute(): void {
        register_rest_route(get_option('stylesheet') . '/v1', '/get-items/', [
            'methods' => 'GET',
            'callback' => [$this, 'getItems'],
            'permission_callback' => '__return_true',
        ]);
    }

    /**
     * Public post types that may be requested via the get-items endpoint.
     * @return string[]
     */
    private function getAllowedPostTypes(): array {
        return array_values(get_post_types(['public' => true], 'names'));
    }

    public function getItems(WP_REST_Request $request): array {
        $parameters = $request->get_params();
        $defaultImage = get_field('default_image', 'option');
        $allowedPostTypes = $this->getAllowedPostTypes();

        $args = [
            'post_type' => 'page',
            'post_status' => 'publish',
            'posts_per_page' => 6,
            'paged' => 1,
        ];

        if (!empty($parameters['pt'])) {
            $requestedPostType = sanitize_key($parameters['pt']);
            if (in_array($requestedPostType, $allowedPostTypes, true)) {
                $args['post_type'] = $requestedPostType;
            }
        }
        if (!empty($parameters['ppp'])) {
            $args['posts_per_page'] = min(max(1, (int)$parameters['ppp']), self::MAX_POSTS_PER_PAGE);
        }
        if (!empty($parameters['pagination'])) {
            $args['paged'] = max(1, (int)$parameters['pagination']);
        }
        if (!empty($parameters['categories'])) {
            $args['cat'] = sanitize_text_field($parameters['categories']);
        }
        if (!empty($parameters['search'])) {
            $args['s'] = sanitize_text_field($parameters['search']);
        }

        $query = new WP_Query($args);

        if (function_exists('relevanssi_do_query') && !empty($parameters['search'])) {
            relevanssi_do_query($query);
        }

        $items = [];
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $id = get_the_ID();
                $featuredImageUrl = '';
                $thumbnailId = get_post_thumbnail_id($id);

                if ($thumbnailId) {
                    $featuredImageUrl = (string)wp_get_attachment_image_url($thumbnailId, 'medium_large');
                } elseif (is_array($defaultImage) && !empty($defaultImage['ID'])) {
                    $featuredImageUrl = (string)wp_get_attachment_image_url($defaultImage['ID'], 'medium_large');
                }

                $items[] = [
                    'id' => $id,
                    'title' => get_the_title(),
                    'permalink' => get_permalink(),
                    'excerpt' => get_the_excerpt(),
                    'date' => get_the_date(get_option('date_format')),
                    'featuredImageUrl' => $featuredImageUrl,
                ];
            }
            wp_reset_postdata();
        }

        $categories = [];
        if (!empty($args['post_type']) && $args['post_type'] !== 'any') {
            $terms = get_terms([
                'taxonomy' => 'category',
                'hide_empty' => true,
            ]);

            if (!is_wp_error($terms)) {
                foreach ($terms as $term) {
                    $categories[] = [
                        'term_id' => $term->term_id,
                        'name' => $term->name,
                    ];
                }
            }
        }

        return [
            'items' => $items,
            'itemsTotalCount' => (int)$query->found_posts,
            'getCategories' => $categories,
        ];
    }
}
