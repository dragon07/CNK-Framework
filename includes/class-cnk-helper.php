<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * ============================================================
 *  CNK_Helper (Router)
 *  ------------------------------------------------------------
 *  Chức năng:
 *  - Tự động load tất cả file helper trong /includes/helpers/
 *  - Hỗ trợ mở rộng thêm các helper khác mà không cần sửa Loader
 *  - Giữ nguyên backward compatibility với các class helper cũ
 *
 *  Ví dụ tự động load:
 *  - class-cnk-helper-post.php
 *  - class-cnk-helper-cache.php
 *  - class-cnk-helper-form.php
 * ============================================================
 */

class CNK_Helper {

    public static function init() {
        $helper_dir = trailingslashit( CNK_INC ) . 'helpers/';

        if ( is_dir( $helper_dir ) ) {
            foreach ( glob( $helper_dir . 'class-cnk-helper-*.php' ) as $file ) {
                if ( file_exists( $file ) ) {
                    require_once $file;
                }
            }
        }

        do_action( 'cnk_helpers_loaded' );
    }
}

// Khởi chạy router helper
CNK_Helper::init();
