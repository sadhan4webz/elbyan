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
	
	public function includes() {

		//WP Core
		require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );


		//Core
		require_once WC_CLASS_BOOKING_PLUGIN_DIR . '/includes/class-wccb-install.php';
		include_once WC_CLASS_BOOKING_PLUGIN_DIR . '/includes/functions.php';
		include_once WC_CLASS_BOOKING_PLUGIN_DIR . '/includes/class-wccb-helper.php';
		include_once WC_CLASS_BOOKING_PLUGIN_DIR . '/includes/class-wccb-settings.php';
		include_once WC_CLASS_BOOKING_PLUGIN_DIR . '/includes/class-wccb-scheduler.php';
		include_once WC_CLASS_BOOKING_PLUGIN_DIR . '/includes/class-wccb-email-content.php';
		include_once WC_CLASS_BOOKING_PLUGIN_DIR . '/includes/class-wccb-notification.php';

		//include_once WC_CLASS_BOOKING_PLUGIN_DIR . '/includes/class-wccb-email-class-notification.php';
		//include_once WC_CLASS_BOOKING_PLUGIN_DIR . '/includes/class-wccb-email-handler.php';
		

		//Frontend
		include_once WC_CLASS_BOOKING_PLUGIN_DIR . '/includes/frontend/class-wccb-frontend.php';
		include_once WC_CLASS_BOOKING_PLUGIN_DIR . '/includes/frontend/class-wccb-frontend-myaccount.php';
		include_once WC_CLASS_BOOKING_PLUGIN_DIR . '/includes/frontend/views/class-wccb-frontend-view.php';
		include_once WC_CLASS_BOOKING_PLUGIN_DIR . '/includes/frontend/views/class-wccb-frontend-myaccount-view.php';

		// Admin
		include_once WC_CLASS_BOOKING_PLUGIN_DIR . '/includes/admin/class-wccb-admin.php';
		include_once WC_CLASS_BOOKING_PLUGIN_DIR . '/includes/admin/views/class-wccb-admin-view.php';
	}
	
	public function init_hooks(){
		
		//Core
		$settings = new WCCB_Settings();
		$settings->init();

		$scheduler = new WCCB_Scheduler();
		$scheduler->init();

		$notification = new WCCB_Notification();
		$notification->init();

		//$email_handler = new WCCB_Email_Handler();

		//Frontend 
		$frontend = new WCCB_Frontend();
		$frontend->init();

		$myaccount = new WCCB_Frontend_Myaccount();
		$myaccount->init();

		//Admin
		$admin_settings = new WCCB_Admin();
		$admin_settings->init();
	}
	
	public static function log($variable) {
		$upload_dir = wp_upload_dir();
		$base_dir 	= $upload_dir['basedir'];
		$base_url 	= $upload_dir['baseurl'];
		$upload_dir = $base_dir . "/wccb-log/";
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