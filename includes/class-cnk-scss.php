<?php

/**
 * Class: CNK_SCSS
 * Version: 3.0.0 (Final — Support CSF options + main.min.css + optimize)
 *
 * ✔ Giữ nguyên toàn bộ logic gốc 2.2.0
 * ✔ Tối ưu hiệu năng
 * ✔ Hỗ trợ CodeStar Framework (CSF)
 * ✔ Đường dẫn SCSS nhập từ Settings (dynamic)
 * ✔ Build main.css & main.min.css
 */

if (! defined('ABSPATH')) exit;

use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\OutputStyle;

class CNK_SCSS
{
    const OPTION_LAST_MTIME    = 'cnk_scss_last_build_mtime';
    const TRANSIENT_LAST_CHECK = 'cnk_scss_last_check_ts';

    const ACTION_COMPILE = 'cnk_compile_scss';
    const CHECK_INTERVAL = 1;


    /**
     * INIT
     */
    public static function init()
    {
        // 🔹 Admin nút Compile ngay (POST)
        add_action('admin_post_' . self::ACTION_COMPILE, [__CLASS__, 'admin_compile_action']);

        // 🔹 Auto compile khi load front-end / admin
        add_action('wp_enqueue_scripts',    [__CLASS__, 'maybe_compile_on_enqueue'], 1);
        add_action('admin_enqueue_scripts', [__CLASS__, 'maybe_compile_on_enqueue'], 1);
    }


    /**
     * ============================================================
     *  HANDLE: Nút "Compile SCSS ngay" trong trang Settings
     * ============================================================
     */
    public static function admin_compile_action()
    {
        if (! wp_verify_nonce($_POST['_wpnonce'] ?? '', 'cnk_compile_scss_nonce')) {
            wp_die('Xác thực không hợp lệ.');
        }

        // Đọc từ CodeStar Framework
        $opts = get_option('cnk_settings', []);

        if (empty($opts['enable_scss'])) {
            wp_safe_redirect(wp_get_referer());
            exit;
        }

        self::compile_all_to_theme(true);

        wp_safe_redirect(wp_get_referer());
        exit;
    }


    /**
     * ============================================================
     *  AUTO COMPILE — chỉ chạy khi Dev Mode bật
     * ============================================================
     */
    public static function maybe_compile_on_enqueue()
    {
        $opts = get_option('cnk_settings', []);

        if (empty($opts['enable_scss'])) return;

        $last = get_transient(self::TRANSIENT_LAST_CHECK);
        if ($last && time() - $last < self::CHECK_INTERVAL) return;

        set_transient(self::TRANSIENT_LAST_CHECK, time(), 5);

        self::compile_all_to_theme();
    }



    /**
     * ============================================================
     *  COMPILE ALL → main.css + main.min.css
     * ============================================================
     */
    public static function compile_all_to_theme($force = false)
    {
        $theme = get_stylesheet_directory();

        // Đọc từ CodeStar Framework
        $opts   = get_option('cnk_settings', []);

        $input  = !empty($opts['scss_input'])  ? ltrim($opts['scss_input'],  "/\\") : 'assets/scss/main.scss';
        $output = !empty($opts['scss_output']) ? ltrim($opts['scss_output'], "/\\") : 'assets/css/main.css';

        $entry = $theme . '/' . $input;
        $output_main = $theme . '/' . $output;

        // File min
        $output_min = preg_replace('/\.css$/', '.min.css', $output_main);

        if (! file_exists($entry)) {
            error_log('[CNK_SCSS] Không tìm thấy file SCSS: ' . $entry);
            return false;
        }

        $scss_dir    = dirname($entry);
        $latest_mtime = self::get_latest_mtime($scss_dir);
        $saved_mtime  = intval(get_option(self::OPTION_LAST_MTIME, 0));

        if (! $force && $latest_mtime <= $saved_mtime) return false;

        // Build file thường
        self::compile_file($entry, $output_main, false);

        // Build file min
        self::compile_file($entry, $output_min, true);

        update_option(self::OPTION_LAST_MTIME, $latest_mtime);
        return true;
    }



    /**
     * ============================================================
     *  COMPILE FILE
     * ============================================================
     */
    protected static function compile_file($entry, $output, $is_min = false)
    {
        if (! class_exists('\ScssPhp\ScssPhp\Compiler')) {

            // Check vendor autoload
            $autoload = CNK_PATH . 'vendor/autoload.php';

            if (file_exists($autoload)) {
                require_once $autoload;
            }
        }

        if (! class_exists('\ScssPhp\ScssPhp\Compiler')) {
            error_log('[CNK_SCSS] ScssPhp không tồn tại.');
            return false;
        }

        try {
            $compiler = new Compiler();

            $compiler->setImportPaths(dirname($entry));

            $compiler->setOutputStyle(
                $is_min ? OutputStyle::COMPRESSED : OutputStyle::EXPANDED
            );

            $css_output = $compiler->compileString(
                file_get_contents($entry)
            )->getCss();

            // Tạo folder nếu chưa có
            if (! file_exists(dirname($output))) {
                wp_mkdir_p(dirname($output));
            }

            file_put_contents($output, $css_output);

            return true;
        } catch (Exception $e) {

            error_log('[CNK_SCSS ERROR] ' . $e->getMessage());
            return false;
        }
    }



    /**
     * Lấy mtime mới nhất trong folder
     */
    protected static function get_latest_mtime($dir)
    {
        if (! is_dir($dir)) return 0;

        $latest = 0;

        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($it as $file) {
            if ($file->isFile()) {
                $mtime = $file->getMTime();
                if ($mtime > $latest) {
                    $latest = $mtime;
                }
            }
        }

        return $latest;
    }
}

CNK_SCSS::init();
