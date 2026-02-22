<?php
// activate plugin
add_action('wp_enqueue_scripts', 'my_theme_enqueue_styles', 11);
function my_theme_enqueue_styles()
{
    wp_enqueue_style('child-style', get_stylesheet_uri());
}

// enable SVG support
function wps_mime_types( $mimes ){
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter( 'upload_mimes', 'wps_mime_types' );