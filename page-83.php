<?php get_header(); ?>


<article class="content px-3 py-5 p-md-5">
    <h2 class="mb-4">THIS IS A PAGE FOR DATABASE[ID=83]</h2>
    <?php
    if (have_posts()) {
        while (have_posts()) {
            the_post();
            the_content();
        }
    }
    ?>
</article>


<?php get_footer(); ?>