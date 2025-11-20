<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * ===============================================================
 *  CNK_AJAX (Router)
 *  ---------------------------------------------------------------
 *  Chức năng:
 *  - Tự động load tất cả các file AJAX con trong /includes/ajax/
 *  - Đảm bảo các class AJAX module hóa được nạp đầy đủ
 *  - Giữ vai trò trung tâm cho toàn bộ hệ thống AJAX framework
 * ===============================================================
 */


class CNK_AJAX {

    /**
     * Khởi tạo AJAX Router
     */
    public static function init() {
        $ajax_dir = trailingslashit( CNK_INC ) . 'ajax/';

        if ( is_dir( $ajax_dir ) ) {
            foreach ( glob( $ajax_dir . 'class-cnk-ajax-*.php' ) as $file ) {
                require_once $file;
            }
        }

        /**
         * Hook mở rộng cho developer khác có thể nạp AJAX riêng
         * Ví dụ: add_action('cnk_ajax_loaded', function(){ ... });
         */
        do_action( 'cnk_ajax_loaded' );
    }
}

// Khởi chạy Router
CNK_AJAX::init();
