<?php

if (file_exists(get_template_directory() . '/vendor/autoload.php')) {
    require_once get_template_directory() . '/vendor/autoload.php';
}

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// ===========================
// JWT Login Endpoint
// ===========================

add_action('rest_api_init', function () {
    register_rest_route('trips/v1', '/login', [
        'methods' => 'POST',
        'callback' => 'trips_login_endpoint',
        'permission_callback' => '__return_true'
    ]);
});

function trips_login_endpoint($request)
{
    $params = $request->get_json_params();
    $username = $params['username'] ?? '';
    $password = $params['password'] ?? '';

    $user = wp_authenticate($username, $password);


    if (!in_array('administrator', (array) $user->roles) && !in_array('editor', (array) $user->roles)) {
        return new WP_Error('invalid', 'You must be an admin or editor to access this endpoint', ['status' => 401]);
    }

    if (is_wp_error($user)) {
        return new WP_Error('invalid', 'Invalid credentials', ['status' => 401]);
    }

    $secret = "mLJ5@ovZ@KQ@QcDabFWJeWanxXSfyo8FVMNYmnSbNBfknm%MU#dieYiWvFvskRZ%";

    $issued  = time();
    $expire  = $issued + 3600;


    $payload = [
        'iss' => get_bloginfo('url'),
        'iat' => $issued,
        'exp' => $expire,
        'sub' => $user->ID
    ];

    $token = JWT::encode($payload, $secret, 'HS256');

    return [
        'token'   => $token,
        'expires' => $expire,
        'user_id' => $user->ID
    ];
}

// ===========================
// JWT Verification
// ===========================

function trips_verify_jwt($request)
{
    $auth = $request->get_header('authorization') ?? '';

    if (empty($auth) && function_exists('apache_request_headers')) {
        $hdrs = apache_request_headers();
        if (!empty($hdrs['Authorization'])) $auth = $hdrs['Authorization'];
    }

    if (strpos($auth, 'Bearer ') !== 0) return false;

    $token = trim(str_replace('Bearer ', '', $auth));

    $secret = "mLJ5@ovZ@KQ@QcDabFWJeWanxXSfyo8FVMNYmnSbNBfknm%MU#dieYiWvFvskRZ%";

    try {
        $payload = JWT::decode($token, new Key($secret, 'HS256'));
    } catch (Exception $e) {
        return false;
    }

    if (empty($payload->sub)) return false;
    return true;
}


// ===========================
// Permission callback
// ===========================

function trips_permission_required($request)
{
    if (trips_verify_jwt($request)) return true;
    return new WP_Error('rest_forbidden', 'Invalid or missing token', ['status' => 401]);
}
