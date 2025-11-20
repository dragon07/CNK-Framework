<?php

/**
 * Plugin Name: CNK Framework
 * Plugin URI:  https://example.com/cnk
 * Description: CNK - modular framework & addons for Elementor: Fancybox, Slider, GetPost, Clients. Version v3.0 Final (Full Dev).
 * Version:     3.0.0
 * Author:      CNK
 * Author URI:  https://example.com
 * Text Domain: cnk
 * Domain Path: /languages
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * ===============================================================
 * CNK Framework - Constants Definition
 * ---------------------------------------------------------------
 * Định nghĩa toàn bộ hằng số đường dẫn và URL cho framework.
 * Sử dụng trailingslashit() để đảm bảo tương thích giữa hệ điều hành.
 * ===============================================================
 */

// Phiên bản Framework
define('CNK_VERSION', '3.1');
define('CNK_BOOTSTRAP_VERSION', '5.0.2');
define('CNK_SWIPER_VERSION', '12.0.3');

// Đường dẫn và URL gốc
define('CNK_PATH', trailingslashit(plugin_dir_path(__FILE__)));
define('CNK_URL',  trailingslashit(plugin_dir_url(__FILE__)));

// Thư mục chính
define('CNK_INC',        trailingslashit(CNK_PATH . 'includes'));
define('CNK_WIDGETS',    trailingslashit(CNK_PATH . 'widgets'));
define('CNK_ASSETS',     trailingslashit(CNK_PATH . 'assets'));
define('CNK_ASSETS_URL', trailingslashit(CNK_URL  . 'assets'));
define('CNK_ADMIN',      trailingslashit(CNK_PATH . 'admin'));
define('CNK_ADMIN_URL',  trailingslashit(CNK_URL  . 'admin'));

// Modules - nơi chứa các module mở rộng (tách biệt với widgets)
define('CNK_MODULES',     trailingslashit(CNK_PATH . 'modules'));
define('CNK_MODULES_URL', trailingslashit(CNK_URL  . 'modules'));

// Phân loại asset con
define('CNK_CSS',        trailingslashit(CNK_ASSETS . 'css'));
define('CNK_CSS_URL',    trailingslashit(CNK_ASSETS_URL . 'css'));

define('CNK_JS',         trailingslashit(CNK_ASSETS . 'js'));
define('CNK_JS_URL',     trailingslashit(CNK_ASSETS_URL . 'js'));

define('CNK_IMG',        trailingslashit(CNK_ASSETS . 'images'));
define('CNK_IMG_URL',    trailingslashit(CNK_ASSETS_URL . 'images'));

// Vendors (thư viện bên thứ 3)
define('CNK_VENDOR',        trailingslashit(CNK_PATH . 'vendor'));
define('CNK_VENDOR_URL',    trailingslashit(CNK_URL  . 'vendor'));

define('CNK_BOOTSTRAP_PATH', trailingslashit(CNK_VENDOR . 'bootstrap'));
define('CNK_BOOTSTRAP_URL',  trailingslashit(CNK_VENDOR_URL . 'bootstrap'));

define('CNK_SWIPER_PATH',    trailingslashit(CNK_VENDOR . 'swiper'));
define('CNK_SWIPER_URL',     trailingslashit(CNK_VENDOR_URL . 'swiper'));


/* Minimal PHP version check */
if (version_compare(PHP_VERSION, '7.4', '<')) {
    add_action('admin_notices', function () {
        echo '<div class="notice notice-error"><p>CNK Framework yêu cầu PHP 7.4 trở lên.</p></div>';
    });
    return;
}

/* Load loader */
require_once CNK_INC . 'class-cnk-loader.php';
CNK_Loader::init();
