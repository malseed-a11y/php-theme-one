<?php get_header();
$queried_object = get_queried_object(); ?>


<article class="content px-3 py-5 p-md-5">
    <h2 class="mb-4">Archive</h2>
    <?php
    if (have_posts()) {
        while (have_posts()) {
            the_post();
            get_template_part('template-parts/content', "archive");
        }
    }
    ?>
</article>


<?php get_footer(); ?>