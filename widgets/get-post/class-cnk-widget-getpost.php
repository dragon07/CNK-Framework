<?php
if (! defined('ABSPATH')) exit;

/**
 * ============================================================
 * CNK_Widget_GetPost
 * ------------------------------------------------------------
 * - Hiển thị danh sách bài viết với lựa chọn post_type, taxonomy, term
 * - Hỗ trợ Load More (AJAX hoặc liên kết)
 * - Tự động nhận slug widget, action name, và template
 * - Dùng CNK_Helper_AJAX + CNK_Helper_Widget để chuẩn hóa xử lý
 * ============================================================
 */

class CNK_Widget_GetPost extends \Elementor\Widget_Base
{

    public function get_name()
    {
        return 'cnk_getpost';
    }
    public function get_title()
    {
        return __('CNK Get Posts', 'cnk');
    }
    public function get_icon()
    {
        return 'eicon-post-list';
    }
    public function get_categories()
    {
        return ['cnk-elements'];
    }

    public function get_style_depends()
    {
        return ['cnk-bootstrap'];
    }
    public function get_script_depends()
    {
        return ['cnk-admin-getpost', 'cnk-getpost-loadmore'];
    }

    /**
     * ============================================================
     * Cấu hình các trường điều khiển trong Elementor
     * ============================================================
     */
    protected function register_controls()
    {

        $this->start_controls_section(
            'section_main',
            [
                'label' => __('Cấu hình Widget', 'cnk'),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // ===== Bắt đầu Tabs =====
        $this->start_controls_tabs('tabs_main');

        // ---------------------------
        // TAB 1 : Query
        // ---------------------------
        $this->start_controls_tab(
            'tab_query',
            ['label' => __('Nội dung', 'cnk')]
        );

        $this->add_control('post_type', [
            'label'       => __('Loại nội dung', 'cnk'),
            'type'        => \Elementor\Controls_Manager::SELECT,
            'options'     => CNK_Helper_Post::get_allowed_post_types(),
            'default'     => 'post',
        ]);

        $this->add_control('taxonomy', [
            'label'       => __('Phân loại nội dung', 'cnk'),
            'type'        => \Elementor\Controls_Manager::SELECT,
            'options'     => [],
            'default'     => 'category',
        ]);

        $this->add_control('terms', [
            'label'       => __('Danh mục con', 'cnk'),
            'type'        => \Elementor\Controls_Manager::SELECT2,
            'multiple'    => true,
            'options'     => [],
        ]);

        $this->end_controls_tab(); // END TAB QUERY


        // ---------------------------
        // TAB 2 : Template
        // ---------------------------
        $this->start_controls_tab(
            'tab_template',
            ['label' => __('Hiển thị', 'cnk')]
        );

        $this->add_control('posts_per_page', [
            'label'   => __('Số lượng hiển thị', 'cnk'),
            'type'    => \Elementor\Controls_Manager::NUMBER,
            'default' => 6,
        ]);

        $template_options = CNK_Helper_Widget::get_widget_templates(__FILE__);

        $this->add_control('template_style', [
            'label'   => __('Kiểu hiển thị', 'cnk'),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'options' => ! empty($template_options) ? $template_options : ['default' => 'Default'],
            'default' => 'default',
        ]);

        $this->add_control(
            'divider_1',
            [
                'type' => \Elementor\Controls_Manager::DIVIDER,
                'style' => 'thin',
            ]
        );

        $this->add_control('enable_load_more', [
            'label' => __('Bật xem thêm', 'cnk'),
            'type'  => \Elementor\Controls_Manager::SWITCHER,
            'label_on'  => __('Yes', 'cnk'),
            'label_off' => __('No', 'cnk'),
            'return_value' => 'yes',
            'default' => 'no',
        ]);

        $this->add_control('load_type', [
            'label'   => __('Kiểu xem thêm', 'cnk'),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'ajax' => __('Ajax', 'cnk'),
                'link' => __('Link', 'cnk'),
            ],
            'default' => 'ajax',
            'condition' => ['enable_load_more' => 'yes'],
        ]);

        $this->end_controls_tab();

        // ===== Kết thúc Tabs =====
        $this->end_controls_tabs();

        // ===== Kết thúc Section =====
        $this->end_controls_section();
    }

    /**
     * ============================================================
     * Render Widget (frontend)
     * ============================================================
     */
    protected function render()
    {

        $s = $this->get_settings_for_display();

        // --- Chuẩn hóa dữ liệu query ---
        $post_type = ! empty($s['post_type']) ? sanitize_text_field($s['post_type']) : 'post';
        $per_page  = ! empty($s['posts_per_page']) ? intval($s['posts_per_page']) : 6;

        $settings_for_helper = [
            'post_type'      => $post_type,
            'posts_per_page' => $per_page,
            'operator'       => 'IN',
        ];

        if (! empty($s['taxonomy'])) {
            $settings_for_helper['taxonomy'] = sanitize_text_field($s['taxonomy']);
        }

        if (! empty($s['terms']) && is_array($s['terms'])) {
            $settings_for_helper['terms'] = $s['terms'];
        }

        // --- Query dữ liệu ---
        $query = CNK_Helper_Post::query_posts($settings_for_helper);

        // --- Lấy slug widget + template ---
        $widget_slug = CNK_Helper_Widget::get_widget_slug(__FILE__);
        $template    = ! empty($s['template_style']) ? sanitize_file_name($s['template_style']) : 'default';
        $tpl_path    = CNK_WIDGETS . "{$widget_slug}/templates/{$template}.php";

        // --- Enqueue JS nếu có Load More ---
        if (isset($s['enable_load_more']) && $s['enable_load_more'] === 'yes' && $s['load_type'] === 'ajax') {
            wp_enqueue_script('cnk-getpost-loadmore');
        }

        // --- Render nội dung template ---
        if (class_exists('CNK_Template_Loader')) {
            $unique_id = 'cnk-' . $widget_slug . '-list-' . $this->get_id();

            CNK_Template_Loader::load($tpl_path, [
                'query'      => $query,
                'settings'   => $s,
                'query_args' => $settings_for_helper,
                'widget_id'  => $unique_id,
                'widget_slug' => $widget_slug,
                'template'   => $template
            ]);

            // --- Hiển thị Load More ---
            // --- Kiểm tra xem có cần hiển thị Load More không ---
            $found_posts = intval($query->found_posts ?? 0);
            $max_pages   = intval($query->max_num_pages ?? 1);
            $per_page    = intval($s['posts_per_page'] ?? get_option('posts_per_page', 3));

            $show_loadmore = (
                ! empty($s['enable_load_more']) &&
                $s['enable_load_more'] === 'yes' &&
                $found_posts > $per_page &&
                $max_pages > 1
            );

            // --- Hiển thị Load More ---
            if ($show_loadmore) {
                $ajax_action = CNK_Helper_AJAX::get_action_from_class(__CLASS__, 'load_more');
                $query_json  = esc_attr(wp_json_encode($settings_for_helper));

                echo '<div class="cnk-load-more-wrap text-center"
                        data-action="' . esc_attr($ajax_action) . '"
                        data-widget="' . esc_attr($widget_slug) . '"
                        data-template="' . esc_attr($template) . '"
                        data-query="' . $query_json . '"
                        data-paged="1"
                        data-type="' . esc_attr($s['load_type']) . '"
                        data-target="#' . esc_attr($unique_id) . '">';

                if ($s['load_type'] === 'ajax') {
                    echo '<button class="btn btn-outline-secondary cnk-load-more-btn">' . esc_html__('Tải thêm', 'cnk') . '</button>';
                } elseif ($s['load_type'] === 'link') {
                    $archive_link = get_post_type_archive_link($post_type);
                    if ($archive_link) {
                        echo '<a class="btn btn-outline-secondary cnk-load-more-link" href="' . esc_url($archive_link) . '">' . esc_html__('Xem thêm', 'cnk') . '</a>';
                    }
                }

                echo '</div>';
            }
        } else {
            // --- Fallback ---
            if ($query->have_posts()) {
                echo '<div class="cnk-getpost">';
                while ($query->have_posts()) {
                    $query->the_post();
                    echo '<div class="cnk-item"><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></div>';
                }
                echo '</div>';
                wp_reset_postdata();
            } else {
                echo '<p>' . esc_html__('Không có bài viết phù hợp.', 'cnk') . '</p>';
            }
        }
    }
}
