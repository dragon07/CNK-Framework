<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * ============================================================
 * CNK_Template_Loader
 * ------------------------------------------------------------
 * - Load template với ưu tiên override trong theme
 * - Hỗ trợ đường dẫn tuyệt đối và tương đối
 * - Tự động chuẩn hóa dữ liệu đầu vào ($post → $query)
 * - Hiển thị thông báo rõ ràng khi không tìm thấy file
 * ============================================================
 */

class CNK_Template_Loader {

    /**
     * Load template và truyền dữ liệu vào
     *
     * @param string $template_name  Đường dẫn tương đối hoặc tuyệt đối
     * @param array  $args           Dữ liệu truyền vào template
     */
    public static function load( $template_name, $args = array() ) {

        // ============================================================
        // Kiểm tra tham số đầu vào
        // ============================================================
        if ( empty( $template_name ) || ! is_string( $template_name ) ) {
            trigger_error( '[CNK_Template_Loader] $template_name phải là chuỗi hợp lệ.', E_USER_WARNING );
            return;
        }

        if ( ! is_array( $args ) ) {
            $args = array();
        }

        /**
         * ============================================================
         * Chuẩn hóa dữ liệu đầu vào: tự động wrap post → query
         * ------------------------------------------------------------
         * Nếu template nhận biến 'post', ta tạo WP_Query ảo để
         * template có thể xử lý thống nhất qua biến $query.
         * ============================================================
         */
        if ( isset( $args['post'] ) && $args['post'] instanceof WP_Post ) {
            // Tạo query ảo chứa duy nhất post này
            $args['query'] = new WP_Query( array(
                'post_type' => $args['post']->post_type,
                'post__in'  => array( $args['post']->ID ),
                'orderby'   => 'post__in',
            ) );
            // Giữ lại $post để các template con (item-*) có thể dùng
        }

        // ============================================================
        // Truyền biến động sang template
        // ============================================================
        if ( ! empty( $args ) ) {
            extract( $args, EXTR_SKIP );
        }

        // ============================================================
        // Ưu tiên load template override trong theme
        // ------------------------------------------------------------
        // - Vị trí override: theme/cnk-templates/{đường dẫn tương đối}
        // - Giúp dev có thể tuỳ biến layout mà không sửa file plugin
        // ============================================================
        $theme_template = trailingslashit( get_stylesheet_directory() ) . 'cnk-templates/' . ltrim( $template_name, '/' );

        if ( file_exists( $theme_template ) ) {
            include $theme_template;
            return;
        }

        // ============================================================
        // Nếu $template_name là đường dẫn tuyệt đối (absolute path)
        // ============================================================
        if ( file_exists( $template_name ) ) {
            include $template_name;
            return;
        }

        // ============================================================
        // Nếu là đường dẫn tương đối → tự nối thêm CNK_PATH
        // ============================================================
        $plugin_template = trailingslashit( CNK_PATH ) . ltrim( $template_name, '/' );

        if ( file_exists( $plugin_template ) ) {
            include $plugin_template;
            return;
        }

        // ============================================================
        // Fallback: hiển thị thông báo lỗi khi không tìm thấy file
        // ============================================================
        printf(
            '<div style="color:red; padding:8px 12px; border:1px solid #f00; background:#fff5f5;">
                <strong>CNK Framework:</strong> Không tìm thấy template <code>%s</code>
            </div>',
            esc_html( $template_name )
        );
    }

    /**
     * Lấy đường dẫn override hợp lệ (nếu có)
     *
     * @param string $widget   Tên widget hoặc thư mục con
     * @param string $template Tên file template (không cần .php)
     * @return string Đường dẫn file template
     */
    public static function locate_template( $widget, $template ) {
        $theme_path = get_stylesheet_directory() . "/cnk-templates/{$widget}/{$template}.php";
        if ( file_exists( $theme_path ) ) {
            return $theme_path;
        }
        return CNK_WIDGETS . "{$widget}/templates/{$template}.php";
    }
}
