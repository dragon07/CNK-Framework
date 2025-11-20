<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * ============================================================
 * CNK_Helper_Post
 * ============================================================
 * - Trả về post types được phép chọn
 * - Lấy taxonomies theo post type
 * - Lấy terms theo taxonomy
 * - Query với chuẩn hóa logic lọc post_type / taxonomy / term
 * ============================================================
 *
 * Lưu ý: giữ nguyên logic gốc, tối ưu hoá về chuẩn mã (sanitization,
 * kiểm tra lỗi, comment tiếng Việt).
 */
class CNK_Helper_Post
{

    /**
     * Trả về danh sách post types (slug => label)
     *
     * @return array
     */
    public static function get_allowed_post_types()
    {
        $args = array(
            'public'             => true,
            'show_ui'            => true,
            'publicly_queryable' => true,
        );

        $post_types = get_post_types($args, 'objects');
        $options    = array();

        $excluded_core = array(
            'page',
            'attachment',
            'revision',
            'nav_menu_item',
            'custom_css',
            'oembed_cache',
            'user_request',
            'wp_block',
            'wp_template',
            'wp_template_part',
        );

        if (empty($post_types) || is_wp_error($post_types)) {
            return $options;
        }

        foreach ($post_types as $slug => $obj) {
            if ('post' === $slug || 'product' === $slug) {
                $options[$slug] = isset($obj->label) ? (string) $obj->label : $slug;
                continue;
            }

            if (in_array($slug, $excluded_core, true)) {
                continue;
            }

            $options[$slug] = isset($obj->label) ? (string) $obj->label : $slug;
        }

        // Fallback nếu rỗng
        if (empty($options) && isset($post_types['post'])) {
            $options['post'] = isset($post_types['post']->label) ? (string) $post_types['post']->label : 'Post';
        }

        return $options;
    }

    /**
     * Lấy danh sách taxonomy (slug => label) theo post type
     *
     * @param string $post_type
     * @return array
     */
    public static function get_taxonomies_by_post_type($post_type = 'post')
    {
        // Kiểm tra hợp lệ
        if (empty($post_type) || ! post_type_exists($post_type)) {
            return array();
        }

        $taxonomies = get_object_taxonomies($post_type, 'objects');
        if (empty($taxonomies) || is_wp_error($taxonomies)) {
            return array();
        }

        $output = array();

        foreach ($taxonomies as $slug => $tax) {
            $is_public = ! empty($tax->public);
            $show_ui   = ! empty($tax->show_ui);

            // Chỉ lấy taxonomy công khai và có UI
            if ($is_public && $show_ui) {
                if (! empty($tax->labels) && ! empty($tax->labels->singular_name)) {
                    $label = (string) $tax->labels->singular_name;
                } elseif (! empty($tax->label)) {
                    $label = (string) $tax->label;
                } else {
                    $label = ucfirst((string) $slug);
                }

                $output[sanitize_key($slug)] = sanitize_text_field($label);
            }
        }

        return $output;
    }

    /**
     * Lấy danh sách term theo taxonomy
     *
     * Trả về mảng các phần tử dạng:
     *  [ ['id'=>int, 'name'=>string, 'slug'=>string], ... ]
     *
     * @param string $taxonomy
     * @param bool   $hide_empty
     * @return array
     */
    public static function get_terms_by_taxonomy($taxonomy = '', $hide_empty = false)
    {
        // Kiểm tra hợp lệ
        if (empty($taxonomy) || ! taxonomy_exists($taxonomy)) {
            return array();
        }

        $terms = get_terms(
            array(
                'taxonomy'   => $taxonomy,
                'hide_empty' => (bool) $hide_empty,
            )
        );

        if (is_wp_error($terms) || empty($terms)) {
            return array();
        }

        $output = array();
        foreach ($terms as $term) {
            $output[] = array(
                'id'   => intval($term->term_id),
                'name' => sanitize_text_field($term->name),
                'slug' => sanitize_title($term->slug),
            );
        }

        return $output;
    }

