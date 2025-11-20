<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * ============================================================
 * CNK_Helper_Widget
 * ------------------------------------------------------------
 * Chứa các hàm tiện ích phục vụ cho CNK Widgets
 * (dùng cho render, load template, AJAX, v.v.)
 * ============================================================
 */
class CNK_Helper_Widget {


    /**
     * Lấy slug widget theo thư mục (vd: get-post)
     *
     * @param string $file Đường dẫn tuyệt đối của file hiện tại (__FILE__)
     * @return string Slug thư mục widget (vd: 'get-post')
     */
    public static function get_widget_slug( $file ) {
        if ( empty( $file ) || ! is_string( $file ) ) {
            return '';
        }

        return basename( dirname( $file ) );
    }

    /**
     * Lấy đường dẫn tuyệt đối tới thư mục widget
     */
    public static function get_widget_dir( $file ) {
        return trailingslashit( dirname( $file ) );
    }

    /**
     * Lấy danh sách template có trong thư mục widget
     */
    public static function get_widget_templates( $file ) {
        $dir = self::get_widget_dir( $file ) . 'templates/';
        if ( ! is_dir( $dir ) ) return [];

        $templates = [];
        foreach ( glob( $dir . '*.php' ) as $f ) {
            $templates[ basename( $f, '.php' ) ] = ucfirst( basename( $f, '.php' ) );
        }
        return $templates;
    }
}
