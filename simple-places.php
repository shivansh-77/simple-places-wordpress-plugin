<?php
/**
 * Plugin Name: Simple Places
 * Description: The simplest possible demo plugin: adds a "Place" custom post type with Latitude/Longitude fields and a shortcode [simple_places] to list them.
 * Version: 1.0.1
 * Author: Shivansh Bajpai
 * License: GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * 1) Register "Place" custom post type.
 */
add_action( 'init', function () {
    register_post_type( 'simple_place', array(
        'labels' => array(
            'name'          => __( 'Places', 'simple-places' ),
            'singular_name' => __( 'Place', 'simple-places' ),
            'add_new_item'  => __( 'Add New Place', 'simple-places' ),
            'edit_item'     => __( 'Edit Place', 'simple-places' ),
        ),
        'public'       => true,
        'show_in_menu' => true,
        'supports'     => array( 'title', 'editor' ),
        'menu_icon'    => 'dashicons-location-alt',
    ) );
} );

/**
 * 2) Add a simple meta box for Latitude/Longitude.
 */
add_action( 'add_meta_boxes', function () {
    add_meta_box(
        'simple_places_meta',
        __( 'Location (Latitude / Longitude)', 'simple-places' ),
        function( $post ) {
            wp_nonce_field( 'simple_places_save', 'simple_places_nonce' );

            $lat = esc_attr( get_post_meta( $post->ID, 'sp_lat', true ) );
            $lng = esc_attr( get_post_meta( $post->ID, 'sp_lng', true ) );

            echo '<p><label>Latitude<br><input type="number" name="sp_lat" step="0.000001" value="' . $lat . '" style="width:100%"></label></p>';
            echo '<p><label>Longitude<br><input type="number" name="sp_lng" step="0.000001" value="' . $lng . '" style="width:100%"></label></p>';
        },
        'simple_place',
        'side'
    );
} );

/**
 * 3) Save the meta values when a Place is saved.
 */
add_action( 'save_post_simple_place', function ( $post_id ) {
    if ( ! isset( $_POST['simple_places_nonce'] ) || ! wp_verify_nonce( $_POST['simple_places_nonce'], 'simple_places_save' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( isset( $_POST['sp_lat'] ) ) {
        update_post_meta( $post_id, 'sp_lat', floatval( $_POST['sp_lat'] ) );
    }
    if ( isset( $_POST['sp_lng'] ) ) {
        update_post_meta( $post_id, 'sp_lng', floatval( $_POST['sp_lng'] ) );
    }
}, 10, 1 );

/**
 * 4) Register the shortcode on 'init' so it also works in PHPUnit/CLI.
 */
add_action( 'init', function () {
    add_shortcode( 'simple_places', 'sp_simple_places_shortcode' );
} );

/**
 * 5) Shortcode handler: [simple_places]
 */
function sp_simple_places_shortcode( $atts ) {
    $q = new WP_Query( array(
        'post_type'      => 'simple_place',
        'posts_per_page' => -1,
        'no_found_rows'  => true,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ) );

    if ( ! $q->have_posts() ) {
        return '<em>No places found. Add some from the admin.</em>';
    }

    $html = '<ul class="simple-places-list">';
    while ( $q->have_posts() ) {
        $q->the_post();
        $lat = esc_html( get_post_meta( get_the_ID(), 'sp_lat', true ) );
        $lng = esc_html( get_post_meta( get_the_ID(), 'sp_lng', true ) );
        $html .= '<li><strong>' . esc_html( get_the_title() ) . '</strong> — Lat: ' . $lat . ', Lng: ' . $lng . '</li>';
    }
    wp_reset_postdata();
    $html .= '</ul>';

    return $html;
}

/**
 * 6) Add a small inline CSS style (optional)
 */
add_action( 'wp_head', function() {
    echo '<style>
        .simple-places-list { list-style-type:none; padding:0; }
        .simple-places-list li { margin-bottom:8px; font-size:17px; line-height:1.6; }
        .simple-places-list li strong { color:#0073aa; }
    </style>';
});
