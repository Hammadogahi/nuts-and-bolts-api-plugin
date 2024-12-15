<?php

/**
 * Plugin Name:       Nuts and Bolts Api
 * Description:       A custom plugin to fetch result from api.
 * Version:           1.0.0
 * Author:            Flixr
 * Text Domain:       nuts-and-bolts
 */


defined('ABSPATH') || exit;

// Define plugin version
define('NAB_PLUGIN_VERSION', '1.0.0');


// Define plugin directory
define('NAB_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Define plugin URL
define('NAB_PLUGIN_URL', plugin_dir_url(__FILE__));



function my_custom_plugin_enqueue_scripts()
{
    wp_enqueue_style('my-custom-plugin-style', NAB_PLUGIN_URL . 'style.css', array(), time());
    wp_enqueue_script('custom-ajax-script', NAB_PLUGIN_URL . 'custom-ajax-script.js', array('jquery'), time(), true);
    wp_localize_script('custom-ajax-script', 'custom_ajax_obj', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('custom_ajax_nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'my_custom_plugin_enqueue_scripts');



function nab_plugin_init()
{
    
    add_shortcode('nab_shortcode', 'nab_shortcode_function');
    add_shortcode('nab_script_shortcode', 'nab_shortcode_v2_function');
}
add_action('init', 'nab_plugin_init');

function nab_shortcode_function($atts)
{

    ob_start(); ?>

    <form id="custom-search-form">
        <input type="text" id="product-url" placeholder="Enter product URL" required>
        <button type="submit"><?php _e('Search', 'nuts-and-bolts'); ?></button>
    </form>

    <form id="newsletter-form" style="display: none;">
        <p class="enter-email"><?php _e('Enter your email to see the result', 'nuts-and-bolts'); ?></p>
        <div id="newletter-wrapper">
            <input type="email" id="newsletter-email" placeholder="Enter your email" required>
            <button type="submit"><?php _e('Subscribe', 'nuts-and-bolts'); ?></button>
        </div>

    </form>
    <div id="waiting-message" style="display:none;">
        <h3>
           <?php _e(" We're working on your report! It’ll be ready soon. Thanks for hanging in there!", 'nuts-and-bolts'); ?>
        </h3>
    </div>
    <div id="loading-spinner" class="spinner" style="display: none;"></div>
    <div id="search-result"></div>


<?php return ob_get_clean();
}

// Script Generate Shortcode Callback

function nab_shortcode_v2_function($atts)
{
    ob_start(); ?>

    <form id="custom-search-form-v2">
        <input type="text" id="product-url-v2" placeholder="Enter product URL" required>
        <button type="submit"><?php _e('Search', 'nuts-and-bolts'); ?></button>
    </form>

    <form id="newsletter-form" style="display: none;">
        <p class="enter-email"><?php _e('Enter your email to see the result', 'nuts-and-bolts'); ?></p>
        <div id="newletter-wrapper">
            <input type="email" id="newsletter-email" placeholder="Enter your email" required>
            <button type="submit"><?php _e('Subscribe', 'nuts-and-bolts'); ?></button>
        </div>
    </form>

    <div id="waiting-message" style="display:none;">
        <h3>
            <?php _e("We're working on your script! It’ll be ready soon. Thanks for hanging in there!", 'nuts-and-bolts'); ?>
        </h3>
    </div>
    <div id="loading-spinner-v2" class="spinner" style="display: none;"></div>
    <div id="search-result-v2"></div>

    <?php return ob_get_clean();
}



//API REQUEST FUNCTION

function custom_api_request($product_url)
{
    $api_url = 'https://api.nutsandbolts.ai/generate_personas'; 
    $api_key = 'enter_api_key';

    // Initialize WP_Http class
    $http = new WP_Http();

    // Set up arguments for the request
    $args = array(
        'headers' => array(
            'x-api-key' => $api_key,
            'Content-Type' => 'application/json',
        ),
        'body'    => json_encode(array(
            'product_url' => $product_url,
        )),
        'timeout' => 360, 
        'method'  => 'POST',
    );

    // Make the request
    $response = $http->post($api_url, $args);

    // Check for errors in the response
    if (is_wp_error($response)) {
        return $response->get_error_message();
    }

    // Retrieve the body from the response
    $response_body = wp_remote_retrieve_body($response);

    return json_decode($response_body, true);
}

//Script API Request Function

function custom_api_request_v2($product_url)
{
    $api_url = 'https://api.nutsandbolts.ai/generate_ad_concepts'; // Replace with the new API endpoint
    $api_key = 'enter_api_key'; // Replace with the new API key

    $http = new WP_Http();

    $args = array(
        'headers' => array(
            'x-api-key' => $api_key,
            'Content-Type' => 'application/json',
        ),
        'body'    => json_encode(array(
            'product_url' => $product_url,
        )),
        'timeout' => 360,
        'method'  => 'POST',
    );

    $response = $http->post($api_url, $args);

    if (is_wp_error($response)) {
        return $response->get_error_message();
    }

    $response_body = wp_remote_retrieve_body($response);

    return $response_body; // Assuming the response is HTML and needs to be echoed directly
}


//AJAX Function
function custom_ajax_search()
{
    check_ajax_referer('custom_ajax_nonce', 'nonce');

    $product_url = isset($_POST['product_url']) ? sanitize_text_field($_POST['product_url']) : '';

    if (empty($product_url)) {
        wp_send_json_error('No URL provided.');
    }

    $response = custom_api_request($product_url);

    if (isset($response['personas']) && is_array($response['personas'])) {
        ob_start();
    ?>
        <div class="personas-container">
            <?php foreach ($response['personas'] as $persona): ?>
                <div class="persona-card">
                    <h2 class="persona-name"><?php echo esc_html($persona['name']); ?></h2>
                    <p class="persona-background"><strong><?php _e('Background:', 'nuts-and-bolts'); ?></strong> <?php echo esc_html($persona['background']); ?></p>
                    <p class="persona-behaviors"><strong><?php _e('Behaviors:', 'nuts-and-bolts'); ?></strong> <?php echo esc_html($persona['behaviors']); ?></p>
                    <p class="persona-demographics"><strong><?php _e('Demographics:', 'nuts-and-bolts'); ?></strong> <?php echo esc_html($persona['demographics']); ?></p>
                    <div class="persona-motivations">
                        <p><strong><?php _e('Primary Motivation:', 'nuts-and-bolts'); ?></strong> <?php echo esc_html($persona['motivations']['primary']); ?></p>
                        <p><strong><?php _e('Secondary Motivation:', 'nuts-and-bolts'); ?></strong> <?php echo esc_html($persona['motivations']['secondary']); ?></p>
                    </div>
                    <p class="persona-preferences"><strong><?php _e('Preferences:', 'nuts-and-bolts'); ?></strong> <?php echo esc_html($persona['preferences']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php
        $custom_markup = ob_get_clean();
        wp_send_json_success($custom_markup);
    } else {
        $error_message = 'Error: Request timed out, seems like server is busy, please try again!';
        error_log($error_message); // Log the error message
        wp_send_json_error($error_message);
    }

    wp_die();
}



add_action('wp_ajax_custom_ajax_search', 'custom_ajax_search');
add_action('wp_ajax_nopriv_custom_ajax_search', 'custom_ajax_search');


// Script Generator API Request Function

function custom_ajax_search_v2()
{
    check_ajax_referer('custom_ajax_nonce', 'nonce');

    $product_url = isset($_POST['product_url']) ? sanitize_text_field($_POST['product_url']) : '';

    if (empty($product_url)) {
        wp_send_json_error('No URL provided.');
    }

    $response = custom_api_request_v2($product_url);

    if (isset($response)) { 
        ob_start();
    ?>
        <div class="data-container">
            <pre>
                <?php echo $response; ?>
            </pre>
        </div>

    <?php
        $custom_markup = ob_get_clean();
        wp_send_json_success($custom_markup);
    } else {
        wp_send_json_error('Error: Invalid response format');
    }

    wp_die();
}

add_action('wp_ajax_custom_ajax_search_v2', 'custom_ajax_search_v2');
add_action('wp_ajax_nopriv_custom_ajax_search_v2', 'custom_ajax_search_v2');


// Newsletter Ajax

function handle_newsletter_subscription()
{
    check_ajax_referer('custom_ajax_nonce', 'nonce');

    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';

    if (empty($email) || !is_email($email)) {
        wp_send_json_error('Please provide a valid email address.');
    }

    // Email details
    $to = 'your email'; 
    $subject = 'New Newsletter Subscription';
    $message = 'A new user has subscribed to your newsletter with the following email address: ' . $email;
    $headers = array('Content-Type: text/html; charset=UTF-8');

    // Send email notification
   $mail_sent =  wp_mail($to, $subject, $message, $headers);

   if($mail_sent) {
     wp_send_json_success('Thank you for subscribing!');
   }

   wp_die();

}
add_action('wp_ajax_handle_newsletter_subscription', 'handle_newsletter_subscription');
add_action('wp_ajax_nopriv_handle_newsletter_subscription', 'handle_newsletter_subscription');

register_activation_hook( __FILE__ , 'nab_activate');

function nab_activate ()
{
    if( !get_option( 'nab_version' ) ) {
        update_option( 'nab_version', NAB_PLUGIN_VERSION );
    }
}


register_deactivation_hook( __FILE__ , 'nab_deactivate' );

function nab_deactivate ( )
{
    delete_option('nab_version');
}

