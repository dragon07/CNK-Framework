<?php
if (! defined('ABSPATH')) exit;

/**
 * CNK_Loader – Load Core, Modules, Elementor, ProElements
 */

class CNK_Loader
{
    /**
     * Khởi chạy CNK Framework
     */
    public static function init()
    {
        self::load_core();

        // Load các modules (Header/Footer…)
        self::load_modules();

        // Chờ Elementor load → load widgets + integration
        add_action('init', array(__CLASS__, 'maybe_load_elementor'), 5);
    }

    /**
     * Load Core System
     */
    private static function load_core()
    {
        // Helper
        if (file_exists(CNK_INC . 'class-cnk-helper.php')) {
            require_once CNK_INC . 'class-cnk-helper.php';
        }

        // Core Files
        $core_files = array(
            CNK_INC . 'class-cnk-assets.php',
            CNK_INC . 'class-cnk-cache.php',
            CNK_INC . 'class-cnk-template-loader.php',
            CNK_INC . 'class-cnk-ajax.php',
            CNK_INC . 'class-cnk-scss.php',
            CNK_INC . 'shortcodes/class-cnk-shortcode-contact.php',
        );

        foreach ($core_files as $file) {
            if (file_exists($file)) {
                require_once $file;
            }
        }

        // Admin
        if (file_exists(CNK_ADMIN . 'class-cnk-admin.php')) {
            require_once CNK_ADMIN . 'class-cnk-admin.php';
        }
    }

    /**
     * Load các Modules mở rộng (Header/Footer Builder)
     */
    private static function load_modules()
    {
        if (! defined('CNK_MODULES')) return;

        $modules = array(
            //'header-footer/header-footer.php',
        );

        foreach ($modules as $module) {
            $file = CNK_MODULES . $module;
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }

    /**
     * Load Elementor Widgets + Integration
     */
    public static function maybe_load_elementor()
    {
        // Elementor chưa load → không làm gì
        if (!class_exists('\Elementor\Plugin')) return;

        // Elementor Integration
        $efile = CNK_INC . 'class-cnk-elementor.php';
        if (file_exists($efile)) {
            require_once $efile;
        }

        // Base Widget
        $base = CNK_INC . 'class-cnk-base-widget.php';
        if (file_exists($base)) {
            require_once $base;
        }

        // Load Widgets
        if (is_dir(CNK_WIDGETS)) {
            foreach (glob(CNK_WIDGETS . '*/class-cnk-widget-*.php') as $path) {
                require_once $path;
            }
        }
    }
}
