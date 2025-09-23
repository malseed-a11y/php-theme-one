<?php


// ===========================
//  JWT Helpers
// ===========================


if (!function_exists('trips_base64url_encode')) {

    function trips_base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

if (!function_exists('trips_base64url_decode')) {

    function trips_base64url_decode($data)
    {
        $remainder = strlen($data) % 4;
        if ($remainder) $data .= str_repeat('=', 4 - $remainder);
        return base64_decode(strtr($data, '-_', '+/'));
    }
}

if (!function_exists('trips_jwt_encode')) {

    function trips_jwt_encode($payload, $secret)
    {
        $header = array('typ' => 'JWT', 'alg' => 'HS256');
        $segments = array(
            trips_base64url_encode(json_encode($header)),
            trips_base64url_encode(json_encode($payload))
        );
        $signing_input = implode('.', $segments);
        $signature = hash_hmac('sha256', $signing_input, $secret, true);
        $segments[] = trips_base64url_encode($signature);
        return implode('.', $segments);
    }
}

if (!function_exists('trips_jwt_decode')) {

    function trips_jwt_decode($jwt, $secret)
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) return false;
        list($headb64, $payloadb64, $sigb64) = $parts;
        $signature = trips_base64url_decode($sigb64);
        $verified_sig = hash_hmac('sha256', $headb64 . '.' . $payloadb64, $secret, true);
        if (!hash_equals($verified_sig, $signature)) return false;
        $payload = json_decode(trips_base64url_decode($payloadb64), true);
        if (isset($payload['exp']) && time() > intval($payload['exp'])) return false;
        return $payload;
    }
}

// ===========================
//  JWT Login Endpoint
// ===========================


add_action('rest_api_init', function () {
    register_rest_route('trips/v1', '/login', array(
        'methods' => 'POST',
        'callback' => 'trips_login_endpoint',
        'permission_callback' => '__return_true'
    ));
});


function trips_login_endpoint($request)
{
    $params = $request->get_json_params();
    $username = $params['username'] ?? '';
    $password = $params['password'] ?? '';
    $user = wp_authenticate($username, $password);
    if (is_wp_error($user)) return new WP_Error('invalid', 'Invalid credentials', array('status' => 401));

    $secret = defined('TRIPS_JWT_SECRET_KEY') ? "TRIPS_JWT_SECRET_KEY" : "6xXt.z4f:H]9te<lR6Y3VeeOfA(YhL6E06Ps{J!1NsbCtf4t(ip!?&2V(QHlrRU:";


    $issued = time();
    $expire = $issued + 3600;
    $payload = array(
        'iss' => get_bloginfo('url'),
        'iat' => $issued,
        'exp' => $expire,
        'sub' => $user->ID
    );
    $token = trips_jwt_encode($payload, $secret);

    return array('token' => $token, 'expires' => $expire, 'user_id' => $user->ID);
}

// ===========================
//  JWT Verification
// ===========================


function trips_verify_jwt($request)
{
    $auth = $request->get_header('authorization') ?? '';
    if (empty($auth) && isset($_SERVER['HTTP_AUTHORIZATION'])) $auth = $_SERVER['HTTP_AUTHORIZATION'];
    if (empty($auth) && function_exists('apache_request_headers')) {
        $hdrs = apache_request_headers();
        if (!empty($hdrs['Authorization'])) $auth = $hdrs['Authorization'];
    }

    if (strpos($auth, 'Bearer ') === 0) $token = trim(str_replace('Bearer ', '', $auth));
    else return false;

    $secret = defined('TRIPS_JWT_SECRET_KEY')   ? "TRIPS_JWT_SECRET_KEY" : "6xXt.z4f:H]9te<lR6Y3VeeOfA(YhL6E06Ps{J!1NsbCtf4t(ip!?&2V(QHlrRU:";
    $payload = trips_jwt_decode($token, $secret);

    if (!$payload || empty($payload['sub'])) return false;
    wp_set_current_user(intval($payload['sub']));
    return true;
}

// ===========================
// Permission callback
// ===========================


function trips_permission_required($request)
{
    if (trips_verify_jwt($request)) return true;
    return new WP_Error('rest_forbidden', 'Invalid or missing token', array('status' => 401));
}
