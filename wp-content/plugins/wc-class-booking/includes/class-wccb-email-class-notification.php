<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


if ( ! class_exists( 'WC_Email' ) ) {
	return;
}

/**
 * Class WC_Customer_Cancel_Order
 */
class WCCB_Email_Class_Notification extends WC_Email {

	/**
	 * Create an instance of the class.
	 *
	 * @access public
	 * @return void
	 */
	function __construct() {
    	// Email slug we can use to filter other data.
		$this->id          = 'wccb_email_class_notification';
		$this->title       = __( 'Email notification for upcoming class', PLUGIN_TEXT_DOMAIN );
		$this->description = __( 'An email sent to the student and tutor for upcoming class on the same day.', PLUGIN_TEXT_DOMAIN );
    	// For admin area to let the user know we are sending this email to customers.
		$this->customer_email = true;
		$this->heading     = __( 'Class Notification', PLUGIN_TEXT_DOMAIN );
		// translators: placeholder is {blogname}, a variable that will be substituted when email is sent out
		$this->subject     = sprintf( _x( '[%s] Upcoming class', 'you have scheduled upcoming class today', PLUGIN_TEXT_DOMAIN ), '{blogname}' );
    
    	// Template paths.
		$this->template_html  = 'emails/wccb-class-notification.php';
		$this->template_plain = 'emails/plain/wccb-class-notification.php';
		$this->template_base  = WC_CLASS_BOOKING_PLUGIN_DIR . 'templates/';
    
    	// Action to which we hook onto to send the email.
		add_action( 'upcoming_class_notification' , array( $this, 'trigger' ) , 10 , 3 );

		echo 'Add action- upcoming_class_notification';

		parent::__construct();
	}

	 /**
	 * Trigger Function that will send this email to the customer.
	 *
	 * @access public
	 * @return void
	 */
	public function trigger( $booking , $student , $tutor ) {

		var_dump($booking).'<br><br><br>'.var_dump($student).'<br><br><br>'.var_dump($tutor);

		$this->object  = $booking;
		$this->recipient = $student->user_email.','.$tutor->user_email;

		if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
			return;
		}

		$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
	}

	/**
	 * Get content html.
	 *
	 * @access public
	 * @return string
	 */
	public function get_content_html() {
		return wc_get_template_html( $this->template_html, array(
			'order'         => $this->object,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => false,
			'plain_text'    => false,
			'email'			=> $this
		), '', $this->template_base );
	}

	/**
	 * Get content plain.
	 *
	 * @return string
	 */
	public function get_content_plain() {
		return wc_get_template_html( $this->template_plain, array(
			'order'         => $this->object,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => false,
			'plain_text'    => true,
			'email'			=> $this
		), '', $this->template_base );
	}
}