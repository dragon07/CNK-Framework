<?php if (!defined('ABSPATH')) exit;

$prefix = 'cnk_settings';

/**
 * Tạo trang menu Settings CNK Framework
 */
CSF::createOptions($prefix, array(
    'menu_title'      => 'CNK Framework',
    'menu_slug'       => 'cnk-framework',
    'menu_icon'       => 'dashicons-admin-generic',
    'framework_title' => 'CNK Framework',
    'menu_position'   => 60,
));

/**
 * TAB: Thông tin liên hệ
 */
/**
 * TAB: Thông tin liên hệ — UI Tối ưu
 */
CSF::createSection($prefix, array(
    'id'    => 'contact_tab',
    'title' => 'Liên hệ',
    'icon'  => 'fa fa-phone',

    'fields' => array(

        array(
            'type'  => 'subheading',
            'title' => 'Thông tin liên hệ',
        ),

        array(
            'id'    => 'contact_phone',
            'type'  => 'text',
            'title' => 'Số điện thoại liên hệ',
            'after' => '
                <p class="cnk-sc-title">
                    Shortcode: <code>[cnk_phone]</code><br>
                    Link gọi nhanh: <code>[cnk_phone_link]</code>
                </p>',
            'attributes' => ['placeholder' => '0987 654 321'],
        ),

        array(
            'id'    => 'contact_zalo',
            'type'  => 'text',
            'title' => 'Zalo',
            'after' => '
                <p class="cnk-sc-title">
                    Shortcode: <code>[cnk_zalo]</code><br>
                    Link chat: <code>[cnk_zalo_link]</code>
                </p>',
            'attributes' => ['placeholder' => '0987 654 321'],
        ),

        array(
            'id'    => 'contact_email',
            'type'  => 'text',
            'title' => 'Email',
            'after' => '<p class="cnk-sc-title">Shortcode: <code>[cnk_email]</code></p>',
            'attributes' => ['placeholder' => 'email@domain.com'],
        ),

        array(
            'id'    => 'contact_facebook',
            'type'  => 'text',
            'title' => 'Facebook',
            'after' => '<p class="cnk-sc-title">Shortcode: <code>[cnk_facebook]</code></p>',
            'attributes' => ['placeholder' => 'https://facebook.com/page'],
        ),

        array(
            'id'    => 'contact_address',
            'type'  => 'textarea',
            'title' => 'Địa chỉ',
            'after' => '
                <p class="cnk-sc-title">
                    Shortcode: <code>[cnk_address]</code>
                </p>',
            'attributes' => ['placeholder' => '123 Lê Lợi, Hà Nội'],
        ),
    ),
));

/**
 * TAB: SCSS Compiler
 */
CSF::createSection($prefix, array(
    'id'    => 'scss_tab',
    'title' => 'SCSS Compiler',
    'icon'  => 'fa fa-code',

    'fields' => array(
        array(
            'type'  => 'subheading',
            'title' => 'Cấu hình SCSS Compiler',
        ),

        array(
            'id'      => 'enable_scss',
            'type'    => 'switcher',
            'title'   => 'Bật SCSS Dev Mode',
            'desc'    => 'Tự động compile SCSS khi reload website.',
            'default' => false,
        ),

        array(
            'id'          => 'scss_input',
            'type'        => 'text',
            'title'       => 'File SCSS đầu vào',
            'default'     => 'assets/scss/main.scss',
            'placeholder' => 'assets/scss/main.scss',
            'desc'        => 'Đường dẫn tính từ theme.',
        ),

        array(
            'id'          => 'scss_output',
            'type'        => 'text',
            'title'       => 'File CSS đầu ra',
            'default'     => 'assets/css/main.css',
            'placeholder' => 'assets/css/main.css',
            'desc'        => 'SCSS Engine sẽ xuất ra cả main.css và main.min.css',
        ),

    ),
));