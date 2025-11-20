<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CNK_Widget_Clients extends CNK_Base_Widget {

    public function get_name() { return 'cnk_clients'; }
    public function get_title() { return __( 'CNK Clients', 'cnk' ); }
    public function get_icon() { return 'eicon-image-box'; }
    public function get_categories() { return array( 'cnk-elements' ); }

    protected function register_controls() {
        $this->start_controls_section( 'section_content', array( 'label' => __( 'Settings', 'cnk' ) ) );
        $this->add_control( 'show_count', array(
            'label' => __( 'Show Count', 'cnk' ),
            'type'  => \Elementor\Controls_Manager::NUMBER,
            'default' => 3,
        ) );
        $this->end_controls_section();
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        CNK_Template_Loader::load( 'clients', 'default', array( 'settings' => $s ) );
    }
}
