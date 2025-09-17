<?php

/**
 * Plugin Name: Trips CPT + JWT REST API
 * Description: Registers Trips CPT, taxonomies, and JWT-protected REST API endpoints.
 * Version: 1.0
 * Author: ChatGPT
 */

// منع الوصول المباشر
if (!defined('ABSPATH')) exit;

/**
 * قبل الاستخدام: ضع في wp-config.php:
 * define('TRIPS_JWT_SECRET', 'مفتاح_طويل_وعشوائي_جدا');
 */

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



// ===========================
// JWT Authentication
// ===========================
require_once __DIR__ . '/jwt_Verification.php';



/**
 * ================================
 * REST API Endpoints for Trips CPT
 * ================================
 *
 * هذا الملف يحتوي على:
 * - تعريف المسارات الخاصة بالـ REST API
 * - دوال CRUD للرحلات (Trips)
 * - دوال إدارة التصنيفات (Categories) والوسوم (Tags)
 */

// تأكد أن الكود يعمل فقط داخل ووردبريس
if (!defined('ABSPATH')) exit;

/**
 * تسجيل جميع مسارات REST API الخاصة بالرحلات
 */
add_action('rest_api_init', 'trips_register_rest_routes');

function trips_register_rest_routes()
{
    /**
     * المسار: /trips/v1/trips
     * العمليات: 
     *   - GET: جلب جميع الرحلات (عام)
     *   - POST: إنشاء رحلة جديدة (محمية)
     */
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

    /**
     * المسار: /trips/v1/trips/{id}
     * العمليات: 
     *   - GET: جلب رحلة واحدة
     *   - PUT: تحديث رحلة
     *   - DELETE: حذف رحلة
     */
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

    /**
     * المسار: /trips/v1/categories
     * العمليات:
     *   - GET: جلب جميع التصنيفات
     *   - POST: إنشاء تصنيف جديد
     */
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

    /**
     * المسار: /trips/v1/tags
     * العمليات:
     *   - GET: جلب جميع الوسوم
     *   - POST: إنشاء وسم جديد
     */
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

// ===========================
// Endpoint Functions
// ===========================

/**
 * جلب جميع الرحلات (مع التصنيفات والوسوم)
 */
function get_all_trips()
{
    $query   = new WP_Query(['post_type' => 'trip', 'posts_per_page' => 20]);
    $results = [];

    while ($query->have_posts()) {
        $query->the_post();
        $results[] = array(
            'id'         => get_the_ID(),
            'title'      => get_the_title(),
            'content'    => get_the_content(),
            'categories' => wp_get_post_terms(get_the_ID(), 'trip_category', ['fields' => 'names']),
            'tags'       => wp_get_post_terms(get_the_ID(), 'trip_tag', ['fields' => 'names']),
        );
    }

    wp_reset_postdata(); // إعادة الحالة لما قبل الاستعلام
    return $results;
}

/**
 * جلب رحلة واحدة بالـ ID
 */
function get_single_trip($request)
{
    $id   = (int) $request['id'];
    $post = get_post($id);

    if (!$post || $post->post_type !== 'trip') {
        return new WP_Error('not_found', 'Trip not found', ['status' => 404]);
    }

    return array(
        'id'         => $post->ID,
        'title'      => $post->post_title,
        'content'    => $post->post_content,
        'categories' => wp_get_post_terms($post->ID, 'trip_category', ['fields' => 'names']),
        'tags'       => wp_get_post_terms($post->ID, 'trip_tag', ['fields' => 'names']),
    );
}

/**
 * إنشاء رحلة جديدة
 */
function create_trip($request)
{
    $params  = $request->get_json_params();
    $post_id = wp_insert_post([
        'post_type'    => 'trip',
        'post_title'   => sanitize_text_field($params['title'] ?? ''), // تنظيف العنوان
        'post_content' => wp_kses_post($params['content'] ?? ''),       // السماح فقط بأكواد HTML آمنة
        'post_status'  => 'publish',
    ]);

    // ربط التصنيفات والوسوم إذا أرسلت
    if (!empty($params['categories'])) {
        wp_set_object_terms($post_id, $params['categories'], 'trip_category');
    }
    if (!empty($params['tags'])) {
        wp_set_object_terms($post_id, $params['tags'], 'trip_tag');
    }

    return get_single_trip(new WP_REST_Request('GET', '/trips/v1/trips/' . $post_id));
}

/**
 * تحديث رحلة موجودة
 */
function update_trip($request)
{
    $id   = (int) $request['id'];
    $post = get_post($id);

    if (!$post || $post->post_type !== 'trip') {
        return new WP_Error('not_found', 'Trip not found', ['status' => 404]);
    }

    $params = $request->get_json_params();
    $update = ['ID' => $id];

    // تحديث الحقول إذا أرسلت
    if (isset($params['title'])) {
        $update['post_title'] = sanitize_text_field($params['title']);
    }
    if (isset($params['content'])) {
        $update['post_content'] = wp_kses_post($params['content']);
    }
    wp_update_post($update);

    // تحقق من التصنيفات والوسوم وإذا غير موجودة يتم إنشاؤها
    if (!empty($params['categories'])) {
        foreach ($params['categories'] as $category) {
            if (!term_exists($category, 'trip_category')) {
                create_trip_category_internal($category);
            }
        }
        wp_set_object_terms($id, $params['categories'], 'trip_category');
    }

    if (!empty($params['tags'])) {
        foreach ($params['tags'] as $tag) {
            if (!term_exists($tag, 'trip_tag')) {
                create_trip_tag_internal($tag);
            }
        }
        wp_set_object_terms($id, $params['tags'], 'trip_tag');
    }

    return get_single_trip(new WP_REST_Request('GET', '/trips/v1/trips/' . $id));
}

/**
 * حذف رحلة (نقلها إلى سلة المهملات)
 */
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
// Categories
// ===========================

/**
 * جلب جميع التصنيفات
 */
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

/**
 * إنشاء تصنيف جديد (مساعد داخلي)
 */
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

/**
 * إنشاء تصنيف جديد (للواجهة البرمجية)
 */
function create_trip_category($request)
{
    $params = $request->get_json_params();
    return create_trip_category_internal($params['name'] ?? '');
}

// ===========================
// Tags
// ===========================

/**
 * إنشاء وسم جديد (مساعد داخلي)
 */
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

/**
 * إنشاء وسم جديد (للواجهة البرمجية)
 */
function create_trip_tag($request)
{
    $params = $request->get_json_params();
    return create_trip_tag_internal($params['name'] ?? '');
}

/**
 * جلب جميع الوسوم
 */
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
