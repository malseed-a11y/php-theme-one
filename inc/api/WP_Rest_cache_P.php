<?php

//============================
// WP Rest Cache

if (class_exists('\WP_REST_Cache_Plugin\Includes\Caching\Caching')) {


    //add enidpoint
    function wprc_add_posts_endpoint($allowed_endpoints)
    {

        if (! isset($allowed_endpoints['trips/v1'])) {

            $allowed_endpoints['trips/v1'] = [];
        }
        $allowed_endpoints['trips/v1'][] = 'trips';
        $allowed_endpoints['trips/v1'][] = 'categories';
        $allowed_endpoints['trips/v1'][] = 'tags';
        $allowed_endpoints['trips/v1'][] = '/trips/(?P<id>\d+)';

        return $allowed_endpoints;
    }
    add_filter('wp_rest_cache/allowed_endpoints', 'wprc_add_posts_endpoint', 10, 1);


    function clear_all_cache()
    {
        $object = \WP_REST_Cache_Plugin\Includes\Caching\Caching::get_instance();
        $clear = $object->clear_caches(true);
        return $clear;
    }


    function clear_object_type_cache($endpoint, $strictness = \WP_REST_Cache_Plugin\Includes\Caching\Caching::FLUSH_LOOSE, $force = true)
    {

        $object = \WP_REST_Cache_Plugin\Includes\Caching\Caching::get_instance();
        $clear = $object->delete_cache_by_endpoint($endpoint, $strictness, $force);
        return $clear;
    }
};
