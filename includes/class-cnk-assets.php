<?php
if (! defined('ABSPATH')) exit;

/**
 * CNK_Assets
 * Đăng ký và nạp tài nguyên CSS/JS cho Frontend / Editor / Admin
 */

class CNK_Assets
{

    public static function init()
    {
        add_action('wp_enqueue_scripts', array(__CLASS__, 'frontend_assets'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'admin_assets'));

        // Elementor Editor
        add_action('elementor/editor/before_enqueue_scripts', array(__CLASS__, 'editor_assets'), 5);
        add_action('elementor/editor/after_enqueue_scripts', array(__CLASS__, 'editor_assets'), 20);

        // Đăng ký script dùng cho widget (Elementor sẽ tự enqueue)
        add_action('wp_enqueue_scripts', [__CLASS__, 'register_widget_scripts'], 1);
    }


    /**
     * ========================================================
     *  ĐĂNG KÝ SCRIPT CHO WIDGET (CHUẨN ELEMENTOR)
     *  - KHÔNG enqueue
     *  - Elementor tự enqueue khi widget gọi get_script_depends()
     * ========================================================
     */
    public static function register_widget_scripts()
    {
        $file = CNK_JS . 'cnk-getpost-loadmore.js';

        if (file_exists($file)) {

            wp_register_script(
                'cnk-getpost-loadmore',
                CNK_JS_URL . 'cnk-getpost-loadmore.js',
                ['jquery'],
                CNK_VERSION,
                true
            );

            // Localize trước khi Elementor enqueue script
            wp_localize_script('cnk-getpost-loadmore', 'cnk_ajax', [
                'url'   => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('cnk_ajax_action'),
            ]);
        }
    }



    /**
     * Nạp tài nguyên cho Frontend
     */
    public static function frontend_assets()
    {

        // Bootstrap (vendors)
        if (! wp_style_is('bootstrap', 'enqueued') && file_exists(CNK_BOOTSTRAP_PATH . 'bootstrap.min.css')) {
            wp_enqueue_style('cnk-bootstrap', CNK_BOOTSTRAP_URL . 'bootstrap.min.css', array(), defined('CNK_BOOTSTRAP_VERSION') ? CNK_BOOTSTRAP_VERSION : CNK_VERSION);
        }

        if (! wp_script_is('bootstrap', 'enqueued') && file_exists(CNK_BOOTSTRAP_PATH . 'bootstrap.bundle.min.js')) {
            wp_enqueue_script('cnk-bootstrap', CNK_BOOTSTRAP_URL . 'bootstrap.bundle.min.js', array('jquery'), defined('CNK_BOOTSTRAP_VERSION') ? CNK_BOOTSTRAP_VERSION : CNK_VERSION, true);
        }

        // Swiper
        if (! wp_style_is('swiper', 'enqueued') && file_exists(CNK_SWIPER_PATH . 'swiper-bundle.min.css')) {
            wp_enqueue_style('cnk-swiper', CNK_SWIPER_URL . 'swiper-bundle.min.css', array(), defined('CNK_SWIPER_VERSION') ? CNK_SWIPER_VERSION : CNK_VERSION);
        }

        if (! wp_script_is('swiper', 'enqueued') && file_exists(CNK_SWIPER_PATH . 'swiper-bundle.min.js')) {
            wp_enqueue_script('cnk-swiper', CNK_SWIPER_URL . 'swiper-bundle.min.js', array(), defined('CNK_SWIPER_VERSION') ? CNK_SWIPER_VERSION : CNK_VERSION, true);
        }


        /**
         * ========================================================
         *  LOAD CSS từ theme (ưu tiên main.min.css)
         * ========================================================
         */

        $theme_css_min = get_stylesheet_directory() . '/assets/css/main.min.css';
        $theme_css     = get_stylesheet_directory() . '/assets/css/main.css';

        if (file_exists($theme_css_min)) {

            wp_enqueue_style(
                'cnk-theme-main',
                get_stylesheet_directory_uri() . '/assets/css/main.min.css',
                array(),
                filemtime($theme_css_min)
            );

            return; // Không load CSS plugin
        }

        if (file_exists($theme_css)) {

            wp_enqueue_style(
                'cnk-theme-main',
                get_stylesheet_directory_uri() . '/assets/css/main.css',
                array(),
                filemtime($theme_css)
            );

            return;
        }


        // Load fallback CSS plugin
        if (file_exists(CNK_CSS . 'cnk-global.css')) {
            wp_enqueue_style('cnk-global', CNK_CSS_URL . 'cnk-global.css', array(), CNK_VERSION);
        }

        if (file_exists(CNK_JS . 'cnk-global.js')) {
            wp_enqueue_script('cnk-global', CNK_JS_URL . 'cnk-global.js', array('jquery'), CNK_VERSION, true);
        }
    }


    /**
     * Tài nguyên cho Elementor Editor
     */
    public static function editor_assets()
    {
        if (file_exists(CNK_JS . 'cnk-editor.js')) {
            wp_enqueue_script('cnk-editor', CNK_JS_URL . 'cnk-editor.js', array('jquery'), CNK_VERSION, true);
        }

        if (file_exists(CNK_JS . 'cnk-admin-getpost.js')) {

            wp_enqueue_script('cnk-admin-getpost', CNK_JS_URL . 'cnk-admin-getpost.js', array('jquery'), CNK_VERSION, true);

            wp_localize_script('cnk-admin-getpost', 'cnk_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('cnk_ajax_action'),
                'select_taxonomy_text' => __('Chọn taxonomy', 'cnk'),
            ));
        }
    }


    /**
     * Tài nguyên cho khu vực admin
     */
    public static function admin_assets()
    {
        if (! wp_style_is('cnk-admin', 'enqueued') && file_exists(CNK_CSS . 'cnk-admin.css')) {
            wp_enqueue_style('cnk-admin', CNK_CSS_URL . 'cnk-admin.css', array(), CNK_VERSION);
        }
    }
}

CNK_Assets::init();
