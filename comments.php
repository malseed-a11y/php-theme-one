<div class="comments-wrapper">

    <?php if (have_comments()) : ?>
        <!-- If there are comments, display them -->
        <div class="comments" id="comments">

            <div class="comments-header">

                <h2 class="comment-reply-title">
                    <?php
                    if (!have_comments()) {
                        echo "Leave a c omment";
                    } else {
                        echo get_comments_number() ."comment";
                    }
                    ?>

                </h2><!-- .comments-title -->

            </div><!-- .comments-header -->

                <?php
                wp_list_comments(
                    array(
                        'style'      => 'div',
                        'avatar_size' => 120,
                        // Removed custom callback to use default WordPress comment display
                    )
                );
                ?>
 
            </div><!-- .comments-inner -->

        </div><!-- comments -->

        <hr class="" aria-hidden="true">

        <?php if (comments_open()) {
            comment_form(
                array(
                    'class_form' => '',
                    'title_reply_before' =>
                    '<h2 id="reply-title" class="comment-reply-title">',
                    'title_reply_after'  => '</h2>',
                )
            );
        }
        ?>
    </div>
    <?php endif; ?>
</div>
