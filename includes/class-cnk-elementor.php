<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * ===============================================================
 * Class CNK_Elementor
 * ---------------------------------------------------------------
 * Đăng ký Category và Widgets cho Elementor
 * - Category: CNK Elements
 * - Tự động load toàn bộ widget trong thư mục /widgets/
 * - Đảm bảo tương thích Elementor >= 3.5
 * ===============================================================
 */

class CNK_Elementor {

    /**
     * Khởi tạo hook
     */
    public static function init() {
        // Đăng ký category CNK
        add_action( 'elementor/elements/categories_registered', [ __CLASS__, 'register_category' ] );

        // Đăng ký toàn bộ widget
        add_action( 'elementor/widgets/register', [ __CLASS__, 'register_widgets' ] );
    }

    /**
     * Đăng ký category "CNK Elements"
     *
     * @param \Elementor\Elements_Manager $elements_manager
     */
    public static function register_category( $elements_manager ) {
        if ( ! $elements_manager instanceof \Elementor\Elements_Manager ) {
            return;
        }

        // Thêm nhóm CNK Elements vào panel Elementor
        $elements_manager->add_category(
            'cnk-elements',
            [
                'title' => __( 'CNK Elements', 'cnk' ),
                'icon'  => 'eicon-plug',
            ],
            1 // Ưu tiên hiển thị đầu tiên
        );
    }

    /**
     * Đăng ký các widget CNK tự động
     *
     * @param \Elementor\Widgets_Manager $widgets_manager
     */
    public static function register_widgets( $widgets_manager ) {
        if ( ! $widgets_manager || ! is_dir( CNK_WIDGETS ) ) {
            return;
        }

        // Duyệt tất cả các file widget theo định dạng class-cnk-widget-*.php
        foreach ( glob( CNK_WIDGETS . '*/class-cnk-widget-*.php' ) as $file ) {
            require_once $file;

            // Tìm tên class để đăng ký
            $content = file_get_contents( $file );
            if ( preg_match( '/class\s+([A-Za-z0-9_]+)/', $content, $match ) ) {
                $class = $match[1];
                if ( class_exists( $class ) ) {
                    $widget_instance = new $class();

                    // Elementor 3.5+ hỗ trợ hàm register()
                    if ( method_exists( $widgets_manager, 'register' ) ) {
                        $widgets_manager->register( $widget_instance );
                    } elseif ( method_exists( $widgets_manager, 'register_widget_type' ) ) {
                        // Hỗ trợ backward compatibility
                        $widgets_manager->register_widget_type( $widget_instance );
                    }
                }
            }
        }
    }
}

// Khởi động CNK Elementor Integration
CNK_Elementor::init();
