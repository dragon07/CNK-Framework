<?php
if ( ! defined( 'ABSPATH' ) ) exit;

trait CNK_Template_Selector {
    protected function register_template_selector_control( $templates = array() ) {
        $default = array( 'default' => __( 'Default', 'cnk' ) );
        $opts = array_merge( $default, (array) $templates );
        $this->add_control( 'template_style', array(
            'label' => __( 'Template', 'cnk' ),
            'type'  => \Elementor\Controls_Manager::SELECT,
            'options' => $opts,
            'default' => 'default',
        ) );
    }
}
