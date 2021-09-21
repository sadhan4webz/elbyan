<?php
/**
 * Class Custom_WC_Email
 */
class WCCB_Email_Handler {

	/**
	 * Custom_WC_Email constructor.
	 */
	public function __construct() {

    	// Filtering the emails and adding our own email.
		add_filter( 'woocommerce_email_classes', array( $this, 'register_email' ), 90, 1 );
	}

	/**
	 * @param array $emails
	 *
	 * @return array
	 */
	public function register_email( $emails ) {
		include_once WC_CLASS_BOOKING_PLUGIN_DIR . '/includes/class-wccb-email-class-notification.php';
		$emails['WCCB_Email_Class_Notification'] = new WCCB_Email_Class_Notification();

		print_r($emails);

		return $emails;
	}
}