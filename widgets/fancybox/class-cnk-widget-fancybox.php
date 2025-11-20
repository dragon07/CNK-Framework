<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CNK_Widget_Fancybox extends CNK_Base_Widget {

    public function get_name() { return 'cnk_fancybox'; }
    public function get_title() { return __( 'CNK Fancybox', 'cnk' ); }
    public function get_icon() { return 'eicon-image-hotspot'; }
    public function get_categories() { return array( 'cnk-elements' ); }

    protected function register_controls() {
        $this->start_controls_section( 'section_content', array( 'label' => __( 'Nội dung', 'cnk' ) ) );
        $this->add_control( 'title', array(
            'label' => __( 'Tiêu đề', 'cnk' ),
            'type'  => \Elementor\Controls_Manager::TEXT,
            'default' => __( 'Tiêu đề Fancybox', 'cnk' ),
        ) );
        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        CNK_Template_Loader::load( 'fancybox', isset( $settings['template_style'] ) ? $settings['template_style'] : 'default', array( 'settings' => $settings ) );
    }
}
