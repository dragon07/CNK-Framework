<?php
if (! defined('ABSPATH')) exit;

/**
 * CNK_Admin
 */
class CNK_Admin
{

    public static function init()
    {

        // Chỉ chạy trong admin
        if (! is_admin()) return;

        // Load CSF loader
        $csf_loader = CNK_ADMIN . 'load/csf-loader.php';
        if (file_exists($csf_loader)) {
            require_once $csf_loader;

            if (class_exists('CNK_CSF_Loader')) {
                CNK_CSF_Loader::init();  // Load CSF + Load config
            }
        }
        // Load Meta Box loader
        $mb_loader = CNK_ADMIN . 'load/metabox-loader.php';
        if (file_exists($mb_loader)) {
            require_once $mb_loader;
            if (class_exists('CNK_MetaBox_Loader')) {
                CNK_MetaBox_Loader::init();  // Load Meta Box core + modules
            }
        }
    }
}

CNK_Admin::init();