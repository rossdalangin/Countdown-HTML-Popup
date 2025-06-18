<?php
/**
 * Plugin Name: Countdown HTML Popup
 * Description: Adds a countdown timer that shows a popup with HTML content after the time runs out.
 * Version: 1.0
 * Author: Ross Dalangin
 */

if (!defined('ABSPATH')) exit;

/** Register custom post type */
function chp_register_post_type() {
    register_post_type('chp_entry', [
        'labels' => [
            'name' => 'Countdown Entries',
            'singular_name' => 'Countdown Entry'
        ],
        'public' => true,
        'menu_icon' => 'dashicons-clock',
        'supports' => ['title'],
        'show_in_rest' => true,
    ]);
}
add_action('init', 'chp_register_post_type');

/** Add Meta Box */
function chp_add_meta_boxes() {
    add_meta_box('chp_meta_box', 'Countdown Settings', 'chp_render_meta_box', 'chp_entry', 'normal', 'high');
}
add_action('add_meta_boxes', 'chp_add_meta_boxes');

function chp_render_meta_box($post) {
    $minutes = get_post_meta($post->ID, '_chp_minutes', true);
    $html = get_post_meta($post->ID, '_chp_html', true);
    $shortcode = '[countdown_html_popup id="' . $post->ID . '"]';
    ?>
    <p><label><strong>Countdown Minutes:</strong></label><br>
    <input type="number" name="chp_minutes" value="<?php echo esc_attr($minutes); ?>" style="width:100px;" min="0"></p>

    <p><label><strong>HTML Content:</strong></label><br>
    <textarea name="chp_html" style="width:100%; height:200px;"><?php echo esc_textarea($html); ?></textarea></p>

    <p><label><strong>Shortcode:</strong></label><br>
    <input type="text" readonly value="<?php echo esc_attr($shortcode); ?>" style="width:100%; font-family:monospace; background:#f9f9f9;"></p>
    <?php
}


function chp_save_post_meta($post_id) {
    if (array_key_exists('chp_minutes', $_POST)) {
        update_post_meta($post_id, '_chp_minutes', intval($_POST['chp_minutes']));
    }
    if (array_key_exists('chp_html', $_POST)) {
        update_post_meta($post_id, '_chp_html', wp_kses_post($_POST['chp_html']));
    }
}
add_action('save_post', 'chp_save_post_meta');

/** Shortcode */
function chp_countdown_shortcode($atts) {
    $atts = shortcode_atts(['id' => 0], $atts);
    $post_id = intval($atts['id']);

    if (!$post_id || get_post_type($post_id) !== 'chp_entry') return '';

    $minutes = intval(get_post_meta($post_id, '_chp_minutes', true));
    $html_content = get_post_meta($post_id, '_chp_html', true);
    $seconds = $minutes * 60;

    ob_start();
    ?>
    <div id="chp-timer-wrapper-<?php echo $post_id; ?>" style="display:flex; justify-content:center; align-items:center; height:200px; margin-bottom:30px;">
        <div id="chp-timer-<?php echo $post_id; ?>" style="
            font-size: 60px;
            font-weight: bold;
            color: #e74c3c;
            font-family: 'Arial Black', sans-serif;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
            border: 4px solid #e74c3c;
            padding: 20px 40px;
            border-radius: 12px;
            background-color: #fff5f5;
            animation: pulse 1.5s infinite;
        "></div>
    </div>

    <div id="chp-popup-<?php echo $post_id; ?>" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); background:#fff; padding:30px 20px 20px 20px; border-radius:12px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); z-index:9999; max-width:90%; width:400px; text-align:center;">
        <button class="chp-close" style="position:absolute; top:10px; right:15px; background:transparent; border:none; font-size:20px; cursor:pointer;">&times;</button>
        <div class="chp-content"><?php echo $html_content; ?></div>
    </div>

    <style>
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    </style>

    <script>
    (function(){
        let timeLeft = <?php echo $seconds; ?>;
        const timer = document.getElementById("chp-timer-<?php echo $post_id; ?>");
        const wrapper = document.getElementById("chp-timer-wrapper-<?php echo $post_id; ?>");
        const popup = document.getElementById("chp-popup-<?php echo $post_id; ?>");
        const closeBtn = popup.querySelector(".chp-close");

        function updateTimer() {
            if (timeLeft <= 0) {
                clearInterval(interval);
                wrapper.style.display = 'none';
                popup.style.display = 'block';
                return;
            }
            const mins = Math.floor(timeLeft / 60);
            const secs = timeLeft % 60;
            timer.innerText = `${mins}:${secs.toString().padStart(2, '0')}`;
            timeLeft--;
        }

        closeBtn.addEventListener("click", () => {
            popup.style.display = 'none';
        });

        updateTimer();
        const interval = setInterval(updateTimer, 1000);
    })();
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('countdown_html_popup', 'chp_countdown_shortcode');
