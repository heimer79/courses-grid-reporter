<?php
/**
 * Plugin Name:       Courses Grid Reporter
 * Description:       Example block scaffolded with Create Block tool.
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            Heimer Martinez
 * Author URI:        https://github.com/heimer79
 *  * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       courses-grid-reporter
 *
 * @package CreateBlock
 */


 if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Incluye el archivo render.php
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
add_action( 'init', 'create_block_courses_grid_reporter_block_init' );


function proxy_request_to_api() {
    // Get the API URL from the request parameters
    $api_url = isset($_GET['api_url']) ? esc_url_raw($_GET['api_url']) : '';

    // If no URL is provided, return an error
    if (empty($api_url)) {
        wp_send_json_error('API URL is missing.');
        return;
    }

    // Perform the API request
    $response = wp_remote_get($api_url);

    // Check if the request was successful
    if (is_wp_error($response)) {
        wp_send_json_error('Error fetching data from API.');
    } else {
        wp_send_json_success(wp_remote_retrieve_body($response));
    }
}
add_action('wp_ajax_nopriv_proxy_request_to_api', 'proxy_request_to_api');
add_action('wp_ajax_proxy_request_to_api', 'proxy_request_to_api');



function allow_cors_from_local() {
    // Verificamos si la solicitud viene de un origen permitido usando una expresión regular
    if (isset($_SERVER['HTTP_ORIGIN']) && preg_match('/^http:\/\/([a-z0-9-]+)\.local$/', $_SERVER['HTTP_ORIGIN'])) {
        header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Credentials: true');
    }

    // Permitimos que las solicitudes de métodos OPTIONS (preflight) respondan correctamente
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
add_action('init', 'allow_cors_from_local');
