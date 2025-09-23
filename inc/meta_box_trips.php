<?php


function trip_add_meta_box()
{
    add_meta_box(
        'trip_details',
        'Trip Details',
        'trip_meta_box_callback',
        'trip',
        'side',
        'low'
    );
}
add_action('add_meta_boxes', 'trip_add_meta_box');

function trip_meta_box_callback($post)
{
    wp_nonce_field('save_trip_details', 'trip_nonce');

    $from_city = get_post_meta($post->ID, '_trip_from_city', true);
    $to_city   = get_post_meta($post->ID, '_trip_to_city', true);
    $date      = get_post_meta($post->ID, '_trip_date', true);
    $time      = get_post_meta($post->ID, '_trip_time', true);

    $cities = array('Cairo', 'Dubai', 'Paris', 'Damascus');
    echo '<div class="trip-meta-box">';

    // City of Departure
    echo '<p class="form-field">
            <label for="trip_from_city"><strong>City of Departure be</strong></label>
            <select name="trip_from_city" id="trip_from_city">
                <option value="">Choose city</option>';
    foreach ($cities as $city) {
        echo '<option value="' . esc_attr($city) . '" ' . selected($from_city, $city, false) . '>' . esc_html($city) . '</option>';
    }
    echo '</select>
          </p>';

    // City of Arrival  
    echo '<p class="form-field">
            <label for="trip_to_city"><strong>City of Arrival be</strong></label>
            <br>
            <input type="text" name="trip_to_city" id="trip_to_city" value="' . esc_attr($to_city) . '"/>
          </p>';

    // Date of the Trip
    echo '<p class="form-field">
            <label for="trip_date"><strong>Date of the Trip be</strong></label> 
            <input type="date" name="trip_date" id="trip_date" value="' . esc_attr($date) . '"/>
          </p>';

    // Time of the Trip
    echo '<p class="form-field">
            <label for="trip_time"><strong>Time of the Trip be</strong></label>
            <select name="trip_time" id="trip_time">
                <option value="">Choose time</option>
                <option value="morning" ' . selected($time, 'morning', false) . '>Morning</option>
                <option value="evening" ' . selected($time, 'evening', false) . '>Evening</option>
            </select>
          </p>';

    echo '</div>';
}

// حفظ البيانات
function save_trip_meta($post_id)
{
    if (!isset($_POST['trip_nonce']) || !wp_verify_nonce($_POST['trip_nonce'], 'save_trip_details')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (isset($_POST['trip_from_city'])) {
        update_post_meta($post_id, '_trip_from_city', sanitize_text_field($_POST['trip_from_city']));
    }


    if (isset($_POST['trip_to_city'])) {
        update_post_meta($post_id, '_trip_to_city', sanitize_text_field($_POST['trip_to_city']));
    }

    if (isset($_POST['trip_date'])) {
        update_post_meta($post_id, '_trip_date', sanitize_text_field($_POST['trip_date']));
    }

    if (isset($_POST['trip_time'])) {
        update_post_meta($post_id, '_trip_time', sanitize_text_field($_POST['trip_time']));
    }
}
add_action('save_post', 'save_trip_meta');
