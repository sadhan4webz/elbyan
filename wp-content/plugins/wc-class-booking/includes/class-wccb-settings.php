<?php
defined( 'ABSPATH' ) || die();
class WCCB_Settings {

	/**
	 * Init class.
	 */
	public function init() {
		$this->register_hooks();
	}

	/**
	 * Hook into WordPress.
	 */
	private function register_hooks() {

		register_activation_hook( WC_CLASS_BOOKING_PLUGIN_FILE, array( 'WCCB_Install', 'install' ) );
		register_deactivation_hook( WC_CLASS_BOOKING_PLUGIN_FILE, array( 'WCCB_Install', 'uninstall' ) );
		
		//Load text domain
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
		
		//Enqueue custom javasacript
		add_action( 'wp_enqueue_scripts', array( $this, 'load_javascript' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_javascript' ) );

		//Enqueue custom css
		add_action( 'wp_enqueue_scripts', array($this ,  'load_css') );
		add_action( 'admin_enqueue_scripts', array($this , 'load_css') );		
		
		// Shortcodes.

	}


	/**
	 * Loads textdomain for plugin.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'wccb', false, WC_CLASS_BOOKING_PLUGIN_FOLDER.'/languages/' );
	}
	
	//Enqueue javascript
	public function load_javascript(){

		if (!is_admin()) {
			wp_enqueue_script(
				'wccb-jquery-ui-js',
				'https://code.jquery.com/ui/1.12.1/jquery-ui.js',
				array( 'jquery' ),
				1,
				1
			);
		}

		wp_enqueue_script(
			'wccb-general-js',
			WC_CLASS_BOOKING_PLUGIN_URL .'/assets/js/wccb-general.js',
			array( 'jquery' ),
			1,
			1
		);

		wp_enqueue_script(
			'wccb-script-js',
			WC_CLASS_BOOKING_PLUGIN_URL .'/assets/js/wccb-scripts.js',
			array( 'jquery' ),
			1,
			1
		);

		$script_config 	                        = array( );
		$script_config['is_admin']              = is_admin();
		$script_config['num_days_calendar']     = NUM_DAYS_CALENDAR;
		$script_config['availability_time_row'] = WCCB_Frontend_View::get_availability_time_row_html('yes');
		$script_config['slot_picked_row']       = WCCB_Frontend_View::get_slot_picked_row_html();
		$script_config['admin_ajax_url']        = WC_CLASS_BOOKING_PLUGIN_URL.'/wccb-admin-ajax.php';
		$script_config['frontend_ajax_url']     = WC_CLASS_BOOKING_PLUGIN_URL.'/wccb-frontend-ajax.php';

		wp_localize_script(
			'wccb-script-js',
			'wccb_config',
			$script_config
		);

		wp_localize_script(
			'wccb-general-js',
			'wccb_config',
			$script_config
		);

	}

	//Enqueue css
	public function load_css()
	{
		wp_enqueue_style('wccb-styles',WC_CLASS_BOOKING_PLUGIN_URL.'/assets/css/wccb-styles.css',array(),1,'all');
		if (!is_admin()) {
			wp_enqueue_style('wccb-styles-jquery-ui','//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css',array(),1,'all');
		}
	}

	public static function get_notification_cron_interval() {

		return 'hourly';
	}

	public static function get_reminder_cron_interval() {
		
		return 'wccb_five_minutes';
	}

	public static function get_completion_cron_interval() {
		
		return 'wccb_five_minutes';
	}
}