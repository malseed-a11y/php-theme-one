<?php

//==========================
// GET =====================
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



function get_single_trip($request)
{
    $request = $request->get_params();
    $id   = (int) $request['id'];
    $post = get_post($id);


    if (!$post || $post->post_type !== 'trip'  || $post->post_status === 'trash') {

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
// POST

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

    $id = wp_insert_post([
        'post_type'    => 'trip',
        'post_title'   => $title,
        'post_content' => $content,
        'post_status'  => 'publish',
    ]);


    if (!empty($params['categories'])) {

        $categories = array_map('sanitize_text_field', (array) $params['categories']);
        wp_set_object_terms($id, $categories, 'trip_category');
    }
    if (!empty($params['tags'])) {

        $tags = array_map('sanitize_text_field', (array) $params['tags']);
        wp_set_object_terms($id, $tags, 'trip_tag');
    }



    $new_post = get_post($id);

    $new_id = $new_post->ID;

    return 'secsses - the new post id:' . $new_id;
}

//==========================
// PUT

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
    $new_trip = new WP_REST_Request('GET', '/trips/v1/trips/' . $id);
    $new_trip->set_param('id', $id);
    $new_trip = get_single_trip($new_trip);

    require_once __DIR__ . '/WP_Rest_cache_P.php';
    clear_object_type_cache('/wordpress/wp-json/trips/v1/trips');

    return $new_trip;
}

//==========================
// DELETE
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


// function get_cache_key($req)
// {
//     $method = $req->get_method();
//     $route = $req->get_route();
//     $params = $req->get_params();

//     $base = strtoupper($method) . '-' . $route . '-' . json_encode($params);

//     return "wp_rest_cash_key_" . md5($base);
// }

// function get_cache($req)
// {
//     return get_transient(get_cache_key($req));
// }

// function set_cache($req, $value, $TTL)
// {

//     return set_transient(get_cache_key($req), $value, $TTL);
// }

// function delete_cache($req)
// {
//     return delete_transient(get_cache_key($req));
// }




// function register_cache_clear_hooks() {}


// function manual_clear()
// {
//     if (isset($_GET['clear'])) {
//         clear();
//         echo 'Cache cleared!';
//         exit;
//     }
// }
