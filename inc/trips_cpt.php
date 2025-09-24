<?php

/**
 * Plugin Name: Trips CPT + JWT REST API
 * Description: Registers Trips CPT, taxonomies, and JWT-protected REST API endpoints.
 * Version: 1.0
 * Author: ChatGPT
 */



//==========================
// Exit if accessed directly
if (!defined('ABSPATH')) exit;
//==========================




// ===========================
// JWT Authentication
require_once __DIR__ . '/jwt_Verification.php';
//============================






// ===========================
// CPT & Taxonomies
// ===========================
add_action('init', function () {

    // CPT Trips
    $labels = array(
        'name' => 'Trips',
        'singular_name' => 'Trip',
        'menu_name' => 'Trips',
        'add_new_item' => 'Add New Trip',
        'edit_item' => 'Edit Trip',
        'all_items' => 'All Trips',
        'view_item' => 'View Trip',
        'search_items' => 'Search Trips',
        'not_found' => 'No trips found',
        'not_found_in_trash' => 'No trips found in Trash',
    );
    register_post_type('trip', array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'show_in_rest' => true,
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'author'),
        'taxonomies' => array('trip_category', 'trip_tag'),
        'rewrite' => array('slug' => 'trips'),
    ));

    // Trip Categories
    register_taxonomy('trip_category', 'trip', array(
        'hierarchical' => true,
        'labels' => array(
            'name' => 'Trip Categories',
            'singular_name' => 'Trip Category',
            'search_items' => 'Search Trip Categories',
            'all_items' => 'All Trip Categories',
            'edit_item' => 'Edit Trip Category',
            'add_new_item' => 'Add New Trip Category'
        ),
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'trip-category')
    ));

    // Trip Tags
    register_taxonomy('trip_tag', 'trip', array(
        'hierarchical' => false,
        'labels' => array(
            'name' => 'Trip Tags',
            'singular_name' => 'Trip Tag',
            'search_items' => 'Search Trip Tags',
            'all_items' => 'All Trip Tags',
            'edit_item' => 'Edit Trip Tag',
            'add_new_item' => 'Add New Trip Tag'
        ),
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'trip-tag')
    ));
});





//==========================
// REST API Endpoints
//==========================

add_action('rest_api_init', 'trips_register_rest_routes');

function trips_register_rest_routes()
{

    $trip_namespace = 'trips/v1';

    register_rest_route($trip_namespace, '/trips', array(
        array(
            'methods'             => 'GET',
            'callback'            => 'get_all_trips',
            'permission_callback' => '__return_true'
        ),
        array(
            'methods'             => 'POST',
            'callback'            => 'create_trip',
            'permission_callback' => 'trips_permission_required'
        ),
    ));


    register_rest_route($trip_namespace, '/trips/(?P<id>\d+)', array(
        array(
            'methods'             => 'GET',
            'callback'            => 'get_single_trip',
            'permission_callback' => '__return_true'
        ),
        array(
            'methods'             => 'PUT',
            'callback'            => 'update_trip',
            'permission_callback' => 'trips_permission_required'
        ),
        array(
            'methods'             => 'DELETE',
            'callback'            => 'delete_trip',
            'permission_callback' => 'trips_permission_required'
        ),
    ));

    register_rest_route($trip_namespace, '/categories', array(
        array(
            'methods'             => 'GET',
            'callback'            => 'list_trip_categories',
            'permission_callback' => '__return_true'
        ),
        array(
            'methods'             => 'POST',
            'callback'            => 'create_trip_category',
            'permission_callback' => 'trips_permission_required'
        ),
    ));


    register_rest_route($trip_namespace, '/tags', array(
        array(
            'methods'             => 'GET',
            'callback'            => 'list_trip_tags',
            'permission_callback' => '__return_true'
        ),
        array(
            'methods'             => 'POST',
            'callback'            => 'create_trip_tag',
            'permission_callback' => 'trips_permission_required'
        ),
    ));
}



//==========================
// REST API Endpoints Functions
require_once __DIR__ . '/api/custom_endpoints.php';
//==========================


//==========================
// WP REST API Plugin integration
require_once __DIR__ . '/api/WP_Rest_cache_P.php';
//============================