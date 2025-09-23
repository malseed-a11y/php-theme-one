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

    register_rest_route('trips/v1', '/trips', array(
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


    register_rest_route('trips/v1', '/trips/(?P<id>\d+)', array(
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

    register_rest_route('trips/v1', '/categories', array(
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


    register_rest_route('trips/v1', '/tags', array(
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

// ==================================================================================================================================



//============================
// Endpoint Functions
// ===========================


//==========================
// GET =====================
//==========================

function get_all_trips()
{
    $cache = get_transient('_wp_prfix_trips_');
    if (!$cache) {


        $query   = new WP_Query(['post_type' => 'trip', 'posts_per_page' => 20]);

        if ($query->have_posts() === false) {
            return new WP_Error('not_found', 'NO Trips Found', ['status' => 404]);
            exit;
        }
        $results = [];

        while ($query->have_posts()) {
            $query->the_post();
            $results[] = [
                'id'         => get_the_ID(),
                'title'      => get_the_title(),
                'content'    => get_the_content(),
                'categories' => wp_get_post_terms(get_the_ID(), 'trip_category', ['fields' => 'names']),
                'tags'       => wp_get_post_terms(get_the_ID(), 'trip_tag', ['fields' => 'names']),
            ];
        }

        wp_reset_postdata();

        set_transient('_wp_prfix_trips_', $results, 60 * HOUR_IN_SECONDS);
        $cache = get_transient('_wp_prfix_trips_');
    };
    return $cache;
};

//==========================
// GET id
//==========================



function get_single_trip($request)
{
    $id   = (int) $request['id'];
    $post = get_post($id);


    if (!$post || $post->post_type !== 'trip') {

        return new WP_Error('not_found', 'Trip not found', ['status' => 404]);
    }


    return [

        'id'         => $post->ID,
        'title'      => $post->post_title,
        'content'    => $post->post_content,
        'categories' => wp_get_post_terms($post->ID, 'trip_category', ['fields' => 'names']),
        'tags'       => wp_get_post_terms($post->ID, 'trip_tag', ['fields' => 'names']),
    ];
}

// ===========================
// POST=======================
// =========================== 

function create_trip($request)
{
    $params  = $request->get_json_params();

    $title = isset($params['title']) ? sanitize_text_field($params['title']) : '';
    $content = isset($params['content']) ? wp_kses_post($params['content']) : '';


    if (strlen($title) > 200) {
        return new WP_Error('invalid_title', 'Title is too long (max 200 characters)', ['status' => 400]);
    }

    if (strlen($content) > 10000) {
        return new WP_Error('invalid_content', 'Content is too long (max 10000 characters)', ['status' => 400]);
    }

    $post_id = wp_insert_post([
        'post_type'    => 'trip',
        'post_title'   => $title,
        'post_content' => $content,
        'post_status'  => 'publish',
    ]);


    if (!empty($params['categories'])) {

        $categories = array_map('sanitize_text_field', (array) $params['categories']);
        wp_set_object_terms($post_id, $categories, 'trip_category');
    }
    if (!empty($params['tags'])) {

        $tags = array_map('sanitize_text_field', (array) $params['tags']);
        wp_set_object_terms($post_id, $tags, 'trip_tag');
    }
    $new_post = get_post($post_id);

    return [
        'id'         => $new_post->ID,
        'title'      => $new_post->post_title,
        'content'    => $new_post->post_content,
        'categories' => wp_get_post_terms($post_id, 'trip_category', ['fields' => 'names']),
        'tags'       => wp_get_post_terms($post_id, 'trip_tag', ['fields' => 'names']),
    ];
}

//==========================
// PUT======================
//==========================

function update_trip($request)
{
    $id   = (int) $request['id'];
    $post = get_post($id);


    if (!$post || $post->post_type !== 'trip') {
        return new WP_Error('not_found', 'Trip not found', ['status' => 404]);
    }

    $params = $request->get_json_params();
    $update = ['ID' => $id];


    if (isset($params['title'])) {
        $title = sanitize_text_field($params['title']);
        if (strlen($title) > 200) {
            return new WP_Error('invalid_title', 'Title is too long (max 200 characters)', ['status' => 400]);
        }
        $update['post_title'] = $title;
    }
    if (isset($params['content'])) {
        $content = wp_kses_post($params['content']);
        if (strlen($content) > 10000) {
            return new WP_Error('invalid_content', 'Content is too long (max 10000 characters)', ['status' => 400]);
        }
        $update['post_content'] = $content;
    }
    wp_update_post($update);


    if (!empty($params['categories'])) {
        $categories = array_map('sanitize_text_field', (array) $params['categories']);
        foreach ($categories as $category) {
            if (!term_exists($category, 'trip_category')) {
                create_trip_category_internal($category);
            }
        }
        wp_set_object_terms($id, $categories, 'trip_category');
    }

    if (!empty($params['tags'])) {
        $tags = array_map('sanitize_text_field', (array) $params['tags']);
        foreach ($tags as $tag) {
            if (!term_exists($tag, 'trip_tag')) {
                create_trip_tag_internal($tag);
            }
        }
        wp_set_object_terms($id, $tags, 'trip_tag');
    }

    $updated_post = get_post($id);

    return [
        'id'         => $updated_post->ID,
        'title'      => $updated_post->post_title,
        'content'    => $updated_post->post_content,
        'categories' => wp_get_post_terms($id, 'trip_category', ['fields' => 'names']),
        'tags'       => wp_get_post_terms($id, 'trip_tag', ['fields' => 'names']),
    ];
}

//==========================
// DELETE===================
//==========================
function delete_trip($request)
{
    $id   = (int) $request['id'];
    $post = get_post($id);

    if (!$post || $post->post_type !== 'trip') {
        return new WP_Error('not_found', 'Trip not found', ['status' => 404]);
    }

    wp_trash_post($id);
    return ['deleted' => true, 'id' => $id];
}


// ===========================
// Categories=================
// ===========================


function list_trip_categories()
{
    $terms = get_terms(['taxonomy' => 'trip_category', 'hide_empty' => false]);
    $out   = [];

    foreach ($terms as $t) {
        $out[] = [
            'id'   => $t->term_id,
            'name' => $t->name,
            'slug' => $t->slug,
        ];
    }

    return $out;
}


//==========================
// create a category
//==========================

function create_trip_category_internal($name)
{
    $name = sanitize_text_field($name);

    if (empty($name)) {
        return new WP_Error('invalid', 'Name required', ['status' => 400]);
    }

    $res = wp_insert_term($name, 'trip_category');
    if (is_wp_error($res)) return $res;

    $term = get_term($res['term_id']);
    return ['id' => $term->term_id, 'name' => $term->name, 'slug' => $term->slug];
}


function create_trip_category($request)
{
    $params = $request->get_json_params();
    return create_trip_category_internal($params['name'] ?? '');
}

// ===========================


// ===========================
// Tags=======================
// ===========================


function list_trip_tags()
{
    $terms = get_terms(['taxonomy' => 'trip_tag', 'hide_empty' => false]);
    $out   = [];

    foreach ($terms as $t) {
        $out[] = [
            'id'   => $t->term_id,
            'name' => $t->name,
            'slug' => $t->slug,
        ];
    }

    return $out;
}


//==========================
// create a tag
//==========================

function create_trip_tag_internal($name)
{
    $name = sanitize_text_field($name);

    if (empty($name)) {
        return new WP_Error('invalid', 'Name required', ['status' => 400]);
    }

    $res = wp_insert_term($name, 'trip_tag');
    if (is_wp_error($res)) return $res;

    $term = get_term($res['term_id']);
    return ['id' => $term->term_id, 'name' => $term->name, 'slug' => $term->slug];
}


function create_trip_tag($request)
{
    $params = $request->get_json_params();
    return create_trip_tag_internal($params['name'] ?? '');
}






//===========================================================================================================================================
//============================
//============================
// Cashing
//============================


function get_cache_key($req)
{
    $method = $req->get_method();
    $route = $req->get_route();
    $params = $req->get_params();

    $base = strtoupper($method) . '-' . $route . '-' . json_encode($params);

    return "wp_rest_cash_key_" . md5($base);
}

function get_cache($req)
{
    return get_transient(get_cache_key($req));
}

function set_cache($req, $value, $TTL)
{

    return set_transient(get_cache_key($req), $value, $TTL);
}

function delete_cache($req)
{
    return delete_transient(get_cache_key($req));
}

function clear()
{
    global $wpdb;
    $prefix = is_multisite() ? "_site_transient_" : "_transient_";
    $like_main = $wpdb->esc_like($prefix . 'wp_rest_cash_key_' . '%');
    $like_timeout = $wpdb->esc_like($prefix . 'timeout_' . "wp_rest_cash_key_" . '%');

    $wpdb->query(
        "DELETE FROM $wpdb->options WHERE option_name LIKE %s OR option_name LIKE %s",
        $like_main,
        $like_timeout
    );
}


function register_cache_clear_hooks() {}


function manual_clear()
{
    if (isset($_GET['clear'])) {
        clear();
        echo 'Cache cleared!';
        exit;
    }
}
