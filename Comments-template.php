<?php

/**
 * Template Name: Comment Form
 * 
 */

get_header();


?>

<article class="content px-3 py-5 p-md-5">


    <form class="special-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php?action=send_message_form')); ?>">
        <?php wp_nonce_field('send_message_form', 'smf_nonce'); ?>

        <div class="form-group">
            <label for="username">User Name</label>
            <input type="text" id="username" name="username"
                value="<?php echo isset($_GET['username']) ? esc_attr($_GET['username']) : ''; ?>"
                required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" value="
                <?php echo isset($_GET['password']) ? esc_attr($_GET['password']) : ''; ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email"
                value="<?php echo isset($_GET['email']) ? esc_attr($_GET['email']) : ''; ?>"
                required>
        </div>

        <div class="form-group">
            <label for="birthday">Birthday</label>
            <input type="number" id="birthday" name="birthday"
                value="<?php echo isset($_GET['birthday']) ? esc_attr($_GET['birthday']) : ''; ?>"
                required>
        </div>

        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title"
                value="<?php
                        echo isset($_GET['title']) ? esc_attr($_GET['title']) : ''; ?>"
                required>
        </div>

        <div class="form-group">
            <label for="fav_food">Favourite Food</label>
            <input type="text" id="fav_food" name="fav_food"
                value="<?php echo isset($_GET['fav_food']) ? esc_attr($_GET['fav_food']) : ''; ?>"
                required>
        </div>

        <div class="form-group">
            <label for="message">Message</label>
            <textarea id="message" name="message" required><?php echo isset($_GET['message']) ? esc_textarea($_GET['message']) : ''; ?></textarea>
        </div>
        <button type="submit" name="clear_cache">Submit</button>
    </form>


    <?php
    function wp_get_comments()
    {
        global $wpdb;
        $table_comments = $wpdb->prefix . 'table_comments';

        $results = get_transient('_wp_prfix_comments_');
        if (!$results) {

            $results = $wpdb->get_results("SELECT * FROM $table_comments");

            set_transient('_wp_prfix_comments_', $results, 60 * HOUR_IN_SECONDS);
            $results = get_transient('_wp_prfix_comments_');
        }

        if (isset($_POST['clear_cache'])) {
            delete_transient('_wp_prfix_comments_');
        }


        if (empty($results)) {
            return '<p class="comments-no-data">No data found</p>';
        }

        $output = '<table class="special-table">';
        $output .= '<thead><tr>';
        foreach ($results[0] as $key => $value) {
            $output .= '<th>' . esc_html($key) . '</th>';
        }
        $output .= '</tr></thead><tbody>';

        foreach ($results as $row) {
            $output .= '<tr>';
            foreach ($row as $value) {
                $output .= '<td>' . esc_html($value) . '</td>';
            }
            $output .= '</tr>';
        }

        $output .= '</tbody></table>';
        return $output;
    }


    echo wp_get_comments();

    ?>
</article>

<?php get_footer(); ?>