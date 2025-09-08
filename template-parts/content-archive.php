<div class="container">
<div class="post mb-5">
    <div class="media">
        <?php if(has_post_thumbnail()): ?>
            <img class="mr-3 img-fluid post-thumb d-none d-md-flex" src="<?php the_post_thumbnail_url('thumbnail'); ?>" alt="<?php the_title_attribute(); ?>">
        <?php endif; ?>
        <div class="media-body">
            <h3 class="title mb-1"> <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
            <div class="meta mb-1">
                <span class="date">Published <?php the_date('F j, Y'); ?> </span>
                <span class="comment"><a href="<?php comments_link(); ?>">
                    <?php comments_number('0 comments', '1 comment', '% comments'); ?></a></span>
            </div>
            <div class="intro">
                <?php the_excerpt(); ?>
            </div>
            <a class="more-link" href="<?php the_permalink(); ?>">Read more &rarr;</a>
        </div>
    </div>
</div>

			
</div>
	