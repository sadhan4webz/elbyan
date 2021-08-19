<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Class_Booking {
	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since  1.26.0
	 */
	private static $instance = null;
	
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->includes();
		$this->init_hooks();	
	}
	
	public function includes(){

		//Core
		require_once WC_CLASS_BOOKING_PLUGIN_DIR . '/includes/class-wccb-install.php';
		//include_once WC_CLASS_BOOKING_PLUGIN_DIR . '/includes/functions.php'; //error ocurred when include functions.php
		include_once WC_CLASS_BOOKING_PLUGIN_DIR . '/includes/class-wccb-settings.php';

		//Frontend
		include_once WC_CLASS_BOOKING_PLUGIN_DIR . '/includes/frontend/class-wccb-frontend.php';

		// Admin
		include_once WC_CLASS_BOOKING_PLUGIN_DIR . '/includes/admin/class-wccb-admin.php';
	}
	
	public function init_hooks(){
		
		//Core
		$settings = new WCCB_Settings();
		$settings->init();

		//Frontend 
		$frontend = new WCCB_Frontend();
		$frontend->init();

		//Admin
		$admin_settings = new WCCB_Admin();
		$admin_settings->init();
	}
	
	public static function log($variable) {
		$upload_dir = wp_upload_dir();
		$base_dir 	= $upload_dir['basedir'];
		$base_url 	= $upload_dir['baseurl'];
		$upload_dir = $base_dir . "/wccb/";
		if (! is_dir($upload_dir)) {
			mkdir( $upload_dir, 0755 );
		}
		
		$log_file = $upload_dir.date_i18n('d-m-Y').'-log.txt';
		$myfile = fopen($log_file, "a") or die("Unable to open file!");
		ob_start();
		echo $variable.PHP_EOL;
		$txt = ob_get_clean();
		fwrite($myfile, $txt);
		fclose($myfile);
	}
}