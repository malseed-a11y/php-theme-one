<!DOCTYPE html>
<html lang="en">

<head>

    <!-- Meta -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Blog Site Template">
    <meta name="author" content="https://youtube.com/FollowAndrew">
    <link rel="shortcut icon" href="/assets/images/logo.png">

    <?php wp_head(); ?>

</head>

<body>


    <header class="header text-center">
        <a class="site-title pt-lg-4 mb-0" href="index.html"><?php bloginfo('name'); ?></a>

        <nav class="navbar navbar-expand-lg navbar-dark">

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navigation" aria-controls="navigation" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div id="navigation" class="collapse navbar-collapse flex-column">
                <?php
                if (function_exists('the_custom_logo')) {
                    $custom_logo_id = get_theme_mod('custom_logo');
                    $logo = wp_get_attachment_image_src($custom_logo_id);
                }
                ?>
                <img class="mb-3 mx-auto logo" src="<?php echo esc_url($logo[0]); ?>" alt="logo">
                <?php wp_nav_menu(array(
                    //add the styling to li and a tags
                    'link_before' => '<li class="nav-item">',
                    'link_after' => '</li>',
                    'menu' => 'primary',
                    'container' => '',
                    'theme_location' => 'primary',
                    'items_wrap' => '<ul id="%1$s" class="navbar-nav flex-column text-sm-center text-md-left %2$s">%3$s</ul>',

                ));
                ?>

                <hr>

                <ul class="social-list list-inline py-3 mx-auto">
                    <li class="list-inline-item"><a href="#"><i class="fab fa-twitter fa-fw"></i></a></li>
                    <li class="list-inline-item"><a href="#"><i class="fab fa-linkedin-in fa-fw"></i></a></li>
                    <li class="list-inline-item"><a href="#"><i class="fab fa-github-alt fa-fw"></i></a></li>
                    <li class="list-inline-item"><a href="#"><i class="fab fa-stack-overflow fa-fw"></i></a></li>
                    <li class="list-inline-item"><a href="#"><i class="fab fa-codepen fa-fw"></i></a></li>
                </ul>




        </nav>

        <?php
        dynamic_sidebar('sidebar-1');
        ?>
    </header>
    <div class="main-wrapper">
        <header class="page-title theme-bg-light text-center gradient py-5">
            <h1 class="heading"><?php
                                //if is category page or tag page or author page or archive or search or post page or page
                                if (is_category()) {
                                    single_cat_title();
                                } elseif (is_tag()) {
                                    single_tag_title();
                                } elseif (is_author()) {
                                    the_post();
                                    echo 'Author: ' . get_the_author();
                                } elseif (is_page()) {
                                    the_title();
                                } elseif (is_archive()) {
                                    the_archive_title();
                                    //add single post title
                                } elseif (is_single()) {
                                    the_title();
                                } else {
                                }
                                ?></h1>
        </header>