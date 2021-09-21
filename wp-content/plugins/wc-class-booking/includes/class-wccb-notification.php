<?php
defined( 'ABSPATH' ) || die();

class WCCB_Notification {

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

		//Actions
		add_action ( 'class_booking_notification' , array( $this , 'send_class_booking_notification_student' ) , 10 , 1 );
		add_action ( 'class_booking_notification' , array( $this , 'send_class_booking_notification_tutor' ) , 10 , 1 );
		add_action ( 'class_booking_notification' , array( $this , 'send_class_booking_notification_admin' ) , 10 , 1 );

		add_action ( 'reschedule_class_notification' , array( $this , 'send_reschedule_class_notification_student' ) , 10 , 1 );
		add_action ( 'reschedule_class_notification' , array( $this , 'send_reschedule_class_notification_tutor' ) , 10 , 1 );
		add_action ( 'reschedule_class_notification' , array( $this , 'send_reschedule_class_notification_admin' ) , 10 , 1 );


		add_action ( 'cancelled_class_notification' , array( $this , 'send_cancelled_class_notification_student' ) , 10 , 1 );
		add_action ( 'cancelled_class_notification' , array( $this , 'send_cancelled_class_notification_tutor' ) , 10 , 1 );
		add_action ( 'cancelled_class_notification' , array( $this , 'send_cancelled_class_notification_admin' ) , 10 , 1 );



		add_action ( 'upcoming_class_notification' , array( $this , 'send_upcoming_class_notification_student' ) , 10 , 3 );
		add_action ( 'upcoming_class_notification' , array( $this , 'send_upcoming_class_notification_tutor' ) , 10 , 3 );

		add_action ( 'upcoming_class_reminder' , array( $this , 'send_upcoming_class_reminder_student' ) , 10 , 3 );
		add_action ( 'upcoming_class_reminder' , array( $this , 'send_upcoming_class_reminder_tutor' ) , 10 , 3 );



