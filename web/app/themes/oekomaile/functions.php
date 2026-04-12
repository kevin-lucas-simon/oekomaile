<?php

// activate plugin
add_action('wp_enqueue_scripts', 'my_theme_enqueue_styles', 11);
function my_theme_enqueue_styles()
{
    wp_enqueue_style('child-style', get_stylesheet_uri());
}

// enable SVG support
function wps_mime_types($mimes)
{
    $mimes['svg'] = 'image/svg+xml';

    return $mimes;
}
add_filter('upload_mimes', 'wps_mime_types');

// add custom post type
function contributor_custom_post_type()
{
    register_post_type('contributor',
        [
            'labels' => [
                'name' => __('Mitwirkende', 'oekomaile'),
                'singular_name' => __('Mitwirkende:r', 'oekomaile'),
            ],
            'public' => true,
            'has_archive' => true,
            'rewrite' => ['slug' => 'mitwirkende'],
            'show_in_rest' => true,
            'hierarchical' => true,
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'page-attributes'],
        ]
    );
}
add_action('init', 'contributor_custom_post_type');
