<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CNK_Widget_Slider extends CNK_Base_Widget {

    public function get_name() { return 'cnk_slider'; }
    public function get_title() { return __( 'CNK Slider', 'cnk' ); }
    public function get_icon() { return 'eicon-slider'; }
    public function get_categories() { return array( 'cnk-elements' ); }

    protected function register_controls() {
        $this->start_controls_section( 'section_slides', array( 'label' => __( 'Slides', 'cnk' ) ) );
        $this->add_control( 'cnk_slides', array(
            'label' => __( 'Slides (JSON demo)', 'cnk' ),
            'type'  => \Elementor\Controls_Manager::TEXTAREA,
            'default' => json_encode( array( array( 'title' => 'Slide 1' ), array( 'title' => 'Slide 2' ) ) ),
        ) );
        $this->end_controls_section();
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        $slides = json_decode( $s['cnk_slides'], true );
        CNK_Template_Loader::load( 'slider', 'default', array( 'slides' => $slides ) );
    }
}
