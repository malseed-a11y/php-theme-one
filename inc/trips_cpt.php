<?php

/* ===========================
     Register Trips Custom Post Type
=========================== */
function register_trip_cpt()
{

    $labels = array(
        'name'                  => _x('Trips', 'Post Type General Name', 'textdomain'),
        'singular_name'         => _x('Trip', 'Post Type Singular Name', 'textdomain'),
        'menu_name'             => __('Trips', 'textdomain'),
        'name_admin_bar'        => __('Trip', 'textdomain'),
        'add_new_item'          => __('Add New Trip', 'textdomain'),
        'edit_item'             => __('Edit Trip', 'textdomain'),
        'all_items'             => __('All Trips', 'textdomain'),
        'view_item'             => __('View Trip', 'textdomain'),
        'search_items'          => __('Search Trips', 'textdomain'),
        'not_found'             => __('No trips found', 'textdomain'),
        'not_found_in_trash'    => __('No trips found in Trash', 'textdomain'),
    );

    $args = array(
        'labels'                => $labels,
        'description'           => 'Travel trips information',
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'query_var'             => false,
        'menu_icon'             => 'dashicons-palmtree',
        'supports'              => array('title', 'editor', 'thumbnail',  'excerpt', 'author'),
        'taxonomies'            => array('trip_category', 'trip_tag'),
        'has_archive'           => true,
        'show_in_rest'          => true,
        'publicly_queryable'    => true,
        'rewrite'               => array('slug' => 'trips'),
        'capability_type'       => 'post',
    );

    register_post_type('trip', $args);
}
add_action('init', 'register_trip_cpt');

/* ===========================
    Register Custom Taxonomies
=========================== */

function register_trip_taxonomies()
{

    // Trip Categories (hierarchical)
    $labels_cat = array(
        'name'              => _x('Trip Categories', 'taxonomy general name', 'textdomain'),
        'singular_name'     => _x('Trip Category', 'taxonomy singular name', 'textdomain'),
        'search_items'      => __('Search Trip Categories', 'textdomain'),
        'all_items'         => __('All Trip Categories', 'textdomain'),
        'edit_item'         => __('Edit Trip Category', 'textdomain'),
        'add_new_item'      => __('Add New Trip Category', 'textdomain'),
        'new_item_name'     => __('New Trip Category Name', 'textdomain'),
    );

    $args_cta = array(
        'hierarchical'      => true,
        'labels'            => $labels_cat,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'trip-category'),
    );
    register_taxonomy(
        'trip_category',
        'trip',
        $args_cta
    );


    $labels_tag = array(
        'name'              => _x('Trip Tags', 'taxonomy general name', 'textdomain'),
        'singular_name'     => _x('Trip Tag', 'taxonomy singular name', 'textdomain'),
        'search_items'      => __('Search Trip Tags', 'textdomain'),
        'all_items'         => __('All Trip Tags', 'textdomain'),
        'edit_item'         => __('Edit Trip Tag', 'textdomain'),
        'add_new_item'      => __('Add New Trip Tag', 'textdomain'),
        'new_item_name'     => __('New Trip Tag Name', 'textdomain'),
    );


    $args_tag = array(
        'hierarchical'      => false,
        'labels'            => $labels_tag,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'trip-tag'),
    );

    register_taxonomy(
        'trip_tag',
        'trip',
        $args_tag
    );
}
add_action('init', 'register_trip_taxonomies');


require_once(dirname(__FILE__) . '/meta_box_trips.php');
