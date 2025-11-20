<?php
if (! defined('ABSPATH')) exit;

/**
 * ============================================================
 *  CNK_Helper_AJAX
 *  ------------------------------------------------------------
 *  Chức năng:
 *  - Cung cấp các hàm tiện ích dùng chung cho mọi module AJAX
 *  - Chuẩn hóa xử lý dữ liệu, phản hồi JSON, và bảo mật nonce
 *  - Giảm trùng lặp giữa các file AJAX (vd: Post, Slider, Gallery...)
 *  - Hỗ trợ auto-register hooks cho cả logged-in & guest users
 * ============================================================
 */

class CNK_Helper_AJAX
{

    /**
     * ============================================================
     * Sinh tự động tên AJAX action từ class + method
     * ------------------------------------------------------------
     * - Tự động chuyển class CNK_Widget_GetPost → module: getpost
     * - Method load_more → action: load_more
     * - Kết quả: cnk_getpost_load_more
     * ============================================================
     */
    public static function get_action_from_class($class, $method = '')
    {
        if (! $class) return 'cnk_undefined_action';

        // Loại bỏ prefix CNK_, CNK_Widget_, CNK_AJAX_
        $module = strtolower(preg_replace('/^cnk_(widget_|ajax_)?/i', '', $class));
        $module = sanitize_key(str_replace('_', '-', $module));

        $method = sanitize_key($method ?: 'action');

        return 'cnk_' . $module . '_' . $method;
    }

    /**
     * ============================================================
     * Đăng ký AJAX Hook tự động (cho cả login & guest)
     * ------------------------------------------------------------
     * Thay vì viết 2 dòng add_action(), chỉ cần gọi:
     * CNK_Helper_AJAX::register_action( 'cnk_action', [ __CLASS__, 'callback' ] );
     * ============================================================
     *
     * @param string   $action   Tên action (không có prefix wp_ajax_)
     * @param callable $callback Hàm xử lý callback
     */
    public static function register_action($action, $callback)
    {
        if (empty($action) || ! is_callable($callback)) {
            self::log(['action' => $action, 'callback' => $callback], 'Invalid register_action');
            return;
        }

        add_action("wp_ajax_{$action}", $callback);
        add_action("wp_ajax_nopriv_{$action}", $callback);

        // Ghi log khi WP_DEBUG bật
        self::log("Registered AJAX: {$action}", 'HOOK');
    }

    /**
     * ============================================================
     * Kiểm tra và xác thực AJAX request
     * ------------------------------------------------------------
     * - Kiểm tra header, action và nonce (nếu có)
     * - Nếu không hợp lệ sẽ dừng và trả lỗi JSON
     * ============================================================
     *
     * @param string $nonce_action   Tên action để kiểm tra nonce
     * @param string $nonce_field    Tên field nonce (mặc định: 'nonce')
     * @param bool   $die_on_fail    Có dừng request khi sai không (mặc định: true)
     * @return bool
     */
    public static function verify_request($nonce_action = 'cnk_ajax_action', $nonce_field = 'nonce', $die_on_fail = true)
    {

        // Kiểm tra header AJAX
        if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            if ($die_on_fail) {
                self::send_error(['message' => 'Invalid AJAX request (Header mismatch).']);
            }
            return false;
        }

        // Kiểm tra nonce nếu có
        if (isset($_POST[$nonce_field])) {
            $nonce = sanitize_text_field($_POST[$nonce_field]);
            if (! wp_verify_nonce($nonce, $nonce_action)) {
                if ($die_on_fail) {
                    self::send_error(['message' => 'Security check failed (invalid nonce).']);
                }
                return false;
            }
        }

        return true;
    }

    /**
     * ============================================================
     * Gửi phản hồi JSON thành công (chuẩn CNK)
     * ============================================================
     *
     * @param array $data Mảng dữ liệu trả về
     */
    public static function send_success($data)
    {
        wp_send_json_success($data);
    }

    /**
     * ============================================================
     * Gửi phản hồi JSON thất bại (chuẩn CNK)
     * ============================================================
     *
     * @param array $data Mảng thông tin lỗi
     */
    public static function send_error($data = array())
    {
        $response = array(
            'success' => false,
            'data'    => $data,
        );
        wp_send_json($response);
    }

    /**
     * ============================================================
     * Làm sạch dữ liệu mảng đệ quy (sanitize sâu)
     * ------------------------------------------------------------
     * - Áp dụng sanitize_text_field() cho từng chuỗi trong mảng
     * ============================================================
     *
     * @param mixed $input Dữ liệu đầu vào
     * @return mixed
     */
    public static function sanitize_recursive($input)
    {
        if (is_array($input)) {
            $sanitized = array();
            foreach ($input as $key => $value) {
                $sanitized[sanitize_text_field($key)] = self::sanitize_recursive($value);
            }
            return $sanitized;
        }
        return is_scalar($input) ? sanitize_text_field($input) : $input;
    }

    /**
     * ============================================================
     * Log thông tin AJAX vào file debug.log (chỉ khi WP_DEBUG = true)
     * ============================================================
     *
     * @param mixed  $data  Dữ liệu cần log
     * @param string $label Nhãn log
     */
    public static function log($data, $label = 'AJAX')
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $output = "\n[CNK {$label}] " . print_r($data, true) . "\n";
            error_log($output);
        }
    }

    /**
     * ============================================================
     * Hàm trả nhanh phản hồi lỗi nếu thiếu dữ liệu bắt buộc
     * ------------------------------------------------------------
     * @param array  $required_keys  Mảng key bắt buộc
     * @param array  $data           Dữ liệu nhận được
     * @return bool
     * ============================================================
     */
    public static function check_required_fields($required_keys = array(), $data = array())
    {
        foreach ($required_keys as $key) {
            if (! isset($data[$key]) || empty($data[$key])) {
                self::send_error(['message' => "Missing required field: {$key}"]);
                return false;
            }
        }
        return true;
    }
}