    /**
     * Chuẩn hoá tham số truy vấn bài viết
     *
     * @param array $settings
     * @return array
     */
    public static function normalize_query_args($settings = [])
    {

        // Convert WP-style tax_query → CNK format
        if (isset($settings['tax_query']) && empty($settings['taxonomy'])) {
            $tq = $settings['tax_query'];
            if (is_array($tq) && ! empty($tq)) {
                $first = reset($tq);
                if (! empty($first['taxonomy'])) {
                    $settings['taxonomy'] = sanitize_text_field($first['taxonomy']);
                    $settings['terms']    = isset($first['terms']) ? (array) $first['terms'] : [];
                }
            }
        }

        return [

            // Loại bài viết
            'post_type'       => ! empty($settings['post_type'])
                ? sanitize_text_field($settings['post_type'])
                : 'post',

            // Taxonomy
            'taxonomy'        => ! empty($settings['taxonomy'])
                ? sanitize_text_field($settings['taxonomy'])
                : '',

            // Terms (slug, id, string đều OK)
            'terms'           => ! empty($settings['terms'])
                ? array_map('sanitize_text_field', (array) $settings['terms'])
                : [],

            // Số lượng bài
            'posts_per_page'  => ! empty($settings['posts_per_page'])
                ? absint($settings['posts_per_page'])
                : 10,

            // IN | AND
            'operator'        => ! empty($settings['operator'])
                ? strtoupper($settings['operator'])
                : 'IN',

            // Pagination
            'paged'           => ! empty($settings['paged'])
                ? max(1, absint($settings['paged']))
                : 1,

            // Offset (nếu có)
            'offset'          => isset($settings['offset'])
                ? absint($settings['offset'])
                : 0,
        ];
    }

    /**
     * Thực hiện truy vấn bài viết với chuẩn hoá post_type / taxonomy / terms
     *
     * @param array $settings
     * @return WP_Query
     */
    public static function query_posts($settings = [])
    {

        // Chuẩn hóa dữ liệu input
        $s = self::normalize_query_args($settings);

        $args = [
            'post_type'           => $s['post_type'],
            'post_status'         => 'publish',
            'posts_per_page'      => max(1, $s['posts_per_page']),
            'paged'               => $s['paged'],
            'offset'              => $s['offset'],
            'ignore_sticky_posts' => true,
            'no_found_rows'       => false, // quan trọng cho paginate
        ];

        // Không có taxonomy → query đơn giản
        if (empty($s['taxonomy'])) {
            return new WP_Query($args);
        }

        // Kiểm tra taxonomy hợp lệ cho post_type
        $valid = get_object_taxonomies($s['post_type'], 'names');
        if (empty($valid) || ! in_array($s['taxonomy'], $valid, true)) {
            return new WP_Query($args);
        }

        // Nếu có taxonomy nhưng không có term → EXISTS
        if (empty($s['terms'])) {
            $args['tax_query'] = [
                [
                    'taxonomy' => $s['taxonomy'],
                    'operator' => 'EXISTS',
                ]
            ];
            return new WP_Query($args);
        }

        // Resolve term ids (slug | id | name)
        $term_ids = [];

        foreach ($s['terms'] as $t) {

            if (is_numeric($t)) {
                $term = get_term(intval($t), $s['taxonomy']);
                if ($term && ! is_wp_error($term)) {
                    $term_ids[] = intval($t);
                }
                continue;
            }

            // slug → id
            $term = get_term_by('slug', $t, $s['taxonomy']);
            if ($term && ! is_wp_error($term)) {
                $term_ids[] = intval($term->term_id);
                continue;
            }

            // name → id
            $term = get_term_by('name', $t, $s['taxonomy']);
            if ($term && ! is_wp_error($term)) {
                $term_ids[] = intval($term->term_id);
            }
        }

        // Nếu không có term hợp lệ → query rỗng
        if (empty($term_ids)) {
            return new WP_Query(['post__in' => [0]]);
        }

        $args['tax_query'] = [
            [
                'taxonomy' => $s['taxonomy'],
                'field'    => 'term_id',
                'terms'    => array_unique($term_ids),
                'operator' => $s['operator'],
            ]
        ];

        return new WP_Query($args);
    }
}
