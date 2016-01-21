<?php
/**
 * Mm Clean Content
 *
 * @since             1.0.0
 * @package           Mm_Clean_Content
 *
 * @wordpress-plugin
 * Plugin Name:       Mm Clean Content
 * Description:       A utility for stripping specific HTML tags and attributes from post content.
 * Version:           1.0.0
 * Author:            MIGHTYminnow, Braad Martin, Rodrigo Salles
 * Author URI:        http://mightyminnow.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       mm-clean-content
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

define( 'MM_CLEAN_CONTENT_VERSION', '1.0.0' );
define( 'MM_CLEAN_CONTENT_SLUG', 'mm-clean-content' );
define( 'MM_CLEAN_CONTENT_PATH', plugin_dir_path( __FILE__ ) );
define( 'MM_CLEAN_CONTENT_URL', plugin_dir_url( __FILE__ ) );
define( 'MM_CLEAN_CONTENT_BASENAME', plugin_basename( __FILE__ ) );

// Include the main plugin class.
require_once MM_CLEAN_CONTENT_PATH . 'classes/class-mm-clean-content.php';

/**
 * Set the default allowed elements and attributes on plugin activation.
 *
 * @since  1.0.0
 */
register_activation_hook( __FILE__, array( 'Mm_Clean_Content', 'set_default_values' ) );

/**
 * Initialize the plugin.
 *
 * @since  1.0.0
 */
function mm_clean_content_init() {

	// Only load if we're in the admin or serving an Ajax request.
	if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
		$clean_content = new Mm_Clean_Content();
		$clean_content->initialize();
	}
}
mm_clean_content_init();
