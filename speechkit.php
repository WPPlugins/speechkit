<?php

/**
 *
 * @link              https://speechkit.io
 * @since             1.0.0
 * @package           Speechkit
 *
 * @wordpress-plugin
 * Plugin Name:       SpeechKit
 * Plugin URI:        https://speechkit.io
 * Description:       Enables voice on your posts through speechkit.io
 * Version:           1.1.3
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       speechkit
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-speechkit-activator.php
 */
function activate_speechkit() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-speechkit-activator.php';
	Speechkit_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-speechkit-deactivator.php
 */
function deactivate_speechkit() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-speechkit-deactivator.php';
	Speechkit_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_speechkit' );
register_deactivation_hook( __FILE__, 'deactivate_speechkit' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-speechkit.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_speechkit() {

	$plugin = new Speechkit();
	$plugin->run();

}
run_speechkit();
