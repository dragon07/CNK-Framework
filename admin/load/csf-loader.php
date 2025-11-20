<?php
if (! defined('ABSPATH')) exit;

/**
 * CNK_CSF_Loader
 * - Chỉ load trong admin (tối ưu performance)
 * - Tránh conflict nếu theme đã tích hợp CSF
 * - Load config options CNK
 */

class CNK_CSF_Loader
{

    public static function init()
    {

        // Chỉ load trong admin
        if (! is_admin()) return;

        // Nếu CSF đã tồn tại (theme/plugin khác đã load)
        if (class_exists('CSF')) return;

        // Đường dẫn CSF
        $csf_path = CNK_VENDOR . 'codestar/codestar-framework.php';

        if (file_exists($csf_path)) {
            require_once $csf_path;
        }

        // Load file cấu hình CNK Settings
        $cfg = CNK_ADMIN . 'config/cnk-settings.php';
        if (file_exists($cfg)) {
            require_once $cfg;
        }
    }
}