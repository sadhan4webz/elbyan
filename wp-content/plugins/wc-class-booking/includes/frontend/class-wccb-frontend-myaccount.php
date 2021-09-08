<?php
defined( 'ABSPATH' ) || die();
class WCCB_Frontend_Myaccount {

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
		add_action ( 'init', array( $this , 'add_myaccount_endpoint') );
		add_action ( 'template_redirect' , array( $this , 'save_tutor_availability' ) );
		add_action ( 'init' , array( $this , 'action_handler' ) );

		//Filters
		add_filter ( 'woocommerce_account_menu_items', array( $this , 'customize_my_account_menu' ) );
		
	}

	public function add_myaccount_endpoint() {
		if (is_array($this->get_myaccount_menu( true ))) {
			foreach ($this->get_myaccount_menu( true ) as $key => $value) {
				add_rewrite_endpoint( $key , EP_PAGES );
				add_action( 'woocommerce_account_'.$key.'_endpoint', array( 'WCCB_Frontend_Myaccount_View' , 'render_my_account_'.$key.'_content') );
			}
		}
	}

	public function get_myaccount_menu( $all = false ) {
		$tutor_menu   = array(
			'availability' => 'Availability Settings',
			'bookings'     => 'My Bookings',
		);
		$student_menu = array(
			'classes'      => 'My Classes',
			'hours'        => 'My Hours'
		);

		$admin_menu = array(
			'classes'      => 'Student Classes',
			'hours'        => 'Student Hours',
			'bookings'     => 'Tutor Bookings',
		);
		
		if( $all ){
			return array_merge( $tutor_menu, $student_menu );
		}
		
		$user_meta          = get_user_meta(get_current_user_id() , 'wp_capabilities' , true );
		if (!empty($user_meta)) {
			$myaccount_menu = array_key_exists('wccb_tutor' , $user_meta) ? $tutor_menu : array_key_exists('wccb_student' , $user_meta) ? $student_menu : $admin_menu;
		}

		return $myaccount_menu;
	}

	public function customize_my_account_menu( $links ) {
		unset($links['downloads']);
		unset($links['edit-address']);
		if (array_key_exists('wccb_tutor' , get_user_meta(get_current_user_id() , 'wp_capabilities' , true ) ) ) {
			unset($links['orders']);
			$links = array_slice( $links, 0, 1, true ) + $this->get_myaccount_menu() + array_slice( $links, 1, NULL, true );
		}
		else if(array_key_exists('administrator' , get_user_meta(get_current_user_id() , 'wp_capabilities' , true ) )) {
			unset($links['orders']);
			unset($links['edit-account']);
			unset($links['customer-logout']);
			$links = array_slice( $links, 0, 1, true ) + $this->get_myaccount_menu() + array_slice( $links, 1, NULL, true );
		}
		else {
			$links = array_slice( $links, 0, 2, true ) + $this->get_myaccount_menu() + array_slice( $links, 2, NULL, true );
		}

		return $links;
	}

	public function save_tutor_availability() {

		if (isset( $_POST['save-tutor-availability-nonce'] ) && wp_verify_nonce( $_POST['save-tutor-availability-nonce'], 'save_tutor_availability' ) ) {

			$customer_id     = get_current_user_id();
			$validation_flag = 1;
			$week_day        = WCCB_Helper::get_weekdays_array();

			foreach ($week_day as $key => $value) {
				$lower_key = strtolower($key);

				if (empty($_POST[$lower_key.'_is_unavailable'])) {

					if (empty($_POST[$lower_key.'_start_time'])) {
						wc_add_notice( __( $key.' start time is required field' , PLUGIN_TEXT_DOMAIN ) , 'error' );
						$validation_flag = 0;
					}

					if (empty($_POST[$lower_key.'_end_time'])) {
						wc_add_notice( __( $key.' end time is required field' , PLUGIN_TEXT_DOMAIN ) , 'error' );
						$validation_flag = 0;
					}
				}
			}

			if ($validation_flag) {
				$availability = array();
				foreach (WCCB_Helper::get_weekdays_array() as $key => $value) {
					$lower_key = strtolower($key);
					$temp_day  = array(
						'is_unavailable' => $_POST[$lower_key.'_is_unavailable'],
						'start_time'     => $_POST[$lower_key.'_start_time'],
						'end_time'       => $_POST[$lower_key.'_end_time']
					);

					$availability[$lower_key] = $temp_day;
				}
				update_user_meta( $customer_id , 'availability' , $availability );

				wc_add_notice( __( 'Availability saved successfully', PLUGIN_TEXT_DOMAIN ), 'success' );
			}
		}
	}

	public static function cancel_booking_class( $booking_id ) {
		if (empty($booking_id)) {
			return;
		}

		global $wpdb;
		$table_booking = $wpdb->prefix.'booking_history';
		$query           = "SELECT * FROM $table_booking WHERE ID='".$booking_id."'";
		$results         = $wpdb->get_results( $query, ARRAY_A ); // db call ok. no cache ok.
		if (count($results)>0) {
			//Update hour for cancel booking
			$hour_table  = $wpdb->prefix.'hour_history';
			$query2      = "SELECT * FROM $hour_table WHERE ID='".$results[0]['hour_id']."'";
			$results2    = $wpdb->get_results( $query2, ARRAY_A ); // db call ok. no cache ok.
			$used_hours  = $results2[0]['used_hours']-1;

			$wpdb->update(
			    $hour_table,
			    array( 
			        'used_hours' => $used_hours
			    ),
			    array(
			        'ID'         => $results2[0]['ID']
			    )
			);

			$wpdb->update(
				$table_booking,

				array(
					'status' => 'Cancelled'
				),

				array('ID'   => $results[0]['ID'])
			);
			
			return true;
		}
		else {
			return false;
		}
	}

	public static function reschedule_booking_class( $booking_id , $slot ) {
		if (empty($booking_id) || empty($slot)) {
			return;
		}

		global $wpdb;
		$table_name = $wpdb->prefix.'booking_history';
		$query           = "SELECT * FROM $table_name WHERE ID='".$booking_id."'";
		$results         = $wpdb->get_results( $query, ARRAY_A ); // db call ok. no cache ok.
		if (count($results)>0) {
			$slot_date_time = explode('|', $slot[0]);

			$wpdb->update(
				$table_name,

				array(
					'class_date'=>$slot_date_time[0], 
					'class_time' => $slot_date_time[1]
				),

				array('ID'=>$booking_id)
			);
			
			return true;
		}
		else {
			return false;
		}

	} 

	public function action_handler() {
		if (!empty($_REQUEST['action_do'])) {
			switch ($_REQUEST['action_do']) {
				case 'reschedule':
					if (!empty($_REQUEST['booking_id'])) {
						if ( isset( $_POST['save_reschedule_nonce_field'] ) && wp_verify_nonce( $_POST['save_reschedule_nonce_field'], 'save_reschedule' ) 
						) {
							if(WCCB_Frontend_Myaccount::reschedule_booking_class($_REQUEST['booking_id'] , $_POST['slot'])) {
								wc_add_notice( __( 'The class has been reschedule successfully.' , PLUGIN_TEXT_DOMAIN ) , 'success' );
							}
							else {
								wc_add_notice( __( 'The booking ID not exist for reschedule' , PLUGIN_TEXT_DOMAIN ) , 'error' );
							}
						}
						else {
						   wc_add_notice( __( 'You are not authorize to reschedule the class' , PLUGIN_TEXT_DOMAIN ) , 'error' );
						}
					}
				break;

				case 'cancel_class':
					if (!empty($_REQUEST['booking_id'])) {
						if(WCCB_Frontend_Myaccount::cancel_booking_class($_REQUEST['booking_id'])) {
							wc_add_notice( __( 'The booking has been cancelled and hour has been credit back to your account' , PLUGIN_TEXT_DOMAIN ) , 'success' );
						}
						else {
							wc_add_notice( __( 'The booking ID not exist for cancellation' , PLUGIN_TEXT_DOMAIN ) , 'error' );
						}
					}
				break;
			}
		}
	}

}