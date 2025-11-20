<?php
if (!defined('ABSPATH')) exit;

/**
 * CNK Shortcodes - Contact Info
 * Tạo các shortcode lấy dữ liệu từ CodeStar Framework
 */

class CNK_Shortcode_Contact
{

    public static function init()
    {

        add_shortcode('cnk_phone',           [__CLASS__, 'phone']);
        add_shortcode('cnk_phone_link',      [__CLASS__, 'phone_link']);
        add_shortcode('cnk_phone_display',   [__CLASS__, 'phone_display']);
        add_shortcode('cnk_zalo',            [__CLASS__, 'zalo']);
        add_shortcode('cnk_zalo_link',       [__CLASS__, 'zalo_link']);
        add_shortcode('cnk_email',           [__CLASS__, 'email']);
        add_shortcode('cnk_address',         [__CLASS__, 'address']);
        add_shortcode('cnk_facebook',        [__CLASS__, 'facebook']);
    }

    /** Lấy settings từ CSF */
    private static function get($key)
    {
        $opts = get_option('cnk_settings', []);
        return $opts[$key] ?? '';
    }

    public static function phone()
    {
        return esc_html(self::get('contact_phone'));
    }

    public static function phone_link()
    {
        $phone = self::get('contact_phone');
        if (!$phone) return '';
        $clean = preg_replace('/(?!^\+)[^\d]/', '', $phone);
        return 'tel:' . esc_attr($clean);
    }

    public static function zalo()
    {
        return esc_html(self::get('contact_zalo'));
    }

    public static function zalo_link()
    {
        $zalo = self::get('contact_zalo');
        if (!$zalo) return '';
        $clean = preg_replace('/(?!^\+)[^\d]/', '',  $zalo);
        return 'https://zalo.me/' . esc_attr($clean);
    }

    public static function email()
    {
        return esc_html(self::get('contact_email'));
    }

    public static function address()
    {
        return nl2br(esc_html(self::get('contact_address')));
    }

    public static function facebook()
    {
        $fb = self::get('contact_facebook');
        return esc_url($fb);
    }
}

CNK_Shortcode_Contact::init();
