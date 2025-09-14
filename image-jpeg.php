<?php get_header(); ?>

<article class="content px-3 py-5 p-md-5">
    <?php
    if (have_posts()) {
        while (have_posts()) {
            the_post();
            the_title('<h1>', '</h1>');
            the_content();


            if (wp_attachment_is_image(get_the_ID())) {
                echo wp_get_attachment_image(get_the_ID(), 'large');
            }
        }
    }
    ?>
</article>

<?php get_footer(); ?>