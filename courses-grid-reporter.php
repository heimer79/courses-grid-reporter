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
