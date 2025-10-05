<?php
/**
 * Minimal bootstrap for running PHPUnit against a local WP install.
 * 1) Adjust the path below if your WordPress root is different.
 * 2) Keep this file inside your plugin's /tests folder.
 */

// 👉 EDIT THIS PATH if your WordPress install is elsewhere.
$wp_load = 'C:/xampp/htdocs/wordpress/wp-load.php';

if ( ! file_exists( $wp_load ) ) {
    fwrite( STDERR, "Cannot find wp-load.php at: $wp_load\nEdit tests/bootstrap.php to match your local path.\n" );
    exit(1);
}

require $wp_load;

// Load the plugin file so its hooks are available.
if ( ! function_exists( 'sp_simple_places_shortcode' ) ) {
    require_once dirname(__DIR__) . '/simple-places.php';
}

