<?php

/**
 * Portfolio Agency Pro functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Portfolio_Agency_Pro
 */

if (! defined('_S_VERSION')) {
    // Replace the version number of the theme on each release.
    define('_S_VERSION', '1.0.0');
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 */
function portfolio_agency_pro_setup()
{
    // Make theme available for translation
    load_theme_textdomain('portfolio-agency-pro', get_template_directory() . '/languages');

    // Add default posts and comments RSS feed links to head
    add_theme_support('automatic-feed-links');

    // Let WordPress manage the document title
    add_theme_support('title-tag');

    // Enable support for Post Thumbnails on posts and pages
    add_theme_support('post-thumbnails');

    // Add custom image sizes for portfolio
    add_image_size('portfolio-thumbnail', 400, 300, true);
    add_image_size('portfolio-large', 800, 600, true);

    // This theme uses wp_nav_menu() in multiple locations
    register_nav_menus(
        array(
            'primary' => esc_html__('Primary Navigation', 'portfolio-agency-pro'),
            'footer'  => esc_html__('Footer Navigation', 'portfolio-agency-pro'),
        )
    );

    // Switch default core markup to output valid HTML5
    add_theme_support(
        'html5',
        array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
            'style',
            'script',
        )
    );

    // Set up the WordPress core custom background feature
    add_theme_support(
        'custom-background',
        apply_filters(
            'portfolio_agency_pro_custom_background_args',
            array(
                'default-color' => 'ffffff',
                'default-image' => '',
            )
        )
    );

    // Add theme support for selective refresh for widgets
    add_theme_support('customize-selective-refresh-widgets');

    // Add support for core custom logo
    add_theme_support(
        'custom-logo',
        array(
            'height'      => 250,
            'width'       => 250,
            'flex-width'  => true,
            'flex-height' => true,
        )
    );

    // Add support for wide alignment
    add_theme_support('align-wide');

    // Add support for editor styles
    add_theme_support('editor-styles');
}
add_action('after_setup_theme', 'portfolio_agency_pro_setup');



function mosaab_menus()
{
    $locations = array(
        'primary' => "Desktop Primary Left Sidebar",
        'footer' => "Footer Menu Items"

    );
    register_nav_menus($locations);
}

add_action('init', 'mosaab_menus');







function mosaab_register_styles()
{
    // Enqueue main theme CSS
    $version = wp_get_theme()->get('Version');
    wp_enqueue_style(
        'mosaab-style',
        get_template_directory_uri() . './style.css',
        array('mosaab-bootstrap'),
        $version,
        'all'
    );
    // Enqueue Bootstrap CSS
    wp_enqueue_style(
        'mosaab-bootstrap',
        "https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css",
        array(),
        '4.4.1',
        'all'
    );

    // Enqueue Font Awesome CSS
    wp_enqueue_style(
        'mosaab-fontawesome',
        "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css",
        array(),
        '5.13.0',
        'all'
    );

    // Enqueue Swiper CSS
    wp_enqueue_style(
        'mosaab-swiper',
        "https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css",
        array(),
        '11',
        'all'
    );
}
add_action('wp_enqueue_scripts', 'mosaab_register_styles');



function mosaab_register_scripts()
{
    // Enqueue main theme JavaScript
    // Enqueue jQuery
    wp_enqueue_script(
        'mosaab-jquery',
        "https://code.jquery.com/jquery-3.4.1.slim.min.js",
        array(),
        '3.4.1',
        true
    );
    // Enqueue Popper
    wp_enqueue_script(
        'mosaab-popper',
        "https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js",
        array('mosaab-jquery'),
        '1.16.0',
        true
    );
    // Enqueue Bootstrap JavaScript
    wp_enqueue_script(
        'mosaab-bootstrap',
        "https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js",
        array('mosaab-jquery', 'mosaab-popper'),
        '4.4.1',
        true
    );
    // Enqueue Swiper JavaScript
    wp_enqueue_script(
        'mosaab-swiper',
        "https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js",
        array('mosaab-bootstrap'),
        '11',
        true
    );
    // Enqueue main theme JavaScript
    wp_enqueue_script(
        'mosaab-main',
        get_template_directory_uri() . './assets/js/main.js',
        array('mosaab-swiper'),
        '1.0',
        true
    );
}

add_action('wp_enqueue_scripts', 'mosaab_register_scripts');



function dequeue_swiper_on_red_set()
{
    if (is_single() && is_singular('post')) {
        global $post;

        if ($post && $post->post_name === 'red-set') {
            wp_dequeue_script('mosaab-swiper');
            wp_dequeue_style('mosaab-swiper');
        }
    }
}
add_action('wp_enqueue_scripts', 'dequeue_swiper_on_red_set', 20);




function mosaab_widgets()
{
    register_sidebar(
        array(

            'before_widget' => '<ul class="social-list list-inline py-3 mx-auto">',
            'after_widget' => '</ul>',
            'before_title' => '',
            'after_title' => '',

            'name' => 'Sidebar',
            'id' => 'sidebar-1',
            'description' => 'Sidebar Widgets',
        )
    );
    register_sidebar(
        array(
            'name' => 'Footer',
            'id' => 'footer',
            'description' => 'Footer Widgets',

            'before_widget' => '<ul class="social-list list-inline py-3 mx-auto">',
            'after_widget' => '</ul>',
            'before_title' => '',
            'after_title' => '',

        )
    );
}
add_action('widgets_init', 'mosaab_widgets');

// Include custom database functions

function generate_random_bar_color()
{
    $main_color = apply_filters('content_banner_color', '#ff0000');
    // Generate relatively similar color
    $r = hexdec(substr($main_color, 1, 2)) + rand(-20, 20);
    $g = hexdec(substr($main_color, 3, 2)) + rand(-20, 20);
    $b = hexdec(substr($main_color, 5, 2)) + rand(-20, 20);
    $r = max(0, min(255, $r));
    $g = max(0, min(255, $g));
    $b = max(0, min(255, $b));
    return sprintf('#%02x%02x%02x', $r, $g, $b);
}


function add_banner_to_content($content)
{
    if (is_page('about-us')) {
        $users_count = apply_filters('count_users', 0);
        $random_color = generate_random_bar_color();
        $banner = '<div class="custom-banner" style="background-color:' . $random_color . '; color: #fff; padding: 10px; text-align: center; margin-bottom: 20px;">
        <h2>Special Offer!</h2>
        <p>Get 20% off on all products. Use code: SAVE20 at checkout.</p>
        <p>Total Registered Users: ' . $users_count . '</p>
        </div>';
        $content = $banner . $content;
        do_action('banner_added', $content, $random_color);
    }
    return $content;
}



add_action('the_content', 'add_banner_to_content', 10, 1);








function activate()
{
    global $wpdb;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $charset_collate = $wpdb->get_charset_collate();

    $wpdb->query('START TRANSACTION');

    // cpomments tabble
    $table_comments = $wpdb->prefix . 'table_comments';
    $sql = "CREATE TABLE $table_comments (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        username VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        birthday VARCHAR(255) NOT NULL, 
        title VARCHAR(255) NOT NULL,
        fav_food VARCHAR(255) NOT NULL,
        message VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    $result_taxonomy = dbDelta($sql);

    if ($result_taxonomy) {
        $wpdb->query('COMMIT');
        return true;
    } else {
        $wpdb->query('ROLLBACK');
        return false;
    }
}




add_action('after_setup_theme', 'activate');






require_once(dirname(__FILE__) . '/inc/trips_cpt.php');
require_once(dirname(__FILE__) . '/inc/meta_box_trips.php');
require_once(dirname(__FILE__) . '/form-handler.php');
