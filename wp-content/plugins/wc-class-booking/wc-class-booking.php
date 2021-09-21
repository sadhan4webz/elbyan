<?php
/**
 * Plugin Name:       Woocommerce Class Booking
 * Plugin URI:        https://www.webzstore.com/
 * Description:       Woocommerce class booking.
 * Version:           1.0
 * Author:            Webzstore
 * Author URI:        https://www.webzstore.com/
 * Text Domain:       wccb
 * Domain Path:       /languages
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

ini_set( 'error_log', WP_CONTENT_DIR . '/wccb-debug.log' );

// Define constants.
define( 'WC_CLASS_BOOKING_VERSION', '1.0' );
define( 'WC_CLASS_BOOKING_PLUGIN_FILE', __FILE__ );
define( 'WC_CLASS_BOOKING_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'WC_CLASS_BOOKING_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
define( 'WC_CLASS_BOOKING_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'WC_CLASS_BOOKING_PLUGIN_FOLDER',  dirname( plugin_basename( __FILE__ ) ) );
//define( 'WC_CLASS_BOOKING_PYMENT_METHODS_PLACEHOLDER', 877 );

define('PLUGIN_TEXT_DOMAIN' , 'wccb');
define('NUM_DAYS_CALENDAR' , 7);
define('HOUR_EXPIRE_DAYS' , 35);
define('SEND_CLASS_REMINDER_BEFORE', 10); //value in minute
define('RESCHEDULE_CLASS_BEFORE_HOURS', 12); //value in hours
define('CANCEL_CLASS_BEFORE_HOURS', 12); //value in hours

require_once dirname( __FILE__ ) . '/includes/class-wc-class-booking-dependency-checker.php';
if ( ! WC_Class_Booking_Dependency_Checker::check_dependencies() ) {
	return;
}

require_once dirname( __FILE__ ) . '/includes/class-wc-class-booking.php';
function WCCB(){
	return WC_Class_Booking::instance();
}

// Global for backwards compatibility.
$GLOBALS['wccb'] = WCCB();