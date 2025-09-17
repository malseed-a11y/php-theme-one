<?php


// ===========================
//  JWT Helpers
// ===========================

/*
 * دوال المساعدة للتشفير وفك التشفير base64url
 * تستخدم هذه الدوال لتحويل البيانات إلى صيغة آمنة للاستخدام في URLs
 */

if (!function_exists('trips_base64url_encode')) {

    function trips_base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

if (!function_exists('trips_base64url_decode')) {
    /*
     * دالة فك تشفير البيانات من صيغة base64url
     * تقوم بإضافة علامات = المطلوبة للتكملة
     * وإعادة الرموز الأصلية مثل + و /
     */
    function trips_base64url_decode($data)
    {
        $remainder = strlen($data) % 4;
        if ($remainder) $data .= str_repeat('=', 4 - $remainder);
        return base64_decode(strtr($data, '-_', '+/'));
    }
}

if (!function_exists('trips_jwt_encode')) {
    /*
     * دالة إنشاء توكن JWT
     * تقوم بإنشاء توكن يتكون من ثلاثة أجزاء:
     * 1. الترويسة: تحتوي على نوع التوكن وخوارزمية التشفير
     * 2. البيانات: تحتوي على المعلومات المراد تخزينها
     * 3. التوقيع: للتحقق من صحة التوكن
     */
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
    /*
     * دالة فك وتحقق من صحة توكن JWT
     * تقوم بالخطوات التالية:
     * 1. تقسيم التوكن إلى أجزائه الثلاثة
     * 2. التحقق من صحة التوقيع
     * 3. التحقق من صلاحية التوكن
     * 4. إرجاع البيانات المخزنة في التوكن
     */
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

/*
 * تسجيل نقطة نهاية REST API للتسجيل الدخول
 * تقوم بإنشاء مسار /trips/v1/login
 * تستقبل طلبات POST فقط
 * تتطلب اسم المستخدم وكلمة المرور
 */
add_action('rest_api_init', function () {
    register_rest_route('trips/v1', '/login', array(
        'methods' => 'POST',
        'callback' => 'trips_login_endpoint',
        'permission_callback' => '__return_true'
    ));
});

/*
 * معالج تسجيل الدخول
 * يقوم بالتحقق من بيانات المستخدم وإنشاء توكن JWT
 * الخطوات:
 * 1. التحقق من صحة بيانات الدخول
 * 2. إنشاء توكن يحتوي على معلومات المستخدم
 * 3. تحديد وقت انتهاء صلاحية التوكن (ساعة واحدة)
 */
function trips_login_endpoint($request)
{
    $params = $request->get_json_params();
    $username = $params['username'] ?? '';
    $password = $params['password'] ?? '';
    $user = wp_authenticate($username, $password);
    if (is_wp_error($user)) return new WP_Error('invalid', 'Invalid credentials', array('status' => 401));

    $secret = defined('TRIPS_JWT_SECRET_KEY') ? "TRIPS_JWT_SECRET_KEY" : "6xXt.z4f:H]9te<lR6Y3VeeOfA(YhL6E06Ps{J!1NsbCtf4t(ip!?&2V(QHlrRU:";
    // $secret = defined('TRIPS_JWT_SECRET_KEY', "6xXt.z4f:H]9te<lR6Y3VeeOfA(YhL6E06Ps{J!1NsbCtf4t(ip!?&2V(QHlrRU:");


    $issued = time();
    $expire = $issued + 3600;
    $payload = array(
        'iss' => get_bloginfo('url'),    // مصدر التوكن
        'iat' => $issued,                 // وقت الإصدار
        'exp' => $expire,                 // وقت انتهاء الصلاحية
        'sub' => $user->ID                // معرف المستخدم
    );
    $token = trips_jwt_encode($payload, $secret);

    return array('token' => $token, 'expires' => $expire, 'user_id' => $user->ID);
}

// ===========================
//  JWT Verification
// ===========================

/*
 * دالة التحقق من صحة توكن JWT
 * تقوم بالتحقق من الطلبات الواردة التي تحتوي على توكن:
 * 1. استخراج التوكن من ترويسة Authorization
 * 2. التحقق من صحة التوكن وصلاحيته
 * 3. تعيين المستخدم الحالي إذا كان التوكن صحيحاً
 */
function trips_verify_jwt($request)
{
    // محاولة الحصول على ترويسة Authorization بعدة طرق
    $auth = $request->get_header('authorization') ?? '';
    if (empty($auth) && isset($_SERVER['HTTP_AUTHORIZATION'])) $auth = $_SERVER['HTTP_AUTHORIZATION'];
    if (empty($auth) && function_exists('apache_request_headers')) {
        $hdrs = apache_request_headers();
        if (!empty($hdrs['Authorization'])) $auth = $hdrs['Authorization'];
    }

    // استخراج التوكن من نص Bearer
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

/*
 * دالة التحقق من الصلاحيات
 * تستخدم للتحقق من صلاحيات الوصول لنقاط النهاية المحمية
 * تتحقق من وجود وصحة توكن JWT
 * ترجع خطأ إذا كان التوكن غير صالح أو غير موجود
 */
function trips_permission_required($request)
{
    if (trips_verify_jwt($request)) return true;
    return new WP_Error('rest_forbidden', 'Invalid or missing token', array('status' => 401));
}
