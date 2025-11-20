<?php
if (! defined('ABSPATH')) exit;

/**
 * CNK_MetaBox_Loader
 *
 * Load thư viện Meta Box từ thư mục vendor của CNK.
 * Không can thiệp vào core Meta Box để dễ update sau này.
 * Tất cả các file Meta Box được giữ nguyên bản.
 */

class CNK_MetaBox_Loader
{

    public static function init()
    {

        // Đường dẫn tới thư viện MetaBox trong plugin CNK
        $mb_path = CNK_VENDOR . 'meta-box/meta-box.php';

        // Kiểm tra tồn tại
        if (file_exists($mb_path)) {
            require_once $mb_path;
        }

        // Load tất cả module mở rộng nếu cần
        // $modules = CNK_VENDOR . 'meta-box/modules/';

        // if (is_dir($modules)) {
        //     foreach (glob($modules . '*.php') as $file) {
        //         require_once $file;
        //     }
        // }
        // Load file cấu hình CNK Settings
        $mtb = CNK_ADMIN . 'config/metabox/cnk-mtb-setttings.php';
        if (file_exists($mtb)) {
            require_once $mtb;
        }
    }
}