		add_action ( 'class_completion_notification' , array( $this , 'send_class_completion_notification_student' ) , 10 , 3 );
		add_action ( 'class_completion_notification' , array( $this , 'send_class_completion_notification_tutor' ) , 10 , 3 );
		add_action ( 'class_completion_notification' , array( $this , 'send_class_completion_notification_admin' ) , 10 , 3 );

	}

	public static function get_email_headers() {

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: '.get_option( 'blogname' ).'<'.get_option( 'admin_email' ).'>'
		);

		return $headers;
	}

	public function send_class_booking_notification_student( $booking_id) {
		global $wpdb;
		//Send student email
		$table_name = $wpdb->prefix.'booking_history';
		$query           = "SELECT * FROM $table_name WHERE ID='".$booking_id."'";
		$results         = $wpdb->get_results( $query ); // db call ok. no cache ok.
		if (count($results)>0) {
			foreach( $results as $row ) {
				$student = get_userdata($row->user_id);
				$tutor   = get_userdata($row->tutor_id);
				$to      = $student->user_email;
				$subject = 'Your class has been successfully booked at '.get_option( 'blogname' );
				$message = WCCB_Email_Content::get_class_booking_content( 'student' , $row , $student , $tutor );
				$headers = WCCB_Notification::get_email_headers();
				 
				wp_mail( $to, $subject, $message, $headers );
			}
		}
	}

	public function send_class_booking_notification_tutor( $booking_id) {
		global $wpdb;
		//Send tutor email
		$table_name = $wpdb->prefix.'booking_history';
		$query           = "SELECT * FROM $table_name WHERE ID='".$booking_id."'";
		$results         = $wpdb->get_results( $query ); // db call ok. no cache ok.
		if (count($results)>0) {
			foreach( $results as $row ) {
				$student = get_userdata($row->user_id);
				$tutor   = get_userdata($row->tutor_id);
				$to      = $tutor->user_email;
				$subject = 'Your class has been successfully booked at '.get_option( 'blogname' );
				$message = WCCB_Email_Content::get_class_booking_content( 'tutor' , $row , $student , $tutor );
				$headers = WCCB_Notification::get_email_headers();
				 
				wp_mail( $to, $subject, $message, $headers );
			}
		}
	}

	public function send_class_booking_notification_admin( $booking_id) {
		global $wpdb;
		//Send admin email
		$table_name = $wpdb->prefix.'booking_history';
		$query           = "SELECT * FROM $table_name WHERE ID='".$booking_id."'";
		$results         = $wpdb->get_results( $query ); // db call ok. no cache ok.
		if (count($results)>0) {
			foreach( $results as $row ) {
				$student = get_userdata($row->user_id);
				$tutor   = get_userdata($row->tutor_id);

				$args   = array(
					'role__in' => array('administrator')
				);
				$admin_user = get_users( $args );

				foreach ($admin_user as $admin) {
					$to      = $admin->user_email;
					$subject = 'Your class has been successfully booked at '.get_option( 'blogname' );
					$message = WCCB_Email_Content::get_class_booking_content( 'admin' , $row , $student , $tutor );
					$headers = WCCB_Notification::get_email_headers();
					 
					wp_mail( $to, $subject, $message, $headers );
				}
			}
		}
	}

	public function send_reschedule_class_notification_student( $booking_id) {
		global $wpdb;
		//Send student email
		$table_name = $wpdb->prefix.'booking_history';
		$query           = "SELECT * FROM $table_name WHERE ID='".$booking_id."'";
		$results         = $wpdb->get_results( $query ); // db call ok. no cache ok.
		if (count($results)>0) {
			foreach( $results as $row ) {
				$student = get_userdata($row->user_id);
				$tutor   = get_userdata($row->tutor_id);

				$to      = $student->user_email;
				$subject = 'Your class has been successfully rescheduled at '.get_option( 'blogname' );
				$message = WCCB_Email_Content::get_class_reschedule_content( 'student' , $row , $student , $tutor );
				$headers = WCCB_Notification::get_email_headers();
				 
				wp_mail( $to, $subject, $message, $headers );
			}
		}
	}

	public function send_reschedule_class_notification_tutor( $booking_id) {
		global $wpdb;
		//Send tutor email
		$table_name = $wpdb->prefix.'booking_history';
		$query           = "SELECT * FROM $table_name WHERE ID='".$booking_id."'";
		$results         = $wpdb->get_results( $query ); // db call ok. no cache ok.
		if (count($results)>0) {
			foreach( $results as $row ) {
				$student = get_userdata($row->user_id);
				$tutor   = get_userdata($row->tutor_id);

				$to      = $tutor->user_email;
				$subject = 'Your class has been successfully rescheduled at '.get_option( 'blogname' );
				$message = WCCB_Email_Content::get_class_reschedule_content( 'tutor' , $row , $student , $tutor );
				$headers = WCCB_Notification::get_email_headers();
				 
				wp_mail( $to, $subject, $message, $headers );
			}
		}
	}

	public function send_reschedule_class_notification_admin( $booking_id) {
		global $wpdb;
		//Send admin email
		$table_name = $wpdb->prefix.'booking_history';
		$query           = "SELECT * FROM $table_name WHERE ID='".$booking_id."'";
		$results         = $wpdb->get_results( $query ); // db call ok. no cache ok.
		if (count($results)>0) {
			foreach( $results as $row ) {
				$student = get_userdata($row->user_id);
				$tutor   = get_userdata($row->tutor_id);

				$args   = array(
					'role__in' => array('administrator')
				);
				$admin_user = get_users( $args );

				foreach ($admin_user as $admin) {
					$to      = $admin->user_email;
					$subject = 'Your class has been successfully booked at '.get_option( 'blogname' );
					$message = WCCB_Email_Content::get_class_reschedule_content( 'admin' , $row , $student , $tutor );
					$headers = WCCB_Notification::get_email_headers();
					 
					wp_mail( $to, $subject, $message, $headers );
				}
			}
		}
	}

	public function send_cancelled_class_notification_student( $booking_id) {
		global $wpdb;
		//Send student email
		$table_name = $wpdb->prefix.'booking_history';
		$query           = "SELECT * FROM $table_name WHERE ID='".$booking_id."'";
		$results         = $wpdb->get_results( $query ); // db call ok. no cache ok.
		if (count($results)>0) {
			foreach( $results as $row ) {
				$student = get_userdata($row->user_id);
				$tutor   = get_userdata($row->tutor_id);

				$to      = $student->user_email;
				$subject = 'Your class has been cancelled at '.get_option( 'blogname' );
				$message = WCCB_Email_Content::get_class_cancelled_content( 'student' , $row , $student , $tutor );
				$headers = WCCB_Notification::get_email_headers();
				 
				wp_mail( $to, $subject, $message, $headers );
			}
		}
	}

	public function send_cancelled_class_notification_tutor( $booking_id) {
		global $wpdb;
		//Send student email
		$table_name = $wpdb->prefix.'booking_history';
		$query           = "SELECT * FROM $table_name WHERE ID='".$booking_id."'";
		$results         = $wpdb->get_results( $query ); // db call ok. no cache ok.
		if (count($results)>0) {
			foreach( $results as $row ) {
				$student = get_userdata($row->user_id);
				$tutor   = get_userdata($row->tutor_id);

				$to      = $tutor->user_email;
				$subject = 'Your class has been cancelled at '.get_option( 'blogname' );
				$message = WCCB_Email_Content::get_class_cancelled_content( 'tutor' , $row , $student , $tutor );
				$headers = WCCB_Notification::get_email_headers();
				 
				wp_mail( $to, $subject, $message, $headers );
			}
		}
	}

	public function send_cancelled_class_notification_admin( $booking_id) {
		global $wpdb;
		//Send admin email
		$table_name = $wpdb->prefix.'booking_history';
		$query           = "SELECT * FROM $table_name WHERE ID='".$booking_id."'";
		$results         = $wpdb->get_results( $query ); // db call ok. no cache ok.
		if (count($results)>0) {
			foreach( $results as $row ) {
				$student = get_userdata($row->user_id);
				$tutor   = get_userdata($row->tutor_id);

				$args   = array(
					'role__in' => array('administrator')
				);
				$admin_user = get_users( $args );

				foreach ($admin_user as $admin) {
					$to      = $admin->user_email;
					$subject = 'One class has been cancelled at '.get_option( 'blogname' );
					$message = WCCB_Email_Content::get_class_cancelled_content( 'admin' , $row , $student , $tutor );
					$headers = WCCB_Notification::get_email_headers();
					 
					wp_mail( $to, $subject, $message, $headers );
				}
			}
		}
	}

	public function send_upcoming_class_notification_student( $booking , $student , $tutor ) {

		//Send student email
		$to      = $student->user_email;
		$subject = 'You have a schedule class today at '.get_option( 'blogname' );
		$message = WCCB_Email_Content::get_class_notification_content( 'student' , $booking , $student , $tutor );
		$headers = WCCB_Notification::get_email_headers();
		 
		wp_mail( $to, $subject, $message, $headers );
	}

	public function send_upcoming_class_notification_tutor( $booking , $student , $tutor ) {

		//Send tutor email
		$to      = $tutor->user_email;
		$subject = 'You have a schedule class today at '.get_option( 'blogname' );
		$message = WCCB_Email_Content::get_class_notification_content( 'tutor' , $booking , $student , $tutor );
		$headers = WCCB_Notification::get_email_headers();
		 
		wp_mail( $to, $subject, $message, $headers );
	}

	public function send_upcoming_class_reminder_student( $booking , $student , $tutor ) {

		//Send student email
		$to      = $student->user_email;
		$subject = 'Class reminder : You have a schedule class today at '.get_option( 'blogname' );
		$message = WCCB_Email_Content::get_class_reminder_content( 'student' , $booking , $student , $tutor );
		$headers = WCCB_Notification::get_email_headers();
		 
		wp_mail( $to, $subject, $message, $headers );
	}

	public function send_upcoming_class_reminder_tutor( $booking , $student , $tutor ) {

		//Send tutor email
		$to      = $tutor->user_email;
		$subject = 'Class reminder: You have a schedule class today at '.get_option( 'blogname' );
		$message = WCCB_Email_Content::get_class_reminder_content( 'tutor' , $booking , $student , $tutor );
		$headers = WCCB_Notification::get_email_headers();
		 
		wp_mail( $to, $subject, $message, $headers );
	}

	public function send_class_completion_notification_student( $booking , $student , $tutor ) {

		//Send student email
		$to      = $student->user_email;
		$subject = 'You have successfully completed your class today at '.get_option( 'blogname' );
		$message = WCCB_Email_Content::get_class_completion_content( 'student' , $booking , $student , $tutor );
		$headers = WCCB_Notification::get_email_headers();
		 
		wp_mail( $to, $subject, $message, $headers );
	}

	public function send_class_completion_notification_tutor( $booking , $student , $tutor ) {

		//Send tutor email
		$to      = $tutor->user_email;
		$subject = 'You have successfully completed your class today at '.get_option( 'blogname' );
		$message = WCCB_Email_Content::get_class_completion_content( 'tutor' , $booking , $student , $tutor );
		$headers = WCCB_Notification::get_email_headers();
		 
		wp_mail( $to, $subject, $message, $headers );
	}

	public function send_class_completion_notification_admin( $booking , $student , $tutor ) {
		$student = get_userdata($booking->user_id);
		$tutor   = get_userdata($booking->tutor_id);

		$args   = array(
			'role__in' => array('administrator')
		);
		$admin_user = get_users( $args );

		foreach ($admin_user as $admin) {
			$to      = $admin->user_email;
			$subject = 'Your class has been successfully booked at '.get_option( 'blogname' );
			$message = WCCB_Email_Content::get_class_cancelled_content( 'admin' , $booking , $student , $tutor );
			$headers = WCCB_Notification::get_email_headers();
			 
			wp_mail( $to, $subject, $message, $headers );
		}
	}

}