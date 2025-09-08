			<div class="container">
			    <header class="content-header">
			        <div class="meta mb-3">
			            <span class="date">Published <?php the_date('F j, Y'); ?> </span>
			            <?php the_tags('<span class="tag"><i class="fa fa-tag"></i> ', ', ', '</span>'); ?>
			            <span class="comment"><a href="#comments"><i class='fa fa-comment'></i> <?php comments_number('0 comments', '1 comment', '% comments'); ?></a></span>
			        </div>
			    </header>
			    <?php
                the_content();
                ?>
			    <?php
                comments_template();

                ?>
			</div>