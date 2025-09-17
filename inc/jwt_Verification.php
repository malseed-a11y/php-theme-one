<?php
// التحقق من وجود ملف autoload.php وتضمينه إذا كان موجوداً
if (file_exists(get_template_directory() . '/vendor/autoload.php')) {
    require_once get_template_directory() . '/vendor/autoload.php';
}

// استيراد المكتبات اللازمة للتعامل مع JWT
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// ===========================
// JWT Login Endpoint
// ===========================

// تسجيل نقطة نهاية جديدة للتعامل مع تسجيل الدخول
add_action('rest_api_init', function () {
    register_rest_route('trips/v1', '/login', [
        'methods' => 'POST', // تحديد طريقة الطلب كـ POST
        'callback' => 'trips_login_endpoint', // تحديد الدالة التي ستعالج الطلب
        'permission_callback' => '__return_true' // السماح لأي شخص بالوصول إلى نقطة النهاية
    ]);
});

// دالة معالجة طلب تسجيل الدخول
function trips_login_endpoint($request)
{
    // استخراج بيانات المستخدم من الطلب
    $params = $request->get_json_params();
    $username = $params['username'] ?? '';
    $password = $params['password'] ?? '';

    // التحقق من صحة بيانات المستخدم
    $user = wp_authenticate($username, $password);
    //FIXME - check if the user is admin or editor

    // التحقق من أن المستخدم لديه صلاحيات مدير أو محرر
    if (!in_array('administrator', (array) $user->roles) && !in_array('editor', (array) $user->roles)) {
        return new WP_Error('invalid', 'You must be an admin or editor to access this endpoint', ['status' => 401]);
    }

    // التحقق من عدم وجود أخطاء في عملية المصادقة
    if (is_wp_error($user)) {
        return new WP_Error('invalid', 'Invalid credentials', ['status' => 401]);
    }

    // المفتاح السري المستخدم لتشفير وفك تشفير التوكن
    $secret = "mLJ5@ovZ@KQ@QcDabFWJeWanxXSfyo8FVMNYmnSbNBfknm%MU#dieYiWvFvskRZ%";

    // تحديد وقت إصدار وانتهاء صلاحية التوكن
    $issued  = time();
    $expire  = $issued + 500; // 

    // إعداد البيانات التي سيتم تضمينها في التوكن
    $payload = [
        'iss' => get_bloginfo('url'), // الجهة المصدرة للتوكن
        'iat' => $issued, // وقت الإصدار
        'exp' => $expire, // وقت انتهاء الصلاحية
        'sub' => $user->ID // معرف المستخدم
    ];

    // إنشاء التوكن
    $token = JWT::encode($payload, $secret, 'HS256');

    // إرجاع التوكن وبيانات الصلاحية ومعرف المستخدم
    return [
        'token'   => $token,
        'expires' => $expire,
        'user_id' => $user->ID
    ];
}

// ===========================
// JWT Verification
// ===========================

// دالة للتحقق من صحة التوكن
function trips_verify_jwt($request)
{
    // استخراج التوكن من رأس الطلب
    $auth = $request->get_header('authorization') ?? '';

    // محاولة استخراج التوكن من رؤوس الطلب باستخدام دالة apache
    if (empty($auth) && function_exists('apache_request_headers')) {
        $hdrs = apache_request_headers();
        if (!empty($hdrs['Authorization'])) $auth = $hdrs['Authorization'];
    }

    // التحقق من أن التوكن يبدأ بكلمة Bearer
    if (strpos($auth, 'Bearer ') !== 0) return false;
    // إزالة كلمة Bearer من التوكن
    $token = trim(str_replace('Bearer ', '', $auth));

    // المفتاح السري للتحقق من صحة التوكن
    $secret = "mLJ5@ovZ@KQ@QcDabFWJeWanxXSfyo8FVMNYmnSbNBfknm%MU#dieYiWvFvskRZ%";

    try {
        // محاولة فك تشفير التوكن والتحقق من صحته
        $payload = JWT::decode($token, new Key($secret, 'HS256'));
    } catch (Exception $e) {
        return false;
    }

    // التحقق من وجود معرف المستخدم في التوكن
    if (empty($payload->sub)) return false;
    return true;
}

// ===========================
// Permission callback
// ===========================

// دالة للتحقق من صلاحيات الوصول
function trips_permission_required($request)
{
    // التحقق من صحة التوكن
    if (trips_verify_jwt($request)) return true;
    // إرجاع خطأ في حالة عدم وجود توكن صحيح
    return new WP_Error('rest_forbidden', 'Invalid or missing token', ['status' => 401]);
}
