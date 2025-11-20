<?php
if (! defined('ABSPATH')) exit;

/**
 * ============================================================
 * CNK_AJAX_Post
 * ============================================================
 * Xử lý các yêu cầu AJAX liên quan đến bài viết:
 * - Lấy taxonomy theo post type
 * - Lấy terms theo taxonomy
 * - Xử lý load more posts dùng chung cho widget
 * ============================================================
 */

class CNK_AJAX_Post
{

    /**
     * Khởi tạo các hook AJAX
     */
    public static function init()
    {
        // AJAX load taxonomy / terms
        CNK_Helper_AJAX::register_action('cnk_load_taxonomy', [__CLASS__, 'load_taxonomy'], true);
        CNK_Helper_AJAX::register_action('cnk_load_terms', [__CLASS__, 'load_terms'], true);

        // AJAX load more posts (dùng chung cho tất cả widget)
        CNK_Helper_AJAX::register_action('cnk_getpost_load_more', [__CLASS__, 'ajax_load_more'], true);
    }

    /**
     * ============================================================
     * Lấy danh sách taxonomy theo post type (sử dụng CNK_Helper_Post)
     * ============================================================
     */
    public static function load_taxonomy()
    {
        CNK_Helper_AJAX::verify_request();
        $data = CNK_Helper_AJAX::sanitize_recursive($_POST);

        CNK_Helper_AJAX::check_required_fields(['post_type'], $data);

        $post_type = sanitize_text_field($data['post_type'] ?? '');

        if (empty($post_type) || ! post_type_exists($post_type)) {
            CNK_Helper_AJAX::send_error([
                'message' => __('Post Type không hợp lệ.', 'cnk'),
            ]);
        }

        // Gọi helper chuẩn hóa
        $taxonomies = CNK_Helper_Post::get_taxonomies_by_post_type($post_type);

        if (empty($taxonomies) || ! is_array($taxonomies)) {
            CNK_Helper_AJAX::send_error([
                'message' => __('Không tìm thấy taxonomy nào cho Post Type này.', 'cnk'),
            ]);
        }

        // Chuẩn hóa output
        $output = [];
        foreach ($taxonomies as $slug => $label) {
            $output[] = [
                'slug'  => sanitize_text_field($slug),
                'label' => sanitize_text_field($label),
            ];
        }

        CNK_Helper_AJAX::send_success($output);
    }


    /**
     * ============================================================
     * Lấy danh sách terms theo taxonomy (sử dụng CNK_Helper_Post)
     * ============================================================
     */
    public static function load_terms()
    {
        CNK_Helper_AJAX::verify_request();
        $data = CNK_Helper_AJAX::sanitize_recursive($_POST);

        CNK_Helper_AJAX::check_required_fields(['taxonomy'], $data);

        $taxonomy = sanitize_text_field($data['taxonomy'] ?? '');

        if (empty($taxonomy) || ! taxonomy_exists($taxonomy)) {
            CNK_Helper_AJAX::send_error([
                'message' => __('Taxonomy không hợp lệ.', 'cnk'),
            ]);
        }

        // Gọi helper chuẩn hóa
        $output = CNK_Helper_Post::get_terms_by_taxonomy($taxonomy, false);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("\n[CNK DEBUG] Terms output for taxonomy: {$taxonomy}");
            error_log(print_r($output, true));
        }

        if (empty($output)) {
            CNK_Helper_AJAX::send_error([
                'message' => __('Không thể lấy danh sách term hoặc rỗng.', 'cnk'),
            ]);
        }

        CNK_Helper_AJAX::send_success($output);
    }

    /**
     * ============================================================
     * Xử lý AJAX Load More Posts (dùng chung cho widget)
     * ============================================================
     */
    public static function ajax_load_more()
    {
        // Bắt đầu buffer để tránh rác
        ob_start();

        CNK_Helper_AJAX::verify_request();
        $data = CNK_Helper_AJAX::sanitize_recursive($_POST);


        $paged = isset($data['paged']) ? max(1, intval($data['paged'])) : 1;
        $query_args = isset($data['query']) && is_array($data['query']) ? $data['query'] : [];

        $query_args = CNK_Helper_Post::normalize_query_args($query_args);
        $query_args['paged'] = $paged;
        $per_page = intval($query_args['posts_per_page'] ?? get_option('posts_per_page'));
        $query_args['offset'] = ($paged - 1) * $per_page;

        $query_args['no_found_rows'] = false;
        $query_args['ignore_sticky_posts'] = true;

        $widget_slug = !empty($data['widget']) ? sanitize_key($data['widget']) : 'get-post';
        $template    = !empty($data['template']) ? sanitize_file_name($data['template']) : 'grid';

        $item_tpl = CNK_WIDGETS . "{$widget_slug}/templates/parts/item-{$template}.php";

        if (!file_exists($item_tpl)) {
            CNK_Helper_AJAX::send_error([
                'message' => "Không tìm thấy template item: {$item_tpl}"
            ]);
        }

        $query = CNK_Helper_Post::query_posts($query_args);

        ob_start();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                CNK_Template_Loader::load($item_tpl, ['post' => get_post()]);
            }
        }
        wp_reset_postdata();
        $html = ob_get_clean();

        // Kết thúc buffer để tránh rác
        ob_end_clean();

        $found_posts = intval($query->found_posts);
        $max_pages   = intval($query->max_num_pages);
        $has_more    = ($max_pages > $paged);

        /**
         * DEBUG LOG
         */
        CNK_Helper_AJAX::log([
            'paged'       => $paged,
            'per_page'    => $per_page,
            'offset'      => $query_args['offset'],
            'found_posts' => $found_posts,
            'max_pages'   => $max_pages,
            'has_more'    => $has_more ? 'YES' : 'NO',
        ], 'LOAD_MORE_DEBUG');

        CNK_Helper_AJAX::send_success([
            'html'      => $html,
            'paged'     => $paged,
            'found'     => $found_posts,
            'max_pages' => $max_pages,
            'has_more'  => $has_more,
        ]);
    }
}

CNK_AJAX_Post::init();
