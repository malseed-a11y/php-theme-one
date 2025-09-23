<?php
add_action('admin_post_send_message_form', 'handle_send_message_form');


function handle_send_message_form()
{
    //  CSRF
    if (!isset($_POST['smf_nonce']) || !wp_verify_nonce($_POST['smf_nonce'], 'send_message_form')) {
        wp_die('Something fishy going on');
    }


    function validate_alpha($field)
    {
        return preg_match("/^[a-zA-Z ]*$/", $field);
    }


    function validate_length($field, $min = 1, $max = 255)
    {
        $len = mb_strlen($field);
        return ($len >= $min && $len <= $max);
    }

    $errors = [];

    $username   = $_POST['username'] ?? '';
    $email      = $_POST['email'] ?? '';
    $birthday   = $_POST['birthday'] ?? '';
    $title      = $_POST['title'] ?? '';
    $fav_food   = $_POST['fav_food'] ?? '';
    $message    = $_POST['message'] ?? '';
    $password = $_POST['password'] ?? '';


    if (!validate_length($password, 8, 50)) {
        $errors[] = 'Password must be 8-50 characters long';
    }


    $password_hash = wp_hash_password($password);

    if (!validate_alpha($username) || !validate_length($username, 2, 50)) {
        $errors[] = 'Username is not valid';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email is not valid';
    }


    if (!is_numeric($birthday) || !validate_length($birthday, 1, 100)) {
        $errors[] = 'Birthday is not valid';
    }

    if (!validate_alpha($title) || !validate_length($title, 1, 100)) {
        $errors[] = 'Title is not valid';
    }

    if (!validate_alpha($fav_food) || !validate_length($fav_food, 1, 50)) {
        $errors[] = 'Favourite food is not valid';
    }

    if (!validate_alpha($message) || !validate_length($message, 1, 500)) {
        $errors[] = 'Message is not valid';
    }


    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo '<span>' . esc_html($error) . '</span><br>';
        }
        exit;
    }


    global $wpdb;

    $table_name = $wpdb->prefix . 'table_comments';

    // استخدام الدالة wpdb->insert الآمنة
    $result = $wpdb->insert(
        $table_name,
        [
            'username'  => sanitize_user($username),
            'email'     => sanitize_email($email),
            'birthday'  => sanitize_text_field($birthday),
            'title'     => sanitize_text_field($title),
            'fav_food'  => sanitize_text_field($fav_food),
            'message'   => sanitize_textarea_field($message),
            'password'  => $password_hash,
            'created_at' => current_time('mysql'),
        ],
        [
            '%s',
            '%s',
            '%d',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s'
        ]
    );

    if ($result === false) {

        wp_die('Database insertion failed: ' . $wpdb->last_error);
    } else {

        header('Location:http://localhost/wordpress/comments-form/');
    }
}
