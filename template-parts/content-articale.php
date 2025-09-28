			<div class="container">
				<header class="content-header">
					<div class="meta mb-3">
						<h1 class="title"><?php echo apply_filters('the_title-filter', get_the_title()); ?>
						</h1>
						<!-- Slider main container -->
						<div class="swiper">
							<!-- Additional required wrapper -->
							<div class="swiper-wrapper">
								<!-- Slides -->
								<div class="swiper-slide">Slide 1</div>
								<div class="swiper-slide">Slide 2</div>
								<?php if (has_post_thumbnail()) { ?>
									<div class="swiper-slide">
										<img class=" lazyload blur-up " data-srcset="<?php the_post_thumbnail_url('large'); ?>" alt="<?php the_title_attribute(); ?>">
									</div>
								<?php } ?>

							</div>
							<!-- If we need pagination -->
							<div class="swiper-pagination"></div>

							<!-- If we need navigation buttons -->
							<div class="swiper-button-prev"></div>
							<div class="swiper-button-next"></div>

							<!-- If we need scrollbar -->
							<div class="swiper-scrollbar"></div>
						</div>
						<span class="date">Published <?php the_date('F j, Y'); ?>
						</span>
						<?php the_tags('<span class="tag"><i class="fa fa-tag"></i> ', ', ', '</span>'); ?>
						<span class="comment">
							<a href="#comments">
								<i class='fa fa-comment'></i> <?php comments_number('0 comments', '1 comment', '% comments'); ?>
							</a>
						</span>
					</div>
				</header>
				<?php
				the_content();
				?>
				<?php
				comments_template();

				?>
			</div>