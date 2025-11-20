<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * CNK_Base_Widget - base class for widgets
 */

if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
    // Elementor not active yet - do not fatal
    return;
}

if ( ! class_exists( 'CNK_Base_Widget' ) ) {
    abstract class CNK_Base_Widget extends \Elementor\Widget_Base {

        protected function render_template( $widget_slug, $template = 'default', $data = array() ) {
            if ( class_exists( 'CNK_Template_Loader' ) ) {
                CNK_Template_Loader::load( $widget_slug, $template, $data );
            }
        }

        protected function register_template_selector( $widget_slug, $extra = array() ) {
            $default = array( 'default' => __( 'Default', 'cnk' ) );
            $opts = array_merge( $default, (array) $extra );
            $this->add_control( 'template_style', array(
                'label' => __( 'Template', 'cnk' ),
                'type'  => \Elementor\Controls_Manager::SELECT,
                'options' => $opts,
                'default' => 'default',
            ) );
        }
    }
}
