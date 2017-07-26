<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://speechkit.io
 * @since      1.0.0
 *
 * @package    Speechkit
 * @subpackage Speechkit/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Speechkit
 * @subpackage Speechkit/admin
 * @author     Kostas Vaggelakos <kostas@speechkit.io>
 */
class Speechkit_Admin {

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
	 * Status constants
	 */
	 const STATUS_PROCESSED = 'Processed';
	 const STATUS_PROCESSING = 'Processing';
	 const STATUS_ERROR = 'Error';


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->api = new SpeechkitAPI(
			get_option('sk_api_key'),
			get_option('sk_backend'),
			get_option('sk_news_site_id'),
			get_option('sk_voice_id')
		);
	}

	public static function get_all_cron_post_ids() {
		$cron_jobs = get_option( 'cron' );
		$sk_cron_jobs = array();
		$post_ids = array();

		foreach ($cron_jobs as $key => $cron_job) {
			if (is_array($cron_job) && array_key_exists('check_speechkit_status_action', $cron_job)) {
				array_push($sk_cron_jobs, $cron_job);
			}
		}

		foreach ($sk_cron_jobs as $key => $sk_cron_job) {
			$cron_data = $sk_cron_job['check_speechkit_status_action'];
			// $random_id = reset($cron_data);
			$cron_args = reset($cron_data)['args'];
			// echo "cron args: ".var_dump($cron_args);
			array_push($post_ids, reset($cron_args));
		}

		return $post_ids;
	}

	public static function get_processing_posts() {
		$posts = get_option('sk_processing_posts');
		if (is_array($posts)) {
			return $posts;
		}
		return array();
	}

	public function add_menu() {
		add_options_page('Speechkit', 'Speechkit', 'manage_options', 'speechkit', array($this, 'create_admin_interface') );
	}

	public function add_meta_box() {
		add_meta_box('speechkit', 'Speechkit', array( $this, 'create_admin_meta_box' ), 'post', 'side', 'default' );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/speechkit-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/speechkit-admin.js', array( 'jquery' ), $this->version, false );
	}

	public function create_admin_interface() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/speechkit-admin-display.php';
	}

	public function create_admin_meta_box() {
		wp_enqueue_script( $this->plugin_name.'-metabox', plugin_dir_url( __FILE__ ) . 'js/speechkit-admin-metabox.js', array( 'jquery' ), $this->version, false );
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/speechkit-admin-metabox.php';
	}

	public function setup_cron_schedules($schedules) {
		if(!isset($schedules["1min"])){
        $schedules["1min"] = array(
            'interval' => 1*60,
            'display' => __('Once every 1 minutes'));
    }
    return $schedules;
	}

	public function settings_init() {
		register_setting( 'speechkit-settings', 'sk_enabled' );
		register_setting( 'speechkit-settings', 'sk_news_site_id' );
		register_setting( 'speechkit-settings', 'sk_api_key' );
		register_setting( 'speechkit-settings', 'sk_voice_id' );
		register_setting( 'speechkit-settings', 'sk_backend' );
		register_setting( 'speechkit-settings', 'sk_analytics_id');
		register_setting( 'speechkit-settings', 'sk_analytics_key');
		register_setting( 'speechkit-settings', 'sk_player_version');
		register_setting( 'speechkit-settings', 'sk_execution_order');
		register_setting( 'speechkit-settings', 'sk_processing_posts');
	}

	public function ensure_settings() {
		// TODO: Call this and make sure all settings are set before doing anything
		return get_option('sk_enabled') == 1;
	}

	public function publish_post_hook($post_id) {
		// Figure out if this post already is speechkitified
		if (!get_post_meta($post_id, 'speechkit_info', true) && $this->ensure_settings()) {
			$this->set_status($post_id, self::STATUS_PROCESSING);
			$this->create_article($post_id);
		}
	}

	public function create_article($post_id) {
		if ($this->ensure_settings()) {
			$response = $this->api->create_article($post_id);
			if ( is_wp_error($response) || $response['response']['code'] >= 400 ) {
				$this->set_status($post_id, self::STATUS_ERROR);
			} else {
				// Schedule a 1min cron job to check back with speechkit until synthesizing is done
				$this->schedule_status_check();
			}
		}
	}

	public function check_speechkit_status() {
		$unprocessed_post_ids = self::get_processing_posts();
		if (empty($unprocessed_post_ids)) {
			$this->clear_status_check();
			return;
		}

		foreach ($unprocessed_post_ids as $post_id) {
			$this->check_speechkit_status_for_post($post_id);
		}
	}

	public function check_speechkit_status_for_post($post_id) {
		$response = $this->api->get_article($post_id);
		if ( $response['response']['code'] < 400 ) {
			$body = json_decode(wp_remote_retrieve_body($response), true);
			if (!empty($body) && !empty($body['media'])) {
				if ($body['state'] == "processed") {
					// We're done.
					$this->set_metadata_info($post_id, $body);
					$this->set_status($post_id, self::STATUS_PROCESSED);
				}
			}
		}
	}

	public function clear_cache($post_id) {
		try {
			// W3TC
			if (function_exists('w3tc_flush_post')) {
				w3tc_flush_post($post_id);
			}
			// Litespeed cache
			if (function_exists('litespeed_purge_single_post')) {
				litespeed_purge_single_post($post_id);
			} else if (class_exists('LiteSpeed_Cache')) {
				LiteSpeed_Cache::get_instance()->purge_single_post($post_id);
			}
		} catch (Exception $e) {
		}
	}

	public function set_status($post_id, $status) {

		// Keep a list of all unprocessed posts and remove it if done
		$posts = self::get_processing_posts();

		if ($status == self::STATUS_PROCESSED) {
			$posts = array_diff($posts, array($post_id));
		} else {
			array_push($posts, $post_id);
		}

		// Store the posts in the list
		$posts = array_unique($posts, SORT_NUMERIC);
		update_option('sk_processing_posts', $posts);

		// Set the status for this particular post
		update_post_meta($post_id, 'speechkit_status', $status);

		// Clear the cache if any
		$this->clear_cache($post_id);
	}

	public function set_metadata_info($post_id, $metadata) {
		update_post_meta($post_id, 'speechkit_info', $metadata);
	}

	public function clear_status_check() {
		wp_clear_scheduled_hook('check_speechkit_status_action');

		// Get rid of legacy cron jobs
		$post_ids = self::get_all_cron_post_ids();
		foreach ($post_ids as $post_id) {
			wp_clear_scheduled_hook('check_speechkit_status_action', array($post_id));
		}
	}

	public function schedule_status_check() {
		$this->clear_status_check();
		wp_schedule_event(time(), '1min', 'check_speechkit_status_action');
	}

	/**
	* Settings page actions
	*/

	public function speechkit_reload_all() {
		if (!$this->ensure_settings()) {
			die();
		}
		$this->check_speechkit_status();
		die();
	}

	/**
	* Meta box actions
	*/

	public function speechkit_regenerate() {
		if (!$this->ensure_settings()) {
			die();
		}

		try {
			$post_id = $_POST['post_id'];
			$response = $this->api->update_article($post_id);
			if ($response['response']['code'] == 404) {
				// If it's 404, generate a new one
				$response = $this->api->create_article($post_id);
			}
			if ($response['response']['code'] >= 400) {
				$this->set_status($post_id, self::STATUS_ERROR);
			} else {
				$this->set_status($post_id, self::STATUS_PROCESSING);
				$this->schedule_status_check();
			}
		} catch (Exception $e) {
			$this->set_status($post_id, self::STATUS_ERROR);
		}

		die();
	}

	public function speechkit_toggle() {
		$post_id = $_POST['post_id'];
		$current_value = get_post_meta($post_id, 'speechkit_disabled', true);
		$speechkit_disabled = empty($current_value) ? 1 : !intval($current_value);
		update_post_meta($post_id, 'speechkit_disabled', $speechkit_disabled);
		die();
	}

	public function speechkit_reload() {
		if (!$this->ensure_settings()) {
			die();
		}
		$post_id = $_POST['post_id'];
		$this->check_speechkit_status_for_post($post_id);
		die();
	}

}
