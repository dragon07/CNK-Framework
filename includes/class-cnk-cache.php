<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * CNK_Cache - helper for transient caching
 */

class CNK_Cache {

    public static function get( $key ) {
        return get_transient( $key );
    }

    public static function set( $key, $value, $seconds = HOUR_IN_SECONDS ) {
        set_transient( $key, $value, $seconds );
    }

    public static function clear_group( $group ) {
        // simple placeholder - in real project maintain list of keys per group
    }
}
