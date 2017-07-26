<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://speechkit.io
 * @since      1.0.0
 *
 * @package    Speechkit
 * @subpackage Speechkit/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Speechkit
 * @subpackage Speechkit/includes
 * @author     Kostas Vaggelakos <kostas@speechkit.io>
 */
class Speechkit {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Speechkit_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'speechkit';
		$this->version = '1.1.3';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Speechkit_Loader. Orchestrates the hooks of the plugin.
	 * - Speechkit_i18n. Defines internationalization functionality.
	 * - Speechkit_Admin. Defines all hooks for the admin area.
	 * - Speechkit_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-speechkit-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-speechkit-i18n.php';

		/**
		* SK API
		*/
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-speechkit-api.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-speechkit-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-speechkit-public.php';

		$this->loader = new Speechkit_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Speechkit_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Speechkit_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Speechkit_Admin( $this->get_plugin_name(), $this->get_version() );

		// Enqueu
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// Menu
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_menu' );
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'add_meta_box' );

		// Actions
		$this->loader->add_action( 'admin_init', $plugin_admin, 'settings_init' );
		$this->loader->add_action( 'publish_post', $plugin_admin, 'publish_post_hook' );
		$this->loader->add_action( 'check_speechkit_status_action', $plugin_admin, 'check_speechkit_status' );

		// Admin actions
		$this->loader->add_action( 'wp_ajax_sk_regenerate', $plugin_admin, 'speechkit_regenerate' );
		$this->loader->add_action( 'wp_ajax_sk_toggle', $plugin_admin, 'speechkit_toggle' );
		$this->loader->add_action( 'wp_ajax_sk_reload', $plugin_admin, 'speechkit_reload' );
		$this->loader->add_action( 'wp_ajax_sk_reload_all', $plugin_admin, 'speechkit_reload_all' );

		// $this->loader->add_action( 'save_post', $plugin_admin, 'publish_post_hook' );

		// Filters
		$this->loader->add_filter( 'cron_schedules', $plugin_admin, 'setup_cron_schedules' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Speechkit_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		// Filters
		$execution_order = get_option('sk_execution_order');
		(isset($execution_order) && is_numeric($execution_order)) ? $execution_order = trim( $execution_order ) : $execution_order = 10;
		$this->loader->add_filter( 'the_content', $plugin_public, 'speechkit_content', $execution_order );

		// Shortcode
		// $this->loader->add_shortcode( 'speechkit', $plugin_public, 'speechkit_shortcode' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Speechkit_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
