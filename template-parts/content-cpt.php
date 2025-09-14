			<div class="container">
			    <header class="content-header">
			        <div class="meta mb-3">
			            <h1 class="title"><?php echo apply_filters('the_title-filter', get_the_title()); ?>
			            </h1>

			            <span class="date">Published <?php the_date('F j, Y');


                                                        $cat_list = wp_get_post_terms(get_the_ID(), 'trip_category');
                                                        $i = 0;
                                                        foreach ($cat_list as $cat) {
                                                            if (isset($cat->name)) {
                                                                if ($i == 0) {
                                                                    echo '<span class="category"><i class="fa fa-tag"></i> ' . $cat->name . '</span>';
                                                                } else {
                                                                    echo ', <span class="category"><i class="fa fa-tag"></i> ' . $cat->name . ' , ' . '</span>';
                                                                }
                                                            }
                                                            $i++;
                                                        }


                                                        $term_list = wp_get_post_terms(get_the_ID(), 'trip_tag');
                                                        $i = 0;
                                                        foreach ($term_list as $term) {
                                                            if (isset($term->name)) {
                                                                if ($i == 0) {
                                                                    echo '<span class="tag"><i class="fa fa-tag"></i> ' . $term->name . '</span>';
                                                                } else {
                                                                    echo ', <span class="tag"><i class="fa fa-tag"></i> ' . $term->name . '</span>';
                                                                }
                                                            }
                                                            $i++;
                                                        }
                                                        ?>
			            </span>
			            <?php the_tags('<span class="tag"><i class="fa fa-tag"></i> ', ', ', '</span>'); ?>
			            <span class="comment">
			                <a href="#comments">
			                    <i class='fa fa-comment'></i> <?php comments_number('0 comments', '1 comment', '% comments'); ?>
			                </a>
			            </span>
			        </div>
			    </header>
			    <div class="trip-meta-box">
			        <?php
                    $trip_details = array(
                        'from_city' => get_post_meta(get_the_ID(), '_trip_from_city', true),
                        'to_city'   => get_post_meta(get_the_ID(), '_trip_to_city', true),
                        'date'      => get_post_meta(get_the_ID(), '_trip_date', true),
                        'time'      => get_post_meta(get_the_ID(), '_trip_time', true)
                    );

                    if (array_filter($trip_details)) {
                        echo '<h3>Trip Details</h3>';

                        foreach ($trip_details as $key => $value) {
                            if ($value) {
                                switch ($key) {
                                    case 'from_city':
                                        echo '<span class="from">' . esc_html($value) . '</span>';
                                        break;
                                    case 'to_city':
                                        echo '<span class="to">' . esc_html($value) . '</span>';
                                        break;
                                    case 'date':
                                        echo '<span class="date">' . date('F j, Y', strtotime($value)) . '</span>';
                                        break;
                                    case 'time':
                                        echo '<span class="time">' . ($value == 'morning' ? 'Morning' : 'Evening') . '</span>';
                                        break;
                                }
                            }
                        }
                    }
                    ?>
			    </div>

			    <?php
                the_content();
                ?>
			    <?php
                comments_template();

                ?>
			</div>