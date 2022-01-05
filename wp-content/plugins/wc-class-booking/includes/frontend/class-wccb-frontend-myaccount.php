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
		add_action ( 'template_redirect' , array( $this , 'action_handler' ) );
		//add_action ( 'woocommerce_before_account_navigation' , array( 'WCCB_Frontend_Myaccount_View' , 'show_welcome_text' ) );
		add_action ( 'woocommerce_account_dashboard' , array( 'WCCB_Frontend_Myaccount_View' , 'show_dashboard_content' ) );
		add_action ( 'woocommerce_edit_account_form' , array( 'WCCB_Frontend_Myaccount_View' , 'show_edit_profile_content' ) );
		add_action ( 'woocommerce_save_account_details', array( $this , 'save_edit_profile') , 10, 1 );
		add_action ( 'woocommerce_edit_account_form_tag', function(){ echo 'enctype="multipart/form-data"';} );

		add_action( 'woocommerce_save_account_details_errors', array( $this , 'validate_my_account_custom_field') , 10, 1 );

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

		$end_points = array(
			'availability' => 'Availability Settings',
			'bookings'     => 'My Bookings',
			'classes'      => 'My Classes',
			'hours'        => 'My Hours',
			'add_hour'     => 'Add Student Hour',
			'deduct_hour'  => 'Deduct Student Hour',
		);

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
			'add_hour'     => 'Add Student Hour',
			'deduct_hour'  => 'Deduct Student Hour',
		);
		
		if( $all ) {
			return $end_points;
		}
		
		$user_meta          = get_user_meta(get_current_user_id() , 'wp_capabilities' , true );
		if (!empty($user_meta)) {
			if (array_key_exists('wccb_tutor' , $user_meta)) {
				$myaccount_menu =  $tutor_menu;
			}
			else if (array_key_exists('wccb_student' , $user_meta)) {
				$myaccount_menu =  $student_menu;
			}
			else {
				$myaccount_menu = $admin_menu;
			}
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
			//unset($links['customer-logout']);
			$links = array_slice( $links, 0, 1, true ) + $this->get_myaccount_menu() + array_slice( $links, 1, NULL, true );
		}
		else {
			$links = array_slice( $links, 0, 2, true ) + $this->get_myaccount_menu() + array_slice( $links, 2, NULL, true );
		}

		return $links;
	}

	public function save_tutor_availability() {

		if (isset( $_POST['save-tutor-availability-nonce'] ) && wp_verify_nonce( $_POST['save-tutor-availability-nonce'], 'save_tutor_availability' ) ) {

			$customer_id        = get_current_user_id();
			$validation_flag    = 1;
			$field_array        = $_POST;
			$availability_times = array();

			foreach (WCCB_Helper::get_weekdays_array() as $key => $value) {
				$lower_key = strtolower($key);
				$availability_times[$lower_key]['is_unavailable'] =  $field_array[$lower_key.'_is_unavailable'];

				if (!empty($field_array[$lower_key.'_start_time'])) {
					$temp_start_time = $field_array[$lower_key.'_start_time'];
					$temp_end_time   = $field_array[$lower_key.'_end_time'];

					foreach ($field_array[$lower_key.'_start_time'] as $key2 => $value2 ) {
						if ($temp_start_time[$key2] < $temp_end_time[$key2] ) {

							$time_flag = 1;
							foreach ( $availability_times[$lower_key]['available_time'] as $key3 => $value3) {
								if ($temp_start_time[$key2] == $value3['start_time'] && $temp_end_time[$key2] == $value3['end_time']) {
									$time_flag = 0;
									$validation_flag = 0;
									wc_add_notice( __('Start time and end time is same for '.$lower_key , WC_CLASS_BOOKING_TEXT_DOMAIN) , 'error' );
								}
								
								if ($temp_start_time[$key2] >= $value3['end_time']) {
									$time_flag = 1;
								}
								else {
									$time_flag = 0;
									$validation_flag = 0;
									wc_add_notice( __('Start time and end time ordering is not properly set for '.$lower_key , WC_CLASS_BOOKING_TEXT_DOMAIN) , 'error' );
								}
							}

							if ($time_flag) {
								$availability_times[$lower_key]['available_time'][] = array(
									'start_time' => $temp_start_time[$key2], 
									'end_time'   => $temp_end_time[$key2]
								);
							}
						}
						else {
							wc_add_notice( __('Start time is greater than end time for '.$lower_key , WC_CLASS_BOOKING_TEXT_DOMAIN) , 'error' );
							$validation_flag = 0;
						}
					}
				}
			}

			if ($validation_flag) {
				$availability_times = WCCB_Frontend::get_avilability_times_from_post($_POST);
				update_user_meta( $customer_id , 'availability' , $availability_times );

				wc_add_notice( __( 'Availability saved successfully', WC_CLASS_BOOKING_TEXT_DOMAIN ), 'success' );
			}
		}
	}

	public static function cancel_booking_class( $booking_id ) {
		if (empty($booking_id)) {
			return;
		}

		global $wpdb;
		$table_booking   = $wpdb->prefix.'booking_history';
		$query           = "SELECT * FROM $table_booking WHERE ID='".$booking_id."'";
		$results         = $wpdb->get_results( $query, ARRAY_A ); // db call ok. no cache ok.
		if (count($results)>0) {

			$class_time_exp  = explode('-' , trim($results[0]['class_time']));
			$class_date_time = $results[0]['class_date'].' '.$class_time_exp[0];
			$validation_flag  = WCCB_Frontend_Myaccount::validate_class_for_reschedule_and_cancel('cancel' , $class_date_time);

			if ($validation_flag) {

				//Calculate hour from slot
				$slots_time       = array(
					array(
						'start_time' => $class_time_exp[0],
						'end_time'   => $class_time_exp[1]
					)
				);
				$slot_hour        = WCCB_Helper::get_total_hours_from_slots( $slots_time );

				//Update hour for cancel booking
				$hour_table  = $wpdb->prefix.'hour_history';
				$query2      = "SELECT * FROM $hour_table WHERE ID='".$results[0]['hour_id']."'";
				$results2    = $wpdb->get_results( $query2, ARRAY_A ); // db call ok. no cache ok.
				$used_hours  = $results2[0]['used_hours']-(float)$slot_hour;

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

				//Insert booking meta
				$meta_table_name = $wpdb->prefix.'booking_history_meta';
				$booking_meta    = array(
					'cancelled_by'   => get_current_user_id(),
					'cancelled_date' => wp_date('Y-m-d H:i:s'),
				);

				foreach ($booking_meta as $key => $value) {
					$data = array(
						'booking_id'   => $results[0]['ID'],
						'meta_key'     => $key,
						'meta_value'   => maybe_serialize($value)
					);
					$wpdb->insert($meta_table_name , $data);
				}

				do_action('cancelled_class_notification' , $booking_id );
				
				return true;
			}
			else {
				wc_add_notice( __('Your class is not eligible to cancel now. You have to cancel the class before '.WC_CLASS_BOOKING_CANCEL_CLASS_BEFORE_HOURS.' hours remaining.' , WC_CLASS_BOOKING_TEXT_DOMAIN ) , 'error' );
				return false;
			}
		}
		else {
			wc_add_notice( __( 'The booking ID not exist for cancellation' , WC_CLASS_BOOKING_TEXT_DOMAIN ) , 'error' );
			return false;
		}
	}

	public static function get_student_total_available_hours( $student_id , $hour_id = '' ) {
		if (empty($student_id)) {
			return;
		}
		global $wpdb;
		$table_name = $wpdb->prefix.'hour_history';
		$total_available_hours = 0;
		if (empty($hour_id)) {
			$query   = "SELECT * FROM $table_name WHERE user_id='".$student_id."'";
		}
		else {
			$query   = "SELECT * FROM $table_name WHERE ID='".$hour_id."'";
		}
		
		$results = $wpdb->get_results( $query, ARRAY_A ); // db call ok. no cache ok.

		foreach ($results as $key => $value) {
			$days = WCCB_Helper::get_date_difference( $value['date_purchased'] , date('Y-m-d') );
			if ($days < WC_CLASS_BOOKING_HOUR_EXPIRE_DAYS ) {
				$total_available_hours += $value['purchased_hours'] - ((float)$value['used_hours'] + (float)$value['deducted_hours']);
			}
		}
		
		return (float)$total_available_hours;
	}

	public static function get_student_date_wise_available_hours( $student_id , $product_id = '' ) {
		if (empty($student_id)) {
			return;
		}
		global $wpdb;
		$table_name = $wpdb->prefix.'hour_history';
		$available_hours = array();

		if (empty($product_id)) {
			$query   = "SELECT * FROM $table_name WHERE user_id='".$student_id."' and product_id='".$product_id."' order by ID asc";
		}
		else {
			$query   = "SELECT * FROM $table_name WHERE user_id='".$student_id."' order by ID asc";
		}
		
		$results = $wpdb->get_results( $query, ARRAY_A ); // db call ok. no cache ok.

		foreach ($results as $key => $value) {
			$days           = WCCB_Helper::get_date_difference( $value['date_purchased'] , wp_date('Y-m-d') );
			$remaining_hour = $value['purchased_hours'] - $value['used_hours'];
			if ($days < WC_CLASS_BOOKING_HOUR_EXPIRE_DAYS && $remaining_hour > 0 ) {
				$temp_key = WCCB_Helper::get_particular_date($value['date_purchased'] , WC_CLASS_BOOKING_HOUR_EXPIRE_DAYS);
				$available_hours[$temp_key] = array(
					'hour_id'        => $value['ID'],
					'remaining_hour' => $remaining_hour
				);
			}
		}
		
		return $available_hours;
	}

	public static function save_booking_class( $field_array ) {
		global $wpdb;
		$passed = true;
		$slots                     = WCCB_Helper::get_unique_array($field_array['slot']);
		$hour_expire_date          = $field_array['hour_expire_date'];
		$date_wise_slot            = WCCB_Frontend::get_date_wise_slots( $slots );
		$total_available_hours     = WCCB_Frontend_Myaccount::get_student_total_available_hours($field_array['user_id'] , $field_array['hour_id']);
		$date_wise_available_hours = WCCB_Frontend_Myaccount::get_student_date_wise_available_hours($field_array['user_id'] , $field_array['product_id'] );

		//Calculate total hours from boking slots
		$slots_time = array();
		foreach ($date_wise_slot as $key => $value) {
			foreach ($value as $key2 => $value2) {
				$slot_time  = explode('-', $value2);
				$temp_time = array(
					'start_time' => $slot_time[0],
					'end_time'   => $slot_time[1]
				);
				array_push($slots_time, $temp_time);
			}
		}
		$total_requested_hour = WCCB_Helper::get_total_hours_from_slots( $slots_time );

		//Check if available hour is greater than booking hours
		if ($passed) {
			if ($total_requested_hour > $total_available_hours ) {
				$passed = false;
				wc_add_notice( __('You have added more slots than you have total available hours. Total available hours : '.$total_available_hours , WC_CLASS_BOOKING_TEXT_DOMAIN ) , 'error');
			}
		}

		//Check if tutor is availavle for booking hours
		if (!empty($date_wise_slot) && $passed == true ) {
			foreach ($date_wise_slot as $key => $value) {
				foreach ($value as $key2 => $value2) {
					//Check if tutor is booked for slot by other student
					$passed = WCCB_Frontend::date_wise_slot_availability_validation( $field_array['tutor_id'] , $key , $value2 );
					if (!$passed) {
						wc_add_notice( __( 'The slot '.wp_date('D M j, Y',strtotime($key)).', '.$value2.' already booked. Try other slots.' , WC_CLASS_BOOKING_TEXT_DOMAIN ) , 'error');
						//unset($_REQUEST['post']);
					}
				}
			}
		}

		if ($passed) {
			$used_flag = 0;
			foreach ($slots  as $key => $value) {
				$used_flag      = 0;
				$slot_date_time = explode( '|' , $value);
				$days = WCCB_Helper::get_date_difference( $slot_date_time[0] , $hour_expire_date ); //Slot date , Hour expire date
				//echo $slot_date_time[0].'hi'.$hour_expire_date;
				//echo 'hello'.$days;
				$used_flag = $days > 0 ? 1 : 0;

				if (!$used_flag) {
					$passed = false;
					wc_add_notice( __('You don\'t have available hours to book slot for the date : '.WCCB_Helper::display_date($slot_date_time[0]).', '.$slot_date_time[1], WC_CLASS_BOOKING_TEXT_DOMAIN ) , 'error');
				}
			}
		}

		if ($passed) {
			//Insert slots into booking history
			if (!empty($date_wise_slot)) {
				
				foreach ($date_wise_slot as $key => $value) {

					//Get database hour
					$hour_id     = $field_array['hour_id'];
					$hour_table  = $wpdb->prefix.'hour_history';
					$query2      = "SELECT * FROM $hour_table WHERE ID='".$hour_id."'";
					$results2    = $wpdb->get_results( $query2 ); // db call ok. no cache ok.
					$used_hours  = $results2[0]->used_hours+$total_requested_hour;
					///////////////////////

					foreach ($value as $key2 => $value2) {
						$product        = wc_get_product($field_array['product_id']);
						$table_name     = $wpdb->prefix.'booking_history';
						$data = array(
							'user_id'      => $field_array['user_id'],
							'product_id'   => $product->get_id(),
							'amount'       => $product->get_regular_price(),
							'tutor_id'     => $field_array['tutor_id'],
							'hour_id'      => $hour_id,
							'class_date'   => $key,
							'class_time'   => $value2,
							'status'       => 'Upcoming',
							'booking_date' => wp_date('Y-m-d H:i:s')
						);
						
						if ($wpdb->insert($table_name , $data)) {
							do_action('class_booking_notification' , $wpdb->insert_id);
						}
						else {
							$passed = false;
							wc_add_notice( __('Databse:error during class booking', WC_CLASS_BOOKING_TEXT_DOMAIN ) , 'error');
						}
					}

					//Decrease hours after booking
					$wpdb->update(
					    $hour_table,
					    array( 
					        'used_hours' => $used_hours
					    ),
					    array(
					        'ID'         => $hour_id
					    )
					);
					
				}
			}
			//End booking history
		}

		return $passed;
	}

	public static function validate_class_for_reschedule_and_cancel( $type , $class_date_time) {
		$validation_flag = false;

		if (!empty($class_date_time)) {

			$hour_from       = ($type == 'reschedule') ? WC_CLASS_BOOKING_RESCHEDULE_CLASS_BEFORE_HOURS : WC_CLASS_BOOKING_CANCEL_CLASS_BEFORE_HOURS;
			$user            = get_userdata( get_current_user_id() );
			$role_key        = $user->roles[0];
			$datetime1       = new DateTime(date('Y-m-d h:i a'));
			$datetime2       = new DateTime($class_date_time);
			$interval        = $datetime1->diff($datetime2);
			$days            = $interval->d;
			$hour            = $interval->h;

			//echo 'Days :'.$days.'<br>Hour:'.$hour.'<br> Hour from:'.$hour_from;

			if ( $days > 0 ) {
				$validation_flag = true;
			}
			else if ( $days == 0 ) {
				if ($hour > $hour_from) {
					$validation_flag = true;
				}
			}

			if ($role_key == 'administrator') {
				$validation_flag = true;
			}
		}

		return $validation_flag;
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

			

			$class_time_exp  = explode('-' , $results[0]['class_time']);
			$class_date_time = $results[0]['class_date'].' '.$class_time_exp[0];
			$validation_flag = WCCB_Frontend_Myaccount::validate_class_for_reschedule_and_cancel('reschedule', $class_date_time);

			if ($validation_flag) {

				$slot_date_time = explode('|', $slot[0]);

				$wpdb->update(
					$table_name,

					array(
						'class_date'=>$slot_date_time[0], 
						'class_time' => $slot_date_time[1]
					),

					array('ID' => $booking_id )
				);

				//Insert booking meta
				$meta_table_name = $wpdb->prefix.'booking_history_meta';
				$booking_meta    = array(
					'reschedule_by'   => get_current_user_id(),
					'reschedule_date' => wp_date('Y-m-d H:i:s'),
				);

				foreach ($booking_meta as $key => $value) {
					$query2   = "SELECT * FROM $meta_table_name WHERE booking_id='".$booking_id."' and meta_key='".$key."'";
					$results2 = $wpdb->get_results( $query2 ); // db call ok. no cache ok.
					if (count($results2)) {
						$meta_value     = maybe_unserialize($results2[0]->meta_value);
						if (is_array($meta_value)) {
							array_push($meta_value, $value );
						}
						else {
							$meta_value = array($meta_value , $value );
						}
						

						$wpdb->update(
							$meta_table_name,

							array(
								'meta_key'   => $results2[0]->meta_key, 
								'meta_value' => maybe_serialize($meta_value)
							),

							array('ID' => $results2[0]->ID)
						);
					}
					else {
						$data = array(
							'booking_id'   => $booking_id,
							'meta_key'     => $key,
							'meta_value'   => $value
						);

						$wpdb->insert($meta_table_name , $data);
					}
				}

				do_action('reschedule_class_notification' , $booking_id );
				
				return true;
			}
			else {
				wc_add_notice( __('Your class is not eligible to reschedule now. You have to reschedule class before '.WC_CLASS_BOOKING_RESCHEDULE_CLASS_BEFORE_HOURS.' hours remaining.' , WC_CLASS_BOOKING_TEXT_DOMAIN ) , 'error' );
				return false;
			}
		}
		else {
			wc_add_notice( __( 'The booking ID not exist for reschedule' , WC_CLASS_BOOKING_TEXT_DOMAIN ) , 'error' );
			return false;
		}

	} 

	public static function save_class_notes($field_array) {
		global $wpdb;
		$meta_table_name = $wpdb->prefix.'booking_history_meta';
		$query   = "SELECT * FROM $meta_table_name WHERE booking_id='".$field_array['booking_id']."' and meta_key='notes'";
		$results = $wpdb->get_results( $query ); // db call ok. no cache ok.
		if (count($results)) {			

			$wpdb->update(
				$meta_table_name,

				array(
					'meta_key'   => 'notes', 
					'meta_value' => $field_array['notes']
				),

				array('ID' => $results[0]->ID)
			);
		}
		else {
			$data = array(
				'booking_id'   => $field_array['booking_id'],
				'meta_key'     => 'notes',
				'meta_value'   => $field_array['notes']
			);

			$wpdb->insert($meta_table_name , $data);
		}

		return true;
	}

	public function action_handler() {
		if (!empty($_REQUEST['action_do'])) {
			switch ($_REQUEST['action_do']) {
				case 'save_booking':
					$error_flag = 0;
					if ( !isset( $_POST['save_booking_nonce_field'] ) && !wp_verify_nonce( $_POST['save_booking_nonce_field'], 'save_booking' ) 
						){
						$error_flag = 1;
						wc_add_notice( __( 'You are not authorize to book the class' , WC_CLASS_BOOKING_TEXT_DOMAIN ) , 'error' );
					}
					if (empty($_REQUEST['product_id'])) {
						$error_flag = 1;
						wc_add_notice( __( 'Please select class for booking slot' , WC_CLASS_BOOKING_TEXT_DOMAIN ) , 'error' );
					}
					if (empty($_REQUEST['tutor_id'])) {
						$error_flag = 1;
						wc_add_notice( __( 'Please select tutor for booking slot' , WC_CLASS_BOOKING_TEXT_DOMAIN ) , 'error' );
					}
					if (count($_REQUEST['slot']) == 0) {
						$error_flag = 1;
						wc_add_notice( __( 'Please select available slot to book class' , WC_CLASS_BOOKING_TEXT_DOMAIN ) , 'error' );
					}

					if (!$error_flag) {
						if(WCCB_Frontend_Myaccount::save_booking_class($_REQUEST)) {
							wc_add_notice( __( 'The class has been booked successfully.' , WC_CLASS_BOOKING_TEXT_DOMAIN ) , 'success' );
							//unset($_REQUEST['slot']);

							$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

							wp_redirect( $url );
							exit;
						}
					}
					
				break;

				case 'save_notes':
					$error_flag = 0;
					if ( !isset( $_POST['save_notes_nonce_field'] ) && !wp_verify_nonce( $_POST['save_notes_nonce_field'], 'save_notes' ) 
						){
						$error_flag = 1;
						wc_add_notice( __( 'You are not authorize to the notes' , WC_CLASS_BOOKING_TEXT_DOMAIN ) , 'error' );
					}
					if (empty($_REQUEST['notes'])) {
						$error_flag = 1;
						wc_add_notice( __( 'Please enter notes' , WC_CLASS_BOOKING_TEXT_DOMAIN ) , 'error' );
					}

					if (!$error_flag) {
						if(WCCB_Frontend_Myaccount::save_class_notes($_POST)) {
							wc_add_notice( __( 'The notes has been saved successfully.' , WC_CLASS_BOOKING_TEXT_DOMAIN ) , 'success' );
						}
					}
					
				break;

				case 'reschedule':
					if (!empty($_REQUEST['booking_id'])) {
						if ( isset( $_POST['save_reschedule_nonce_field'] ) && wp_verify_nonce( $_POST['save_reschedule_nonce_field'], 'save_reschedule' ) 
						) {
							$slots = WCCB_Helper::get_unique_array($_POST['slot']);
							if(WCCB_Frontend_Myaccount::reschedule_booking_class($_REQUEST['booking_id'] , $slots )) {
								wc_add_notice( __( 'The class has been reschedule successfully.' , WC_CLASS_BOOKING_TEXT_DOMAIN ) , 'success' );
							}
						}
						else {
						   wc_add_notice( __( 'You are not authorize to reschedule the class' , WC_CLASS_BOOKING_TEXT_DOMAIN ) , 'error' );
						}
					}
				break;

				case 'cancel_class':
					if (!empty($_REQUEST['booking_id'])) {
						if(WCCB_Frontend_Myaccount::cancel_booking_class($_REQUEST['booking_id'])) {
							wc_add_notice( __( 'The booking has been cancelled and hour has been credit back to your account' , WC_CLASS_BOOKING_TEXT_DOMAIN ) , 'success' );
						}
					}
				break;
			}
		}
	}

	public function validate_my_account_custom_field( $args ) {
		if (empty($_POST['mobile_no'])) {
			$args->add( 'error', __( 'Mobile no. can\'t be empty', WC_CLASS_BOOKING_TEXT_DOMAIN ),'');
		}

	    if ( !empty( $_POST['mobile_no'] ) ) // Your custom field
	    {
	        if(strlen($_POST['mobile_no']) > 14 ) // condition to be adapted
	        $args->add( 'error', __( 'Mobile no. should not be more than 14 digit', WC_CLASS_BOOKING_TEXT_DOMAIN ),'');
	    }

	    if ( !empty( $_FILES['profile_image']['name'] ) ) {
			// Allowed image types
			$allowed_image_types = array('image/jpeg','image/png');
			// Check conditions
	    	if(!in_array($_FILES['profile_image']['type'], $allowed_image_types) ) {
	    		$args->add( 'error', __( 'The image type you are trying to upload is not allowed', WC_CLASS_BOOKING_TEXT_DOMAIN ),'');
	    	}
	    }
	}

	public function save_edit_profile( $user_id ) {

		if (!empty($_POST['mobile_no'])) {
			update_user_meta( $user_id , 'mobile_no' , $_POST['mobile_no']);
		}

		
		if ( !empty( $_FILES['profile_image']['name'] ) ) {

			$attachment_id = media_handle_upload( 'profile_image', 0 );
	        if ( is_wp_error( $attachment_id ) ) {
	            wc_add_notice( $attachment_id->get_error_message() , 'error' );
	        } 
	        else {
	            update_user_meta( $user_id, 'profile_image', $attachment_id );
	        }
	   	}
	}
}