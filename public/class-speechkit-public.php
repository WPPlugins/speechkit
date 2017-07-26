<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://speechkit.io
 * @since      1.0.0
 *
 * @package    Speechkit
 * @subpackage Speechkit/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Speechkit
 * @subpackage Speechkit/public
 * @author     Kostas Vaggelakos <kostas@speechkit.io>
 */
class Speechkit_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	// public function speechkit_shortcode( $atts, $content = null ) {
	// 	return '<span class="caption">' . $content . '</span>';
	// }

	public function speechkit_content($content) {
		if (!is_single()) {
			return $content;
		}

		$status = get_post_meta(get_the_ID(), "speechkit_status", true);
		if ($status && $status == Speechkit_Admin::STATUS_PROCESSED) {
			$plugin_content = apply_filters('sk_the_content', $this->create_plugin_interface());
			return $plugin_content.$content;
		}

		return $content;
	}

	public function create_plugin_interface() {
		ob_start();
    include( plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/speechkit-public-display.php' );
    $var=ob_get_contents();
    ob_end_clean();
    return $var;
		// require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/speechkit-public-display.php';
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Speechkit_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Speechkit_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_register_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/speechkit-public.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Speechkit_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Speechkit_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/speechkit-public.js', array( 'jquery' ), $this->version, false );

		// wp_enqueue_script( 'speechkit', 'http://localhost:4000/speechkit.js', false );
		$player_version = get_option('sk_player_version') ?: '1.5.10';
		wp_enqueue_script( 'speechkit', 'https://cdn.jsdelivr.net/npm/@speechkit/speechkit-audio-player@'.$player_version.'/dist/speechkit.js', array(), null, false );

	}

}
