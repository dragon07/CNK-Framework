<?php

/**
 * CNK Header Footer Module - Giai đoạn 1
 *
 * - Tạo post type cnk_template.
 * - Cho phép chỉnh bằng Elementor.
 * - Chọn 1 header + 1 footer dùng cho toàn site (option).
 * - Render qua wp_head / wp_footer.
 */

if (! defined('ABSPATH')) {
    exit;
}

class CNK_Header_Footer
{

    const CPT           = 'cnk_template';
    const META_LOCATION = '_cnk_location';        // header | footer
    const OPTION_HEADER = 'cnk_active_header_id'; // int
    const OPTION_FOOTER = 'cnk_active_footer_id'; // int

    public static function init()
    {
        // Đăng ký post type + meta
        add_action('init', [__CLASS__, 'register_post_type']);
        add_action('init', [__CLASS__, 'register_meta']);

        // Cho phép Elementor dùng trên post type này
        add_filter('elementor/cpt_support', [__CLASS__, 'enable_elementor_for_cpt']);

        // Render frontend
        add_action('wp_head',   [__CLASS__, 'render_header'], 1);
        add_action('wp_footer', [__CLASS__, 'render_footer'], 1);
    }

    /**
     * Đăng ký post type cnk_template.
     */
    public static function register_post_type()
    {
        $labels = [
            'name'               => __('CNK Templates', 'cnk'),
            'singular_name'      => __('CNK Template', 'cnk'),
            'add_new'            => __('Add New', 'cnk'),
            'add_new_item'       => __('Add New CNK Template', 'cnk'),
            'edit_item'          => __('Edit CNK Template', 'cnk'),
            'new_item'           => __('New CNK Template', 'cnk'),
            'view_item'          => __('View CNK Template', 'cnk'),
            'search_items'       => __('Search CNK Templates', 'cnk'),
            'not_found'          => __('No CNK Templates found', 'cnk'),
            'not_found_in_trash' => __('No CNK Templates found in Trash', 'cnk'),
        ];

        $args = [
            'label'               => __('CNK Templates', 'cnk'),
            'labels'              => $labels,
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => false, // sẽ add submenu dưới CNK Admin sau
            'show_in_admin_bar'   => false,
            'show_in_nav_menus'   => false,
            'exclude_from_search' => true,
            'publicly_queryable'  => false,
            'hierarchical'        => false,
            'supports'            => ['title', 'editor'],
            'capability_type'     => 'post',
        ];

        register_post_type(self::CPT, $args);
    }

    /**
     * Meta cho loại template: header / footer.
     */
    public static function register_meta()
    {
        register_post_meta(
            self::CPT,
            self::META_LOCATION,
            [
                'type'         => 'string',
                'single'       => true,
                'show_in_rest' => false,
                'default'      => '',
                'auth_callback' => '__return_true',
            ]
        );
    }

    /**
     * Cho phép Elementor edit cnk_template.
     *
     * @param array $post_types
     * @return array
     */
    public static function enable_elementor_for_cpt($post_types)
    {
        if (! in_array(self::CPT, $post_types, true)) {
            $post_types[] = self::CPT;
        }
        return $post_types;
    }

    /**
     * Lấy ID header đang dùng (từ option).
     *
     * @return int
     */
    protected static function get_active_header_id()
    {
        return (int) get_option(self::OPTION_HEADER, 0);
    }

    /**
     * Lấy ID footer đang dùng (từ option).
     *
     * @return int
     */
    protected static function get_active_footer_id()
    {
        return (int) get_option(self::OPTION_FOOTER, 0);
    }

    /**
     * Render header ra frontend.
     */
    public static function render_header()
    {
        if (is_admin()) {
            return;
        }

        $template_id = self::get_active_header_id();
        self::render_elementor_template($template_id, 'header');
    }

    /**
     * Render footer ra frontend.
     */
    public static function render_footer()
    {
        if (is_admin()) {
            return;
        }

        $template_id = self::get_active_footer_id();
        self::render_elementor_template($template_id, 'footer');
    }

    /**
     * Hàm dùng chung để render template Elementor.
     *
     * @param int    $post_id
     * @param string $context  header|footer (chỉ để debug)
     */
    protected static function render_elementor_template($post_id, $context = '')
    {
        $post_id = (int) $post_id;

        if (! $post_id) {
            return;
        }

        if (! did_action('elementor/loaded') || ! class_exists('\Elementor\Plugin')) {
            return;
        }

        // Đảm bảo post là cnk_template
        $post_type = get_post_type($post_id);
        if ($post_type !== self::CPT) {
            return;
        }

        // Kiểm tra meta location nếu có.
        $location = get_post_meta($post_id, self::META_LOCATION, true);
        if ($context && $location && $location !== $context) {
            return;
        }

        echo "\n<!-- CNK {$context} start (#{$post_id}) -->\n";
        echo \Elementor\Plugin::instance()
            ->frontend
            ->get_builder_content_for_display($post_id);
        echo "\n<!-- CNK {$context} end -->\n";
    }
}

// Khởi động module với tên class mới
CNK_Header_Footer::init();
