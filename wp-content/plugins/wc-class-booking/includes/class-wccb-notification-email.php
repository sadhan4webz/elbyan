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
		add_action ( 'class_booking_notification_once' , array( $this , 'send_class_booking_email_once_student' ) , 10 , 3 );
		add_action ( 'class_booking_notification_once' , array( $this , 'send_class_booking_email_once_tutor' ) , 10 , 3 );
		add_action ( 'class_booking_notification_once' , array( $this , 'send_class_booking_email_once_admin' ) , 10 , 3 );


		add_action ( 'class_booking_notification' , array( $this , 'send_class_booking_email_student' ) , 10 , 1 );
		add_action ( 'class_booking_notification' , array( $this , 'send_class_booking_email_tutor' ) , 10 , 1 );
		add_action ( 'class_booking_notification' , array( $this , 'send_class_booking_email_admin' ) , 10 , 1 );

		add_action ( 'reschedule_class_notification' , array( $this , 'send_reschedule_class_email_student' ) , 10 , 1 );
		add_action ( 'reschedule_class_notification' , array( $this , 'send_reschedule_class_email_tutor' ) , 10 , 1 );
		add_action ( 'reschedule_class_notification' , array( $this , 'send_reschedule_class_email_admin' ) , 10 , 1 );


		add_action ( 'cancelled_class_notification' , array( $this , 'send_cancelled_class_email_student' ) , 10 , 1 );
		add_action ( 'cancelled_class_notification' , array( $this , 'send_cancelled_class_email_tutor' ) , 10 , 1 );
		add_action ( 'cancelled_class_notification' , array( $this , 'send_cancelled_class_email_admin' ) , 10 , 1 );



		add_action ( 'upcoming_class_notification' , array( $this , 'send_upcoming_class_email_student' ) , 10 , 3 );
		add_action ( 'upcoming_class_notification' , array( $this , 'send_upcoming_class_email_tutor' ) , 10 , 3 );


		add_action ( 'upcoming_class_reminder' , array( $this , 'send_upcoming_class_reminder_email_student' ) , 10 , 3 );
		add_action ( 'upcoming_class_reminder' , array( $this , 'send_upcoming_class_reminder_email_tutor' ) , 10 , 3 );


		add_action ( 'class_date_time_passed_notification' , array( $this , 'send_class_date_time_passed_email_tutor' ) , 10 , 3 );


		//add_action ( 'class_completion_notification' , array( $this , 'send_class_completion_email_student' ) , 10 , 3 );
		//add_action ( 'class_completion_notification' , array( $this , 'send_class_completion_email_tutor' ) , 10 , 3 );
		//add_action ( 'class_completion_notification' , array( $this , 'send_class_completion_email_admin' ) , 10 , 3 );

		//add_action ( 'class_status_notification' , array( $this , 'send_class_status_email_student' ) , 10 , 3 );
		//add_action ( 'class_status_notification' , array( $this , 'send_class_status_email_tutor' ) , 10 , 3 );
		//add_action ( 'class_status_notification' , array( $this , 'send_class_status_email_admin' ) , 10 , 3 );

		add_action ( 'deduct_hour_notification' , array( $this , 'send_deduct_hour_email_student' ) , 10 , 2 );

	}

	public static function get_email_headers() {

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: '.get_option( 'blogname' ).' <'.get_option( 'admin_email' ).'>'
		);

		return $headers;
	}

	public function send_class_booking_email_once_student( $booking_ids , $user_id , $tutor_id ) {
		$student = get_userdata($user_id);
		$tutor   = get_userdata($tutor_id);
		$to      = $student->user_email;
		$subject = 'Your class has been successfully booked at '.get_option( 'blogname' );
		$message = WCCB_Email_Content::get_class_booking_content_once( 'student' , $booking_ids , $student , $tutor );
		$headers = WCCB_Notification::get_email_headers();
		if (!empty($booking_ids)) {
		 	wp_mail( $to, $subject, $message, $headers );
		}
	}

	public function send_class_booking_email_once_tutor( $booking_ids , $user_id , $tutor_id ) {
		if (empty($tutor_id)) {
			return;
		}
		$student = get_userdata($user_id);
		$tutor   = get_userdata($tutor_id);
		$to      = $tutor->user_email;
		$subject = 'One student has booked class with you at '.get_option( 'blogname' );
		$message = WCCB_Email_Content::get_class_booking_content_once( 'tutor' , $booking_ids , $student , $tutor );
		$headers = WCCB_Notification::get_email_headers();
		 
		wp_mail( $to, $subject, $message, $headers );
	}

	public function send_class_booking_email_once_admin( $booking_ids , $user_id , $tutor_id ) {
		$student = get_userdata($user_id);
		$tutor   = get_userdata($tutor_id);
		$to      = get_option('admin_email');
		$subject = 'One student successfully booked class at '.get_option( 'blogname' );
		$message = WCCB_Email_Content::get_class_booking_content_once( 'admin' , $booking_ids , $student , $tutor );
		$headers = WCCB_Notification::get_email_headers();
		 
		if (!empty($booking_ids)) {
		 	wp_mail( $to, $subject, $message, $headers );
		}
	}

	public function send_class_booking_email_student( $booking_id) {
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

	public function send_class_booking_email_tutor( $booking_id) {
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
				$subject = 'One student has booked class with you at '.get_option( 'blogname' );
				$message = WCCB_Email_Content::get_class_booking_content( 'tutor' , $row , $student , $tutor );
				$headers = WCCB_Notification::get_email_headers();
				 
				wp_mail( $to, $subject, $message, $headers );
			}
		}
	}

	public function send_class_booking_email_admin( $booking_id) {
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
					$subject = 'One student successfully booked class at '.get_option( 'blogname' );
					$message = WCCB_Email_Content::get_class_booking_content( 'admin' , $row , $student , $tutor );
					$headers = WCCB_Notification::get_email_headers();
					 
					wp_mail( $to, $subject, $message, $headers );
				}
			}
		}
	}

	public function send_reschedule_class_email_student( $booking_id) {
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

	public function send_reschedule_class_email_tutor( $booking_id) {
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
				$subject = 'One student has rescheduled his/her class at '.get_option( 'blogname' );
				$message = WCCB_Email_Content::get_class_reschedule_content( 'tutor' , $row , $student , $tutor );
				$headers = WCCB_Notification::get_email_headers();
				 
				wp_mail( $to, $subject, $message, $headers );
			}
		}
	}

	public function send_reschedule_class_email_admin( $booking_id) {
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
					$subject = 'One student has rescheduled his/her class at '.get_option( 'blogname' );
					$message = WCCB_Email_Content::get_class_reschedule_content( 'admin' , $row , $student , $tutor );
					$headers = WCCB_Notification::get_email_headers();
					 
					wp_mail( $to, $subject, $message, $headers );
				}
			}
		}
	}

	public function send_cancelled_class_email_student( $booking_id) {
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

	public function send_cancelled_class_email_tutor( $booking_id) {
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
				$subject = 'Your class has been cancelled at '.get_option( 'blogname' );
				$message = WCCB_Email_Content::get_class_cancelled_content( 'tutor' , $row , $student , $tutor );
				$headers = WCCB_Notification::get_email_headers();
				 
				wp_mail( $to, $subject, $message, $headers );
			}
		}
	}

	public function send_cancelled_class_email_admin( $booking_id) {
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

	public function send_upcoming_class_email_student( $booking , $student , $tutor ) {
		global $wpdb;

		$table_name = $wpdb->prefix.'booking_history_meta';
		$query      = "SELECT * FROM $table_name WHERE booking_id='".$booking->ID."' and meta_key='_upcoming_notification_student_done'";
		$results    = $wpdb->get_results( $query );

		// Allow code execution only once 
	    if( count($results) == 0 ) {
			//Send student email
			$to      = $student->user_email;
			$subject = 'You have a schedule class today at '.get_option( 'blogname' );
			$message = WCCB_Email_Content::get_class_notification_content( 'student' , $booking , $student , $tutor );
			$headers = WCCB_Notification::get_email_headers();
			 
			wp_mail( $to, $subject, $message, $headers );

			$data = array(
				'booking_id'   => $booking->ID,
				'meta_key'     => '_upcoming_notification_student_done',
				'meta_value'   => 'yes'
			);

			$wpdb->insert($table_name , $data);
		}
	}

	public function send_upcoming_class_email_tutor( $booking , $student , $tutor ) {
		global $wpdb;

		$table_name = $wpdb->prefix.'booking_history_meta';
		$query      = "SELECT * FROM $table_name WHERE booking_id='".$booking->ID."' and meta_key='_upcoming_notification_tutor_done'";
		$results    = $wpdb->get_results( $query );

		// Allow code execution only once 
	    if( count($results) == 0 ) {

			//Send tutor email
			$to      = $tutor->user_email;
			$subject = 'You have a schedule class today at '.get_option( 'blogname' );
			$message = WCCB_Email_Content::get_class_notification_content( 'tutor' , $booking , $student , $tutor );
			$headers = WCCB_Notification::get_email_headers();
			 
			wp_mail( $to, $subject, $message, $headers );

			$data = array(
				'booking_id'   => $booking->ID,
				'meta_key'     => '_upcoming_notification_tutor_done',
				'meta_value'   => 'yes'
			);

			$wpdb->insert($table_name , $data);
		}
	}

	public function send_upcoming_class_reminder_email_student( $booking , $student , $tutor ) {

		//Send student email
		$to      = $student->user_email;
		$subject = 'Class reminder : You have a schedule class today at '.get_option( 'blogname' );
		$message = WCCB_Email_Content::get_class_reminder_content( 'student' , $booking , $student , $tutor );
		$headers = WCCB_Notification::get_email_headers();
		 
		wp_mail( $to, $subject, $message, $headers );
	}

	public function send_upcoming_class_reminder_email_tutor( $booking , $student , $tutor ) {

		//Send tutor email
		$to      = $tutor->user_email;
		$subject = 'Class reminder: You have a schedule class today at '.get_option( 'blogname' );
		$message = WCCB_Email_Content::get_class_reminder_content( 'tutor' , $booking , $student , $tutor );
		$headers = WCCB_Notification::get_email_headers();
		 
		wp_mail( $to, $subject, $message, $headers );
	}

	public function send_class_date_time_passed_email_tutor( $booking , $student , $tutor ) {

		//Send tutor email
		$to      = $tutor->user_email;
		$subject = 'Our system indicates that your lesson has passed the time';
		$message = WCCB_Email_Content::get_class_date_time_passed_content( 'tutor' , $booking , $student , $tutor );
		$headers = WCCB_Notification::get_email_headers();
		 
		wp_mail( $to, $subject, $message, $headers );
	}

	public function send_class_status_email_student( $booking , $student , $tutor ) {
		global $wpdb;

		$meta_key   = strtolower(str_replace(' ', '_', $booking->delivery->status )).'_notification_email_student';
		$table_name = $wpdb->prefix.'booking_history_meta';
		$query      = "SELECT * FROM $table_name WHERE booking_id='".$booking->ID."' and meta_key='".$meta_key."'";
		$results    = $wpdb->get_results( $query );

		// Allow code execution only once 
	    if( count($results) == 0 ) {

	    	$to      = $student->user_email;
			$subject = 'Tutor has updated the delivery status of your class at '.get_option( 'blogname' );
			$message = WCCB_Email_Content::get_class_status_content( 'student' , $booking , $student , $tutor );
			$headers = WCCB_Notification::get_email_headers();
			 
			wp_mail( $to, $subject, $message, $headers );

	    	$data = array(
				'booking_id'   => $booking->ID,
				'meta_key'     => $meta_key,
				'meta_value'   => 'yes'
			);

			$wpdb->insert($table_name , $data);
	    }
	}

	public function send_class_status_email_tutor( $booking , $student , $tutor ) {
		global $wpdb;

		$meta_key   = strtolower(str_replace(' ', '_', $booking->delivery->status )).'_notification_email_tutor';
		$table_name = $wpdb->prefix.'booking_history_meta';
		$query      = "SELECT * FROM $table_name WHERE booking_id='".$booking->ID."' and meta_key='".$meta_key."'";
		$results    = $wpdb->get_results( $query );

		// Allow code execution only once 
	    if( count($results) == 0 ) {

	    	$to      = $tutor->user_email;
			$subject = 'You have updated the delivery status of your class at '.get_option( 'blogname' );
			$message = WCCB_Email_Content::get_class_status_content( 'tutor' , $booking , $student , $tutor );
			$headers = WCCB_Notification::get_email_headers();
			 
			wp_mail( $to, $subject, $message, $headers );

	    	$data = array(
				'booking_id'   => $booking->ID,
				'meta_key'     => $meta_key,
				'meta_value'   => 'yes'
			);

			$wpdb->insert($table_name , $data);
	    }
	}

	public function send_class_status_email_admin( $booking , $student , $tutor ) {
		global $wpdb;

		$meta_key   = strtolower(str_replace(' ', '_', $booking->delivery->status )).'_notification_email_admin';
		$table_name = $wpdb->prefix.'booking_history_meta';
		$query      = "SELECT * FROM $table_name WHERE booking_id='".$booking->ID."' and meta_key='".$meta_key."'";
		$results    = $wpdb->get_results( $query );

		// Allow code execution only once 
	    if( count($results) == 0 ) {

	    	$to      = get_option('admin_email');
			$subject = 'Tutor has updated the delivery status of one class at '.get_option( 'blogname' );
			$message = WCCB_Email_Content::get_class_status_content( 'admin' , $booking , $student , $tutor );
			$headers = WCCB_Notification::get_email_headers();
			 
			wp_mail( $to, $subject, $message, $headers );

	    	$data = array(
				'booking_id'   => $booking->ID,
				'meta_key'     => $meta_key,
				'meta_value'   => 'yes'
			);

			$wpdb->insert($table_name , $data);
	    }
	}


	public function send_class_completion_email_student( $booking , $student , $tutor ) {

		global $wpdb;

		$table_name = $wpdb->prefix.'booking_history_meta';
		$query      = "SELECT * FROM $table_name WHERE booking_id='".$booking->ID."' and meta_key='_completion_notification_student_done'";
		$results    = $wpdb->get_results( $query );

		// Allow code execution only once 
	    if( count($results) == 0 ) {

			//Send student email
			$to      = $student->user_email;
			$subject = 'Our system indicates that you have successfully completed a lesson , please contact your tutor if this has not happend';
			$message = WCCB_Email_Content::get_class_completion_content( 'student' , $booking , $student , $tutor );
			$headers = WCCB_Notification::get_email_headers();
			 
			wp_mail( $to, $subject, $message, $headers );

			$data = array(
				'booking_id'   => $booking->ID,
				'meta_key'     => '_completion_notification_student_done',
				'meta_value'   => 'yes'
			);

			$wpdb->insert($table_name , $data);
		}
	}

	public function send_class_completion_email_tutor( $booking , $student , $tutor ) {

		global $wpdb;
		
		$table_name = $wpdb->prefix.'booking_history_meta';
		$query      = "SELECT * FROM $table_name WHERE booking_id='".$booking->ID."' and meta_key='_completion_notification_tutor_done'";
		$results    = $wpdb->get_results( $query );

		// Allow code execution only once 
	    if( count($results) == 0 ) {

			//Send tutor email
			$to      = $tutor->user_email;
			$subject = 'You have successfully completed a lesson at '.get_option( 'blogname' );
			$message = WCCB_Email_Content::get_class_completion_content( 'tutor' , $booking , $student , $tutor );
			$headers = WCCB_Notification::get_email_headers();
			 
			wp_mail( $to, $subject, $message, $headers );

			$data = array(
				'booking_id'   => $booking->ID,
				'meta_key'     => '_completion_notification_tutor_done',
				'meta_value'   => 'yes'
			);

			$wpdb->insert($table_name , $data);
		}
	}

	public function send_class_completion_email_admin( $booking , $student , $tutor ) {

		global $wpdb;
		
		$table_name = $wpdb->prefix.'booking_history_meta';
		$query      = "SELECT * FROM $table_name WHERE booking_id='".$booking->ID."' and meta_key='_completion_notification_admin_done'";
		$results    = $wpdb->get_results( $query );

		// Allow code execution only once 
	    if( count($results) == 0 ) {

			$student = get_userdata($booking->user_id);
			$tutor   = get_userdata($booking->tutor_id);

			$args   = array(
				'role__in' => array('administrator')
			);
			$admin_user = get_users( $args );

			foreach ($admin_user as $admin) {
				$to      = $admin->user_email;
				$subject = 'One Student have successfully completed a lesson at '.get_option( 'blogname' );
				$message = WCCB_Email_Content::get_class_completion_content( 'admin' , $booking , $student , $tutor );
				$headers = WCCB_Notification::get_email_headers();
				 
				wp_mail( $to, $subject, $message, $headers );
			}

			$data = array(
				'booking_id'   => $booking->ID,
				'meta_key'     => '_completion_notification_admin_done',
				'meta_value'   => 'yes'
			);

			$wpdb->insert($table_name , $data);
		}
	}

	public function send_deduct_hour_email_student( $hour_obj , $hour ) {

		//Send student email
		$student = get_userdata($hour_obj->user_id);
		$to      = $student->user_email;
		$subject = 'Your purchased hours has been deducted by admin at '.get_option( 'blogname' );
		$message = WCCB_Email_Content::get_hour_deducted_content( $hour_obj , $student , $hour );
		$headers = WCCB_Notification::get_email_headers();
		 
		wp_mail( $to, $subject, $message, $headers );
	}

}