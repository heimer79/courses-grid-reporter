<?php
/**
 * Plugin Name:       Courses Grid Reporter
 * Description:       Example block scaffolded with Create Block tool.
 * Requires at least: 6.1
 * Requires PHP:      8.0
 * Version:           1.0.0
 * Author:            Heimer Martinez
 * Author URI:        https://github.com/heimer79
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/quick-guide-gplv3.html
 * Text Domain:       courses-grid-reporter
 *
 * @package CreateBlock
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Include the render.php file that contains the render callback function.
require_once __DIR__ . '/build/render.php';

/**
 * Registers the block using the metadata loaded from the `block.json` file
 * and sets up the render callback for the dynamic block.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function create_block_courses_grid_reporter_block_init() {
    register_block_type( __DIR__ . '/build', array(
        'render_callback' => 'render_courses_grid_reporter_block',
    ) );
}
// Hook the block registration to the 'init' action.
add_action( 'init', 'create_block_courses_grid_reporter_block_init' );

/**
 * Proxies requests to the API.
 *
 * This function handles AJAX requests by forwarding them to the specified API URL.
 * It retrieves data from the API and returns it to the front end.
 */
function proxy_request_to_api() {
    // Get the API URL from the request parameters.
    $api_url = isset($_GET['api_url']) ? esc_url_raw($_GET['api_url']) : '';

    // If no URL is provided, return an error response.
    if (empty($api_url)) {
        wp_send_json_error('API URL is missing.');
        return;
    }

    // Perform the API request.
    $response = wp_remote_get($api_url);

    // Check if the request was successful and return the response.
    if (is_wp_error($response)) {
        wp_send_json_error('Error fetching data from API.');
    } else {
        wp_send_json_success(wp_remote_retrieve_body($response));
    }
}
// Hook the proxy request function to handle both logged-in and non-logged-in users.
add_action('wp_ajax_nopriv_proxy_request_to_api', 'proxy_request_to_api');
add_action('wp_ajax_proxy_request_to_api', 'proxy_request_to_api');

/**
 * Allows CORS requests from local development environments.
 *
 * This function sets the appropriate headers to enable CORS for requests coming
 * from local domains. It also handles preflight requests by responding with the
 * correct headers for methods and credentials.
 */
function allow_cors_from_local() {
    // Check if the request comes from an allowed origin using a regular expression.
    if (isset($_SERVER['HTTP_ORIGIN']) && preg_match('/^http:\/\/([a-z0-9-]+)\.local$/', $_SERVER['HTTP_ORIGIN'])) {
        header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Credentials: true');
    }

    // Handle OPTIONS requests (preflight) to ensure they respond correctly.
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        }
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
            header('Access-Control-Allow-Headers: ' . $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']);
        }
        exit;
    }
}
// Hook the CORS function to the 'init' action to allow CORS from local environments.
add_action('init', 'allow_cors_from_local');
